<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>

    </style>
    <title>Daily Call Example</title>
</head>

<body>
    <div class="controls flex gap-4 my-4">
        <button id="join-btn"
            class="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            Join Room
        </button>

        <button id="leave-btn"
            class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
            disabled>
            Leave
        </button>

        <button id="record-btn"
            class="px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition"
            disabled>
            Record
        </button>
    </div>
    <div class="my-6 max-w-md">
        <h2 class="text-lg font-bold mb-2">Chat</h2>
        <div id="chat-box" class="h-64 overflow-y-auto border rounded p-2 bg-gray-50 mb-2"></div>
        <div class="flex gap-2">
            <input id="chat-input" type="text" placeholder="Type your message..."
                class="flex-grow border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <button id="send-chat-btn"
                class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                Send
            </button>
        </div>
    </div>

    <div class="controls flex gap-4 my-4">
        <button id="toggle-camera"
            class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
            disabled>
            Toggle Camera
        </button>

        <button id="toggle-mic"
            class="px-4 py-2 bg-purple-600 text-white font-semibold rounded-lg shadow-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition"
            disabled>
            Toggle Microphone
        </button>
        <button id="screen-share-btn"
            class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
            disabled>
            Start Screen Share
        </button>
    </div>

    <div class="controls">
        <select id="camera-selector">
            <option value="" disabled selected>Select a camera</option>
        </select>
        <select id="mic-selector">
            <option value="" disabled selected>Select a microphone</option>
        </select>
    </div>

    <div id="status">
        <div id="camera-state">Camera: Off</div>
        <div id="mic-state">Mic: Off</div>
        <div id="participant-count">Participants: 0</div>
        <div id="active-speaker">Active Speaker: None</div>
    </div>

    <div id="videos"></div>
    <div id="shared-screen-area" class="p-4 border-2 border-red-500 rounded-lg w-full max-w-3xl mx-auto my-4 hidden">
        <h3 class="text-red-600 font-bold mb-2">Tela Compartilhada</h3>
        <div id="shared-screen-container" class="aspect-video bg-black rounded overflow-hidden"></div>
    </div>

    <script src="https://unpkg.com/@daily-co/daily-js"></script>
    <script>
        /**
         * Initializes a new instance of the `DailyCallManager` class, creating
         * a Daily.co call object and setting initial states for camera and
         * microphone muting, as well as the current room URL. It then calls the
         * `initialize` method to set up event listeners and UI interactions.
         */
        class DailyCallManager {
            constructor() {
                this.call = Daily.createCallObject();
                this.currentRoomUrl = null;
                this.isRecording = false; // Define the initial recording status
                this.isScreenSharing = false;
                this.initialize();
            }

            /**
             * Performs initial setup of event listeners and UI component interactions.
             */
            async initialize() {
                this.setupEventListeners();
                document
                    .getElementById('toggle-camera')
                    .addEventListener('click', () => this.toggleCamera());
                document
                    .getElementById('toggle-mic')
                    .addEventListener('click', () => this.toggleMicrophone());
                document
                    .getElementById('screen-share-btn')
                    .addEventListener('click', () => this.toggleScreenShare());
                this.setupChat();
            }

            setupChat() {
                const chatInput = document.getElementById('chat-input');
                const sendChatBtn = document.getElementById('send-chat-btn');
                const chatBox = document.getElementById('chat-box');

                // Envia a mensagem para todos os participantes
                sendChatBtn.addEventListener('click', () => {
                    const message = chatInput.value.trim();
                    if (message) {
                        this.call.sendAppMessage({
                            text: message
                        }, '*'); // '*' envia para todos
                        this.appendChatMessage('You', message);
                        chatInput.value = '';
                    }
                });

                // Escuta mensagens recebidas
                this.call.on('app-message', (event) => {
                    const sender = event.from?.user_name || 'Participant';
                    const message = event.data.text;
                    this.appendChatMessage(sender, message);
                });
            }

            // Adiciona uma mensagem no chat box
            appendChatMessage(sender, message) {
                const chatBox = document.getElementById('chat-box');
                const messageEl = document.createElement('div');
                messageEl.className = 'mb-1';
                messageEl.innerHTML = `<span class="font-semibold">${sender}:</span> ${message}`;
                chatBox.appendChild(messageEl);
                chatBox.scrollTop = chatBox.scrollHeight; // Mantém sempre na última mensagem
            }
            toggleScreenShare() {
                const screenShareBtn = document.getElementById('screen-share-btn');
                if (this.isScreenSharing) {
                    this.call.stopScreenShare();
                } else {
                    this.call.startScreenShare();
                }
            }
            /**
             * Configures event listeners for various call-related events.
             */
            setupEventListeners() {
                const events = {
                    'active-speaker-change': this.handleActiveSpeakerChange.bind(this),
                    error: this.handleError.bind(this),
                    'joined-meeting': this.handleJoin.bind(this),
                    'left-meeting': this.handleLeave.bind(this),
                    'participant-joined': this.handleParticipantJoinedOrUpdated.bind(this),
                    'participant-left': this.handleParticipantLeft.bind(this),
                    'participant-updated': this.handleParticipantJoinedOrUpdated.bind(this),
                    'screen-share-started': this.handleScreenShareStarted.bind(this),
                    'screen-share-stopped': this.handleScreenShareStopped.bind(this),
                };

                Object.entries(events).forEach(([event, handler]) => {
                    this.call.on(event, handler);
                });
            }
            // Define o texto do botão baseado no status
            handleScreenShareStarted() {
                this.isScreenSharing = true;
                const screenShareBtn = document.getElementById('screen-share-btn');
                screenShareBtn.textContent = 'Stop Screen Share';
                screenShareBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                screenShareBtn.classList.add('bg-red-600', 'hover:bg-red-700');
            }

            handleScreenShareStopped() {
                this.isScreenSharing = false;
                const screenShareBtn = document.getElementById('screen-share-btn');
                screenShareBtn.textContent = 'Start Screen Share';
                screenShareBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                screenShareBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                document.getElementById('shared-screen-area').classList.add('hidden');
                document.getElementById('shared-screen-container').innerHTML = '';
            }
            /**
             * Handler for the local participant joining:
             * - Prints the room URL
             * - Enables the toggle camera, toggle mic, and leave buttons
             * - Gets the initial track states
             * - Sets up and enables the device selectors
             * @param {Object} event - The joined-meeting event object.
             */
            handleJoin(event) {
                const tracks = event.participants.local.tracks;

                console.log(`Successfully joined: ${this.currentRoomUrl}`);

                // Update the participant count
                this.updateAndDisplayParticipantCount();

                // Enable the leave button
                document.getElementById('leave-btn').disabled = false;
                document.getElementById('record-btn').disabled = false;

                // Enable the toggle camera and mic buttons and selectors
                document.getElementById('toggle-camera').disabled = false;
                document.getElementById('toggle-mic').disabled = false;
                document.getElementById('camera-selector').disabled = false;
                document.getElementById('mic-selector').disabled = false;
                document.getElementById('screen-share-btn').disabled = false;

                // Set up the camera and mic selectors
                this.setupDeviceSelectors();

                // Initialize the camera and microphone states and UI for the local
                // participant
                Object.entries(tracks).forEach(([trackType, trackInfo]) => {
                    this.updateUiForDevicesState(trackType, trackInfo);
                });
            }

            /**
             * Handler for participant leave events:
             * - Confirms leaving with a console message
             * - Disable the toggle camera and mic buttons
             * - Resets the camera and mic selectors
             * - Updates the call state in the UI
             * - Removes all video containers
             */
            handleLeave() {
                console.log('Successfully left the call');

                // Update the join and leave button states
                document.getElementById('leave-btn').disabled = true;
                document.getElementById('record-btn').disabled = true;
                document.getElementById('join-btn').disabled = false;

                // Disable the toggle camera and mic buttons
                document.getElementById('toggle-camera').disabled = true;
                document.getElementById('toggle-mic').disabled = true;

                // Reset and disable the camera and mic selectors
                const cameraSelector = document.getElementById('camera-selector');
                const micSelector = document.getElementById('mic-selector');
                cameraSelector.selectedIndex = 0;
                micSelector.selectedIndex = 0;
                cameraSelector.disabled = true;
                micSelector.disabled = true;

                // Update the call state in the UI
                document.getElementById('camera-state').textContent = 'Camera: Off';
                document.getElementById('mic-state').textContent = 'Mic: Off';
                document.getElementById(
                    'participant-count'
                ).textContent = `Participants: 0`;
                document.getElementById(
                    'active-speaker'
                ).textContent = `Active Speaker: None`;

                // Remove all video containers
                const videosDiv = document.getElementById('videos');
                while (videosDiv.firstChild) {
                    videosDiv.removeChild(videosDiv.firstChild);
                }
            }

            /**
             * Handles fatal errors emitted from the Daily call object.
             * These errors result in the participant leaving the meeting. A
             * `left-meeting` event will also be sent, so we still rely on that event
             * for cleanup.
             * @param {Object} e - The error event object.
             */
            handleError(e) {
                console.error('DAILY SENT AN ERROR!', e.error ? e.error : e.errorMsg);
            }

            /**
             * Handles participant-left event:
             * - Cleans up the video and audio tracks for the participant
             * - Removes the related UI elements
             * @param {Object} event - The participant-left event object.
             */
            handleParticipantLeft(event) {
                const participantId = event.participant.session_id;

                // Clean up the video and audio tracks for the participant
                this.destroyTracks(['video', 'audio'], participantId);

                // Now, remove the related video UI
                document.getElementById(`video-container-${participantId}`)?.remove();

                // Update the participant count
                this.updateAndDisplayParticipantCount();
            }

            /**
             * Handles participant-joined and participant-updated events:
             * - Updates the participant count
             * - Creates a video container for new participants
             * - Creates an audio element for new participants
             * - Manages video and audio tracks based on their current state
             * - Updates device states for the local participant
             * @param {Object} event - The participant-joined, participant-updated
             * event object.
             */
            handleParticipantJoinedOrUpdated(event) {
                const {
                    participant
                } = event;
                const participantId = participant.session_id;
                const isLocal = participant.local;
                const tracks = participant.tracks;

                // Always update the participant count regardless of the event action
                this.updateAndDisplayParticipantCount();

                // Create a video container if one doesn't exist
                if (!document.getElementById(`video-container-${participantId}`)) {
                    this.createVideoContainer(participantId);
                }

                // Create an audio element for non-local participants if one doesn't exist
                if (!document.getElementById(`audio-${participantId}`) && !isLocal) {
                    this.createAudioElement(participantId);
                }

                Object.entries(tracks).forEach(([trackType, trackInfo]) => {
                    if (trackInfo.persistentTrack) {
                        if (!(isLocal && (trackType === 'audio' || trackType === 'screenVideo'))) {
                            this.startOrUpdateTrack(trackType, trackInfo, participantId);
                        }
                    } else {
                        this.destroyTracks([trackType], participantId);
                    }

                    if (trackType === 'video' || trackType === 'screenVideo') {
                        this.updateVideoUi(trackInfo, participantId, trackType);
                    }

                    if (isLocal) {
                        this.updateUiForDevicesState(trackType, trackInfo);
                    }
                });
            }

            /**
             * Updates the UI with the current active speaker's identity.
             * @param {Object} event - The active speaker change event object.
             */
            handleActiveSpeakerChange(event) {
                document.getElementById(
                    'active-speaker'
                ).textContent = `Active Speaker: ${event.activeSpeaker.peerId}`;
            }

            /**
             * Tries to join a call with provided room URL and optional join token.
             * @param {string} roomUrl - The URL of the room to join.
             * @param {string|null} joinToken - An optional token for joining the room.
             */
            async joinRoom(roomUrl, joinToken = null) {
                if (!roomUrl) {
                    console.error('Room URL is required to join a room.');
                    return;
                }

                this.currentRoomUrl = roomUrl;

                const joinOptions = {
                    url: roomUrl
                };
                if (joinToken) {
                    joinOptions.token = joinToken;
                    console.log('Joining with a token.');
                } else {
                    console.log('Joining without a token.');
                }

                try {
                    // Disable the join button to prevent multiple attempts to join
                    document.getElementById('join-btn').disabled = true;
                    // Join the room
                    await this.call.join(joinOptions);
                    this.isRecording = false; // Define o status inicial da gravação

                    document.getElementById('record-btn').addEventListener('click', async () => {
                        if (!this.isRecording) {
                            try {
                                await this.call.startRecording({
                                    width: 854,
                                    height: 480,
                                    fps: 24,
                                    videoBitrate: 1000,
                                    audioBitrate: 64,
                                    layout: {
                                        preset: 'default',
                                        max_cam_streams: 5,
                                    },
                                });
                                this.isRecording = true;
                                document.getElementById('record-btn').textContent = 'Stop Recording';
                            } catch (e) {
                                console.error('Error starting recording:', e);
                            }
                        } else {
                            try {
                                await this.call.stopRecording();
                                this.isRecording = false;
                                document.getElementById('record-btn').textContent = 'Record';
                            } catch (e) {
                                console.error('Error stopping recording:', e);
                            }
                        }
                    });

                } catch (e) {
                    console.error('Join failed:', e);
                }
            }

            /**
             * Creates and sets up a new video container for a specific participant. This
             * function dynamically generates a video element along with a container and
             * an overlay displaying the participant's ID. The newly created elements are
             * appended to a designated parent in the DOM, preparing them for video
             * streaming or playback related to the specified participant.
             *
             * @param {string} participantId - The unique identifier for the participant.
             */
            createVideoContainer(participantId) {
                // Create a video container for the participant
                const videoContainer = document.createElement('div');
                videoContainer.id = `video-container-${participantId}`;
                videoContainer.className = 'video-container';
                document.getElementById('videos').appendChild(videoContainer);

                // Add an overlay to display the participant's session ID
                const sessionIdOverlay = document.createElement('div');
                sessionIdOverlay.className = 'session-id-overlay';
                sessionIdOverlay.textContent = participantId;
                videoContainer.appendChild(sessionIdOverlay);

                // Create a video element for the participant
                const videoEl = document.createElement('video');
                videoEl.className = 'video-element';
                videoContainer.appendChild(videoEl);

            }

            /**
             * Creates an audio element for a particular participant. This function is
             * responsible for dynamically generating a standalone audio element that can
             * be used to play audio streams associated with the specified participant.
             * The audio element is appended directly to the document body or a relevant
             * container, thereby preparing it for playback of the participant's audio.
             *
             * @param {string} participantId - A unique identifier corresponding to the participant.
             */
            createAudioElement(participantId) {
                // Create an audio element for the participant
                const audioEl = document.createElement('audio');
                audioEl.id = `audio-${participantId}`;
                document.body.appendChild(audioEl);
            }

            /**
             * Updates the media track (audio or video) source for a specific participant
             * and plays the updated track. It checks if the source track needs to be
             * updated and performs the update if necessary, ensuring playback of the
             * media track.
             *
             * @param {string} trackType - Specifies the type of track to update ('audio'
             * or 'video'), allowing the function to dynamically adapt to the track being
             * processed.
             * @param {Object} track - Contains the media track data, including the
             * `persistentTrack` property which holds the actual MediaStreamTrack to be
             * played or updated.
             * @param {string} participantId - Identifies the participant whose media
             * track is being updated.
             */
            startOrUpdateTrack(trackType, track, participantId) {
                // Construct the selector string or ID based on the trackType.
                let selector;
                if (trackType === 'video') {
                    selector = `#video-container-${participantId} video.video-element`;
                } else if (trackType === 'screenVideo') {
                    selector = `#video-container-${participantId} video.screen-video-element`;
                } else {
                    selector = `audio-${participantId}`;
                }

                // Retrieve the specific media element from the DOM.
                let trackEl =
                    trackType === 'video' ?
                    document.querySelector(selector) :
                    document.getElementById(selector);

                if (trackType === 'screenVideo') {
                    const sharedScreenContainer = document.getElementById('shared-screen-container');
                    let screenVideoEl = sharedScreenContainer.querySelector(
                        `video[data-participant="${participantId}"]`);

                    if (!screenVideoEl) {
                        screenVideoEl = document.createElement('video');
                        screenVideoEl.className = 'w-full h-full object-contain bg-black rounded shadow hidden';
                        screenVideoEl.dataset.participant = participantId;
                        screenVideoEl.autoplay = true;
                        screenVideoEl.playsInline = true;
                        sharedScreenContainer.innerHTML =
                            ''; // limpa para sempre mostrar só um compartilhamento de tela
                        sharedScreenContainer.appendChild(screenVideoEl);
                    }

                    document.getElementById('shared-screen-area').classList.remove('hidden');
                    trackEl = screenVideoEl;
                }


                // Error handling if the target media element does not exist.
                if (!trackEl) {
                    console.error(
                        `${trackType} element does not exist for participant: ${participantId}`
                    );
                    return;
                }

                // Check for the need to update the media source. This is determined by
                // checking whether the existing srcObject's tracks include the new
                // persistentTrack. If there are no existing tracks or the new track is not
                // among them, an update is necessary.
                const existingTracks = trackEl.srcObject?.getTracks();
                const needsUpdate = !existingTracks?.includes(track.persistentTrack);

                // Perform the media source update if needed by setting the srcObject of
                // the target element to a new MediaStream containing the provided
                // persistentTrack.
                if (needsUpdate) {
                    trackEl.srcObject = new MediaStream([track.persistentTrack]);

                    // Once the media metadata is loaded, attempts to play the track. Error
                    // handling for play failures is included to catch and log issues such as
                    // autoplay policies blocking playback.
                    trackEl.onloadedmetadata = () => {
                        trackEl.classList.remove('hidden');
                        trackEl
                            .play()
                            .catch((e) =>
                                console.error(
                                    `Error playing ${trackType} for participant ${participantId}:`,
                                    e
                                )
                            );
                    };
                }
            }

            /**
             * Shows or hides the video element for a participant, including managing
             * the visibility of the video based on the track state.
             * @param {Object} track - The video track object.
             * @param {string} participantId - The ID of the participant.
             */
            updateVideoUi(track, participantId, trackType) {
                let videoEl;
                if (trackType === 'video') {
                    videoEl = document
                        .getElementById(`video-container-${participantId}`)
                        .querySelector('video.video-element');
                } else if (trackType === 'screenVideo') {
                    // Pega o vídeo compartilhado que está no container reservado
                    videoEl = document
                        .getElementById('shared-screen-container')
                        .querySelector(`video[data-participant="${participantId}"]`);
                }
                if (!videoEl) {
                    // Se for tela compartilhada e não existe o video, apenas ignora (não é um erro)
                    if (trackType === 'screenVideo') {
                        return;
                    }
                    console.error(`Video element does not exist for ${trackType} of participant: ${participantId}`);
                    return;
                }


                switch (track.state) {
                    case 'off':
                    case 'interrupted':
                    case 'blocked':
                        videoEl.classList.add('hidden');
                        break;
                    case 'playable':
                    default:
                        videoEl.classList.remove('hidden');
                        break;
                }
            }

            /**
             * Cleans up specified media track types (e.g., 'video', 'audio') for a given
             * participant by stopping the tracks and removing their corresponding
             * elements from the DOM. This is essential for properly managing resources
             * when participants leave or change their track states.
             * @param {Array} trackTypes - An array of track types to destroy, e.g.,
             * ['video', 'audio'].
             * @param {string} participantId - The ID of the participant.
             */
            destroyTracks(trackTypes, participantId) {
                trackTypes.forEach((trackType) => {
                    const elementId = `${trackType}-${participantId}`;
                    const element = document.getElementById(elementId);
                    if (element) {
                        element.srcObject = null; // Release media resources
                        element.parentNode.removeChild(element); // Remove element from the DOM
                    }
                });
            }

            /**
             * Toggles the local video track's mute state.
             */
            toggleCamera() {
                this.call.setLocalVideo(!this.call.localVideo());
            }

            /**
             * Toggles the local audio track's mute state.
             */
            toggleMicrophone() {
                this.call.setLocalAudio(!this.call.localAudio());
            }

            /**
             * Updates the UI to reflect the current states of the local participant's
             * camera and microphone.
             * @param {string} trackType - The type of track, either 'video' for cameras
             * or 'audio' for microphones.
             * @param {Object} trackInfo - The track object.
             */
            updateUiForDevicesState(trackType, trackInfo) {
                // For video, set the camera state
                if (trackType === 'video') {
                    document.getElementById('camera-state').textContent = `Camera: ${
        this.call.localVideo() ? 'On' : 'Off'
      }`;
                } else if (trackType === 'audio') {
                    // For audio, set the mic state
                    document.getElementById('mic-state').textContent = `Mic: ${
        this.call.localAudio() ? 'On' : 'Off'
      }`;
                }
            }

            /**
             * Sets up device selectors for cameras and microphones by dynamically
             * populating them with available devices and attaching event listeners to
             * handle device selection changes.
             */
            async setupDeviceSelectors() {
                // Fetch current input devices settings and an array of available devices.
                const selectedDevices = await this.call.getInputDevices();
                const {
                    devices: allDevices
                } = await this.call.enumerateDevices();

                // Element references for camera and microphone selectors.
                const selectors = {
                    videoinput: document.getElementById('camera-selector'),
                    audioinput: document.getElementById('mic-selector'),
                };

                // Prepare selectors by clearing existing options and adding a
                // non-selectable prompt.
                Object.values(selectors).forEach((selector) => {
                    selector.innerHTML = '';
                    const promptOption = new Option(
                        `Select a ${selector.id.includes('camera') ? 'camera' : 'microphone'}`,
                        '',
                        true,
                        true
                    );
                    promptOption.disabled = true;
                    selector.appendChild(promptOption);
                });

                // Create and append options to the selectors based on available devices.
                allDevices.forEach((device) => {
                    if (device.label && selectors[device.kind]) {
                        const isSelected =
                            selectedDevices[device.kind === 'videoinput' ? 'camera' : 'mic']
                            .deviceId === device.deviceId;
                        const option = new Option(
                            device.label,
                            device.deviceId,
                            isSelected,
                            isSelected
                        );
                        selectors[device.kind].appendChild(option);
                    }
                });

                // Listen for user device change requests.
                Object.entries(selectors).forEach(([deviceKind, selector]) => {
                    selector.addEventListener('change', async (e) => {
                        const deviceId = e.target.value;
                        const deviceOptions = {
                            [deviceKind === 'videoinput' ? 'videoDeviceId' :
                                'audioDeviceId'
                            ]: deviceId,
                        };
                        await this.call.setInputDevicesAsync(deviceOptions);
                    });
                });
            }

            /**
             * Updates the UI with the current number of participants.
             * This method combines getting the participant count and updating the UI.
             */
            updateAndDisplayParticipantCount() {
                const participantCount =
                    this.call.participantCounts().present +
                    this.call.participantCounts().hidden;
                document.getElementById(
                    'participant-count'
                ).textContent = `Participants: ${participantCount}`;
            }

            /**
             * Leaves the call and performs necessary cleanup operations like removing
             * video elements.
             */
            async leave() {
                try {
                    await this.call.leave();
                    document.querySelectorAll('#videos video, audio').forEach((el) => {
                        el.srcObject = null; // Release media resources
                        el.remove(); // Remove the element from the DOM
                    });
                } catch (e) {
                    console.error('Leaving failed', e);
                }
            }
        }

        /**
         * Main entry point: Setup and event listener bindings after the DOM is fully
         * loaded.
         */
        document.addEventListener('DOMContentLoaded', async () => {
            const dailyCallManager = new DailyCallManager();

            // Bind the join call action to the join button.
            document
                .getElementById('join-btn')
                .addEventListener('click', async function() {
                    const res = await fetch('/api/create-room', {
                        method: 'POST'
                    });
                    const {
                        url
                    } = await res.json();
                    await dailyCallManager.joinRoom(url);
                });

            // Bind the leave call action to the leave button.
            document.getElementById('leave-btn').addEventListener('click', function() {
                dailyCallManager.leave();
            });
            document.getElementById('leave-btn').addEventListener('click', function() {

            });
        });
    </script>
</body>

</html>
