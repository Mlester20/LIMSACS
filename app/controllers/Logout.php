<?php
session_destroy();
require_once __DIR__ . '/../../database/config/config.php';

header("Location: " . BASE_URL . "/index.php");
