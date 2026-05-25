#!/usr/bin/env bash
# health-check.sh — Verifica a saúde do sistema em produção

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PORT="${APP_PORT:-8000}"
HOST="${APP_HOST:-127.0.0.1}"

echo ""
echo "======================================="
echo "  Lymity IA — Health Check"
echo "  $(date '+%Y-%m-%d %H:%M:%S')"
echo "======================================="
echo ""

# Artisan health-check
echo "--- [1/3] Artisan system:health-check ---"
php "$DIR/artisan" system:health-check 2>&1 || true
echo ""

# HTTP check
echo "--- [2/3] HTTP Check ($HOST:$PORT) ---"
if command -v curl &>/dev/null; then
    STATUS=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "http://$HOST:$PORT/" 2>/dev/null || echo "000")
    if [ "$STATUS" = "200" ] || [ "$STATUS" = "301" ] || [ "$STATUS" = "302" ]; then
        echo -e "\033[0;32m[OK] HTTP $STATUS — servidor respondendo\033[0m"
    else
        echo -e "\033[0;31m[ERROR] HTTP $STATUS — servidor pode não estar rodando\033[0m"
    fi
else
    echo "[SKIP] curl não disponível"
fi
echo ""

# Últimas linhas do log
echo "--- [3/3] Últimas entradas do laravel.log ---"
LOG="$DIR/storage/logs/laravel.log"
if [ -f "$LOG" ]; then
    tail -n 30 "$LOG" | grep -E "\[ERROR\]|\[CRITICAL\]|\[WARNING\]" || echo "(nenhum erro/warning recente)"
else
    echo "(laravel.log não encontrado)"
fi

echo ""
echo "======================================="
echo "  Health Check concluído"
echo "======================================="
echo ""
