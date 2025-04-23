<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <script crossorigin src="https://unpkg.com/@daily-co/daily-js"></script>
    </head>
    <body>
            <button onclick="startCall()">Iniciar Chamada</button>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <video id="local-video" autoplay muted playsinline width="320" height="240" style="background-color: black;"></video>
                <video id="remote-video" autoplay playsinline width="320" height="240" style="background-color: black;"></video>
            </div>

            <script>
              let callObject;

              async function startCall() {
                // 1. Criar a sala via backend
                const res = await fetch('/api/create-room', { method: 'POST' });
                const { url } = await res.json();

                // 2. Criar o call object (modo customizado, sem iframe)
                callObject = window.DailyIframe.createCallObject();

                // 3. Listener para exibir vídeo local
                callObject.on('track-started', (e) => {
                    if (e.participant.local && e.track.kind === 'video') {
                        document.getElementById('local-video').srcObject = e.stream;
                    }

                    if (!e.participant.local && e.track.kind === 'video') {
                        document.getElementById('remote-video').srcObject = e.stream;
                    }
                });

                await callObject.join({ url });

                // 5. (Opcional) Iniciar gravação automática
                callObject.startRecording();
              }
        </script>
      </body>
</html>
