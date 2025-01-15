<!DOCTYPE html>
<html>
<head>
    <title>Football Match Live Score</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen font-['Poppins']">
    <div class="max-w-4xl mx-auto pt-10 px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Live Football Match</h1>
            <div class="text-sm text-gray-600" id="match-status">Loading...</div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-8">
                <div class="flex justify-between items-center">
                    <div class="text-center flex-1">
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                            <img src="{{ asset('images/manunited.jpg') }}" alt="Manchester United" class="w-24 h-24 mx-auto mb-4">
                            <div class="text-white text-2xl font-semibold mb-2" id="team-a">Team A</div>
                            <div class="text-5xl font-bold text-white" id="score-a">0</div>
                        </div>
                    </div>

                    <div class="px-8">
                        <div class="text-white/50 text-xl font-semibold">VS</div>
                    </div>

                    <div class="text-center flex-1">
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                            <img src="{{ asset('images/liverpool.jpg') }}" alt="Liverpool" class="w-24 h-24 mx-auto mb-4">
                            <div class="text-white text-2xl font-semibold mb-2" id="team-b">Team B</div>
                            <div class="text-5xl font-bold text-white" id="score-b">0</div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="p-6">
                <div class="flex justify-between items-center text-sm text-gray-600">
                    <div>
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full">LIVE</span>
                    </div>
                    <div id="match-timer" class="font-semibold"></div>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center">
            <div class="text-red-500 hidden rounded-lg p-4 bg-red-50 border border-red-200" id="error-message">
                <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Connection lost. Attempting to reconnect...
            </div>
        </div>
    </div>

    <script>
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            encrypted: true
        });

        const channel = pusher.subscribe('football-match');

        channel.bind('score.update', function(data) {
            updateMatchDisplay(data.matchData);
        });
        

        pusher.connection.bind('state_change', function(states) {
            const errorMessage = document.getElementById('error-message');
            if (states.current === 'disconnected' || states.current === 'failed') {
                errorMessage.classList.remove('hidden');
            } else if (states.current === 'connected') {
                errorMessage.classList.add('hidden');
            }
        });

        function updateMatchDisplay(matchData) {
            document.getElementById('team-a').textContent = matchData.team_a;
            document.getElementById('team-b').textContent = matchData.team_b;
            document.getElementById('score-a').textContent = matchData.score_a;
            document.getElementById('score-b').textContent = matchData.score_b;
            document.getElementById('match-status').textContent = matchData.status;
            document.getElementById('match-timer').textContent = `${matchData.match_time}'`;
        }

        let currentTime = 0;
        setInterval(() => {
            currentTime += 1;
            document.getElementById('match-timer').textContent = `${currentTime}'`;
        }, 60000); 

        fetch('/api/match-data')
            .then(response => response.json())
            .then(data => {
                updateMatchDisplay(data);
                currentTime = parseInt(data.match_time) || 0;
            })
            .catch(error => {
                console.error('Error loading initial match data:', error);
                document.getElementById('match-status').textContent = 'Error loading match data';
            });
    </script>
</body>
</html>