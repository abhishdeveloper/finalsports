<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Security::escape($game['name']); ?> Lobby</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem; border-radius: 8px; margin-bottom: 20px;}
        .container { display: flex; gap: 20px; }
        .card { background: white; padding: 1.5rem; border-radius: 8px; flex: 1; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        .btn { padding: 0.5rem 1rem; background: #28a745; color: white; text-decoration: none; border-radius: 4px; display: inline-block; cursor: pointer; border: none; }
        .room-item { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        input[type="number"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100px; }
    </style>
</head>
<body>

<div class="header">
    <h2><?php echo Security::escape($game['name']); ?> Lobby</h2>
    <div>
        <span style="margin-right: 15px;">Coins: <strong><?php echo Security::escape($user['coins']); ?></strong> 🪙</span>
        <a href="/game/index" class="btn" style="background: #6c757d;">Back to Games</a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
        <?php echo Security::escape($error); ?>
    </div>
<?php endif; ?>

<div class="container">
    <div class="card">
        <h3>Create a Match</h3>
        <p>Set an entry fee. If you win, you get your fee back plus 60% of the opponent's fee (20% platform fee).</p>
        <form action="/game/create/<?php echo $game['slug']; ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
            <label>Entry Fee:</label>
            <input type="number" name="entry_fee" min="10" value="50" required>
            <button type="submit" class="btn">Create & Wait</button>
        </form>
    </div>

    <div class="card">
        <h3>Waiting Rooms</h3>
        <?php if (empty($rooms)): ?>
            <p>No waiting rooms. Create one!</p>
        <?php else: ?>
            <?php foreach ($rooms as $room): ?>
                <div class="room-item">
                    <div>
                        <strong>Host:</strong> <?php echo Security::escape($room['p1_email']); ?><br>
                        <strong>Fee:</strong> <?php echo Security::escape($room['entry_fee']); ?> 🪙
                    </div>
                    <?php if ($room['player1_id'] != $user['id']): ?>
                        <form action="/game/join/<?php echo $room['id']; ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                            <button type="submit" class="btn" style="background: #007bff;">Join</button>
                        </form>
                    <?php else: ?>
                        <span style="color: #888;">Waiting for player...</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Refresh page occasionally to see new rooms
    setTimeout(() => {
        window.location.reload();
    }, 10000);
</script>

</body>
</html>
