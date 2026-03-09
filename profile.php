<?php
// profile.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

$pdo   = getDB();
$uid   = $_SESSION['user_id'];
$error = '';
$success = '';

// Récupère les infos de l'utilisateur connecté
$stmt = $pdo->prepare('SELECT nom, prenom, email, role FROM users WHERE id = :id');
$stmt->execute([':id' => $uid]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $prenom   = trim($_POST['prenom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email)) {
        $error = 'Nom, prénom et email sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif ($password !== '' && strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== '' && $password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        // Vérifie que l'email n'est pas déjà pris par quelqu'un d'autre
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id');
        $stmt->execute([':email' => $email, ':id' => $uid]);
        if ($stmt->fetch()) {
            $error = 'Cette adresse email est déjà utilisée.';
        } else {
            if ($password !== '') {
                // Mise à jour avec nouveau mot de passe
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('UPDATE users SET nom = :nom, prenom = :prenom, email = :email, password_hash = :hash, updated_at = NOW() WHERE id = :id');
                $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':email' => $email, ':hash' => $hash, ':id' => $uid]);
            } else {
                // Mise à jour sans changer le mot de passe
                $stmt = $pdo->prepare('UPDATE users SET nom = :nom, prenom = :prenom, email = :email, updated_at = NOW() WHERE id = :id');
                $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':email' => $email, ':id' => $uid]);
            }

            // Met à jour le nom en session
            $_SESSION['user_nom'] = $prenom . ' ' . $nom;

            // Recharge les infos
            $user['nom']    = $nom;
            $user['prenom'] = $prenom;
            $user['email']  = $email;

            $success = 'Profil mis à jour avec succès.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil utilisateur - Site de Ticketing</title>
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
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="settings.php">Paramètres</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Profil utilisateur</h2>
            <p>Rôle : <strong><?= htmlspecialchars($user['role']) ?></strong></p>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <form action="profile.php" method="post">
                <input type="text" name="prenom" placeholder="Prénom"
                       value="<?= htmlspecialchars($user['prenom']) ?>" required>
                <input type="text" name="nom" placeholder="Nom"
                       value="<?= htmlspecialchars($user['nom']) ?>" required>
                <input type="email" name="email" placeholder="Email"
                       value="<?= htmlspecialchars($user['email']) ?>" required>
                <input type="password" name="password" placeholder="Nouveau mot de passe (optionnel)">
                <input type="password" name="confirm_password" placeholder="Confirmer le nouveau mot de passe">
                <button type="submit">Mettre à jour</button>
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