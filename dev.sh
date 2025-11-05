#!/bin/bash

# Script para gerenciar o ambiente de desenvolvimento Laravel com Docker

function show_help() {
    echo "📋 Laravel Docker - Comandos de Desenvolvimento"
    echo ""
    echo "Uso: ./dev.sh [comando]"
    echo ""
    echo "Comandos disponíveis:"
    echo "  start     - Iniciar apenas Laravel (sem Vite)"
    echo "  dev       - Iniciar Laravel + Vite (desenvolvimento completo)"
    echo "  stop      - Parar todos os containers"
    echo "  restart   - Reiniciar todos os containers"
    echo "  logs      - Ver logs do Laravel"
    echo "  logs-vite - Ver logs do Vite"
    echo "  artisan   - Executar comando artisan (ex: ./dev.sh artisan migrate)"
    echo "  npm       - Executar comando npm (ex: ./dev.sh npm install)"
    echo "  build     - Construir assets para produção"
    echo "  status    - Verificar status dos containers"
    echo ""
    echo "🌐 URLs:"
    echo "  Laravel: http://localhost:8000"
    echo "  Vite:    http://localhost:5173"
}

case "$1" in
    "start")
        echo "🚀 Iniciando Laravel..."
        docker-compose up -d app
        echo "✅ Laravel iniciado em: http://localhost:8000"
        ;;
    "dev")
        echo "🚀 Iniciando desenvolvimento completo (Laravel + Vite)..."
        docker-compose --profile dev up -d
        echo "✅ Ambiente de desenvolvimento iniciado:"
        echo "   🌐 Laravel: http://localhost:8000"
        echo "   🎨 Vite:    http://localhost:5173"
        ;;
    "stop")
        echo "🛑 Parando todos os containers..."
        docker-compose --profile dev down
        echo "✅ Containers parados"
        ;;
    "restart")
        echo "🔄 Reiniciando containers..."
        docker-compose --profile dev down
        docker-compose --profile dev up -d
        echo "✅ Containers reiniciados"
        ;;
    "logs")
        echo "📋 Logs do Laravel (Ctrl+C para sair):"
        docker-compose logs -f app
        ;;
    "logs-vite")
        echo "📋 Logs do Vite (Ctrl+C para sair):"
        docker-compose logs -f vite
        ;;
    "artisan")
        shift
        echo "🎯 Executando: php artisan $@"
        docker-compose exec app php artisan "$@"
        ;;
    "npm")
        shift
        echo "📦 Executando: npm $@"
        docker-compose exec app npm "$@"
        ;;
    "build")
        echo "🏗️ Construindo assets para produção..."
        docker-compose exec app npm run build
        echo "✅ Assets construídos"
        ;;
    "status")
        echo "📊 Status dos containers:"
        docker-compose ps
        ;;
    *)
        show_help
        ;;
esac
