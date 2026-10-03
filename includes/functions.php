<?php
session_start();

// Dynamic BASE_URL detection
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    define('BASE_URL', '/pojokrumah/public/');
} else {
    // For hosting, if the domain points directly to the root, we might want to hide /public/
    // This is handled by .htaccess in the root.
    define('BASE_URL', '/');
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin');
}

function redirect($url)
{
    header("Location: " . BASE_URL . $url);
    exit;
}

function checkAuth()
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function checkGuest()
{
    if (isLoggedIn()) {
        if (isAdmin()) {
            redirect('admin/index.php');
        } else {
            redirect('index.php');
        }
    }
}

function checkAdmin()
{
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "admin/login.php");
        exit;
    }
    if (!isAdmin()) {
        redirect('index.php');
    }
}
function checkSuperAdmin()
{
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "admin/login.php");
        exit;
    }
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
        // Redirect if not superadmin (maybe to admin dashboard with error, or just index)
        redirect('admin/index.php');
    }
}
?>