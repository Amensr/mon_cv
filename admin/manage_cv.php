<?php
require_once '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Vérifier les droits admin
// ...

$cvs = get_all_cvs($conn);
?>

<div class="admin-container">
    <h1>Gestion des CV</h1>
    
    <div class="search-bar">
        <input type="text" id="search-cv" placeholder="Rechercher un CV...">
        <button class="search-btn">🔍</button>
    </div>
    
    <div class="cv-list">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Utilisateur</th>
                    <th>Thème</th>
                    <th>Créé le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cvs as $cv): ?>
                <tr>
                    <td><?= $cv['id'] ?></td>
                    <td><?= htmlspecialchars($cv['title']) ?></td>
                    <td><?= get_username_by_id($conn, $cv['user_id']) ?></td>
                    <td><?= htmlspecialchars($cv['theme_name']) ?></td>
                    <td><?= date('d/m/Y', strtotime($cv['created_at'])) ?></td>
                    <td class="actions">
                        <a href="../preview.php?id=<?= $cv['id'] ?>" target="_blank" class="btn-view">👁️</a>
                        <button class="btn-delete" data-id="<?= $cv['id'] ?>">🗑️</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination">
        <!-- Pagination -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche en temps réel
    document.getElementById('search-cv').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.cv-list tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Suppression de CV
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const cvId = this.getAttribute('data-id');
            
            if (confirm('Voulez-vous vraiment supprimer ce CV ?')) {
                fetch('../api/admin.php?action=delete_cv&id=' + cvId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('tr').style.animation = 'fadeOut 0.3s ease';
                            setTimeout(() => {
                                this.closest('tr').remove();
                            }, 300);
                        }
                    });
            }
        });
    });
    
    // Effet de survol sur les lignes
    const rows = document.querySelectorAll('.admin-table tbody tr');
    rows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>