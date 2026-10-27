<?php
require_once __DIR__ . '/Model.php';

    class NotificationsModel extends Model{
        protected $notifications = 'notifications';
        protected $users = 'users';

        public function create($data){
            try{
                $query = "INSERT INTO {$this->notifications} (user_id, type, title, message, link, reference_table, reference_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'isssssi',
                    $data['user_id'],
                    $data['type'],
                    $data['title'],
                    $data['message'],
                    $data['link'],
                    $data['reference_table'],
                    $data['reference_id']
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log('Create notification error: ' . $e->getMessage());
                return false;
            }
        }

        /**
         * Sends the same notification to every user of a given role (e.g. every
         * registrar), one row each, so each recipient has their own read state.
         */
        public function notifyRole($role, array $payload){
            try{
                $stmt = $this->con->prepare("SELECT id FROM {$this->users} WHERE role = ?");
                $stmt->bind_param('s', $role);
                $stmt->execute();
                $result = $stmt->get_result();

                while($row = $result->fetch_assoc()){
                    $this->create(array_merge($payload, ['user_id' => $row['id']]));
                }
                return true;
            }catch(Exception $e){
                error_log('Notify role error: ' . $e->getMessage());
                return false;
            }
        }

        public function getRecentForUser($userId, $limit = 10){
            try{
                $query = "SELECT * FROM {$this->notifications} WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('ii', $userId, $limit);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log('Get notifications error: ' . $e->getMessage());
                return [];
            }
        }

        public function countUnread($userId){
            try{
                $query = "SELECT COUNT(*) AS total FROM {$this->notifications} WHERE user_id = ? AND is_read = 0";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                return (int) ($result->fetch_assoc()['total'] ?? 0);
            }catch(Exception $e){
                error_log('Count unread notifications error: ' . $e->getMessage());
                return 0;
            }
        }

        public function markAllAsRead($userId){
            try{
                $stmt = $this->con->prepare("UPDATE {$this->notifications} SET is_read = 1 WHERE user_id = ? AND is_read = 0");
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log('Mark all notifications read error: ' . $e->getMessage());
                return false;
            }
        }
    }
