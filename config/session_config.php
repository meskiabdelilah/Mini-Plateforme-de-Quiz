<?php
    
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,      // Khass ykoun 3ndek HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}