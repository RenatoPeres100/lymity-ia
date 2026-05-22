<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $default  = config('database.default');
        $database = config("database.connections.{$default}.database");

        if ($database === 'lymity_ia') {
            throw new \RuntimeException(
                "SEGURANÇA: Testes apontando para banco PRODUÇÃO (lymity_ia). " .
                "Execute com APP_ENV=testing ou configure .env.testing com DB_DATABASE=lymity_ia_test."
            );
        }
    }
}
