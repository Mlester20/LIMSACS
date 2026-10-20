<?php
require_once __DIR__ . '/../Model.php';

    class StudentsDocumentModel extends Model{
        protected $student_documents = 'student_documents';
        protected $students = 'students';
        protected $document_types = 'document_types';
        protected $user = 'users';

        public function index(){
            try{
                $query ="SELECT
                    sd.*,
                    s.first_name as student_first_name,
                    s.last_name as student_last_name,
                    dt.document_name as document_type_name,
                    u.full_name as uploaded_by_name,
                    sd.uploaded_at
                    FROM {$this->student_documents} sd
                    LEFT JOIN {$this->students} s ON sd.student_id = s.id
                    LEFT JOIN {$this->document_types} dt ON sd.document_type_id = dt.id
                    LEFT JOIN {$this->user} u ON sd.uploaded_by = u.id
                    ORDER BY s.last_name, s.first_name, sd.uploaded_at DESC
                ";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function getPaginated($limit, $offset, $status = ''){
            try{
                $where = $status !== '' ? 'WHERE sd.status = ?' : '';
                $query ="SELECT
                    sd.*,
                    s.first_name as student_first_name,
                    s.last_name as student_last_name,
                    s.lrn,
                    dt.document_name as document_type_name,
                    u.full_name as uploaded_by_name,
                    sd.uploaded_at,
                    ah.grade_level AS current_grade_level,
                    sec.section_name AS current_section_name,
                    adv.full_name AS adviser_name
                    FROM {$this->student_documents} sd
                    LEFT JOIN {$this->students} s ON sd.student_id = s.id
                    LEFT JOIN {$this->document_types} dt ON sd.document_type_id = dt.id
                    LEFT JOIN {$this->user} u ON sd.uploaded_by = u.id
                    LEFT JOIN academic_history ah ON ah.id = (
                        SELECT ah2.id FROM academic_history ah2 WHERE ah2.student_id = s.id ORDER BY ah2.id DESC LIMIT 1
                    )
                    LEFT JOIN sections sec ON ah.section_id = sec.id
                    LEFT JOIN {$this->user} adv ON sec.adviser_id = adv.id
                    {$where}
                    ORDER BY s.last_name, s.first_name, sd.uploaded_at DESC
                    LIMIT ? OFFSET ?
                ";
                $stmt = $this->con->prepare($query);
                if($status !== ''){
                    $stmt->bind_param("sii", $status, $limit, $offset);
                }else{
                    $stmt->bind_param("ii", $limit, $offset);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return [];
            }
        }

        public function getTotalCount($status = ''){
            try{
                $where = $status !== '' ? 'WHERE sd.status = ?' : '';
                $query = "SELECT COUNT(*) as total FROM {$this->student_documents} sd {$where}";
                $stmt = $this->con->prepare($query);
                if($status !== ''){
                    $stmt->bind_param("s", $status);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                return (int)$row['total'];
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return 0;
            }
        }

        public function create($data){
            try{
                $query = "INSERT INTO {$this->student_documents} (student_id, document_type_id, file_path, status, remarks, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    "iisssi",
                    $data['student_id'],
                    $data['document_type_id'],
                    $data['file_path'],
                    $data['status'],
                    $data['remarks'],
                    $data['uploaded_by']
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function update($id, $data){
            try{
                // Only update file_path if it's not null (i.e., a new file was uploaded)
                if($data['file_path'] !== null){
                    $query = "UPDATE {$this->student_documents} SET student_id = ?, document_type_id = ?, file_path = ?, status = ?, remarks = ?, uploaded_by = ? WHERE id = ?";
                    $stmt = $this->con->prepare($query);
                    $stmt->bind_param(
                        "iisssii",
                        $data['student_id'],
                        $data['document_type_id'],
                        $data['file_path'],
                        $data['status'],
                        $data['remarks'],
                        $data['uploaded_by'],
                        $id
                    );
                } else {
                    // Don't update file_path, keep existing value
                    $query = "UPDATE {$this->student_documents} SET student_id = ?, document_type_id = ?, status = ?, remarks = ?, uploaded_by = ? WHERE id = ?";
                    $stmt = $this->con->prepare($query);
                    $stmt->bind_param(
                        "iissii",
                        $data['student_id'],
                        $data['document_type_id'],
                        $data['status'],
                        $data['remarks'],
                        $data['uploaded_by'],
                        $id
                    );
                }
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        /**
         * Required + active document types this student has no student_documents
         * row for yet (any status counts as "has it" — this is about documents
         * never submitted at all, not about re-submission after a Rejected one).
         * @param int $student_id
         * @return array
         */
        public function getMissingRequiredDocuments($student_id){
            try{
                $query = "SELECT dt.id, dt.document_name
                    FROM {$this->document_types} dt
                    WHERE dt.is_required = 1 AND dt.is_active = 1
                    AND dt.id NOT IN (
                        SELECT document_type_id FROM {$this->student_documents} WHERE student_id = ?
                    )
                    ORDER BY dt.document_name ASC
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return [];
            }
        }

        public function updateStatus($id, $status){
            try{
                $query = "UPDATE {$this->student_documents} SET status = ? WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("si", $status, $id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function delete($id){
            try{
                $query = "DELETE FROM {$this->student_documents} WHERE id = ? ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function getByStudentId($student_id){
            try{
                $query ="SELECT
                    sd.*,
                    dt.document_name
                    FROM {$this->student_documents} sd
                    LEFT JOIN {$this->document_types} dt ON sd.document_type_id = dt.id
                    WHERE sd.student_id = ?
                    ORDER BY dt.document_name ASC
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return [];
            }
        }

        public function find($id){
            try{
                $query ="SELECT
                    sd.*,
                    s.first_name as student_first_name,
                    s.last_name as student_last_name,
                    dt.name as document_type_name,
                    u.full_name as uploaded_by_name,
                    sd.uploaded_at
                    FROM {$this->student_documents} sd
                    LEFT JOIN {$this->students} s ON sd.student_id = s.id
                    LEFT JOIN {$this->document_types} dt ON sd.document_type_id = dt.id
                    LEFT JOIN {$this->user} u ON sd.uploaded_by = u.id
                    WHERE sd.id = ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function search($keyword){
            try{
                $search_term = "%" . $keyword . "%";
                $query ="SELECT
                    sd.*,
                    s.first_name as student_first_name,
                    s.last_name as student_last_name,
                    dt.name as document_type_name,
                    u.full_name as uploaded_by_name,
                    sd.uploaded_at
                    FROM {$this->student_documents} sd
                    LEFT JOIN {$this->students} s ON sd.student_id = s.id
                    LEFT JOIN {$this->document_types} dt ON sd.document_type_id = dt.id
                    LEFT JOIN {$this->user} u ON sd.uploaded_by = u.id
                    WHERE s.lrn LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("ss", $search_term, $search_term);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }
    }