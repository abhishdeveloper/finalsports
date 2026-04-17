<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predict - <?php echo Security::escape($match['title']); ?></title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem; border-radius: 8px; margin-bottom: 20px;}
        .card { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
        .question { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .question p { font-weight: bold; }
        .option { margin-bottom: 5px; }
        button { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 1.1em;}
    </style>
</head>
<body>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;">
        <h2><?php echo Security::escape($match['title']); ?></h2>
        <div>
            <p><strong>Your Coins:</strong> <?php echo Security::escape($user['coins']); ?></p>
            <p><strong>Entry Fee:</strong> <?php echo Security::escape($match['entry_fee']); ?></p>
            <p><strong>Time Left:</strong> <span id="timer" data-time="<?php echo strtotime($match['end_time']); ?>">Loading...</span></p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div style="color: red; margin-bottom: 15px;"><?php echo Security::escape($error); ?></div>
    <?php endif; ?>

    <form id="predictionForm" action="/match/predict/<?php echo $match['id']; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="question">
                <p><?php echo ($index + 1) . '. ' . Security::escape($q['question_text']); ?></p>
                <?php foreach ($q['options'] as $opt): ?>
                    <div class="option">
                        <label>
                            <input type="radio" name="predictions[<?php echo $q['id']; ?>]" value="<?php echo $opt['id']; ?>" required>
                            <?php echo Security::escape($opt['option_text']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" id="submitBtn">Submit Predictions (-<?php echo Security::escape($match['entry_fee']); ?> Coins)</button>
    </form>

    <div style="text-align: center; margin-top: 15px;">
        <a href="/match/list" style="color: #666;">Cancel</a>
    </div>
</div>

<script>
    function updateTimer() {
        const timerEl = document.getElementById('timer');
        const submitBtn = document.getElementById('submitBtn');
        const now = Math.floor(Date.now() / 1000);
        const targetTime = parseInt(timerEl.getAttribute('data-time'));
        const diff = targetTime - now;

        if (diff <= 0) {
            timerEl.innerHTML = "<span style='color:red;'>Time is up!</span>";
            submitBtn.disabled = true;
            submitBtn.style.background = "#ccc";
            submitBtn.innerText = "Predictions Closed";
        } else {
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;
            timerEl.innerText = `${hours}h ${minutes}m ${seconds}s`;
        }
    }

    setInterval(updateTimer, 1000);
    updateTimer();
</script>

</body>
</html>
