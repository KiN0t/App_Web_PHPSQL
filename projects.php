<?php
// projects.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

$pdo  = getDB();
$uid  = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

// Récupère le client_id si besoin
$cid = null;
if ($role === 'client') {
    $stmt = $pdo->prepare('SELECT client_id FROM users WHERE id = :uid');
    $stmt->execute([':uid' => $uid]);
    $cid = $stmt->fetchColumn();
}

// Création d'un projet (admin seulement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'admin' && isset($_POST['nom'])) {
    $nom        = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $statut     = $_POST['statut'];
    $client_id  = (int)$_POST['client_id'];

    if (!empty($nom) && !empty($statut) && $client_id > 0) {
        $stmt = $pdo->prepare('INSERT INTO projets (client_id, nom, description, statut) VALUES (:cid, :nom, :desc, :statut)');
        $stmt->execute([':cid' => $client_id, ':nom' => $nom, ':desc' => $description, ':statut' => $statut]);
        $new_projet_id = $pdo->lastInsertId();

        // Lie les collaborateurs sélectionnés (optionnel)
        $collab_ids = $_POST['collaborateurs'] ?? [];
        foreach ($collab_ids as $collab_id) {
            $collab_id = (int)$collab_id;
            if ($collab_id > 0) {
                $stmt2 = $pdo->prepare('INSERT IGNORE INTO projet_collaborateurs (projet_id, user_id) VALUES (:pid, :uid)');
                $stmt2->execute([':pid' => $new_projet_id, ':uid' => $collab_id]);
            }
        }
    }
    header('Location: projects.php');
    exit;
}

// Suppression (admin seulement)
if (isset($_GET['delete']) && $role === 'admin') {
    $stmt = $pdo->prepare('DELETE FROM projets WHERE id = :id');
    $stmt->execute([':id' => (int)$_GET['delete']]);
    header('Location: projects.php');
    exit;
}

// Récupère les projets selon le rôle
if ($role === 'admin') {
    $projets = $pdo->query('SELECT p.*, c.nom AS client_nom FROM projets p JOIN clients c ON p.client_id = c.id ORDER BY p.created_at DESC')->fetchAll();
} elseif ($role === 'collaborateur') {
    $stmt = $pdo->prepare('SELECT p.*, c.nom AS client_nom FROM projets p JOIN clients c ON p.client_id = c.id JOIN projet_collaborateurs pc ON p.id = pc.projet_id WHERE pc.user_id = :uid ORDER BY p.created_at DESC');
    $stmt->execute([':uid' => $uid]);
    $projets = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare('SELECT p.*, c.nom AS client_nom FROM projets p JOIN clients c ON p.client_id = c.id WHERE p.client_id = :cid ORDER BY p.created_at DESC');
    $stmt->execute([':cid' => $cid]);
    $projets = $stmt->fetchAll();
}

// Liste des clients pour le formulaire admin
$clients = [];
$collaborateurs = [];
if ($role === 'admin') {
    $clients = $pdo->query('SELECT id, nom FROM clients ORDER BY nom')->fetchAll();
    $collaborateurs = $pdo->query("SELECT id, prenom, nom, email FROM users WHERE role = 'collaborateur' ORDER BY nom")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets - Site de Ticketing</title>
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
                <li><a href="profile.php">Profil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="projects-container">
            <section class="projects-list">
                <h2>Liste des projets</h2>
                <?php if (empty($projets)): ?>
                    <p>Aucun projet disponible.</p>
                <?php else: ?>
                <?php foreach ($projets as $projet): ?>
                <div class="project-item" data-status="<?= $projet['statut'] ?>">
                    <div class="project-header">
                        <h3 class="project-title"><?= htmlspecialchars($projet['nom']) ?></h3>
                        <span class="badge <?= $projet['statut'] ?>"><?= ucfirst(str_replace('-', ' ', $projet['statut'])) ?></span>
                    </div>
                    <p class="project-description"><?= htmlspecialchars($projet['description']) ?></p>
                    <p><small>Client : <?= htmlspecialchars($projet['client_nom']) ?></small></p>
                    <div class="project-actions">
                        <a href="project_detail.php?id=<?= $projet['id'] ?>" class="btn-details">Voir détails</a>
                        <?php if ($role === 'admin'): ?>
                            <a href="?delete=<?= $projet['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer ce projet ?')">Supprimer</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <!-- Formulaire visible pour tous mais création réservée à admin -->
            <section class="project-form">
                <h2>Créer un projet</h2>
                <?php if ($role === 'admin'): ?>
                <form method="post" action="">
                    <input type="text" name="nom" placeholder="Nom du projet" required>
                    <textarea name="description" placeholder="Description" rows="4"></textarea>
                    <select name="client_id" required>
                        <option value="">Sélectionner un client</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="statut" required>
                        <option value="">Statut</option>
                        <option value="actif">Actif</option>
                        <option value="archivé">Archivé</option>
                        <option value="terminé">Terminé</option>
                    </select>
                    <label>Collaborateurs (optionnel)</label>
                    <select name="collaborateurs[]" multiple size="4">
                        <?php foreach ($collaborateurs as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom'] . ' — ' . $c['email']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small>Maintiens Ctrl (ou Cmd) pour sélectionner plusieurs collaborateurs.</small>
                    <button type="submit">Créer le projet</button>
                </form>
                <?php else: ?>
                    <p>Seul un administrateur peut créer des projets.</p>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <footer>
        <p>&copy; 2026 Site de Ticketing. Tous droits réservés.</p>
    </footer>
    <script>
        function toggleMenu() { document.getElementById('nav-menu').classList.toggle('show'); }
    </script>
</body>
</html>