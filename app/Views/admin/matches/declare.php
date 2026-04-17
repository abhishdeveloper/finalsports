<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declare Result - <?php echo Security::escape($match['title']); ?></title>
    <style>
        body { font-family: sans-serif; background: #e9ecef; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 20px;}
        .card { background: white; padding: 2rem; border-radius: 8px; max-width: 800px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .question { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .question p { font-weight: bold; }
        .option { margin-bottom: 5px; }
        button { padding: 10px 15px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1.1em;}
    </style>
</head>
<body>

<div class="header">
    <h2>Declare Correct Answers: <?php echo Security::escape($match['title']); ?></h2>
    <a href="/adminMatch/results" style="color: white; text-decoration: none;">Back</a>
</div>

<div class="card">
    <form action="/adminMatch/declare/<?php echo $match['id']; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="question">
                <p><?php echo ($index + 1) . '. ' . Security::escape($q['question_text']); ?></p>
                <?php foreach ($q['options'] as $opt): ?>
                    <div class="option">
                        <label>
                            <input type="radio" name="correct_options[<?php echo $q['id']; ?>]" value="<?php echo $opt['id']; ?>" required>
                            <?php echo Security::escape($opt['option_text']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <!-- We'll change this submit button later when hooking up the ranking engine -->
        <button type="submit">Save Answers & Rank Users</button>
    </form>
</div>

</body>
</html>
