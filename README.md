# 🟢 Daily Video Call & Recording Manager

Este projeto é uma aplicação desenvolvida em **Laravel** com integração ao serviço de videochamadas da **[Daily.co](https://www.daily.co/)**. Ele permite:

- ✅ Criação de salas de videoconferência.
- 🎥 Gravação das chamadas com armazenamento em nuvem.
- 📋 Listagem das gravações realizadas no dia.
- 📥 Download das gravações diretamente pela aplicação.
- 🎛️ Funcionalidade de compartilhamento de tela.
- 🟣 Efeito de desfoque de fundo (Background Blur) nas chamadas de vídeo.

## 🚀 Tecnologias utilizadas

- Backend: Laravel (PHP)
- Frontend: Tailwind CSS + JavaScript (com Daily JS SDK)
- Videochamadas: Daily.co API

---

## ⚙️ Como rodar o projeto

1. Clone o repositório:
   ```bash
   git clone https://seu-repo-aqui.git
   cd nome-do-projeto
   ```
2. Instale as dependências do PHP:
    ```bash 
    composer install
    ```
3.  Instale as dependências do PHP:
    ```bash 
    npm install
    ```
4. Configure as variáveis de ambiente: 
   - Copie o arquivo .env.example para .env:
    ```bash 
    cp .env.example .env
    ```
   - Adicione a variável DAILY_API_KEY no arquivo .env:
    ```bash 
    DAILY_API_KEY=seu-token-da-daily
    ```
🛠️ A chave DAILY_API_KEY é fornecida pela Daily.co na sua conta de desenvolvedor.

5. Gere a chave da aplicação Laravel:
    ```bash 
    php artisan key:generate
    ```
6.  Execute o servidor Laravel:
    ```bash 
    php artisan serve
    ```
7.  Inicie o Vite para os assets:
    ```bash 
    npm run dev
    ```
## 🖥️ Acesso ao sistema
Após iniciar os servidores, acesse no navegador:
    ```bash
    http://localhost:8000
    ```
## 📄 Licença
Este projeto está licenciado sob a licença MIT. Consulte o arquivo LICENSE para mais detalhes.

## 🤝 Contribuindo
Contribuições são bem-vindas!
Abra uma issue ou pull request com sugestões de melhorias ou correções.

## 💡 Links úteis
Documentação da Daily: https://docs.daily.co/reference

Blog sobre Background Blur: https://www.daily.co/blog/add-background-blur-to-a-daily-call-with-our-newest-api/

Página de preços da Daily: https://www.daily.co/pricing/
