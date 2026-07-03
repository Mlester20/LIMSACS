<?php
require_once __DIR__ . '/../models/Model.php';

    class SchoolYearService extends Model{
        private $syModel;

        /**
         * @param mixed $con mysqli connection
         * @param object $syModel a SchoolYearModel instance (admin or registrar variant
         *        both duplicate the same class name, so this is injected rather than
         *        required directly here to avoid a "Cannot redeclare class" collision
         *        when both a role's own model file and this service are loaded together)
         */
        public function __construct($con, $syModel){
            parent::__construct($con);
            $this->syModel = $syModel;
        }

        /**
         * A school year should be auto-ended when it's still active
         * and its end_date has already passed (compared as real dates,
         * not strings).
         * @param array $schoolYear a row from the school_year table
         * @return bool
         */
        public function shouldAutoEnd(array $schoolYear): bool{
            if(($schoolYear['status'] ?? null) !== 'active'){
                return false;
            }

            $endDate = DateTime::createFromFormat('Y-m-d', $schoolYear['end_date'] ?? '');
            if(!$endDate){
                return false;
            }

            $today = new DateTime('today');
            return $endDate < $today;
        }

        /**
         * Archive every active school year whose end_date has passed.
         * @return array the rows that were closed
         */
        public function closeExpiredYears(): array{
            $closed = [];

            try{
                $activeYears = $this->syModel->findActive();
                if(empty($activeYears)){
                    return $closed;
                }

                foreach($activeYears as $sy){
                    if(!$this->shouldAutoEnd($sy)){
                        continue;
                    }

                    $endedAt = date('Y-m-d H:i:s');
                    if($this->syModel->closeYear($sy['id'], $endedAt)){
                        $sy['status'] = 'archived';
                        $sy['ended_at'] = $endedAt;
                        $sy['auto_ended'] = 1;
                        $closed[] = $sy;
                    }
                }
            }catch(Exception $e){
                error_log($e->getMessage());
            }

            return $closed;
        }
    }
