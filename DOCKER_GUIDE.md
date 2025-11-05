# Laravel Docker - Comandos de Desenvolvimento

## 🚀 Início Rápido

```bash
# Setup inicial
./setup.sh

# Ou manualmente:
docker-compose up -d
```

## 📝 Comandos Essenciais

### Gerenciamento dos Containers

```bash
# Iniciar containers
docker-compose up -d

# Parar containers
docker-compose down

# Rebuild (apenas quando necessário)
docker-compose build --no-cache
docker-compose up -d

# Ver logs em tempo real
docker-compose logs -f app
```

### Laravel/PHP

```bash
# Comandos Artisan
docker-compose exec app php artisan migrate
docker-compose exec app php artisan make:controller ExampleController
docker-compose exec app php artisan route:list
docker-compose exec app php artisan tinker

# Composer
docker-compose exec app composer install
docker-compose exec app composer require package/name
docker-compose exec app composer update

# Acessar bash do container
docker-compose exec app bash
```

### Frontend (Node.js/NPM)

```bash
# Instalar dependências
docker-compose exec app npm install

# Build para produção
docker-compose exec app npm run build

# Desenvolvimento com hot reload (Vite)
docker-compose --profile dev up -d
# Acesse: http://localhost:5173
```

## 🔄 Fluxo de Desenvolvimento

### ✅ Mudanças que NÃO precisam rebuild:

-   Arquivos PHP (.php)
-   Templates Blade (.blade.php)
-   Arquivos de configuração (.env, config/\*.php)
-   Rotas (routes/\*.php)
-   Migrações
-   CSS/JS (após compilar)

### ❗ Mudanças que PRECISAM rebuild:

-   Dockerfile
-   Dependências do Composer (composer.json)
-   Extensões PHP
-   Configurações do Apache

## 🛠️ Resolução de Problemas

### Permissões no WSL

```bash
# Se tiver problemas de permissão:
chmod -R 755 storage bootstrap/cache
```

### Cache Limpo

```bash
# Limpar todos os caches
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan cache:clear
```

### Reset Completo

```bash
# Parar tudo e reconstruir
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Logs e Debug

```bash
# Ver logs do Laravel
docker-compose exec app tail -f storage/logs/laravel.log

# Ver logs do Apache
docker-compose logs app

# Verificar processos dentro do container
docker-compose exec app ps aux
```

## 🌐 URLs

-   **Aplicação Laravel**: http://localhost:8000
-   **Vite (dev)**: http://localhost:5173 (quando usar --profile dev)

## 📁 Estrutura de Volumes

```
Host                    ->  Container
.                       ->  /var/www/html
./storage              ->  /var/www/html/storage
./bootstrap/cache      ->  /var/www/html/bootstrap/cache
(node_modules excluído do volume para performance)
(vendor excluído do volume para performance)
```

Isso garante que suas mudanças sejam refletidas imediatamente no container!
