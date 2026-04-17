<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Games</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem; border-radius: 8px; margin-bottom: 20px;}
        .card { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 0 5px rgba(0,0,0,0.1); text-align: center; }
        .btn { padding: 0.5rem 1rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;}
    </style>
</head>
<body>

<div class="header">
    <h2>Mini Games (1v1)</h2>
    <div>
        <span style="margin-right: 15px;">Coins: <strong><?php echo Security::escape($user['coins']); ?></strong> 🪙</span>
        <a href="/user/index" class="btn" style="background: #6c757d;">Back Home</a>
    </div>
</div>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php foreach ($games as $game): ?>
        <div class="card" style="width: 250px;">
            <h3><?php echo Security::escape($game['name']); ?></h3>
            <p>Play 1v1 and win 80% of the pot!</p>
            <a href="/game/lobby/<?php echo $game['slug']; ?>" class="btn">Enter Lobby</a>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
