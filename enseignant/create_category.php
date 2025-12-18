<?PHP
require_once __DIR__ . '/../config/session_config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'enseignant') {
    header('location: ../auth/login.php');
    exit;
}


$errors = [];
$success = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Erreur de sécurité : Token CSRF invalide.";
    }

    $nom_categorie = trim($_POST['nom_categorie'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($nom_categorie === '') {
        $errors[] = "Le nom est obligatoire";
}  elseif (strlen($nom_categorie) < 2 || (strlen($nom_categorie) > 50)) {
        $errors[] = "Le nom doit être entre 2 et 50 caractères";
    }

    if ($description === '') {
        $errors[] = 'La description est obligatoire';
    }

    if (empty($errors)) {
        try {
            // Vérifier si le nom  existe déjà
            $checknom_categorie = $pdo->prepare("SELECT id_categorie FROM categorie WHERE nom_categorie = ? AND created_by = ?");
            $checknom_categorie->execute([$nom_categorie, $_SESSION['id_user']]);

            if ($checknom_categorie->fetch()) {
                $errors[] = "Cette Nom est déjà utilisé.";
            } else {

                $sql = "INSERT INTO categorie (nom_categorie, description, created_by) values (?, ?, ?)";
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $nom_categorie,
                    $description,
                    $_SESSION['id_user']
                ]);

                $success = true;
                header('location: dashboard.php?msg=cat_added');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur technique : " . $e->getMessage();
        }
    }
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors; // n-7etto l-errors f session
    header('Location: dashboard.php'); // n-rjj3o l-user l-dashbord fin kayna l-modal
    exit;
}
