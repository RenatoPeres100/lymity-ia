#!/usr/bin/env bash
# deploy-check.sh — Verifica pré-requisitos antes de fazer deploy na VPS

set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PASS=0
WARN=0
FAIL=0

green()  { echo -e "\033[0;32m[OK]     $1\033[0m"; }
yellow() { echo -e "\033[1;33m[WARN]   $1\033[0m"; }
red()    { echo -e "\033[0;31m[ERROR]  $1\033[0m"; }

echo ""
echo "======================================="
echo "  Lymity IA — Deploy Check"
echo "  $(date '+%Y-%m-%d %H:%M:%S')"
echo "  Projeto: $DIR"
echo "======================================="
echo ""

# PHP
if command -v php &>/dev/null; then
    PHP_VER=$(php -r "echo PHP_VERSION;")
    green "PHP instalado: $PHP_VER"
    ((PASS++))
else
    red "PHP não encontrado"
    ((FAIL++))
fi

# Composer
if command -v composer &>/dev/null; then
    COMPOSER_VER=$(composer --version 2>&1 | head -1)
    green "Composer: $COMPOSER_VER"
    ((PASS++))
else
    red "Composer não encontrado"
    ((FAIL++))
fi

# artisan
if [ -f "$DIR/artisan" ]; then
    green "artisan encontrado: $DIR/artisan"
    ((PASS++))
else
    red "artisan não encontrado em $DIR"
    ((FAIL++))
fi

# .env
if [ -f "$DIR/.env" ]; then
    green ".env existe"
    ((PASS++))
else
    red ".env não encontrado — copie de .env.production.example"
    ((FAIL++))
fi

# APP_KEY
if [ -f "$DIR/.env" ] && grep -q "^APP_KEY=base64:" "$DIR/.env"; then
    green "APP_KEY preenchida"
    ((PASS++))
elif [ -f "$DIR/.env" ] && grep -q "^APP_KEY=$" "$DIR/.env"; then
    red "APP_KEY vazia — execute: php artisan key:generate"
    ((FAIL++))
else
    yellow "APP_KEY: não foi possível verificar"
    ((WARN++))
fi

# APP_DEBUG
if [ -f "$DIR/.env" ] && grep -q "^APP_DEBUG=false" "$DIR/.env"; then
    green "APP_DEBUG=false"
    ((PASS++))
elif [ -f "$DIR/.env" ] && grep -q "^APP_DEBUG=true" "$DIR/.env"; then
    yellow "APP_DEBUG=true — recomendado false em produção"
    ((WARN++))
fi

# storage gravável
if [ -w "$DIR/storage" ]; then
    green "storage/ gravável"
    ((PASS++))
else
    red "storage/ não gravável — execute: chmod -R 775 storage/"
    ((FAIL++))
fi

# bootstrap/cache gravável
if [ -w "$DIR/bootstrap/cache" ]; then
    green "bootstrap/cache/ gravável"
    ((PASS++))
else
    red "bootstrap/cache/ não gravável — execute: chmod -R 775 bootstrap/cache/"
    ((FAIL++))
fi

# vendor
if [ -d "$DIR/vendor" ]; then
    green "vendor/ existe (composer install OK)"
    ((PASS++))
else
    yellow "vendor/ não existe — execute: composer install"
    ((WARN++))
fi

# Laravel version
if [ -f "$DIR/artisan" ]; then
    LARAVEL_VER=$(php "$DIR/artisan" --version 2>&1 || echo "erro")
    green "Laravel: $LARAVEL_VER"
    ((PASS++))
fi

# health-check command
if php "$DIR/artisan" list 2>/dev/null | grep -q "system:health-check"; then
    echo ""
    green "system:health-check disponível — executando..."
    echo "---------------------------------------"
    php "$DIR/artisan" system:health-check 2>&1 || true
    echo "---------------------------------------"
else
    yellow "system:health-check não encontrado"
    ((WARN++))
fi

# Resumo
echo ""
echo "======================================="
echo "  Resultado: OK=$PASS  WARN=$WARN  ERR=$FAIL"
echo "======================================="
echo ""

if [ "$FAIL" -gt 0 ]; then
    echo "RESULTADO: FALHOU — corrija os erros antes de fazer deploy."
    exit 1
else
    echo "RESULTADO: APROVADO — pronto para deploy."
    exit 0
fi
