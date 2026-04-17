<?php

class GameModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllGames() {
        $stmt = $this->db->query("SELECT * FROM games WHERE status = 'active'");
        return $stmt->fetchAll();
    }

    public function getGameBySlug($slug) {
        $stmt = $this->db->prepare("SELECT * FROM games WHERE slug = :slug AND status = 'active' LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }

    public function getWaitingRooms($gameId) {
        $stmt = $this->db->prepare("SELECT r.*, u.email as p1_email FROM game_rooms r JOIN users u ON r.player1_id = u.id WHERE r.game_id = :game_id AND r.status = 'waiting'");
        $stmt->execute(['game_id' => $gameId]);
        return $stmt->fetchAll();
    }

    public function getRoomById($roomId) {
        $stmt = $this->db->prepare("SELECT * FROM game_rooms WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $roomId]);
        return $stmt->fetch();
    }

    public function createRoom($gameId, $player1Id, $entryFee, $initialState) {
        try {
            $this->db->beginTransaction();

            // Deduct coins from Player 1
            $stmt = $this->db->prepare("UPDATE users SET coins = coins - :fee WHERE id = :id AND coins >= :fee");
            $stmt->execute(['fee' => $entryFee, 'id' => $player1Id]);

            if ($stmt->rowCount() == 0) {
                $this->db->rollBack();
                return false; // Not enough coins
            }

            // Create room
            $stmt = $this->db->prepare("INSERT INTO game_rooms (game_id, player1_id, entry_fee, game_state) VALUES (:game_id, :player1_id, :entry_fee, :game_state)");
            $stmt->execute([
                'game_id' => $gameId,
                'player1_id' => $player1Id,
                'entry_fee' => $entryFee,
                'game_state' => json_encode($initialState)
            ]);
            $roomId = $this->db->lastInsertId();

            $this->db->commit();
            return $roomId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function joinRoom($roomId, $player2Id) {
        try {
            $this->db->beginTransaction();

            // Lock room row
            $stmt = $this->db->prepare("SELECT * FROM game_rooms WHERE id = :id AND status = 'waiting' FOR UPDATE");
            $stmt->execute(['id' => $roomId]);
            $room = $stmt->fetch();

            if (!$room) {
                $this->db->rollBack();
                return false; // Room full or doesn't exist
            }

            if ($room['player1_id'] == $player2Id) {
                 $this->db->rollBack();
                 return false; // Can't play against yourself
            }

            // Deduct coins from Player 2
            $stmt = $this->db->prepare("UPDATE users SET coins = coins - :fee WHERE id = :id AND coins >= :fee");
            $stmt->execute(['fee' => $room['entry_fee'], 'id' => $player2Id]);

            if ($stmt->rowCount() == 0) {
                $this->db->rollBack();
                return 'insufficient_funds';
            }

            // Update room to playing
            $stmt = $this->db->prepare("UPDATE game_rooms SET player2_id = :player2_id, status = 'playing', current_turn = player1_id WHERE id = :id");
            $stmt->execute([
                'player2_id' => $player2Id,
                'id' => $roomId
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function updateGameState($roomId, $newStateArray, $nextTurnId) {
        $stmt = $this->db->prepare("UPDATE game_rooms SET game_state = :state, current_turn = :turn WHERE id = :id");
        return $stmt->execute([
            'state' => json_encode($newStateArray),
            'turn' => $nextTurnId,
            'id' => $roomId
        ]);
    }

    public function endGame($roomId, $winnerId) {
        try {
            $this->db->beginTransaction();

            $room = $this->getRoomById($roomId);
            $totalPool = $room['entry_fee'] * 2;
            $platformFee = $totalPool * 0.20;
            $winnerPrize = $totalPool - $platformFee;

            // Give winner their prize
            $stmt = $this->db->prepare("UPDATE users SET coins = coins + :prize WHERE id = :winner_id");
            $stmt->execute(['prize' => $winnerPrize, 'winner_id' => $winnerId]);

            // Mark room completed
            $stmt = $this->db->prepare("UPDATE game_rooms SET status = 'completed', winner_id = :winner_id, current_turn = NULL WHERE id = :id");
            $stmt->execute(['winner_id' => $winnerId, 'id' => $roomId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
