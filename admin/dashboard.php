<?php
require_once '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || !is_admin($conn, $_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Récupérer les statistiques
$stats = [
    'total_users' => get_total_count($conn, 'users'),
    'total_cvs' => get_total_count($conn, 'cvs'),
    'total_invitations' => get_total_count($conn, 'invitations'),
    'new_this_week' => get_new_this_week($conn)
];
?>
<div class="admin-dashboard">
    <h1>Tableau de Bord Administrateur</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_users'] ?></div>
            <div class="stat-label">Utilisateurs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_cvs'] ?></div>
            <div class="stat-label">CV Créés</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_invitations'] ?></div>
            <div class="stat-label">Invitations</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['new_this_week'] ?></div>
            <div class="stat-label">Nouveaux cette semaine</div>
        </div>
    </div>
    
    <div class="admin-sections">
        <section class="recent-activity">
            <h2>Activité Récente</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (get_recent_activity($conn) as $activity): ?>
                    <tr>
                        <td><?= htmlspecialchars($activity['username']) ?></td>
                        <td><?= htmlspecialchars($activity['action']) ?></td>
                        <td><?= format_date($activity['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <section class="quick-actions">
            <h2>Actions Rapides</h2>
            <div class="action-buttons">
                <a href="manage-cv.php" class="btn">Gérer les CV</a>
                <a href="manage-invitations.php" class="btn">Gérer les Invitations</a>
                <a href="manage-users.php" class="btn">Gérer les Utilisateurs</a>
            </div>
        </section>
    </div>
</div>

<style>
.admin-dashboard {
    padding: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.stat-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #666;
    font-size: 1rem;
}

.admin-sections {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.admin-table th, .admin-table td {
    padding: 0.8rem 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.admin-table th {
    background: #f5f5f5;
    font-weight: 600;
}

.action-buttons {
    display: grid;
    gap: 1rem;
    margin-top: 1rem;
}

.action-buttons .btn {
    display: block;
    text-align: center;
    padding: 1rem;
}

@media (max-width: 768px) {
    .admin-sections {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>