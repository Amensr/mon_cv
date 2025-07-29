<?php
require_once '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || !is_admin($conn, $_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Récupérer toutes les invitations
$invitations = get_all_invitations($conn);
?>

<div class="admin-container">
    <h1>Gérer les Invitations</h1>
    
    <div class="admin-toolbar">
        <div class="search-box">
            <input type="text" id="search-invitations" placeholder="Rechercher des invitations...">
        </div>
        <div class="filter-options">
            <select id="filter-type">
                <option value="">Tous les types</option>
                <?php foreach (get_invitation_types($conn) as $type): ?>
                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Type</th>
                <th>Utilisateur</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invitations as $inv): ?>
            <tr>
                <td><?= $inv['id'] ?></td>
                <td><?= htmlspecialchars($inv['title']) ?></td>
                <td><?= htmlspecialchars($inv['type_name']) ?></td>
                <td><?= htmlspecialchars($inv['username']) ?></td>
                <td><?= format_date($inv['created_at']) ?></td>
                <td class="actions">
                    <a href="../preview-invitation.php?id=<?= $inv['id'] ?>" target="_blank" class="btn-view">👁️</a>
                    <button class="btn-delete" data-id="<?= $inv['id'] ?>">🗑️</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche en temps réel
    document.getElementById('search-invitations').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.admin-table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Filtrage par type
    document.getElementById('filter-type').addEventListener('change', function() {
        const typeId = this.value;
        const rows = document.querySelectorAll('.admin-table tbody tr');
        
        rows.forEach(row => {
            if (!typeId) {
                row.style.display = '';
                return;
            }
            
            const rowType = row.querySelector('td:nth-child(3)').textContent;
            const typeOption = document.querySelector(`#filter-type option[value="${typeId}"]`);
            
            if (typeOption && rowType === typeOption.textContent) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Suppression d'invitation
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const invitationId = this.getAttribute('data-id');
            
            if (confirm('Voulez-vous vraiment supprimer cette invitation ?')) {
                fetch('../api/invitations.php?action=delete&id=' + invitationId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('tr').style.animation = 'fadeOut 0.3s ease';
                            setTimeout(() => {
                                this.closest('tr').remove();
                            }, 300);
                        } else {
                            alert('Erreur: ' + (data.message || 'Échec de la suppression'));
                        }
                    });
            }
        });
    });
});
</script>

<style>
.admin-container {
    padding: 2rem;
}

.admin-toolbar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-box input {
    padding: 0.8rem 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 250px;
}

.filter-options select {
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.admin-table th {
    background: #f5f5f5;
    padding: 1rem;
    text-align: left;
}

.admin-table td {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid #eee;
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn-view, .btn-delete {
    padding: 0.3rem 0.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.btn-view {
    background: #3498db;
    color: white;
}

.btn-delete {
    background: #e74c3c;
    color: white;
}

.btn-view:hover, .btn-delete:hover {
    opacity: 0.8;
    transform: scale(1.1);
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

@media (max-width: 768px) {
    .admin-toolbar {
        flex-direction: column;
    }
    
    .search-box input, .filter-options select {
        width: 100%;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>