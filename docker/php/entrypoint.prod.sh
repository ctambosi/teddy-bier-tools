#!/bin/sh
set -e

wait_for_service() {
    local host=$1
    local port=$2
    local name=$3

    echo ">>> Aguardando $name..."
    while ! timeout 1 bash -c "echo > /dev/tcp/$host/$port" 2>/dev/null; do
        sleep 1
    done
    echo ">>> $name disponível"
}

wait_for_service mysql 3306 "MySQL"
wait_for_service redis 6379 "Redis"

cd /var/www

echo ">>> Limpando cache existente..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo ">>> Cache de configuração..."
php artisan config:cache || {
    echo "Aviso: não foi possível cachear configuração (dependencies de dev podem estar faltando)"
    php artisan config:clear || true
}

echo ">>> Cache de rotas..."
php artisan route:cache

echo ">>> Cache de views..."
php artisan view:cache

# Corrigir permissões dos arquivos de cache de views gerados como root
chown -R www-data:www-data storage/framework/views 2>/dev/null || true

if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo ">>> Executando migrações..."
    php artisan migrate --force
fi

echo ">>> Iniciando php-fpm..."
exec php-fpm
