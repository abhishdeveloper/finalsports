<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        .container { display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], input[type="email"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .btn-back { display: inline-block; padding: 8px 12px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

<div class="header">
    <h2>Account Settings</h2>
    <a href="/user/index" class="btn-back">Back Home</a>
</div>

<?php if (isset($error)): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
        <?php echo Security::escape($error); ?>
    </div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
        <?php echo Security::escape($success); ?>
    </div>
<?php endif; ?>

<div class="container">
    <!-- Profile Form -->
    <div class="card">
        <h3>Update Profile</h3>
        <form action="/user/settings" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="update_profile">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo Security::escape($user['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo Security::escape($user['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo Security::escape($user['phone'] ?? ''); ?>">
            </div>

            <button type="submit">Save Profile</button>
        </form>
    </div>

    <!-- Password Form -->
    <div class="card">
        <h3>Change Password</h3>
        <form action="/user/settings" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" style="background: #28a745;">Change Password</button>
        </form>
    </div>
</div>

</body>
</html>
