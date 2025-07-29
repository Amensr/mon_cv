document.addEventListener('DOMContentLoaded', function() {
    // Gestion du menu mobile
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Gestion des messages flash
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 5000);
    });
    
    // Confirmation avant suppression
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
    
    // Animation des éléments au défilement
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate-fade-in, .animate-slide-up');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 100) {
                element.classList.add('animated');
            }
        });
    };
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Exécuter une fois au chargement
});

// Fonction pour afficher les aperçus en temps réel
function setupLivePreview(sourceElementId, targetElementId) {
    const sourceElement = document.getElementById(sourceElementId);
    const targetElement = document.getElementById(targetElementId);
    
    if (sourceElement && targetElement) {
        sourceElement.addEventListener('input', function() {
            targetElement.textContent = this.value;
        });
    }
}

// Fonction utilitaire pour les requêtes AJAX
function makeAjaxRequest(url, method, data, successCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            successCallback(JSON.parse(xhr.responseText));
        } else {
            console.error('Erreur AJAX:', xhr.statusText);
        }
    };
    
    xhr.onerror = function() {
        console.error('Erreur réseau');
    };
    
    xhr.send(data);
}