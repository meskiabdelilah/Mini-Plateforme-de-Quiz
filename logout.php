<?php
session_start();
session_unset();     // Khwi l-m3loumat
session_destroy();   // 7re9 la session
header("Location: login.php");
exit;
?>