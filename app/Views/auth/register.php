<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - College Sports App</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f4f4; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        .error { color: red; margin-bottom: 1rem; font-size: 0.9em; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.5rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        .link { text-align: center; margin-top: 1rem; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="container">
    <h2>Register</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo Security::escape($error); ?></div>
    <?php endif; ?>

    <form action="/auth/register" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">

        <label for="username">Username (Optional):</label>
        <input type="text" id="username" name="username">

        <label for="login_id">Email or Phone:</label>
        <input type="text" id="login_id" name="login_id" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Register</button>
    </form>

    <div class="link">
        Already have an account? <a href="/auth/login">Login</a>
    </div>
</div>

</body>
</html>
