<?php

class AjaxGameController extends Controller {
    private $gameModel;
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->gameModel = $this->model('GameModel');

        // Only allow AJAX requests (basic check)
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            die(json_encode(['error' => 'Invalid request']));
        }

        if (!$this->userModel->isLoggedIn()) {
            die(json_encode(['error' => 'Not authenticated']));
        }

        header('Content-Type: application/json');
    }

    public function status($roomId) {
        $userId = Session::get('user_id');
        $room = $this->gameModel->getRoomById($roomId);

        if (!$room) {
            echo json_encode(['error' => 'Room not found']);
            return;
        }

        if ($room['player1_id'] != $userId && $room['player2_id'] != $userId) {
            echo json_encode(['error' => 'Not your room']);
            return;
        }

        $state = json_decode($room['game_state'], true) ?: [];

        // Determine roles relative to the requesting user
        $isPlayer1 = ($room['player1_id'] == $userId);

        echo json_encode([
            'status' => $room['status'],
            'is_my_turn' => ($room['current_turn'] == $userId),
            'am_i_player1' => $isPlayer1,
            'winner_id' => $room['winner_id'],
            'state' => $state
        ]);
    }

    public function move($roomId) {
        $userId = Session::get('user_id');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Invalid method']);
            return;
        }

        // We could require CSRF here, but for rapid AJAX polling in a game,
        // standard auth cookie validation + custom header is usually sufficient.
        // For bank-grade, we verify token if passed via POST.
        if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
            echo json_encode(['error' => 'CSRF validation failed']);
            return;
        }

        $room = $this->gameModel->getRoomById($roomId);
        if (!$room || $room['status'] !== 'playing') {
            echo json_encode(['error' => 'Game is not active']);
            return;
        }

        if ($room['current_turn'] != $userId) {
            echo json_encode(['error' => 'Not your turn']);
            return;
        }

        $state = json_decode($room['game_state'], true);
        $action = $_POST['action'] ?? null;

        if (!$action) {
             echo json_encode(['error' => 'No action provided']);
             return;
        }

        // --- FOOTBALL LOGIC ---
        // Player 1 = Striker (first to pick), Player 2 = Goalie
        // We do 3 rounds.
        if ($room['game_id'] == 1) { // Assuming 1 is football
            $isPlayer1 = ($room['player1_id'] == $userId);

            if ($isPlayer1) {
                // Striker picks
                $state['turn_action'] = $action; // left, center, right
                $nextTurn = $room['player2_id'];
            } else {
                // Goalie picks
                $strikerAction = $state['turn_action'];
                $goalieAction = $action;

                // Resolve round
                if ($strikerAction !== $goalieAction) {
                    $state['p1_score']++;
                } else {
                    // Save! No points, or point to p2 if you want. Let's say striker gets point if different.
                    $state['p2_score']++; // Give goalie a point for saving
                }

                $state['round']++;
                $state['turn_action'] = null;
                $nextTurn = $room['player1_id'];
            }

            // Check End Game (Best of 3 rounds, so first to 2 points wins)
            $winnerId = null;
            if ($state['p1_score'] == 2 || $state['p2_score'] == 2) {
                $winnerId = ($state['p1_score'] == 2) ? $room['player1_id'] : $room['player2_id'];
                $this->gameModel->endGame($roomId, $winnerId);
                echo json_encode(['success' => true, 'game_over' => true]);
                return;
            }

            // Update State
            $this->gameModel->updateGameState($roomId, $state, $nextTurn);
            echo json_encode(['success' => true]);
            return;
        }

        echo json_encode(['error' => 'Unknown game']);
    }
}
