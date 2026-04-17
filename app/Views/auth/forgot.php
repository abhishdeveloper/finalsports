<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f4f4; margin: 0; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 320px; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9em; text-align: center; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9em; text-align: center; }
        input[type="email"] { width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .link { text-align: center; margin-top: 1rem; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="container">
    <h2 style="margin-top: 0;">Reset Password</h2>
    <p style="font-size: 0.9em; color: #555;">Enter your email address and we'll send you a link to reset your password.</p>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo Security::escape($error); ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="success"><?php echo Security::escape($success); ?></div>
    <?php else: ?>
        <form action="/auth/forgot" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">

            <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Send Reset Link</button>
        </form>
    <?php endif; ?>

    <div class="link">
        <a href="/auth/login">Back to Login</a>
    </div>
</div>

</body>
</html>
