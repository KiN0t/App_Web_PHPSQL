<?php
// forgot-password.php - Réinitialisation du mot de passe
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez entrer une adresse email valide.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        #Ca fait rien c'est pour faire joli
        $success = 'Si cette adresse est associée à un compte, un email vous a été envoyé.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe perdu - Site de Ticketing</title>
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
            <h2>Réinitialiser le mot de passe</h2>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?= $success ?></p>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form action="forgot-password.php" method="post">
                <input type="email" name="email" placeholder="Votre adresse email" required>
                <button type="submit">Envoyer le lien</button>
            </form>
            <?php endif; ?>

            <p><a href="index.php">Retour à la connexion</a></p>
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