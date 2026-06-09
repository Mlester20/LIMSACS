<?php
require_once __DIR__ . '/../Model.php';

    class DocumentTypesModel extends Model{
        protected $table = 'document_types';

        public function index(){
            try{
                $query = "SELECT * FROM {$this->table} ORDER BY id DESC";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();

                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        public function create($data){
            try{
                $query = "INSERT INTO {$this->table} (document_name, is_required, is_active) VALUES (?, ?, ?)";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'sii', 
                    $data['document_name'], 
                    $data['is_required'], 
                    $data['is_active']
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                return $e->getMessage();
                return false;
            }
        }

        public function update($id, $data){
            try{
                $query = "UPDATE {$this->table} SET document_name = ?, is_required = ?, is_active = ? WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'siii', 
                    $data['document_name'], 
                    $data['is_required'], 
                    $data['is_active'], 
                    $id
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        public function delete($id){
            try{
                $query = "DELETE FROM {$this->table} WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                return $e->getMessage();
            }
        }
    }