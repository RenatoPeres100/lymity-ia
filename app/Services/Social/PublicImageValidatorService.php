<?php

namespace App\Services\Social;

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

    public function isValidPublicImageUrl(?string $url): bool
    {
        if (empty($url)) return false;
        if (!str_starts_with($url, 'https://')) return false;
        if (!filter_var($url, FILTER_VALIDATE_URL)) return false;

        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) return false;

        foreach (self::PRIVATE_RANGES as $private) {
            if (str_starts_with($host, $private) || $host === $private) return false;
        }

        try {
            $response = Http::timeout(5)->head($url);
            $contentType = $response->header('Content-Type') ?? '';
            return $response->successful() && str_contains($contentType, 'image/');
        } catch (\Throwable $e) {
            Log::debug('[PublicImageValidator] HEAD request failed for ' . $url . ': ' . $e->getMessage());
            return false;
        }
    }

    public function explain(?string $url): string
    {
        if (empty($url)) {
            return 'URL de imagem não informada.';
        }

        if (!str_starts_with($url, 'https://')) {
            return 'Imagem precisa estar em uma URL pública HTTPS acessível pela Meta.';
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return 'URL de imagem inválida.';
        }

        $host = parse_url($url, PHP_URL_HOST);
        foreach (self::PRIVATE_RANGES as $private) {
            if (str_starts_with($host ?? '', $private) || ($host ?? '') === $private) {
                return 'URL aponta para rede privada ou localhost. Imagem precisa ser acessível publicamente pela Meta.';
            }
        }

        try {
            $response = Http::timeout(5)->head($url);
            if (!$response->successful()) {
                return "URL retornou status HTTP {$response->status()}. Verifique se a imagem está disponível publicamente.";
            }
            $contentType = $response->header('Content-Type') ?? '';
            if (!str_contains($contentType, 'image/')) {
                return "URL não retorna uma imagem (Content-Type: {$contentType}). Use uma URL que aponte diretamente para um arquivo de imagem.";
            }
            return 'URL válida.';
        } catch (\Throwable $e) {
            return 'Não foi possível verificar a URL: ' . $e->getMessage() . '. Certifique-se de que a imagem está em uma URL HTTPS pública.';
        }
    }
}
