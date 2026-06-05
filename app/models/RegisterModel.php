<?php

    class RegisterModel{
        private $con;

        public function __construct($connection) {
            $this->con = $connection;
        }

        /**
         * Register a new user
         * @param string $fullName
         * @param string $email
         * @param string $password
         * @param string $role
         * @return array
         */
        public function register($fullName, $email, $password, $role = 'user') {
            // Validate input
            if (empty($fullName) || empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'All fields are required'
                ];
            }

            // Check if email already exists
            $checkEmail = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->con->prepare($checkEmail);
            
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Database error: ' . $this->con->error
                ];
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $stmt->close();
                return [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
            }
            $stmt->close();

            // Hash password with bcrypt (cost = 10)
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

            // Get current timestamp
            $now = date('Y-m-d H:i:s');

            // Insert user into database
            $insertQuery = "INSERT INTO users (full_name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->con->prepare($insertQuery);

            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Database error: ' . $this->con->error
                ];
            }

            $stmt->bind_param("ssssss", $fullName, $email, $hashedPassword, $role, $now, $now);
            
            if ($stmt->execute()) {
                $stmt->close();
                return [
                    'success' => true,
                    'message' => 'User registered successfully',
                    'user_id' => $this->con->insert_id
                ];
            } else {
                $stmt->close();
                return [
                    'success' => false,
                    'message' => 'Failed to register user: ' . $this->con->error
                ];
            }
        }
    }

?>