<!DOCTYPE html>
<html>
<head>
    <title>Elite Football Match Live Score</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        @keyframes scorePulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .score-change {
            animation: scorePulse 0.5s ease-in-out;
        }
        .stadium-bg {
            background-image: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(0,0,0,0.7)),
                            url('https://images.unsplash.com/photo-1522778119026-d647f0596c20?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwxfDB8MXxyYW5kb218MHx8c29jY2VyLXN0YWRpdW18fHx8fHwxNjk0MTYyMjc4&ixlib=rb-4.0.3&q=80&utm_campaign=api-credit&utm_medium=referral&utm_source=unsplash_source&w=1080');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 to-black font-['Inter']">
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-12">
            <h1 class="font-['Montserrat'] text-4xl font-black text-white mb-2 tracking-tight">ELITE LEAGUE</h1>
            <div class="text-blue-400 font-medium tracking-wider">LIVE MATCH DAY</div>
        </div>
        <div class="max-w-5xl mx-auto">
            <div class="stadium-bg rounded-3xl overflow-hidden shadow-2xl">
                <div class="backdrop-blur-sm bg-black/30 p-8">
                    <div class="flex justify-between items-center mb-8">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                <span class="ml-2 text-red-400 font-semibold">LIVE</span>
                            </div>
                            <div class="text-gray-400">•</div>
                            <div class="text-gray-300" id="match-status">Loading...</div>
                        </div>
                        <div class="bg-white/10 rounded-full px-4 py-1 backdrop-blur-md">
                            <span class="text-white font-semibold" id="match-timer">0'</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-8">
                        <div class="text-center flex-1">
                            <div class="bg-white/5 rounded-2xl p-6 backdrop-blur-md border border-white/10 transition-all duration-300 hover:bg-white/10">
                                <div class="text-white text-3xl font-['Montserrat'] font-bold mb-4" id="team-a">Team A</div>
                                <div class="text-7xl font-['Montserrat'] font-black text-white transition-all" id="score-a">0</div>
                            </div>
                        </div>

                        <div class="px-8">
                            <div class="text-white/30 text-2xl font-['Montserrat'] font-bold">VS</div>
                        </div>

                        <div class="text-center flex-1">
                            <div class="bg-white/5 rounded-2xl p-6 backdrop-blur-md border border-white/10 transition-all duration-300 hover:bg-white/10">
                                <div class="text-white text-3xl font-['Montserrat'] font-bold mb-4" id="team-b">Team B</div>
                                <div class="text-7xl font-['Montserrat'] font-black text-white transition-all" id="score-b">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 text-center text-white/80">
                        <div class="bg-white/5 rounded-xl p-3 backdrop-blur-md">
                            <div class="text-sm text-white/60 mb-1">Possession</div>
                            <div class="font-semibold">52% - 48%</div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 backdrop-blur-md">
                            <div class="text-sm text-white/60 mb-1">Shots on Goal</div>
                            <div class="font-semibold">7 - 5</div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 backdrop-blur-md">
                            <div class="text-sm text-white/60 mb-1">Corner Kicks</div>
                            <div class="font-semibold">4 - 3</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center transition-all duration-300">
                <div class="text-red-400 hidden rounded-xl p-4 bg-red-900/50 backdrop-blur-md border border-red-500/20" id="error-message">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Connection lost. Attempting to reconnect...
                </div>
            </div>
        </div>
    </div>

    <script>
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            encrypted: true
        });

        const channel = pusher.subscribe('football-match');

        let previousScores = { a: 0, b: 0 };
        function animateScore(elementId) {
            const element = document.getElementById(elementId);
            element.classList.add('score-change');
            setTimeout(() => {
                element.classList.remove('score-change');
            }, 500);
        }

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
            
            if (previousScores.a !== matchData.score_a) {
                animateScore('score-a');
            }
            if (previousScores.b !== matchData.score_b) {
                animateScore('score-b');
            }
            
            document.getElementById('score-a').textContent = matchData.score_a;
            document.getElementById('score-b').textContent = matchData.score_b;
            document.getElementById('match-status').textContent = matchData.status;
            document.getElementById('match-timer').textContent = `${matchData.match_time}'`;

            previousScores = { 
                a: matchData.score_a, 
                b: matchData.score_b 
            };
        }
        fetch('/api/match-data')
            .then(response => response.json())
            .then(data => {
                updateMatchDisplay(data);
                previousScores = {
                    a: data.score_a,
                    b: data.score_b
                };
            })
            .catch(error => {
                console.error('Error loading initial match data:', error);
                document.getElementById('match-status').textContent = 'Error loading match data';
            });
    </script>
</body>
</html>