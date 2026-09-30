<?php
require_once __DIR__ . '/../session.php';
require_login();
header('Location: ../projects.php');
exit;

