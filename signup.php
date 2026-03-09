<?php
// signup.php - Page d'inscription
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $prenom   = trim($_POST['prenom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validations
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        $pdo = getDB();

        // Vérifie si l'email existe déjà
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $error = 'Cette adresse email est déjà utilisée.';
        } else {
            // Insertion — role 'collaborateur' par défaut, un admin pourra changer
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('
                INSERT INTO users (nom, prenom, email, password_hash, role)
                VALUES (:nom, :prenom, :email, :password_hash, :role)
            ');
            $stmt->execute([
                ':nom'           => $nom,
                ':prenom'        => $prenom,
                ':email'         => $email,
                ':password_hash' => $hash,
                ':role'          => 'collaborateur',
            ]);

            $success = 'Compte créé avec succès ! <a href="index.php">Se connecter</a>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Site de Ticketing</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Site de Ticketing</h1>
        <nav>
            <div class="menu-toggle" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul id="nav-menu">
                <li><a href="home.php">Accueil</a></li>
                <li><a href="index.php">Connexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Inscription</h2>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?= $success ?></p>
            <?php endif; ?>

            <form action="signup.php" method="post">
                <input type="text" name="prenom" placeholder="Prénom"
                       value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                <input type="text" name="nom" placeholder="Nom"
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                <input type="email" name="email" placeholder="Email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <input type="password" name="password" placeholder="Mot de passe (min. 8 caractères)" required>
                <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
                <button type="submit">S'inscrire</button>
            </form>
        </section>
    </main>
    <footer>
        <p>&copy; 2026 Site de Ticketing. Tous droits réservés.</p>
    </footer>
    <script>
        function toggleMenu() {
            document.getElementById('nav-menu').classList.toggle('show');
        }
    </script>
</body>
</html>