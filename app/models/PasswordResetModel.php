<?php
require_once __DIR__ . '/Model.php';

    class PasswordResetModel extends Model{
        protected $table = 'password_resets';
        protected $users = 'users';

        public function deleteTokensForUser($user_id){
            try{
                $stmt = $this->con->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log('Error deleting reset tokens: ' . $e->getMessage());
                return false;
            }
        }

        /**
         * expires_at is computed by MySQL itself (NOW() + interval), not by PHP's
         * date()/time() -- keeps token expiry immune to any mismatch between the
         * web server's PHP timezone setting and the DB server's timezone (a real
         * mismatch was caught here during testing: expires_at computed in PHP
         * landed hours off from MySQL's own NOW(), making tokens look expired
         * the instant they were created).
         * @param int $user_id
         * @param string $tokenHash
         * @param int $ttlSeconds
         */
        public function createToken($user_id, $tokenHash, $ttlSeconds){
            try{
                $stmt = $this->con->prepare(
                    "INSERT INTO {$this->table} (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))"
                );
                $stmt->bind_param('isi', $user_id, $tokenHash, $ttlSeconds);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log('Error creating reset token: ' . $e->getMessage());
                return false;
            }
        }

        /**
         * Whether this user has requested a reset within the last $windowSeconds
         * -- computed entirely in SQL (same timezone-safety reasoning as above).
         * @param int $user_id
         * @param int $windowSeconds
         * @return bool
         */
        public function isRateLimited($user_id, $windowSeconds){
            try{
                $stmt = $this->con->prepare(
                    "SELECT id FROM {$this->table} WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND) LIMIT 1"
                );
                $stmt->bind_param('ii', $user_id, $windowSeconds);
                $stmt->execute();
                return $stmt->get_result()->num_rows > 0;
            }catch(Exception $e){
                error_log('Error checking reset rate limit: ' . $e->getMessage());
                return false;
            }
        }

        /**
         * A not-yet-used, not-yet-expired token row, joined with the owning user.
         * @param string $tokenHash
         * @return array|null
         */
        public function findValidToken($tokenHash){
            try{
                $query = "SELECT pr.id, pr.user_id, pr.expires_at, pr.used_at,
                        u.email, u.full_name, u.role
                    FROM {$this->table} pr
                    JOIN {$this->users} u ON u.id = pr.user_id
                    WHERE pr.token_hash = ? AND pr.used_at IS NULL AND pr.expires_at > NOW()
                    LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('s', $tokenHash);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                return $row ?: null;
            }catch(Exception $e){
                error_log('Error validating reset token: ' . $e->getMessage());
                return null;
            }
        }

        public function updateUserPassword($user_id, $hashedPassword){
            try{
                $stmt = $this->con->prepare(
                    "UPDATE {$this->users} SET password = ?, updated_at = NOW() WHERE id = ?"
                );
                $stmt->bind_param('si', $hashedPassword, $user_id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log('Error updating password: ' . $e->getMessage());
                return false;
            }
        }
    }
