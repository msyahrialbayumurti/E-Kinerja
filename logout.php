<?php
/**
 * logout.php - Session Invalidation
 */

require_once 'config/database.php';
require_once 'config/security.php';

// Destroy session dan hapus cookie sesi
terminate_session();

// Redirect ke login
header("Location: views/login.php?logged_out=1");
exit;
?>
