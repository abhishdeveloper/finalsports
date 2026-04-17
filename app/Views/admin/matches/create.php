<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Match</title>
    <style>
        body { font-family: sans-serif; background: #e9ecef; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 20px;}
        .content { background: white; padding: 2rem; border-radius: 8px; max-width: 600px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="number"], input[type="datetime-local"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<div class="header">
    <h2>Create New Match</h2>
    <a href="/adminMatch/index" class="btn" style="color: white; text-decoration: none;">Back</a>
</div>

<div class="content">
    <?php if (isset($error)): ?><div style="color: red; margin-bottom: 10px;"><?php echo Security::escape($error); ?></div><?php endif; ?>

    <form action="/adminMatch/create" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">

        <div class="form-group">
            <label>Match Title (e.g. India vs Australia)</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Sport</label>
            <input type="text" name="sport" required>
        </div>
        <div class="form-group">
            <label>Entry Fee (Coins)</label>
            <input type="number" name="entry_fee" value="50" min="0" required>
        </div>
        <div class="form-group">
            <label>End Time (Prediction Closes)</label>
            <input type="datetime-local" name="end_time" required>
        </div>

        <button type="submit">Create Match</button>
    </form>
</div>

</body>
</html>
