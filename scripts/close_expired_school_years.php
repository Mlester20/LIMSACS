<?php
// Crontab (run once daily, e.g. at 1:00 AM):
// 0 1 * * * /usr/bin/php /path/to/LIMSACS/scripts/close_expired_school_years.php >> /path/to/LIMSACS/storage/logs/close_expired_school_years.log 2>&1

require_once __DIR__ . '/../database/config/config.php';
require_once __DIR__ . '/../app/models/Model.php';
require_once __DIR__ . '/../app/models/admin/SchoolYearModel.php';
require_once __DIR__ . '/../app/services/SchoolYearService.php';

$service = new SchoolYearService($con, new SchoolYearModel($con));
$closed = $service->closeExpiredYears();

if(empty($closed)){
    echo '[' . date('Y-m-d H:i:s') . "] No expired active school years found.\n";
}else{
    echo '[' . date('Y-m-d H:i:s') . '] Closed ' . count($closed) . " school year(s):\n";
    foreach($closed as $sy){
        echo " - {$sy['school_year']} (id: {$sy['id']}, end_date: {$sy['end_date']})\n";
    }
}
