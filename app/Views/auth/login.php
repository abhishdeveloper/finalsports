<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - College Sports App</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f4f4; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        .error { color: red; margin-bottom: 1rem; font-size: 0.9em; }
        .success { color: green; margin-bottom: 1rem; font-size: 0.9em; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .link { text-align: center; margin-top: 1rem; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="container">
    <h2>Login</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo Security::escape($error); ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="success"><?php echo Security::escape($success); ?></div>
    <?php endif; ?>

    <form action="/auth/login" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">

        <label for="login_id">Username, Email, or Phone:</label>
        <input type="text" id="login_id" name="login_id" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <div class="link" style="margin-bottom: 10px;">
        <a href="/auth/forgot">Forgot Password?</a>
    </div>

    <div class="link">
        Don't have an account? <a href="/auth/register">Register</a>
    </div>
</div>

</body>
</html>
