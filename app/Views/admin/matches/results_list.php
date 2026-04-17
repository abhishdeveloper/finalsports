<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awaited Matches - Admin</title>
    <style>
        body { font-family: sans-serif; background: #e9ecef; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 20px;}
        .content { background: white; padding: 2rem; border-radius: 8px; }
        .btn { padding: 0.5rem 1rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    </style>
</head>
<body>

<div class="header">
    <h2>Matches Awaiting Results</h2>
    <a href="/admin/index" class="btn" style="background: #6c757d;">Back to Dashboard</a>
</div>

<div class="content">
    <?php if (isset($_GET['msg'])): ?>
        <p style="color: green;"><?php echo Security::escape($_GET['msg']); ?></p>
    <?php endif; ?>

    <?php if (empty($matches)): ?>
        <p>No matches currently awaiting results.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Sport</th>
                    <th>End Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matches as $match): ?>
                <tr>
                    <td><?php echo $match['id']; ?></td>
                    <td><?php echo Security::escape($match['title']); ?></td>
                    <td><?php echo Security::escape($match['sport']); ?></td>
                    <td><?php echo Security::escape($match['end_time']); ?></td>
                    <td>
                        <a href="/adminMatch/declare/<?php echo $match['id']; ?>" class="btn" style="background: #dc3545;">Declare Result</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
