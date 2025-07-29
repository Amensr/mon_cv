const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const mysql = require('mysql2/promise');
const jwt = require('jsonwebtoken');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: ["http://localhost", "https://votre-domaine.com"],
    methods: ["GET", "POST"]
  }
});

// Configuration de la base de données
const dbConfig = {
  host: 'localhost',
  user: 'cv_user',
  password: 'votre_mot_de_passe_secure',
  database: 'cv_creator'
};

// Middleware pour vérifier le JWT
const authenticateToken = async (socket, next) => {
  const token = socket.handshake.auth.token;
  if (!token) return next(new Error('Authentification requise'));

  try {
    const decoded = jwt.verify(token, 'votre_secret_jwt_super_secure');
    const conn = await mysql.createConnection(dbConfig);
    const [user] = await conn.execute(
      'SELECT id, username, is_premium FROM users WHERE id = ?', 
      [decoded.userId]
    );
    conn.end();

    if (!user.length) return next(new Error('Utilisateur non trouvé'));
    
    socket.user = user[0];
    next();
  } catch (err) {
    next(new Error('Token invalide'));
  }
};

// Gestion des connexions
io.use(authenticateToken).on('connection', (socket) => {
  console.log(`Utilisateur connecté: ${socket.user.username}`);

  // Rejoindre une room de collaboration CV
  socket.on('join_cv_session', async (cvId) => {
    const conn = await mysql.createConnection(dbConfig);
    const [cv] = await conn.execute(
      'SELECT user_id, title FROM cvs WHERE id = ?', 
      [cvId]
    );

    if (!cv.length) {
      socket.emit('error', 'CV non trouvé');
      return;
    }

    // Vérifier les permissions
    if (cv[0].user_id !== socket.user.id && !socket.user.is_premium) {
      socket.emit('error', 'Vous devez être premium pour collaborer');
      return;
    }

    socket.join(`cv_${cvId}`);
    socket.currentCv = cvId;

    // Envoyer l'historique des modifications
    const [history] = await conn.execute(
      'SELECT * FROM cv_history WHERE cv_id = ? ORDER BY created_at DESC LIMIT 50',
      [cvId]
    );

    socket.emit('cv_history', history);

    // Notifier les autres utilisateurs
    socket.to(`cv_${cvId}`).emit('user_joined', {
      userId: socket.user.id,
      username: socket.user.username
    });

    conn.end();
  });

  // Gestion des modifications en temps réel
  socket.on('cv_update', async (update) => {
    if (!socket.currentCv) return;

    const conn = await mysql.createConnection(dbConfig);
    
    try {
      // Sauvegarder dans l'historique
      await conn.execute(
        'INSERT INTO cv_history (cv_id, user_id, section, changes) VALUES (?, ?, ?, ?)',
        [socket.currentCv, socket.user.id, update.section, JSON.stringify(update.changes)]
      );

      // Diffuser la mise à jour
      socket.to(`cv_${socket.currentCv}`).emit('cv_updated', {
        userId: socket.user.id,
        username: socket.user.username,
        section: update.section,
        changes: update.changes,
        timestamp: new Date()
      });

      // Mettre à jour le CV principal (optionnel)
      if (update.saveToMain) {
        await conn.execute(
          `UPDATE cvs SET ${update.section} = ? WHERE id = ?`,
          [JSON.stringify(update.changes), socket.currentCv]
        );
      }
    } catch (err) {
      console.error('Erreur de mise à jour:', err);
    } finally {
      conn.end();
    }
  });

  // Gestion de la présence
  socket.on('cursor_position', (position) => {
    if (!socket.currentCv) return;
    
    socket.to(`cv_${socket.currentCv}`).emit('user_cursor', {
      userId: socket.user.id,
      username: socket.user.username,
      position
    });
  });

  // Gestion de la déconnexion
  socket.on('disconnect', () => {
    if (socket.currentCv) {
      socket.to(`cv_${socket.currentCv}`).emit('user_left', {
        userId: socket.user.id,
        username: socket.user.username
      });
    }
    console.log(`Utilisateur déconnecté: ${socket.user.username}`);
  });
});

// Démarrer le serveur
const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
  console.log(`Serveur temps réel écoutant sur le port ${PORT}`);
});