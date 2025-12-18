<?php
require_once __DIR__ . '/../config/session_config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'enseignant') {
    header('location: ../auth/login.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$errors = [];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('location: dashboard.php');
    exit;
}

$id_cat = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM categorie WHERE id_categorie = ? AND created_by = ?");
$stmt->execute([$id_cat, $id_user]);
$category = $stmt->fetch();

if (!$category) {
    $_SESSION['errors'] = ["Action non autorisée ou catégorie inexistante."];
    header('location: dashboard.php');
    exit;
}


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

            $check = $pdo->prepare("SELECT id_categorie FROM categorie WHERE nom_categorie = ? AND created_by = ? AND id_categorie != ?");
            $check->execute([$nom_categorie, $id_user, $id_cat]);

            if ($check->fetch()) {
                $errors[] = "Cette Nom est déjà utilisé.";
            } else {
                
                $sql = "UPDATE categorie SET nom_categorie = ?, description = ? WHERE id_categorie = ? AND created_by = ?";
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $nom_categorie,
                    $description,
                    $id_cat,   
                    $id_user 
                ]);

                header('location: dashboard.php?msg=cat_updated');
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


?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier Catégorie</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
    <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow">
        <h2 class="text-2xl font-bold mb-6">Modifier la catégorie</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?php foreach($errors as $error): ?> <p><?= $error ?></p> <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="mb-4">
                <label class="block mb-2">Nom</label>
                <input type="text" name="nom_categorie" 
                       value="<?= htmlspecialchars($category['nom_categorie']) ?>" 
                       class="w-full border p-2 rounded">
            </div>

            <div class="mb-4">
                <label class="block mb-2">Description</label>
                <textarea name="description" class="w-full border p-2 rounded"><?= htmlspecialchars($category['description']) ?></textarea>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Enregistrer</button>
                <a href="dashboard.php" class="bg-gray-200 px-4 py-2 rounded">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>