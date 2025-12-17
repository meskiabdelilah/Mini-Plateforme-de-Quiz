<?php
session_start();
require_once __DIR__ . '/../config/database.php'; // تأكد من المسار

/* CSRF token */
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$token = $_SESSION['csrf_token'];

$errors = [];
$success = false;

// Valeurs par défaut pour réafficher le formulaire
$nom = $email = $role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* CSRF check */
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "CSRF token invalide!";
    }

    /* Nom */
    $nom = trim($_POST['nom'] ?? '');
    if ($nom === '') $errors[] = "Le nom est obligatoire.";

    /* Email */
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    /* Password */
    $password = trim($_POST['password'] ?? '');
    if ($password === '') {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Le mot de passe doit contenir une majuscule.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Le mot de passe doit contenir un chiffre.";
    }

    /* Confirm password */
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');
    if ($password !== '' && $confirmPassword !== '' && $password !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    /* Role */
    $role = $_POST['role'] ?? '';
    if ($role === '') $errors[] = "Le rôle est obligatoire.";

    /* Enregistrement en base */
    if (empty($errors)) {
        try {
            // Vérifier si l'email existe déjà
            $checkEmail = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
            $checkEmail->execute([$email]);

            if ($checkEmail->fetch()) {
                $errors[] = "Cet email est déjà utilisé.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (nom, email, password_hash, role) 
                        VALUES (:nom, :email, :password_hash, :role)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nom' => $nom,
                    ':email' => $email,
                    ':password_hash' => $passwordHash,
                    ':role' => $role
                ]);

                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
                $success = true;
                $nom = $email = $role = '';
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Qodex</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-xl w-full max-w-md p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">Créer un compte</h1>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <p class="font-semibold">Inscription réussie !</p>
                <p class="text-sm mt-1">Vous pouvez maintenant vous connecter.</p>
                <a href="login.php" class="inline-block mt-2 text-green-800 hover:underline">
                    <i class="fas fa-sign-in-alt mr-1"></i>Aller à la page de connexion
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors) && !$success): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="POST" action="" class="space-y-4">
                <!-- csrf token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token); ?>">

                <!-- Nom -->
                <div>
                    <label for="nom" class="block text-gray-700 font-semibold mb-1">Nom</label>
                    <input type="text" name="nom" id="nom" placeholder="Votre nom" required
                        value="<?php echo htmlspecialchars($nom); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-gray-700 font-semibold mb-1">Email</label>
                    <input type="email" name="email" id="email" placeholder="exemple@email.com" required
                        value="<?php echo htmlspecialchars($email); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-gray-700 font-semibold mb-1">Mot de passe</label>
                    <input type="password" name="password" id="password" placeholder="********" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="confirmPassword" class="block text-gray-700 font-semibold mb-1">Confirmer le mot de passe</label>
                    <input type="password" name="confirmPassword" id="confirmPassword" placeholder="********" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-gray-700 font-semibold mb-1">Rôle</label>
                    <select name="role" id="role" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Sélectionner un rôle</option>
                        <option value="etudiant" <?php echo $role === 'etudiant' ? 'selected' : ''; ?>>Étudiant</option>
                        <option value="enseignant" <?php echo $role === 'enseignant' ? 'selected' : ''; ?>>Enseignant</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-user-plus mr-2"></i>S'inscrire
                </button>

                <p class="text-sm text-gray-500 text-center mt-4">
                    Déjà un compte ? <a href="login.php" class="text-indigo-600 hover:underline">Se connecter</a>
                </p>
            </form>
        <?php endif; ?>
    </div>

</body>

</html>