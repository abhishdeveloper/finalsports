<?php

class MatchModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- Admin Methods ---

    public function createMatch($title, $sport, $entryFee, $endTime) {
        $stmt = $this->db->prepare("INSERT INTO matches (title, sport, entry_fee, end_time, status) VALUES (:title, :sport, :entry_fee, :end_time, 'open')");
        $stmt->execute([
            'title' => $title,
            'sport' => $sport,
            'entry_fee' => $entryFee,
            'end_time' => $endTime
        ]);
        return $this->db->lastInsertId();
    }

    public function addQuestion($matchId, $questionText) {
        $stmt = $this->db->prepare("INSERT INTO questions (match_id, question_text) VALUES (:match_id, :question_text)");
        $stmt->execute([
            'match_id' => $matchId,
            'question_text' => $questionText
        ]);
        return $this->db->lastInsertId();
    }

    public function addOption($questionId, $optionText) {
        $stmt = $this->db->prepare("INSERT INTO options (question_id, option_text) VALUES (:question_id, :option_text)");
        $stmt->execute([
            'question_id' => $questionId,
            'option_text' => $optionText
        ]);
        return $this->db->lastInsertId();
    }

    public function markMatchesAwaited() {
        // Find matches that are open but end_time has passed
        $stmt = $this->db->query("UPDATE matches SET status = 'awaited' WHERE status = 'open' AND end_time <= NOW()");
        return $stmt->rowCount();
    }

    public function setCorrectOption($questionId, $optionId) {
        // First reset all options for this question to 0
        $stmt = $this->db->prepare("UPDATE options SET is_correct = 0 WHERE question_id = :question_id");
        $stmt->execute(['question_id' => $questionId]);

        // Set the selected one to 1
        $stmt = $this->db->prepare("UPDATE options SET is_correct = 1 WHERE id = :option_id AND question_id = :question_id");
        $stmt->execute(['option_id' => $optionId, 'question_id' => $questionId]);
    }

    public function getAwaitedMatches() {
        // Update statuses just in case before fetching
        $this->markMatchesAwaited();
        $stmt = $this->db->query("SELECT * FROM matches WHERE status = 'awaited' ORDER BY end_time ASC");
        return $stmt->fetchAll();
    }

    public function processResultsAndRank($matchId) {
        try {
            $this->db->beginTransaction();

            $match = $this->getMatchById($matchId);

            // 1. Calculate Score for each participant
            $stmt = $this->db->prepare("
                SELECT p.participant_id, COUNT(p.id) as score
                FROM predictions p
                JOIN options o ON p.option_id = o.id
                JOIN match_participants mp ON p.participant_id = mp.id
                WHERE mp.match_id = :match_id AND o.is_correct = 1
                GROUP BY p.participant_id
            ");
            $stmt->execute(['match_id' => $matchId]);
            $scores = $stmt->fetchAll();

            $updateScoreStmt = $this->db->prepare("UPDATE match_participants SET score = :score WHERE id = :id");
            foreach ($scores as $s) {
                $updateScoreStmt->execute(['score' => $s['score'], 'id' => $s['participant_id']]);
            }

            // 2. Get all participants ordered by score DESC
            $stmt = $this->db->prepare("SELECT * FROM match_participants WHERE match_id = :match_id ORDER BY score DESC, created_at ASC");
            $stmt->execute(['match_id' => $matchId]);
            $participants = $stmt->fetchAll();

            if (count($participants) > 0) {
                $totalPool = count($participants) * $match['entry_fee'];
                $distributablePool = $totalPool * 0.8; // 20% platform cut

                // Simple distribution model: top 1 gets 50%, top 2 gets 30%, top 3 gets 20%
                // (Adjustable based on requirements. If less than 3 players, pool redistributes)
                $payoutPercentages = [0.5, 0.3, 0.2];

                $currentRank = 1;
                $updateRankStmt = $this->db->prepare("UPDATE match_participants SET rank = :rank, won_coins = :won_coins WHERE id = :id");
                $updateUserCoinsStmt = $this->db->prepare("UPDATE users SET coins = coins + :won_coins WHERE id = :user_id");

                $winnersCount = 0;
                foreach ($participants as $p) {
                    if ($p['score'] > 0) $winnersCount++;
                }

                foreach ($participants as $index => $participant) {
                    $wonCoins = 0;

                    if ($participant['score'] > 0) {
                        if ($winnersCount == 1) {
                            $wonCoins = floor($distributablePool);
                        } elseif ($winnersCount == 2) {
                            if ($index == 0) $wonCoins = floor($distributablePool * 0.6); // 60% to 1st
                            if ($index == 1) $wonCoins = floor($distributablePool * 0.4); // 40% to 2nd
                        } else {
                            if (isset($payoutPercentages[$index])) {
                                $wonCoins = floor($distributablePool * $payoutPercentages[$index]);
                            }
                        }
                    }

                    $updateRankStmt->execute([
                        'rank' => $currentRank,
                        'won_coins' => $wonCoins,
                        'id' => $participant['id']
                    ]);

                    if ($wonCoins > 0) {
                        $updateUserCoinsStmt->execute([
                            'won_coins' => $wonCoins,
                            'user_id' => $participant['user_id']
                        ]);
                    }

                    $currentRank++;
                }
            }

            // 3. Mark match as completed
            $stmt = $this->db->prepare("UPDATE matches SET status = 'completed' WHERE id = :match_id");
            $stmt->execute(['match_id' => $matchId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getAllMatchesAdmin() {
        $stmt = $this->db->query("SELECT * FROM matches ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    // --- User Methods ---

    public function getOpenMatches() {
        $stmt = $this->db->query("SELECT * FROM matches WHERE status = 'open' AND end_time > NOW() ORDER BY end_time ASC");
        return $stmt->fetchAll();
    }

    public function hasUserParticipated($matchId, $userId) {
        $stmt = $this->db->prepare("SELECT id FROM match_participants WHERE match_id = :match_id AND user_id = :user_id LIMIT 1");
        $stmt->execute(['match_id' => $matchId, 'user_id' => $userId]);
        return $stmt->fetch() !== false;
    }

    public function submitPrediction($userId, $matchId, $entryFee, $predictions) {
        try {
            $this->db->beginTransaction();

            // Deduct coins
            $stmt = $this->db->prepare("UPDATE users SET coins = coins - :fee WHERE id = :id AND coins >= :fee");
            $stmt->execute(['fee' => $entryFee, 'id' => $userId]);

            if ($stmt->rowCount() == 0) {
                $this->db->rollBack();
                return false; // Not enough coins (race condition check)
            }

            // Record participant
            $stmt = $this->db->prepare("INSERT INTO match_participants (match_id, user_id) VALUES (:match_id, :user_id)");
            $stmt->execute(['match_id' => $matchId, 'user_id' => $userId]);
            $participantId = $this->db->lastInsertId();

            // Record answers
            $stmt = $this->db->prepare("INSERT INTO predictions (participant_id, question_id, option_id) VALUES (:participant_id, :question_id, :option_id)");
            foreach ($predictions as $questionId => $optionId) {
                $stmt->execute([
                    'participant_id' => $participantId,
                    'question_id' => $questionId,
                    'option_id' => $optionId
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // --- Shared Methods ---

    public function getMatchById($matchId) {
        $stmt = $this->db->prepare("SELECT * FROM matches WHERE id = :id");
        $stmt->execute(['id' => $matchId]);
        return $stmt->fetch();
    }

    public function getMatchQuestionsWithOptions($matchId) {
        $stmt = $this->db->prepare("SELECT * FROM questions WHERE match_id = :match_id");
        $stmt->execute(['match_id' => $matchId]);
        $questions = $stmt->fetchAll();

        foreach ($questions as &$question) {
            $stmtOpt = $this->db->prepare("SELECT * FROM options WHERE question_id = :question_id");
            $stmtOpt->execute(['question_id' => $question['id']]);
            $question['options'] = $stmtOpt->fetchAll();
        }

        return $questions;
    }
}
