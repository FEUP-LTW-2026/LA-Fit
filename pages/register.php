<?php
session_start();

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/plans.php';
require_once __DIR__ . '/../database/gyms.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/forms.php';

$db = getDatabaseConnection();
$plans = getAllPlans($db);
$gyms = getAllGyms($db);
$selectedPlan = isset($_GET['plano']) ? (int)$_GET['plano'] : null;
$error = null;

if (isset($_GET['erro'])) {
    $messages = [
        'campos' => 'Preenche os campos obrigatórios para concluirmos a inscrição.',
        'email' => 'O email indicado não é válido.',
        'termos' => 'Tens de aceitar os termos para avançar.',
        'existe' => 'Já existe uma conta com esse username ou email.',
    ];

    $error = $messages[$_GET['erro']] ?? 'Não foi possível concluir a inscrição.';
}

drawHeader('Inscrição - LAFit', 'register', ['../css/register.css']);
drawRegistrationPage($plans, $gyms, $error, $selectedPlan);
drawFooter();
