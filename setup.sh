#!/bin/bash

echo "🚀 Iniciando setup do Laravel com Docker para desenvolvimento..."

# Verificar se estamos no WSL
if grep -qEi "(Microsoft|WSL)" /proc/version &> /dev/null ; then
    echo "✅ WSL detectado - configurando para desenvolvimento"
    WSL_MODE=true
else
    echo "ℹ️  Modo desenvolvimento Linux nativo"
    WSL_MODE=false
fi

# Verificar se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Por favor, inicie o Docker primeiro."
    exit 1
fi

echo "📦 Construindo a imagem Docker..."
docker-compose build

echo "🔧 Configurando permissões para desenvolvimento..."
if [ "$WSL_MODE" = true ]; then
    # No WSL, configurar permissões mais específicas
    chmod -R 755 storage bootstrap/cache
    mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
else
    # Linux nativo
    sudo chown -R $USER:$USER .
    chmod -R 755 storage bootstrap/cache
fi

echo "📄 Criando arquivo .env se não existir..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Arquivo .env criado"
fi

echo "️ Criando banco SQLite se não existir..."
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "✅ Banco SQLite criado"
fi

echo "� Iniciando os containers..."
docker-compose up -d

# Aguardar um pouco para o container inicializar
echo "⏳ Aguardando inicialização do container..."
sleep 10

echo ""
echo "✅ Setup concluído!"
echo "🌐 Aplicação rodando em: http://localhost:8000"
echo ""
echo "📝 Comandos úteis para desenvolvimento:"
echo "  docker-compose up -d                    # Iniciar containers"
echo "  docker-compose down                     # Parar containers"
echo "  docker-compose logs -f app              # Ver logs em tempo real"
echo "  docker-compose exec app php artisan [cmd] # Executar comandos artisan"
echo "  docker-compose exec app composer [cmd]  # Executar comandos composer"
echo "  docker-compose exec app npm [cmd]       # Executar comandos npm"
echo ""
echo "🎨 Para desenvolvimento frontend com hot reload:"
echo "  docker-compose --profile dev up -d      # Incluir serviço Vite"
echo "  # Frontend estará em: http://localhost:5173"
echo ""
echo "🔄 Como as mudanças são aplicadas:"
echo "  ✅ Arquivos PHP: Imediatamente (sem rebuild)"
echo "  ✅ Arquivos Blade: Imediatamente (sem rebuild)"
echo "  ✅ Arquivos CSS/JS: Compile com 'npm run build' ou use Vite"
echo "  ❗ Mudanças no Dockerfile: Necessário rebuild"
