document.addEventListener('DOMContentLoaded', function () {
    // Gestion des onglets
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            // Retire la classe active de tous les boutons et contenus
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(tc => tc.classList.remove('active'));

            // Ajoute la classe active au bouton et au contenu sélectionné
            btn.classList.add('active');
            const tab = btn.getAttribute('data-tab');
            document.getElementById(tab + '-tab').classList.add('active');
        });
    });

    // Gestion de la suppression
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const type = btn.getAttribute('data-type');
            if (confirm('Voulez-vous vraiment supprimer cet élément ?')) {
                fetch('delete_work.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${encodeURIComponent(id)}&type=${encodeURIComponent(type)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Supprime la carte du DOM
                        btn.closest('.work-card').remove();
                    } else {
                        alert('Erreur lors de la suppression.');
                    }
                })
                .catch(() => alert('Erreur lors de la suppression.'));
            }
        });
    });
});