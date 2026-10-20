<?php
require_once __DIR__ . '/../config/config.php';

unset($_SESSION['user']);
session_destroy();

header('Location: /index.php');
exit;
