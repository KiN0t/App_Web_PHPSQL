<?php
// tickets.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

$pdo  = getDB();
$uid  = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

// Récupère les projets accessibles selon le rôle
if ($role === 'admin') {
    $projets = $pdo->query('SELECT id, nom FROM projets ORDER BY nom')->fetchAll();
} elseif ($role === 'collaborateur') {
    $stmt = $pdo->prepare('SELECT p.id, p.nom FROM projets p JOIN projet_collaborateurs pc ON p.id = pc.projet_id WHERE pc.user_id = :uid ORDER BY p.nom');
    $stmt->execute([':uid' => $uid]);
    $projets = $stmt->fetchAll();
} else {
    // client
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :uid');
    $stmt->execute([':uid' => $uid]);
    $client_id = $pdo->prepare('SELECT client_id FROM users WHERE id = :uid');
    $client_id->execute([':uid' => $uid]);
    $cid = $client_id->fetchColumn();
    $stmt = $pdo->prepare('SELECT id, nom FROM projets WHERE client_id = :cid ORDER BY nom');
    $stmt->execute([':cid' => $cid]);
    $projets = $stmt->fetchAll();
}

// Création d'un ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titre'])) {
    $titre       = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $projet_id   = (int)$_POST['projet_id'];
    $type        = ($role === 'admin' && isset($_POST['type'])) ? $_POST['type'] : 'inclus';
    $assigne_a   = !empty($_POST['assigne_a']) ? (int)$_POST['assigne_a'] : null;

    if (!empty($titre) && !empty($description) && $projet_id > 0) {
        $stmt = $pdo->prepare('
            INSERT INTO tickets (projet_id, created_by, assigne_a, titre, description, type, statut)
            VALUES (:projet_id, :created_by, :assigne_a, :titre, :description, :type, "ouvert")
        ');
        $stmt->execute([
            ':projet_id'   => $projet_id,
            ':created_by'  => $uid,
            ':assigne_a'   => $assigne_a,
            ':titre'       => $titre,
            ':description' => $description,
            ':type'        => $type,
        ]);
    }
    header('Location: tickets.php');
    exit;
}

// Suppression d'un ticket (admin seulement)
if (isset($_GET['delete']) && $role === 'admin') {
    $stmt = $pdo->prepare('DELETE FROM tickets WHERE id = :id');
    $stmt->execute([':id' => (int)$_GET['delete']]);
    header('Location: tickets.php');
    exit;
}

// Filtres
$search           = trim($_GET['search'] ?? '');
$status_filter    = $_GET['status'] ?? '';
$projet_filter    = $_GET['projet_id'] ?? '';
$type_filter      = $_GET['type'] ?? '';

// Requête tickets selon le rôle
$where   = [];
$params  = [];

if ($role === 'collaborateur') {
    $where[]            = 't.projet_id IN (SELECT projet_id FROM projet_collaborateurs WHERE user_id = :uid)';
    $params[':uid']     = $uid;
} elseif ($role === 'client') {
    $where[]            = 'p.client_id = :cid';
    $params[':cid']     = $cid;
}

if ($search) {
    $where[]              = '(t.titre LIKE :search OR t.description LIKE :search)';
    $params[':search']    = '%' . $search . '%';
}
if ($status_filter) {
    $where[]              = 't.statut = :statut';
    $params[':statut']    = $status_filter;
}
if ($projet_filter) {
    $where[]              = 't.projet_id = :pid';
    $params[':pid']       = (int)$projet_filter;
}
if ($type_filter && $role === 'admin') {
    $where[]              = 't.type = :type';
    $params[':type']      = $type_filter;
}

$sql = '
    SELECT t.*, p.nom AS projet_nom, u.prenom, u.nom AS user_nom
    FROM tickets t
    JOIN projets p ON t.projet_id = p.id
    JOIN users u ON t.created_by = u.id
' . (!empty($where) ? 'WHERE ' . implode(' AND ', $where) : '') . '
    ORDER BY t.created_at DESC
