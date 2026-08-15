<?php
/**
 * admin/logout.php
 *
 * Securely log the administrator out and destroy the session.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Define admin context ---
define('IRTIJA_ADMIN', true);

// --- Include authentication ---
require_once __DIR__ . '/auth.php';

// --- Perform logout ---
admin_logout();

// --- Redirect to login page with a success message ---
header('Location: login.php?logged_out=1');
exit;
