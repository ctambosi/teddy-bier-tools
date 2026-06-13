#!/bin/sh
set -e

# Garante que o hot file do Vite não existe (resquício de sessões anteriores de npm run dev)
rm -f public/hot

# Instala dependências JS se necessário
if [ ! -d "node_modules" ]; then
    echo ">>> Instalando dependências npm..."
    npm install
fi

# Build apenas se o manifest não existir (primeira vez ou após limpeza manual)
# Para rebuildar: docker exec bier_app npm run build
if [ ! -f "public/build/manifest.json" ]; then
    echo ">>> Buildando assets (primeiro start)..."
    npm run build
fi

exec php-fpm
