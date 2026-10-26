<?php
require_once 'includes/functions.php';
session_unset();
session_destroy();
session_start();
$_SESSION['success'] = 'Đã đăng xuất thành công.';
redirect('index.php');
?>