';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets - Site de Ticketing</title>
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
        <div class="tickets-container">
            <!-- Liste des tickets -->
            <section class="tickets-list">
                <h2>Liste des tickets</h2>

                <button class="filters-toggle" onclick="toggleFilters()">Filtres & Recherche</button>

                <div class="controls-bar collapsed" id="filtersPanel">
                    <form method="get" action="">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="filters-row">
                            <select name="status">
                                <option value="">Tous les statuts</option>
                                <?php foreach (['ouvert','en_cours','en_attente_validation','validé','refusé','fermé'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="projet_id">
                                <option value="">Tous les projets</option>
                                <?php foreach ($projets as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $projet_filter == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($role === 'admin'): ?>
                            <select name="type">
                                <option value="">Tous types</option>
                                <option value="inclus" <?= $type_filter === 'inclus' ? 'selected' : '' ?>>Inclus</option>
                                <option value="facturable" <?= $type_filter === 'facturable' ? 'selected' : '' ?>>Facturable</option>
                            </select>
                            <?php endif; ?>
                        </div>
                        <div class="sort-buttons">
                            <button type="submit" class="sort-btn">Filtrer</button>
                            <a href="tickets.php" class="sort-btn">Réinitialiser</a>
                        </div>
                    </form>
                </div>

                <div id="tickets-list">
                    <?php if (empty($tickets)): ?>
                        <div class="no-results">
                            <p>Aucun ticket trouvé.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket-item">
                            <div class="ticket-header">
                                <h3 class="ticket-title"><?= htmlspecialchars($ticket['titre']) ?></h3>
                                <div>
                                    <span class="badge <?= $ticket['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $ticket['statut'])) ?></span>
                                    <?php if ($role === 'admin' && $ticket['type'] === 'facturable'): ?>
                                        <span class="billable-badge badge" style="background-color:#fff9c4;color:#f57f17;">Facturable</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="ticket-description"><?= htmlspecialchars($ticket['description']) ?></p>
                            <div class="ticket-meta">
                                <span class="project-name"><?= htmlspecialchars($ticket['projet_nom']) ?></span>
                                <span>Par <?= htmlspecialchars($ticket['prenom'] . ' ' . $ticket['user_nom']) ?></span>
                            </div>
                            <div class="ticket-actions">
                                <a href="ticket_detail.php?id=<?= $ticket['id'] ?>" class="btn-details">Voir détails</a>
                                <?php if ($role === 'admin'): ?>
                                    <a href="?delete=<?= $ticket['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer ce ticket ?')">Supprimer</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Formulaire création -->
            <section class="ticket-form">
                <h2>Créer un ticket</h2>
                <form method="post" action="">
                    <input type="text" name="titre" placeholder="Titre du ticket" required>
                    <textarea name="description" placeholder="Description" rows="4" required></textarea>
                    <select name="projet_id" id="select-projet" required>
                        <option value="">Sélectionner un projet</option>
                        <?php foreach ($projets as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="assigne_a" id="select-collab" disabled>
                        <option value="">— Assigner à un collaborateur (optionnel) —</option>
                    </select>
                    <?php if ($role === 'admin'): ?>
                    <select name="type">
                        <option value="inclus">Inclus</option>
                        <option value="facturable">Facturable</option>
                    </select>
                    <?php endif; ?>
                    <button type="submit">Créer le ticket</button>
                </form>
            </section>
        </div>
    </main>
    <footer>
        <p>&copy; 2026 Site de Ticketing. Tous droits réservés.</p>
    </footer>
    <script>
        function toggleMenu() { document.getElementById('nav-menu').classList.toggle('show'); }
        function toggleFilters() { document.getElementById('filtersPanel').classList.toggle('collapsed'); }

        document.getElementById('select-projet').addEventListener('change', function () {
            const projetId = this.value;
            const selectCollab = document.getElementById('select-collab');

            selectCollab.innerHTML = '<option value="">— Assigner à un collaborateur (optionnel) —</option>';

            if (!projetId) {
                selectCollab.disabled = true;
                return;
            }

            fetch('get_collaborateurs.php?projet_id=' + projetId)
                .then(r => r.json())
                .then(collabs => {
                    if (collabs.length === 0) {
                        selectCollab.disabled = true;
                        return;
                    }
                    collabs.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.prenom + ' ' + c.nom + ' (' + c.email + ')';
                        selectCollab.appendChild(opt);
                    });
                    selectCollab.disabled = false;
                });
        });
    </script>
</body>
</html>