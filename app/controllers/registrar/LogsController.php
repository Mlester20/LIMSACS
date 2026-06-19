<?php
session_start();

require_once __DIR__ . '/../../models/registrar/LogsModel.php';
require_once __DIR__ . '/../../../database/config/config.php';

class LogsController {
    private $model;
    private $itemsPerPage = 10;

    public function __construct($con) {
        $this->model = new LogsModel($con);
    }

    /**
     * @param int|string $user_id
     * @return array
     */
    public function getLogs($user_id, $page = 1) {
        $page = max(1, intval($page));
        $offset = ($page - 1) * $this->itemsPerPage;
        $totalCount = $this->model->getTotalCount($user_id);
        $totalPages = (int) ceil($totalCount / $this->itemsPerPage);

        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $this->itemsPerPage;
        }

        return [
            'logs' => $this->model->getLogs($user_id, $this->itemsPerPage, $offset),
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalCount,
            'itemsPerPage' => $this->itemsPerPage,
            'hasNextPage' => $page < $totalPages,
            'hasPrevPage' => $page > 1
        ];
    }
}

try {
    $controller = new LogsController($con);
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
    
    if ($user_id) {
        $paginationData = $controller->getLogs($user_id, $currentPage);
        $logs = $paginationData['logs'];
        $pagination = [
            'currentPage' => $paginationData['currentPage'],
            'totalPages' => $paginationData['totalPages'],
            'totalRecords' => $paginationData['totalRecords'],
            'itemsPerPage' => $paginationData['itemsPerPage'],
            'hasNextPage' => $paginationData['hasNextPage'],
            'hasPrevPage' => $paginationData['hasPrevPage']
        ];
    } else {
        $logs = [];
        $pagination = [
            'currentPage' => 1,
            'totalPages' => 0,
            'totalRecords' => 0,
            'itemsPerPage' => 10,
            'hasNextPage' => false,
            'hasPrevPage' => false
        ];
    }
} catch (Exception $e) {
    error_log("Error in Controller: " . $e->getMessage());
    $logs = [];
    $pagination = [
        'currentPage' => 1,
        'totalPages' => 0,
        'totalRecords' => 0,
        'itemsPerPage' => 10,
        'hasNextPage' => false,
        'hasPrevPage' => false
    ];
}
