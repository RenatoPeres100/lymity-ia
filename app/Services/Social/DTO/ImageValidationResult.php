<?php

namespace App\Services\Social\DTO;

class ImageValidationResult
{
    public function __construct(
        public readonly bool    $valid,
        public readonly ?string $error       = null,
        public readonly ?string $url         = null,
        public readonly ?int    $httpStatus   = null,
        public readonly ?string $contentType  = null,
        public readonly ?int    $width        = null,
        public readonly ?int    $height       = null,
        public readonly ?int    $bytes        = null,
    ) {}

    public static function ok(string $url, int $httpStatus, string $contentType, int $width, int $height, ?int $bytes = null): self
    {
        return new self(true, null, $url, $httpStatus, $contentType, $width, $height, $bytes);
    }

    public static function fail(string $error, string $url = null, ?int $httpStatus = null, ?string $contentType = null): self
    {
        return new self(false, $error, $url, $httpStatus, $contentType);
    }
}
