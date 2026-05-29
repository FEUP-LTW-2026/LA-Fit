<?php
session_start();

require_once __DIR__ . '/../database/csrf.php';
require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/forms.php';

$error = null;

if (isset($_GET['erro'])) {
    $error = 'Username/email ou palavra-passe inválidos.';
}

drawHeader('Área Cliente - LAFit', 'login', ['../css/login.css']);
drawLoginPage($error);
drawFooter();
