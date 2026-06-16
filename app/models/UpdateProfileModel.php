<?php

require_once __DIR__ . '/Model.php';

    class UpdateProfileModel extends Model {
        protected $users = 'users';

        /**
         * Get user by ID
         */
        public function getUserById($id) {
            try {
                $sql = "SELECT id, full_name, email, role, profile_picture, created_at FROM {$this->users} WHERE id = ? LIMIT 1";
                $stmt = $this->con->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            } catch (Exception $e) {
                error_log("Error fetching user by id: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Get user password by ID for verification
         */
        public function getUserPassword($id) {
            try {
                $sql = "SELECT password FROM {$this->users} WHERE id = ? LIMIT 1";
                $stmt = $this->con->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                return $row['password'] ?? null;
            } catch (Exception $e) {
                error_log("Error fetching user password: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Verify password
         */
        public function verifyPassword($password, $hash) {
            return password_verify($password, $hash);
        }

        /**
         * Check if email exists (for uniqueness validation)
         */
        public function emailExists($email, $userId) {
            try {
                $sql = "SELECT id FROM {$this->users} WHERE email = ? AND id != ? LIMIT 1";
                $stmt = $this->con->prepare($sql);
                $stmt->bind_param("si", $email, $userId);
                $stmt->execute();
                
                $result = $stmt->get_result();
                return $result->num_rows > 0;
            } catch (Exception $e) {
                error_log("Error checking email existence: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Update user profile (name, email, and/or password)
         */
        public function updateProfile($id, $name, $email, $password = null) {
            try {
                if ($password === null) {
                    //vardump the full_name
                    $sql = "UPDATE {$this->users} SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?";
                    $stmt = $this->con->prepare($sql);
                    $stmt->bind_param("ssi", $name, $email, $id);
                } else {
                    // Update full_name, email, and password
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $sql = "UPDATE {$this->users} SET full_name = ?, email = ?, password = ?, updated_at = NOW() WHERE id = ?";
                    $stmt = $this->con->prepare($sql);
                    $stmt->bind_param("sssi", $name, $email, $hashedPassword, $id);
                }
                
                return $stmt->execute();
            } catch (Exception $e) {
                error_log("Error updating profile: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Get profile picture path (if stored in database)
         */
        public function getProfilePicture($id) {
            try {
                $sql = "SELECT profile_picture FROM {$this->users} WHERE id = ? LIMIT 1";
                $stmt = $this->con->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $picturePath = $row['profile_picture'] ?? null;
                
                // Normalize path separators to forward slashes for consistency
                if ($picturePath) {
                    $picturePath = str_replace(DIRECTORY_SEPARATOR, '/', $picturePath);
                }
                
                return $picturePath;
            } catch (Exception $e) {
                error_log("Error fetching profile picture: " . $e->getMessage());
                return null;
            }
        }

        /**
         * Update profile picture
         */
        public function updateProfilePicture($id, $picturePath) {
            try {
                $sql = "UPDATE {$this->users} SET profile_picture = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->con->prepare($sql);
                
                if (!$stmt) {
                    error_log("Profile picture update prepare failed: " . $this->con->error);
                    return false;
                }
                
                $stmt->bind_param("si", $picturePath, $id);
                $result = $stmt->execute();
                
                if (!$result) {
                    error_log("Profile picture update execute failed: " . $stmt->error);
                    return false;
                }
                
                $stmt->close();
                return true;
            } catch (Exception $e) {
                error_log("Error updating profile picture: " . $e->getMessage());
                return false;
            }
        }
    }

?>