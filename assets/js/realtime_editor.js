class CVRealTimeEditor {
  constructor(cvId, token) {
    this.cvId = cvId;
    this.token = token;
    this.socket = null;
    this.users = {};
    this.cursors = {};
    this.connect();
  }

  connect() {
    this.socket = io('https://votre-domaine.com:3001', {
      auth: { token: this.token },
      reconnectionAttempts: 5,
      reconnectionDelay: 1000,
      timeout: 20000
    });

    this.setupEventListeners();
  }

  setupEventListeners() {
    this.socket.on('connect', () => {
      console.log('Connecté au serveur temps réel');
      this.socket.emit('join_cv_session', this.cvId);
    });

    this.socket.on('cv_history', (history) => {
      this.displayHistory(history);
    });

    this.socket.on('cv_updated', (update) => {
      this.applyUpdate(update);
    });

    this.socket.on('user_joined', (user) => {
      this.addUser(user);
    });

    this.socket.on('user_left', (user) => {
      this.removeUser(user);
    });

    this.socket.on('user_cursor', (data) => {
      this.updateCursor(data);
    });

    this.socket.on('error', (error) => {
      this.showError(error);
    });

    // Gestion des erreurs
    this.socket.on('connect_error', (err) => {
      console.error('Erreur de connexion:', err.message);
      this.showReconnectionUI();
    });
  }

  addUser(user) {
    this.users[user.userId] = user;
    this.displayCollaborators();
    
    // Créer un curseur pour l'utilisateur
    if (!this.cursors[user.userId]) {
      const cursor = document.createElement('div');
      cursor.className = 'remote-cursor';
      cursor.id = `cursor-${user.userId}`;
      cursor.innerHTML = `<span class="cursor-name">${user.username}</span>`;
      document.body.appendChild(cursor);
      this.cursors[user.userId] = cursor;
    }
    
    // Notification UI
    this.showNotification(`${user.username} a rejoint l'édition`);
  }

  removeUser(user) {
    delete this.users[user.userId];
    this.displayCollaborators();
    
    // Supprimer le curseur
    if (this.cursors[user.userId]) {
      document.body.removeChild(this.cursors[user.userId]);
      delete this.cursors[user.userId];
    }
    
    // Notification UI
    this.showNotification(`${user.username} a quitté l'édition`);
  }

  updateCursor(data) {
    const cursor = this.cursors[data.userId];
    if (cursor) {
      cursor.style.left = `${data.position.x}px`;
      cursor.style.top = `${data.position.y}px`;
      cursor.style.backgroundColor = this.getUserColor(data.userId);
    }
  }

  getUserColor(userId) {
    // Générer une couleur stable basée sur l'ID utilisateur
    const hash = Array.from(userId.toString()).reduce(
      (hash, char) => char.charCodeAt(0) + (hash << 5) - hash, 0
    );
    
    const color = `hsl(${Math.abs(hash % 360)}, 70%, 60%)`;
    return color;
  }

  displayCollaborators() {
    const collaboratorsList = document.getElementById('collaborators-list');
    if (collaboratorsList) {
      collaboratorsList.innerHTML = Object.values(this.users)
        .map(user => `
          <div class="collaborator" style="color: ${this.getUserColor(user.userId)}">
            <span class="user-avatar">${user.username.charAt(0).toUpperCase()}</span>
            ${user.username}
          </div>
        `)
        .join('');
    }
  }

  applyUpdate(update) {
    const section = document.querySelector(`[data-section="${update.section}"]`);
    if (!section) return;

    // Animation de mise à jour
    const highlight = document.createElement('div');
    highlight.className = 'update-highlight';
    highlight.style.backgroundColor = this.getUserColor(update.userId);
    section.appendChild(highlight);
    
    setTimeout(() => {
      highlight.remove();
    }, 1000);

    // Appliquer les changements
    switch (update.section) {
      case 'personal_info':
        this.updatePersonalInfo(update.changes);
        break;
      case 'experiences':
        this.updateExperiences(update.changes);
        break;
      // Autres sections...
    }
  }

  sendUpdate(section, changes, saveToMain = false) {
    if (this.socket && this.socket.connected) {
      this.socket.emit('cv_update', {
        section,
        changes,
        saveToMain
      });
    }
  }

  trackCursor() {
    document.addEventListener('mousemove', (e) => {
      if (this.socket && this.socket.connected && this.cvId) {
        this.socket.emit('cursor_position', {
          x: e.clientX,
          y: e.clientY
        });
      }
    });
  }

  showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'realtime-notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.classList.add('fade-out');
      setTimeout(() => {
        notification.remove();
      }, 500);
    }, 3000);
  }

  showError(error) {
    console.error('Erreur temps réel:', error);
    // Afficher une UI d'erreur élégante
  }

  showReconnectionUI() {
    // Afficher une interface de reconnexion
  }

  disconnect() {
    if (this.socket) {
      this.socket.disconnect();
    }
  }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
  const cvId = document.getElementById('cv-container').dataset.cvId;
  const token = localStorage.getItem('jwt_token');
  
  if (cvId && token) {
    const realtimeEditor = new CVRealTimeEditor(cvId, token);
    
    // Configurer les écouteurs d'édition
    setupRealTimeEditing(realtimeEditor);
    
    // Suivre la position du curseur
    realtimeEditor.trackCursor();
  }
});

function setupRealTimeEditing(editor) {
  // Configurer les événements d'édition pour chaque section
  document.querySelectorAll('[contenteditable="true"]').forEach(element => {
    element.addEventListener('input', debounce(() => {
      const section = element.closest('[data-section]').dataset.section;
      const changes = getSectionData(section);
      editor.sendUpdate(section, changes);
    }, 500));
  });
}

function getSectionData(section) {
  // Récupérer les données de la section
  // Implémentation spécifique à votre structure de données
}

function debounce(func, wait) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}