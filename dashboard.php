<?php
// dashboard.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

$pdo = getDB();

// Stats depuis la vraie DB
$stmt = $pdo->prepare('
    SELECT COUNT(*) FROM tickets t
    JOIN projets p ON t.projet_id = p.id
    WHERE p.client_id IN (
        SELECT client_id FROM users WHERE id = :uid
    ) OR :role IN ("admin", "collaborateur")
');

// Total tickets accessibles selon le rôle
$role = $_SESSION['user_role'];
$uid  = $_SESSION['user_id'];

if ($role === 'admin') {
    $totalTickets = $pdo->query('SELECT COUNT(*) FROM tickets')->fetchColumn();
    $openTickets  = $pdo->query("SELECT COUNT(*) FROM tickets WHERE statut = 'ouvert'")->fetchColumn();
    $totalProjects = $pdo->query('SELECT COUNT(*) FROM projets')->fetchColumn();
} elseif ($role === 'collaborateur') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM tickets t JOIN projet_collaborateurs pc ON t.projet_id = pc.projet_id WHERE pc.user_id = :uid');
    $stmt->execute([':uid' => $uid]);
    $totalTickets = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets t JOIN projet_collaborateurs pc ON t.projet_id = pc.projet_id WHERE pc.user_id = :uid AND t.statut = 'ouvert'");
    $stmt->execute([':uid' => $uid]);
    $openTickets = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM projet_collaborateurs WHERE user_id = :uid');
    $stmt->execute([':uid' => $uid]);
    $totalProjects = $stmt->fetchColumn();
} else {
    // client
    $stmt = $pdo->prepare('SELECT client_id FROM users WHERE id = :uid');
    $stmt->execute([':uid' => $uid]);
    $client_id = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM tickets t JOIN projets p ON t.projet_id = p.id WHERE p.client_id = :cid');
    $stmt->execute([':cid' => $client_id]);
    $totalTickets = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets t JOIN projets p ON t.projet_id = p.id WHERE p.client_id = :cid AND t.statut = 'ouvert'");
    $stmt->execute([':cid' => $client_id]);
    $openTickets = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM projets WHERE client_id = :cid');
    $stmt->execute([':cid' => $client_id]);
    $totalProjects = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Site de Ticketing</title>
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
                <li><a href="projects.php">Projets</a></li>
                <li><a href="tickets.php">Tickets</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="settings.php">Paramètres</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php if ($role === 'admin'): ?>
        <div class="admin-banner">
            <span>Administration</span>
            <a href="promote.php">Promouvoir un utilisateur</a>
        </div>
        <?php endif; ?>
        <section>
            <h2>Tableau de bord</h2>
            <p>Bienvenue, <?= htmlspecialchars($_SESSION['user_nom']) ?> ! Voici un aperçu de vos activités.</p>
            <div class="list">
                <div class="item">
                    <h3>Tickets totaux</h3>
                    <p><?= $totalTickets ?> tickets au total.</p>
                    <a href="tickets.php">Voir tous</a>
                </div>
                <div class="item">
                    <h3>Projets totaux</h3>
                    <p><?= $totalProjects ?> projets au total.</p>
                    <a href="projects.php">Voir tous</a>
                </div>
                <div class="item">
                    <h3>Tickets ouverts</h3>
                    <p><?= $openTickets ?> tickets en cours.</p>
                    <a href="tickets.php">Voir tous</a>
                </div>
            </div>
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