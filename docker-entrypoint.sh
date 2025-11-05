#!/bin/bash

echo "🚀 Iniciando Laravel em modo desenvolvimento..."

# Aguardar um pouco para garantir que os volumes estejam montados
sleep 2

# Configurar Git para evitar problemas de ownership
echo "🔧 Configurando Git..."
git config --global --add safe.directory /var/www/html

# Verificar se o diretório vendor existe, se não, instalar dependências
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Instalando dependências do Composer..."
    composer install --no-interaction --prefer-dist
fi

# Verificar se o diretório node_modules existe, se não, instalar dependências
if [ ! -d "node_modules" ]; then
    echo "📦 Instalando dependências do NPM..."
    npm install --silent
fi

# Verificar se existe .env, se não, criar
if [ ! -f ".env" ]; then
    echo "📄 Criando arquivo .env..."
    cp .env.example .env
fi

# Verificar se a chave da aplicação existe
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Gerando chave da aplicação..."
    php artisan key:generate --no-interaction
fi

# Verificar se o banco SQLite existe
if [ ! -f "database/database.sqlite" ]; then
    echo "🗄️ Criando banco SQLite..."
    touch database/database.sqlite
fi

# Configurar permissões corretas (apenas pastas que precisam)
echo "🔧 Configurando permissões..."
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
if [ -d "database" ]; then
    chmod 666 database/database.sqlite 2>/dev/null || true
fi

# Limpar caches para desenvolvimento
echo "🧹 Limpando caches..."
php artisan config:clear --quiet
php artisan route:clear --quiet
php artisan view:clear --quiet
php artisan cache:clear --quiet

# Executar migrações se necessário
echo "📊 Verificando migrações..."
php artisan migrate --force --no-interaction

echo "✅ Setup concluído! Iniciando Apache..."

# Iniciar Apache em primeiro plano
exec apache2-foreground
