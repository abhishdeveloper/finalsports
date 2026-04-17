<?php

class GameController extends Controller {
    private $userModel;
    private $gameModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->gameModel = $this->model('GameModel');

        if (!$this->userModel->isLoggedIn()) {
            header('Location: /');
            exit;
        }
    }

    public function index() {
        $user = $this->userModel->getById(Session::get('user_id'));
        $games = $this->gameModel->getAllGames();
        $this->view('user/games/index', ['user' => $user, 'games' => $games]);
    }

    public function lobby($slug) {
        $user = $this->userModel->getById(Session::get('user_id'));
        $game = $this->gameModel->getGameBySlug($slug);

        if (!$game) {
            die("Game not found.");
        }

        $rooms = $this->gameModel->getWaitingRooms($game['id']);

        $this->view('user/games/lobby', [
            'user' => $user,
            'game' => $game,
            'rooms' => $rooms,
            'error' => $_GET['error'] ?? null
        ]);
    }

    public function create($slug) {
        $user = $this->userModel->getById(Session::get('user_id'));
        $game = $this->gameModel->getGameBySlug($slug);

        if (!$game) {
            die("Game not found.");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $entryFee = (int)$_POST['entry_fee'];
            if ($entryFee <= 0) {
                header('Location: /game/lobby/' . $slug . '?error=Invalid+amount');
                exit;
            }

            // Game specific initial state setup
            $initialState = [];
            if ($slug == 'football') {
                $initialState = ['round' => 1, 'p1_score' => 0, 'p2_score' => 0, 'turn_action' => null];
            }

            $roomId = $this->gameModel->createRoom($game['id'], $user['id'], $entryFee, $initialState);

            if ($roomId) {
                header('Location: /game/play/' . $roomId);
                exit;
            } else {
                header('Location: /game/lobby/' . $slug . '?error=Not+enough+coins');
                exit;
            }
        }
    }

    public function join($roomId) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $userId = Session::get('user_id');
            $result = $this->gameModel->joinRoom($roomId, $userId);

            if ($result === true) {
                header('Location: /game/play/' . $roomId);
                exit;
            } elseif ($result === 'insufficient_funds') {
                $room = $this->gameModel->getRoomById($roomId);
                $game = $this->gameModel->getGameBySlug('football'); // Assuming simple fallback or fetch game
                header('Location: /game/lobby/football?error=Not+enough+coins');
                exit;
            } else {
                header('Location: /game/index?error=Cannot+join+room');
                exit;
            }
        }
    }

    public function play($roomId) {
        $user = $this->userModel->getById(Session::get('user_id'));
        $room = $this->gameModel->getRoomById($roomId);

        if (!$room) {
            die("Room not found.");
        }

        // Must be a participant
        if ($room['player1_id'] != $user['id'] && $room['player2_id'] != $user['id']) {
            die("You are not part of this game.");
        }

        $gameSlug = 'football'; // You would normally join with game table to get slug

        $this->view('user/games/' . $gameSlug, ['user' => $user, 'room' => $room]);
    }
}
