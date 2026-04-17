<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Matches</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem; border-radius: 8px; margin-bottom: 20px;}
        .match-card { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        .btn { padding: 0.5rem 1rem; background: #28a745; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;}
    </style>
</head>
<body>

<div class="header">
    <h2>Open Matches</h2>
    <div>
        <span style="margin-right: 15px;">Coins: <strong><?php echo Security::escape($user['coins']); ?></strong> 🪙</span>
        <a href="/user/index" class="btn" style="background: #6c757d;">Back Home</a>
    </div>
</div>

<div>
    <?php if (empty($matches)): ?>
        <p>No open matches at the moment. Check back later!</p>
    <?php else: ?>
        <?php foreach ($matches as $match): ?>
            <div class="match-card">
                <h3><?php echo Security::escape($match['title']); ?> (<?php echo Security::escape($match['sport']); ?>)</h3>
                <p><strong>Entry Fee:</strong> <?php echo Security::escape($match['entry_fee']); ?> Coins</p>
                <p><strong>Closes in:</strong> <span class="timer" data-time="<?php echo strtotime($match['end_time']); ?>">Loading...</span></p>
                <a href="/match/predict/<?php echo $match['id']; ?>" class="btn">Predict Now</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    function updateTimers() {
        const timers = document.querySelectorAll('.timer');
        const now = Math.floor(Date.now() / 1000);

        timers.forEach(timer => {
            const targetTime = parseInt(timer.getAttribute('data-time'));
            const diff = targetTime - now;

            if (diff <= 0) {
                timer.innerHTML = "<span style='color:red;'>Closed</span>";
            } else {
                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = diff % 60;
                timer.innerText = `${hours}h ${minutes}m ${seconds}s`;
            }
        });
    }

    setInterval(updateTimers, 1000);
    updateTimers();
</script>

</body>
</html>
