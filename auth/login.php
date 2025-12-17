<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inputs
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation simple
    if ($email === '' || $password === '') {
        $errors[] = "Tous les champs sont obligatoires.";
    } else {
        // Vérifier credentials
        $stmt = $pdo->prepare("SELECT id_user, nom, email, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {

            session_regenerate_id(true);
            // Login réussi
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_name'] = $user['nom'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirection selon le rôle
            if ($user['role'] === 'enseignant') {
                header('Location: ../enseignant/index.php');
                exit;
            } elseif ($user['role'] === 'etudiant') {
                header('Location: ../etudiant/index.php');
                exit;
            } else {
                $errors[] = "Rôle utilisateur non reconnu.";
            }
        } else {
            $errors[] = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Qodex</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-xl w-full max-w-md p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">
            <i class="fas fa-graduation-cap text-indigo-600 mr-2"></i>QuizMaster
        </h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-6 text-center">Connexion</h2>

        <!-- Affichage des erreurs -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <?php foreach ($errors as $error): ?>
                                • <?php echo htmlspecialchars($error); ?><br>
                            <?php endforeach; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered']) && $_GET['registered'] == 'true'): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            Inscription réussie ! Vous pouvez maintenant vous connecter.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <!-- Email -->
            <div>
                <label for="email" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-envelope mr-2 text-gray-400"></i>Email
                </label>
                <input type="email" name="email" id="email" placeholder="exemple@email.com" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-lock mr-2 text-gray-400"></i>Mot de passe
                </label>
                <input type="password" name="password" id="password" placeholder="Votre mot de passe" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg">
                <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
            </button>

            <!-- Lien vers register -->
            <div class="text-center pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-600">
                    Pas encore de compte ? 
                    <a href="register.php" class="text-indigo-600 hover:text-indigo-800 font-semibold hover:underline ml-1">
                        S'inscrire maintenant
                    </a>
                </p>
            </div>
</body>

</html>