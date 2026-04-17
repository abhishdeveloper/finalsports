<?php

class MatchController extends Controller {
    private $userModel;
    private $matchModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->matchModel = $this->model('MatchModel');

        if (!$this->userModel->isLoggedIn()) {
            header('Location: /');
            exit;
        }
    }

    public function list() {
        $user = $this->userModel->getById(Session::get('user_id'));
        $matches = $this->matchModel->getOpenMatches();
        $this->view('user/matches/list', ['matches' => $matches, 'user' => $user]);
    }

    public function predict($matchId) {
        $user = $this->userModel->getById(Session::get('user_id'));
        $match = $this->matchModel->getMatchById($matchId);

        if (!$match) {
            die("Match not found.");
        }

        // Check if user already participated
        if ($this->matchModel->hasUserParticipated($matchId, $user['id'])) {
            die("You have already submitted predictions for this match.");
        }

        // Handle submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            // Server-side time check
            if (strtotime($match['end_time']) < time()) {
                die("Predictions for this match are closed.");
            }

            if ($user['coins'] < $match['entry_fee']) {
                $error = "Not enough coins to enter.";
            } else {
                $predictions = $_POST['predictions'] ?? [];
                $questions = $this->matchModel->getMatchQuestionsWithOptions($matchId);

                if (count($predictions) !== count($questions)) {
                    $error = "Please answer all questions.";
                } else {
                    // Begin transaction logic conceptually in model
                    $success = $this->matchModel->submitPrediction($user['id'], $matchId, $match['entry_fee'], $predictions);
                    if ($success) {
                        header('Location: /user/index?success=prediction_submitted');
                        exit;
                    } else {
                        $error = "Error submitting prediction.";
                    }
                }
            }
        }

        $questions = $this->matchModel->getMatchQuestionsWithOptions($matchId);
        $this->view('user/matches/predict', [
            'match' => $match,
            'questions' => $questions,
            'user' => $user,
            'error' => $error ?? null
        ]);
    }
}
