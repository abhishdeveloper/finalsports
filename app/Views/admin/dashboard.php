<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - College Sports App</title>
    <style>
        body { font-family: sans-serif; background: #e9ecef; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 1rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px;}
        .content { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .btn { padding: 0.5rem 1rem; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #c82333; }
    </style>
</head>
<body>

<div class="header">
    <h2>Admin Dashboard</h2>
    <a href="/auth/logout" class="btn">Logout</a>
</div>

<div class="content">
    <h3>Welcome, Administrator!</h3>
    <p>This is the admin dashboard. Here you will manage users, predictions, and mini-games.</p>
</div>

</body>
</html>
