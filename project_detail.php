<?php
// project_detail.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: projects.php');
    exit;
}

require_once 'db.php';

$pdo  = getDB();
$uid  = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$pid  = (int)$_GET['id'];

// Récupère le projet
$stmt = $pdo->prepare('SELECT p.*, c.nom AS client_nom FROM projets p JOIN clients c ON p.client_id = c.id WHERE p.id = :id');
$stmt->execute([':id' => $pid]);
$projet = $stmt->fetch();

if (!$projet) {
    header('Location: projects.php');
    exit;
}

// Contrôle d'accès
if ($role === 'collaborateur') {
    $stmt = $pdo->prepare('SELECT 1 FROM projet_collaborateurs WHERE projet_id = :pid AND user_id = :uid');
    $stmt->execute([':pid' => $pid, ':uid' => $uid]);
    if (!$stmt->fetch()) { header('Location: projects.php'); exit; }
} elseif ($role === 'client') {
    $stmt = $pdo->prepare('SELECT client_id FROM users WHERE id = :uid');
    $stmt->execute([':uid' => $uid]);
    $cid = $stmt->fetchColumn();
    if ($projet['client_id'] != $cid) { header('Location: projects.php'); exit; }
}

// Tickets du projet
$stmt = $pdo->prepare('SELECT t.*, u.prenom, u.nom AS user_nom FROM tickets t JOIN users u ON t.created_by = u.id WHERE t.projet_id = :pid ORDER BY t.created_at DESC');
$stmt->execute([':pid' => $pid]);
$tickets = $stmt->fetchAll();

// Collaborateurs du projet
$stmt = $pdo->prepare('SELECT u.prenom, u.nom FROM users u JOIN projet_collaborateurs pc ON u.id = pc.user_id WHERE pc.projet_id = :pid');
$stmt->execute([':pid' => $pid]);
$collaborateurs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projet - <?= htmlspecialchars($projet['nom']) ?></title>
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
                <li><a href="projects.php">Projets</a></li>
                <li><a href="tickets.php">Tickets</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Détails du projet</h2>
            <div class="detail">
                <h3><?= htmlspecialchars($projet['nom']) ?></h3>
                <p><strong>Description :</strong> <?= htmlspecialchars($projet['description']) ?></p>
                <p><strong>Statut :</strong> <span class="badge <?= $projet['statut'] ?>"><?= ucfirst($projet['statut']) ?></span></p>
                <p><strong>Client :</strong> <?= htmlspecialchars($projet['client_nom']) ?></p>

                <?php if (!empty($collaborateurs)): ?>
                <p><strong>Collaborateurs :</strong>
                    <?= implode(', ', array_map(fn($c) => htmlspecialchars($c['prenom'] . ' ' . $c['nom']), $collaborateurs)) ?>
                </p>
                <?php endif; ?>

                <h4>Tickets (<?= count($tickets) ?>)</h4>
                <?php if (empty($tickets)): ?>
                    <p>Aucun ticket pour ce projet.</p>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                    <div class="ticket-item">
                        <div class="ticket-header">
                            <span><?= htmlspecialchars($t['titre']) ?></span>
                            <span class="badge <?= $t['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $t['statut'])) ?></span>
                        </div>
                        <a href="ticket_detail.php?id=<?= $t['id'] ?>" class="btn-details">Voir détails</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <a href="projects.php" class="btn-details">Retour aux projets</a>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2026 Site de Ticketing. Tous droits réservés.</p>
    </footer>
    <script>
        function toggleMenu() { document.getElementById('nav-menu').classList.toggle('show'); }
    </script>
</body>
</html>