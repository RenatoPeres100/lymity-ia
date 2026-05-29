<?php

namespace App\Services\Social;

use App\Models\SocialPost;
use App\Services\Social\DTO\ImageValidationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublicImageValidatorService
{
    private const PRIVATE_RANGES = [
        '127.',
        '10.',
        '192.168.',
        '172.16.', '172.17.', '172.18.', '172.19.',
        '172.20.', '172.21.', '172.22.', '172.23.',
        '172.24.', '172.25.', '172.26.', '172.27.',
        '172.28.', '172.29.', '172.30.', '172.31.',
        '0.',
        '::1',
        'localhost',
    ];

    public function validateSocialPostImage(SocialPost $post): ImageValidationResult
    {
        return $this->validateUrl($post->public_image_url ?? '');
    }

    public function validateUrl(string $url): ImageValidationResult
    {
        if (empty($url)) {
            return ImageValidationResult::fail('URL de imagem não informada.');
        }

        if (!str_starts_with($url, 'https://')) {
            return ImageValidationResult::fail('Imagem precisa estar em uma URL HTTPS pública.', $url);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ImageValidationResult::fail('URL de imagem inválida.', $url);
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) {
            return ImageValidationResult::fail('URL sem host válido.', $url);
        }

        foreach (self::PRIVATE_RANGES as $private) {
            if (str_starts_with($host, $private) || $host === $private) {
                return ImageValidationResult::fail(
                    'URL aponta para rede privada ou localhost. Use uma URL HTTPS acessível publicamente.',
                    $url
                );
            }
        }

        try {
            // Try HEAD first; fall back to GET if HEAD is blocked
            $response = Http::withoutVerifying()->timeout(10)->head($url);
            if ($response->status() === 405) {
                $response = Http::withoutVerifying()->timeout(15)->get($url);
            }

            $httpStatus  = $response->status();
            $contentType = strtolower($response->header('Content-Type') ?? '');

            if (!$response->successful()) {
                return ImageValidationResult::fail(
                    "URL retornou HTTP {$httpStatus}. Verifique se a imagem está acessível publicamente.",
                    $url,
                    $httpStatus,
                    $contentType
                );
            }

            if (!str_contains($contentType, 'image/jpeg') && !str_contains($contentType, 'image/png')) {
                // Tolerate missing content-type on HEAD — try GET to confirm
                if (str_starts_with($response->header('Content-Type') ?? '', 'text/html') || empty($contentType)) {
                    $getResp    = Http::withoutVerifying()->timeout(20)->get($url);
                    $contentType = strtolower($getResp->header('Content-Type') ?? '');
                    if (!str_contains($contentType, 'image/jpeg') && !str_contains($contentType, 'image/png')) {
                        return ImageValidationResult::fail(
                            "URL não retorna imagem JPEG ou PNG (Content-Type: {$contentType}).",
                            $url,
                            $getResp->status(),
                            $contentType
                        );
                    }
                    // Use content from GET for dimension check
                    return $this->checkDimensions($getResp->body(), $url, $httpStatus, $contentType);
                }
                return ImageValidationResult::fail(
                    "URL não retorna imagem JPEG ou PNG (Content-Type: {$contentType}). Use uma URL que aponte diretamente para um arquivo de imagem.",
                    $url,
                    $httpStatus,
                    $contentType
                );
            }

            // Download to check dimensions
            $getResp = Http::withoutVerifying()->timeout(20)->get($url);
            return $this->checkDimensions($getResp->body(), $url, $httpStatus, $contentType);
        } catch (\Throwable $e) {
            Log::debug('[PublicImageValidator] Exception for ' . $url . ': ' . $e->getMessage());
            return ImageValidationResult::fail(
                'Não foi possível verificar a URL: ' . $e->getMessage() . '. Certifique-se de que a imagem está em uma URL HTTPS pública.',
                $url
            );
        }
    }

    public function isValidPublicImageUrl(?string $url): bool
    {
        return $this->validateUrl($url ?? '')->valid;
    }

    public function explain(?string $url): string
    {
        $result = $this->validateUrl($url ?? '');
        return $result->valid ? 'URL válida.' : ($result->error ?? 'URL inválida.');
    }

    private function checkDimensions(string $body, string $url, int $httpStatus, string $contentType): ImageValidationResult
    {
        if (empty($body)) {
            return ImageValidationResult::fail('Imagem vazia ou não foi possível baixar.', $url, $httpStatus, $contentType);
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'lymity_img_');
        file_put_contents($tmpFile, $body);

        $info = @getimagesize($tmpFile);
        @unlink($tmpFile);

        if (!$info) {
            return ImageValidationResult::fail('Arquivo não é uma imagem válida ou está corrompido.', $url, $httpStatus, $contentType);
        }

        $width  = $info[0];
        $height = $info[1];
        $minW   = config('social.image.min_width', 320);
        $minH   = config('social.image.min_height', 320);

        if ($width < $minW || $height < $minH) {
            return ImageValidationResult::fail(
                "Imagem muito pequena ({$width}x{$height}px). Mínimo: {$minW}x{$minH}px.",
                $url,
                $httpStatus,
                $contentType,
                $width,
                $height
            );
        }

        $maxMb    = config('social.image.max_size_mb', 8);
        $maxBytes = $maxMb * 1024 * 1024;
        if (strlen($body) > $maxBytes) {
            return ImageValidationResult::fail(
                "Imagem muito grande (" . round(strlen($body) / 1024 / 1024, 1) . "MB). Máximo: {$maxMb}MB.",
                $url,
                $httpStatus,
                $contentType,
                $width,
                $height
            );
        }

        return ImageValidationResult::ok($url, $httpStatus, $contentType, $width, $height, strlen($body));
    }
}
