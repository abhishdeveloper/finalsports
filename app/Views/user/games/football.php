<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penalty Shootout</title>
    <style>
        body { font-family: sans-serif; background: #222; color: white; margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        .header { display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 600px; background: #333; padding: 1rem; border-radius: 8px; margin-bottom: 20px;}
        .game-area { width: 100%; max-width: 600px; background: #28a745; height: 300px; position: relative; border: 5px solid white; border-radius: 8px; overflow: hidden; margin-bottom: 20px; }
        .goal { position: absolute; top: 0; left: 10%; width: 80%; height: 100px; border: 5px solid white; border-top: none; box-sizing: border-box; background: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,255,255,0.2) 10px, rgba(255,255,255,0.2) 20px); }
        .ball { position: absolute; bottom: 20px; left: calc(50% - 15px); width: 30px; height: 30px; background: white; border-radius: 50%; box-shadow: inset -5px -5px 10px rgba(0,0,0,0.5); transition: all 0.5s ease;}
        .goalie { position: absolute; top: 70px; left: calc(50% - 20px); width: 40px; height: 40px; background: red; border-radius: 50%; transition: all 0.5s ease; }

        .controls { display: flex; gap: 10px; width: 100%; max-width: 600px; justify-content: center; }
        .controls button { padding: 10px 20px; font-size: 1.2em; cursor: pointer; border: none; border-radius: 4px; background: #007bff; color: white; }
        .controls button:disabled { background: #555; cursor: not-allowed; }

        .overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 10; }
        .overlay h2 { margin: 0; font-size: 2em; }
    </style>
</head>
<body>

<div class="header">
    <div><strong>Penalty Shootout</strong></div>
    <div>Round: <span id="ui-round">1</span>/3</div>
    <div>Score: <span style="color: yellow;" id="ui-p1-score">0</span> - <span style="color: yellow;" id="ui-p2-score">0</span></div>
</div>

<div class="game-area">
    <div class="goal"></div>
    <div class="goalie" id="goalie"></div>
    <div class="ball" id="ball"></div>

    <div class="overlay" id="status-overlay">
        <h2 id="status-text">Waiting for opponent...</h2>
        <a href="/game/index" id="exit-btn" style="display:none; color: white; margin-top: 20px; background: #dc3545; padding: 10px; text-decoration: none; border-radius: 4px;">Exit</a>
    </div>
</div>

<div style="margin-bottom: 10px;" id="turn-indicator">Connecting...</div>

<div class="controls" id="controls">
    <button onclick="makeMove('left')" disabled>Left</button>
    <button onclick="makeMove('center')" disabled>Center</button>
    <button onclick="makeMove('right')" disabled>Right</button>
</div>

<!-- Need CSRF token for the AJAX POST -->
<input type="hidden" id="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">

<script>
    const roomId = <?php echo $room['id']; ?>;
    const csrfToken = document.getElementById('csrf_token').value;
    let amIPlayer1 = false;
    let isGameOver = false;

    const goalieEl = document.getElementById('goalie');
    const ballEl = document.getElementById('ball');
    const overlay = document.getElementById('status-overlay');
    const statusText = document.getElementById('status-text');
    const turnIndicator = document.getElementById('turn-indicator');
    const controls = document.querySelectorAll('.controls button');

    function setControlsEnabled(enabled) {
        controls.forEach(btn => btn.disabled = !enabled);
    }

    function animateAction(actionStr, isGoalie) {
        const el = isGoalie ? goalieEl : ballEl;
        if (actionStr === 'left') {
            el.style.left = '20%';
            if(!isGoalie) el.style.bottom = '200px';
        } else if (actionStr === 'right') {
            el.style.left = '70%';
            if(!isGoalie) el.style.bottom = '200px';
        } else {
            el.style.left = 'calc(50% - ' + (isGoalie ? '20px' : '15px') + ')';
            if(!isGoalie) el.style.bottom = '200px';
        }
    }

    function resetPositions() {
        goalieEl.style.left = 'calc(50% - 20px)';
        ballEl.style.left = 'calc(50% - 15px)';
        ballEl.style.bottom = '20px';
    }

    function pollStatus() {
        if (isGameOver) return;

        fetch(`/ajaxGame/status/${roomId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                statusText.innerText = data.error;
                return;
            }

            amIPlayer1 = data.am_i_player1;

            if (data.status === 'waiting') {
                overlay.style.display = 'flex';
                statusText.innerText = "Waiting for Player 2...";
                return;
            }

            // Update UI Score
            document.getElementById('ui-p1-score').innerText = data.state.p1_score || 0;
            document.getElementById('ui-p2-score').innerText = data.state.p2_score || 0;
            document.getElementById('ui-round').innerText = Math.min((data.state.p1_score || 0) + (data.state.p2_score || 0) + 1, 3);

            if (data.status === 'completed') {
                isGameOver = true;
                overlay.style.display = 'flex';
                document.getElementById('exit-btn').style.display = 'block';
                if (data.winner_id == <?php echo $user['id']; ?>) {
                    statusText.innerText = "You Won!";
                    statusText.style.color = "gold";
                } else {
                    statusText.innerText = "You Lost.";
                    statusText.style.color = "red";
                }
                setControlsEnabled(false);
                return;
            }

            overlay.style.display = 'none';

            if (data.is_my_turn) {
                turnIndicator.innerText = "Your Turn! (" + (amIPlayer1 ? "Striker" : "Goalie") + ")";
                turnIndicator.style.color = "lightgreen";
                setControlsEnabled(true);
            } else {
                turnIndicator.innerText = "Opponent's Turn...";
                turnIndicator.style.color = "yellow";
                setControlsEnabled(false);
            }
        });
    }

    function makeMove(direction) {
        setControlsEnabled(false);
        turnIndicator.innerText = "Submitting...";

        // Animate locally
        animateAction(direction, !amIPlayer1);

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', direction);

        fetch(`/ajaxGame/move/${roomId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                setControlsEnabled(true);
            } else {
                setTimeout(resetPositions, 1000); // Reset after 1s
                pollStatus();
            }
        });
    }

    // Poll every 2 seconds
    setInterval(pollStatus, 2000);
    pollStatus();
</script>

</body>
</html>
