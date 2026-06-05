<?php

namespace App\Services\AI\Response;

use App\Exceptions\AIInvalidJsonResponseException;
use Illuminate\Support\Facades\Log;

class AIJsonResponseNormalizerService
{
    /**
     * Main entry point: receive raw Gemini response and return clean PHP array.
     */
    public function normalizeToArray(
        string $raw,
        array  $requiredKeys = [],
        ?string $schemaName = null
    ): array {
        $preview = $this->sanitizeForLog($raw);

        try {
            $cleaned = $this->stripCodeFences($raw);
            $cleaned = $this->extractJsonCandidate($cleaned);
            $cleaned = $this->removeInvalidControlCharacters($cleaned);
            $cleaned = $this->fixCommonEncodingIssues($cleaned);

            $data = $this->decodeJson($cleaned);

            if (!empty($requiredKeys)) {
                $this->validateRequiredKeys($data, $requiredKeys);
            }

            return $data;

        } catch (AIInvalidJsonResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AIInvalidJsonResponseException(
                "Resposta JSON inválida do Gemini após normalização" .
                ($schemaName ? " (schema: {$schemaName})" : '') .
                ": " . $e->getMessage(),
                $preview,
                $schemaName ?? '',
                0,
                $e
            );
        }
    }

    /**
     * Remove markdown code fences: ```json ... ``` or ``` ... ```
     */
    public function stripCodeFences(string $raw): string
    {
        // Remove ```json ... ``` or ``` ... ```
        $cleaned = preg_replace('/^```(?:json)?\s*\n?/im', '', $raw);
        $cleaned = preg_replace('/\n?```\s*$/im', '', $cleaned ?? $raw);

        // Remove single backtick wrappers
        $cleaned = trim($cleaned ?? $raw);

        return $cleaned ?: $raw;
    }

    /**
     * Extract the first JSON object or array from the string.
     */
    public function extractJsonCandidate(string $raw): string
    {
        $raw = trim($raw);

        // Already starts with { or [
        if (str_starts_with($raw, '{') || str_starts_with($raw, '[')) {
            return $raw;
        }

        // Find first { or [
        $objPos = strpos($raw, '{');
        $arrPos = strpos($raw, '[');

        $start = match (true) {
            $objPos === false && $arrPos === false => -1,
            $objPos === false                     => $arrPos,
            $arrPos === false                     => $objPos,
            default                               => min($objPos, $arrPos),
        };

        if ($start === -1) {
            return $raw; // no JSON found, let decode fail with clear error
        }

        // Find last } or ] from the end
        $endObj = strrpos($raw, '}');
        $endArr = strrpos($raw, ']');
        $end    = max($endObj === false ? -1 : $endObj, $endArr === false ? -1 : $endArr);

        if ($end === -1 || $end < $start) {
            return substr($raw, $start);
        }

        return substr($raw, $start, $end - $start + 1);
    }

    /**
     * Remove invalid control characters that break JSON parsing.
     * Preserves valid escapes like \n \t \r within JSON strings.
     */
    public function removeInvalidControlCharacters(string $raw): string
    {
        // Strategy: process character by character inside string values
        // to escape literal control characters (0x00-0x1F except valid JSON escapes)
        $result = '';
        $inString = false;
        $escape   = false;
        $len      = strlen($raw);

        for ($i = 0; $i < $len; $i++) {
            $char = $raw[$i];
            $ord  = ord($char);

            if ($escape) {
                $result .= $char;
                $escape  = false;
                continue;
            }

            if ($char === '\\' && $inString) {
                $result .= $char;
                $escape  = true;
                continue;
            }

            if ($char === '"') {
                $inString = !$inString;
                $result  .= $char;
                continue;
            }

            if ($inString && $ord < 0x20) {
                // Replace literal control characters with JSON escape sequences
                $result .= match ($ord) {
                    0x08 => '\\b',
                    0x09 => '\\t',
                    0x0A => '\\n',
                    0x0C => '\\f',
                    0x0D => '\\r',
                    default => '', // strip other control chars
                };
                continue;
            }

            $result .= $char;
        }

        return $result ?: $raw;
    }

    /**
     * Fix common encoding issues: BOM, curly quotes, unescaped slashes.
     */
    public function fixCommonEncodingIssues(string $raw): string
    {
        // Remove UTF-8 BOM
        $raw = ltrim($raw, "\xEF\xBB\xBF");

        // Replace curly/smart quotes with straight quotes
        $raw = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"], ['"', '"', "'", "'"], $raw);

        // Remove zero-width spaces and other invisible Unicode
        $raw = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $raw) ?? $raw;

        return $raw;
    }

    /**
     * Decode JSON string to array. Throws on failure with clear message.
     */
    public function decodeJson(string $json): array
    {
        $json = trim($json);

        // Attempt 1: direct decode
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);

        if (is_array($data)) {
            return $data;
        }

        throw new \RuntimeException("json_decode retornou tipo inesperado: " . gettype($data));
    }

    /**
     * Validate that all required keys exist in the decoded array.
     */
    public function validateRequiredKeys(array $data, array $requiredKeys): void
    {
        $missing = array_filter($requiredKeys, fn($k) => !isset($data[$k]) && !array_key_exists($k, $data));

        if (!empty($missing)) {
            throw new AIInvalidJsonResponseException(
                "JSON válido mas faltam campos obrigatórios: " . implode(', ', $missing),
                json_encode(array_intersect_key($data, array_flip(array_keys($data)))),
                ''
            );
        }
    }

    /**
     * Return a safe preview of the raw string for logging (no secrets, limited length).
     */
    public function sanitizeForLog(string $raw, int $limit = 500): string
    {
        $safe = preg_replace('/\b(sk-|AIza|Bearer\s+)[A-Za-z0-9\-_\.]{10,}/i', '[TOKEN]', $raw) ?? $raw;
        return mb_substr($safe, 0, $limit);
    }
}
