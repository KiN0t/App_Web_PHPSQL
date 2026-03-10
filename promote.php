<?php
// promote.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once 'db.php';

$pdo     = getDB();
$error   = '';
$success = '';

function loadClients(PDO $pdo): array {
    return $pdo->query("
        SELECT u.id, u.prenom, u.nom, u.email, c.nom AS client_nom
        FROM users u
        LEFT JOIN clients c ON u.client_id = c.id
        WHERE u.role = 'client'
        ORDER BY u.nom, u.prenom
    ")->fetchAll();
}

function loadNonAdmins(PDO $pdo): array {
    return $pdo->query("
        SELECT u.id, u.prenom, u.nom, u.email, u.role
        FROM users u
        WHERE u.role != 'admin'
        ORDER BY u.role, u.nom, u.prenom
    ")->fetchAll();
}

$clients   = loadClients($pdo);
$nonAdmins = loadNonAdmins($pdo);
$projets   = $pdo->query("SELECT id, nom FROM projets ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);

    // Client → Collaborateur
    if ($action === 'to_collab') {
        $projet_id = (int)($_POST['projet_id'] ?? 0);

        if ($user_id <= 0) {
            $error = 'Veuillez sélectionner un utilisateur.';
        } elseif ($projet_id <= 0) {
            $error = 'Veuillez sélectionner un projet.';
        } else {
            $stmt = $pdo->prepare("SELECT id, prenom, nom FROM users WHERE id = :id AND role = 'client'");
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'Utilisateur introuvable ou déjà collaborateur/admin.';
            } else {
                $pdo->prepare("UPDATE users SET role = 'collaborateur', client_id = NULL, updated_at = NOW() WHERE id = :id")
                    ->execute([':id' => $user_id]);

                $pdo->prepare("INSERT IGNORE INTO projet_collaborateurs (projet_id, user_id) VALUES (:pid, :uid)")
                    ->execute([':pid' => $projet_id, ':uid' => $user_id]);

                $pdo->prepare("INSERT INTO audit_log (user_id, action, entite, entite_id, detail) VALUES (:uid, 'promotion_collaborateur', 'user', :target, :detail)")
                    ->execute([':uid' => $_SESSION['user_id'], ':target' => $user_id, ':detail' => 'Promu collaborateur sur projet #' . $projet_id]);

                $success   = htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . ' est maintenant collaborateur et assigné au projet.';
                $clients   = loadClients($pdo);
                $nonAdmins = loadNonAdmins($pdo);
            }
        }

    // N'importe qui → Admin
    } elseif ($action === 'to_admin') {
        if ($user_id <= 0) {
            $error = 'Veuillez sélectionner un utilisateur.';
        } else {
            $stmt = $pdo->prepare("SELECT id, prenom, nom, role FROM users WHERE id = :id AND role != 'admin'");
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'Utilisateur introuvable ou déjà admin.';
            } else {
                $pdo->prepare("UPDATE users SET role = 'admin', client_id = NULL, updated_at = NOW() WHERE id = :id")
                    ->execute([':id' => $user_id]);

                $pdo->prepare("INSERT INTO audit_log (user_id, action, entite, entite_id, detail) VALUES (:uid, 'promotion_admin', 'user', :target, :detail)")
                    ->execute([':uid' => $_SESSION['user_id'], ':target' => $user_id, ':detail' => 'Promu admin depuis rôle : ' . $user['role']]);

                $success   = htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . ' est maintenant administrateur.';
                $clients   = loadClients($pdo);
                $nonAdmins = loadNonAdmins($pdo);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des rôles - Site de Ticketing</title>
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

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>

        <!-- Section 1 : Client devient Collaborateur -->
        <section>
            <h2>Promouvoir un client en collaborateur</h2>
            <p>Change le rôle de <strong>client</strong> à <strong>collaborateur</strong> et assigne l'utilisateur à un projet.</p>

            <?php if (empty($clients)): ?>
                <p>Aucun client disponible à promouvoir.</p>
            <?php else: ?>
            <form method="post" action="">
                <input type="hidden" name="action" value="to_collab">

                <label for="user_id_collab">Client à promouvoir</label>
                <select name="user_id" id="user_id_collab" required>
                    <option value="">— Sélectionner un client —</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom'] . ' (' . $c['email'] . ')') ?>
                            <?= $c['client_nom'] ? ' — ' . htmlspecialchars($c['client_nom']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="projet_id">Assigner au projet</label>
                <select name="projet_id" id="projet_id" required>
                    <option value="">— Sélectionner un projet —</option>
                    <?php foreach ($projets as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" onclick="return confirm('Promouvoir en collaborateur ?')">
                    Promouvoir en collaborateur
                </button>
            </form>
            <?php endif; ?>
        </section>

        <hr>

        <!-- Section 2 : Tout le monde devient Admin -->
        <section>
            <h2>Promouvoir en administrateur</h2>
            <p>Change le rôle d'un <strong>client</strong> ou <strong>collaborateur</strong> en <strong>admin</strong>.</p>

            <?php if (empty($nonAdmins)): ?>
                <p>Aucun utilisateur disponible.</p>
            <?php else: ?>
            <form method="post" action="">
                <input type="hidden" name="action" value="to_admin">

                <label for="user_id_admin">Utilisateur à promouvoir</label>
                <select name="user_id" id="user_id_admin" required>
                    <option value="">— Sélectionner un utilisateur —</option>
                    <?php foreach ($nonAdmins as $u): ?>
                        <option value="<?= $u['id'] ?>">
                            <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom'] . ' (' . $u['email'] . ')') ?>
                            — <?= ucfirst($u['role']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" onclick="return confirm('Promouvoir en admin ? Action irréversible depuis cette interface.')">
                    Promouvoir en admin
                </button>
            </form>
            <?php endif; ?>
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