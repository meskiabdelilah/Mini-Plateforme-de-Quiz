<?php
require_once __DIR__ . '/../config/session_config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'enseignant') {
    header('location: ../auth/login.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$errors = [];



if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $id_cat = $_POST['id_categorie'];
    if (empty($token) || $token !== $_SESSION['csrf_token']) {
        $errors[] = "Tentative de sécurité détectée (CSRF invalide).";
    }

    try {
        $Check = $pdo->prepare("SELECT COUNT(*) FROM quiz WHERE id_categorie = ?");
        $Check->execute([$id_cat]);
        if ($Check->fetchColumn() > 0) {
            $errors[] = "Impossible de supprimer : cette catégorie contient des quiz.";
        } else {
            $sql = "DELETE FROM categorie WHERE id_categorie = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_cat]);

            header('location: dashboard.php?msg=cat_DELETE#categories');
            exit;
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur technique : " . $e->getMessage();
    }
}



if (!empty($errors)) {
    $_SESSION['errors'] = $errors; // n-7etto l-errors f session
    header('Location: dashboard.php#categories'); // n-rjj3o l-user l-dashbord fin kayna l-modal
    exit;
}
