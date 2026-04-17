<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f4f4; margin: 0; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 320px; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9em; text-align: center; }
        input[type="password"] { width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.5rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h2 style="margin-top: 0;">Set New Password</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo Security::escape($error); ?></div>
    <?php endif; ?>

    <form action="/auth/reset/<?php echo Security::escape($token); ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">

        <label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">New Password:</label>
        <input type="password" id="password" name="password" required minlength="6">

        <label for="confirm_password" style="display: block; margin-bottom: 5px; font-weight: bold;">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">

        <button type="submit">Update Password</button>
    </form>
</div>

</body>
</html>
