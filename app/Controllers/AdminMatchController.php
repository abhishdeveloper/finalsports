<?php

class AdminMatchController extends Controller {
    private $userModel;
    private $matchModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->matchModel = $this->model('MatchModel');

        if (!$this->userModel->isLoggedIn() || !$this->userModel->isAdmin()) {
            header('Location: /');
            exit;
        }
    }

    public function index() {
        // Automatically check and update statuses of matches whose time has passed
        $this->matchModel->markMatchesAwaited();

        $matches = $this->matchModel->getAllMatchesAdmin();
        $this->view('admin/matches/index', ['matches' => $matches]);
    }

    public function results() {
        $matches = $this->matchModel->getAwaitedMatches();
        $this->view('admin/matches/results_list', ['matches' => $matches]);
    }

    public function declare($matchId) {
        $match = $this->matchModel->getMatchById($matchId);
        if (!$match || $match['status'] !== 'awaited') {
            header('Location: /adminMatch/results');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $correctOptions = $_POST['correct_options'] ?? [];
            foreach ($correctOptions as $questionId => $optionId) {
                $this->matchModel->setCorrectOption($questionId, $optionId);
            }

            $this->matchModel->processResultsAndRank($matchId);

            header('Location: /adminMatch/results?msg=Match+Completed+and+Coins+Distributed');
            exit;
        }

        $questions = $this->matchModel->getMatchQuestionsWithOptions($matchId);
        $this->view('admin/matches/declare', ['match' => $match, 'questions' => $questions]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $title = trim($_POST['title']);
            $sport = trim($_POST['sport']);
            $entryFee = (int)$_POST['entry_fee'];
            $endTime = $_POST['end_time'];

            if (empty($title) || empty($sport) || empty($endTime)) {
                $this->view('admin/matches/create', ['error' => 'Please fill out all fields.']);
                return;
            }

            $matchId = $this->matchModel->createMatch($title, $sport, $entryFee, $endTime);
            if ($matchId) {
                header('Location: /adminMatch/edit/' . $matchId);
                exit;
            }
        }

        $this->view('admin/matches/create');
    }

    public function edit($matchId) {
        $match = $this->matchModel->getMatchById($matchId);
        if (!$match) {
            header('Location: /adminMatch/index');
            exit;
        }

        $questions = $this->matchModel->getMatchQuestionsWithOptions($matchId);

        $this->view('admin/matches/edit', ['match' => $match, 'questions' => $questions]);
    }

    public function addQuestion($matchId) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $questionText = trim($_POST['question_text']);
            $options = $_POST['options'] ?? [];

            if (!empty($questionText) && count($options) >= 2) {
                $questionId = $this->matchModel->addQuestion($matchId, $questionText);
                foreach ($options as $optionText) {
                    if (!empty(trim($optionText))) {
                        $this->matchModel->addOption($questionId, trim($optionText));
                    }
                }
            }
        }
        header('Location: /adminMatch/edit/' . $matchId);
        exit;
    }
}
