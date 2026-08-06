#!/bin/sh
set -e

cd /var/www

# Garante que o hot file do Vite não existe (resquício de sessões anteriores)
rm -f public/hot

# Instala dependências PHP se necessário (verifica se há arquivos, não apenas se diretório existe)
if [ ! -f "vendor/autoload.php" ]; then
    echo ">>> Instalando dependências PHP (Composer)..."
    composer install
fi

# Instala dependências JS se necessário (verifica se há node_modules com conteúdo)
if [ -z "$(find node_modules -maxdepth 1 -mindepth 1 -type d 2>/dev/null | head -1)" ]; then
    echo ">>> Instalando dependências npm..."
    npm install
fi

# Build apenas se o manifest não existir
if [ ! -f "public/build/manifest.json" ]; then
    echo ">>> Buildando assets (primeiro start)..."
    npm run build
fi

exec php-fpm
