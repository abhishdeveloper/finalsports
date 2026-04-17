<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Match Questions</title>
    <style>
        body { font-family: sans-serif; background: #e9ecef; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 20px;}
        .card { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .option-input { margin-bottom: 5px; }
    </style>
</head>
<body>

<div class="header">
    <h2>Edit Match: <?php echo Security::escape($match['title']); ?></h2>
    <a href="/adminMatch/index" style="color: white; text-decoration: none;">Back to List</a>
</div>

<div class="card">
    <h3>Existing Questions</h3>
    <?php if (empty($questions)): ?>
        <p>No questions added yet.</p>
    <?php else: ?>
        <ul>
        <?php foreach ($questions as $q): ?>
            <li>
                <strong><?php echo Security::escape($q['question_text']); ?></strong>
                <ul>
                    <?php foreach ($q['options'] as $opt): ?>
                        <li><?php echo Security::escape($opt['option_text']); ?> <?php echo $opt['is_correct'] ? '(Correct)' : ''; ?></li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Add New Question</h3>
    <form action="/adminMatch/addQuestion/<?php echo $match['id']; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">

        <div class="form-group">
            <label>Question Text</label>
            <input type="text" name="question_text" placeholder="e.g. Who will win the toss?" required>
        </div>

        <div class="form-group">
            <label>Options (Provide at least 2)</label>
            <input type="text" name="options[]" class="option-input" placeholder="Option 1" required>
            <input type="text" name="options[]" class="option-input" placeholder="Option 2" required>
            <input type="text" name="options[]" class="option-input" placeholder="Option 3 (Optional)">
            <input type="text" name="options[]" class="option-input" placeholder="Option 4 (Optional)">
        </div>

        <button type="submit">+ Add Question</button>
    </form>
</div>

</body>
</html>
