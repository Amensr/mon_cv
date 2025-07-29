document.addEventListener('DOMContentLoaded', function() {
    const typeCards = document.querySelectorAll('.type-card');
    const invitationForm = document.getElementById('invitation-form');
    const livePreview = document.getElementById('invitation-preview');
    const colorInputs = document.querySelectorAll('input[type="color"]');
    
    // Sélection du type d'invitation
    typeCards.forEach(card => {
        card.addEventListener('click', function() {
            typeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            
            const typeId = this.getAttribute('data-type-id');
            document.getElementById('selected-type-id').value = typeId;
            
            // Charger le template de prévisualisation
            loadInvitationTemplate(typeId);
        });
    });
    
    // Mise à jour en temps réel de la prévisualisation
    invitationForm.addEventListener('input', function(e) {
        updateLivePreview();
    });
    
    colorInputs.forEach(input => {
        input.addEventListener('input', function() {
            updateLivePreview();
        });
    });
    
    function loadInvitationTemplate(typeId) {
        fetch(`api/invitations.php?action=get_template&type_id=${typeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    livePreview.innerHTML = data.template;
                    updateLivePreview();
                    
                    // Animation de transition
                    livePreview.style.animation = 'fadeIn 0.5s ease';
                    setTimeout(() => {
                        livePreview.style.animation = '';
                    }, 500);
                }
            });
    }
    
    function updateLivePreview() {
        const formData = new FormData(invitationForm);
        const content = {
            event_name: formData.get('event_name') || "Nom de l'Événement",
            date: formData.get('date') || "00/00/0000",
            time: formData.get('time') || "00:00",
            location: formData.get('location') || "Lieu de l'Événement",
            custom_message: formData.get('custom_message') || "Message Personnalisé"
        };
        
        // Mettre à jour le contenu
        Object.keys(content).forEach(key => {
            const elements = livePreview.querySelectorAll(`[data-field="${key}"]`);
            elements.forEach(el => {
                el.textContent = content[key];
            });
        });
        
        // Mettre à jour le design
        const primaryColor = formData.get('primary_color') || '#3498db';
        const secondaryColor = formData.get('secondary_color') || '#2ecc71';
        
        livePreview.style.setProperty('--primary-color', primaryColor);
        livePreview.style.setProperty('--secondary-color', secondaryColor);
        
        // Effet de mise à jour visuelle
        livePreview.style.transform = 'scale(0.98)';
        setTimeout(() => {
            livePreview.style.transform = 'scale(1)';
        }, 200);
    }
    
    // Initialiser le glisser-déposer pour les images
    const dropZone = livePreview.querySelector('.image-upload');
    if (dropZone) {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            if (file.type.match('image.*')) {
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    dropZone.style.backgroundImage = `url(${e.target.result})`;
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
});