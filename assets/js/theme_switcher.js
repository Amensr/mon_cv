document.addEventListener('DOMContentLoaded', function() {
    const themeCards = document.querySelectorAll('.theme-card');
    const cvFormContainer = document.querySelector('.cv-form-container');
    const selectedThemeId = document.getElementById('selected-theme-id');
    
    // Animation de sélection de thème
    themeCards.forEach(card => {
        card.addEventListener('click', function() {
            // Effet visuel de sélection
            themeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            
            // Animation de transition
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 200);
            
            // Afficher le formulaire
            const themeId = this.getAttribute('data-theme-id');
            selectedThemeId.value = themeId;
            
            cvFormContainer.style.opacity = 0;
            cvFormContainer.style.display = 'block';
            
            setTimeout(() => {
                cvFormContainer.style.opacity = 1;
                cvFormContainer.scrollIntoView({ behavior: 'smooth' });
                
                // Charger dynamiquement le CSS du thème
                loadThemeCSS(themeId);
            }, 300);
        });
    });
    
    function loadThemeCSS(themeId) {
        // Enlever les anciens styles de thème
        document.querySelectorAll('link[data-theme]').forEach(link => link.remove());
        
        // Ajouter le nouveau style
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `assets/css/themes/${themeId}.css`;
        link.setAttribute('data-theme', themeId);
        document.head.appendChild(link);
    }
    
    // Effets spéciaux pour les sections du formulaire
    const formSections = document.querySelectorAll('.form-section');
    formSections.forEach((section, index) => {
        section.style.opacity = 0;
        section.style.transform = 'translateY(20px)';
        section.style.transition = `all 0.5s ease ${index * 0.1}s`;
        
        // Observer l'intersection pour déclencher l'animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = 1;
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });
        
        observer.observe(section);
    });
});