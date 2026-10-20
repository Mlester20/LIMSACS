<?php
require_once __DIR__ . '/Model.php';

    class SchoolSettingsModel extends Model{
        protected $table = 'school_settings';

        /**
         * All settings as a flat key => value array.
         * @return array<string,string>
         */
        public function getAll(){
            try{
                $result = [];
                $res = $this->con->query("SELECT setting_key, setting_value FROM {$this->table}");
                while($row = $res->fetch_assoc()){
                    $result[$row['setting_key']] = $row['setting_value'];
                }
                return $result;
            }catch(Exception $e){
                error_log('Error fetching school settings: ' . $e->getMessage());
                return [];
            }
        }

        /**
         * @param string $key
         * @param string|null $value
         * @return bool
         */
        public function set($key, $value){
            try{
                $stmt = $this->con->prepare(
                    "INSERT INTO {$this->table} (setting_key, setting_value) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
                );
                $stmt->bind_param('ss', $key, $value);
                return $stmt->execute();
            }catch(Exception $e){
                error_log('Error saving school setting: ' . $e->getMessage());
                return false;
            }
        }
    }
