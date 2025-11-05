# Laravel Docker - Guia de Desenvolvimento

## 🚀 Início Rápido

### Desenvolvimento Frontend + Backend

```bash
# Inicia Laravel + Vite (recomendado para desenvolvimento)
./dev.sh dev

# Ou manualmente:
docker-compose --profile dev up -d
```

### Apenas Backend

```bash
# Inicia apenas Laravel
./dev.sh start

# Ou manualmente:
docker-compose up -d app
```

## 📝 Comandos Essenciais

### Script de Desenvolvimento (Recomendado)

```bash
./dev.sh dev        # Iniciar Laravel + Vite
./dev.sh start      # Iniciar apenas Laravel
./dev.sh stop       # Parar todos os containers
./dev.sh restart    # Reiniciar
./dev.sh logs       # Ver logs do Laravel
./dev.sh logs-vite  # Ver logs do Vite
./dev.sh status     # Status dos containers

# Comandos Laravel
./dev.sh artisan migrate
./dev.sh artisan make:controller ExampleController

# Comandos NPM
./dev.sh npm install
./dev.sh npm run build
```

### Comandos Docker Diretos

```bash
# Gerenciar containers
docker-compose --profile dev up -d    # Iniciar com Vite
docker-compose up -d app              # Apenas Laravel
docker-compose down                   # Parar

# Logs
docker-compose logs -f app            # Laravel
docker-compose logs -f vite           # Vite

# Executar comandos
docker-compose exec app php artisan [comando]
docker-compose exec app npm [comando]
```

## 🔄 Fluxo de Desenvolvimento Completo

### ✅ Mudanças que são IMEDIATAS (sem restart):

-   **Arquivos PHP** (.php) - Hot reload automático
-   **Templates Blade** (.blade.php) - Hot reload automático
-   **CSS/JS** - Com Vite rodando, hot reload automático
-   **Configurações** (.env, config/\*.php)
-   **Rotas** (routes/\*.php)

### 🎨 **Frontend com Hot Reload:**

1. **Inicie o ambiente completo:**

    ```bash
    ./dev.sh dev
    ```

2. **URLs disponíveis:**

    - **Backend Laravel**: http://localhost:8000
    - **Frontend Vite**: http://localhost:5173 _(hot reload)_

3. **Desenvolvimento:**
    - Edite arquivos CSS/JS em `resources/`
    - Mudanças aparecem **instantaneamente** no navegador
    - Não precisa refresh manual

### ❗ Mudanças que PRECISAM restart:

-   Dockerfile
-   docker-compose.yml
-   Dependências (composer.json, package.json)

## 🛠️ Resolução de Problemas

### Frontend não atualiza automaticamente

```bash
# Verificar se Vite está rodando
./dev.sh logs-vite

# Reiniciar Vite
./dev.sh stop
./dev.sh dev
```

### Problemas de Dependências

```bash
# Reinstalar dependências
./dev.sh npm install

# Limpar cache
./dev.sh artisan config:clear
./dev.sh artisan cache:clear
```

### Reset Completo

```bash
# Parar tudo e reconstruir
./dev.sh stop
docker-compose build --no-cache
./dev.sh dev
```

### Logs e Debug

```bash
# Ver logs em tempo real
./dev.sh logs        # Laravel
./dev.sh logs-vite   # Vite

# Logs específicos do Laravel
docker-compose exec app tail -f storage/logs/laravel.log
```

## 🌐 URLs e Portas

-   **Laravel (Backend)**: http://localhost:8000
-   **Vite (Frontend + Hot Reload)**: http://localhost:5173

## 📁 Estrutura de Volumes

```
Host                    ->  Container
.                       ->  /var/www/html
./storage              ->  /var/www/html/storage
./bootstrap/cache      ->  /var/www/html/bootstrap/cache
```

## 🎯 Workflow Recomendado

1. **Iniciar desenvolvimento:**

    ```bash
    ./dev.sh dev
    ```

2. **Desenvolver:**

    - Backend: Edite arquivos PHP normalmente
    - Frontend: Edite arquivos em `resources/css/` e `resources/js/`
    - Mudanças aparecem automaticamente!

3. **Deploy/Produção:**

    ```bash
    ./dev.sh build  # Gera assets otimizados
    ```

4. **Finalizar:**
    ```bash
    ./dev.sh stop
    ```

## 🚀 Performance

-   **Hot Reload**: Mudanças CSS/JS instantâneas
-   **Volume Caching**: Melhor performance no WSL
-   **Polling**: Detecta mudanças de arquivo automaticamente
-   **Proxy**: Vite funciona perfeitamente com Laravel

Seu ambiente está **100% otimizado** para desenvolvimento Laravel + Frontend! 🎉
