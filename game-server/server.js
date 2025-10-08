const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');

const app = express();
const server = http.createServer(app);

// Configuration CORS pour développement local
const io = socketIo(server, {
  cors: {
    origin: "http://localhost:5173", // Votre frontend Vite
    methods: ["GET", "POST"],
    credentials: true
  }
});

app.use(cors({
  origin: 'http://localhost:5173',
  credentials: true
}));

// Stockage temporaire des parties en mémoire (pour développement)
const gameRooms = new Map();
const players = new Map();

// Connexion d'un joueur
io.on('connection', (socket) => {
  console.log(`🎮 Joueur connecté: ${socket.id}`);

  // Rejoindre une partie
  socket.on('joinGame', (data) => {
    const { gameId, playerName, token } = data;
    
    // TODO: Valider le token JWT avec Symfony API
    console.log(`👤 ${playerName} rejoint la partie ${gameId}`);
    
    // Créer ou rejoindre la room
    if (!gameRooms.has(gameId)) {
      gameRooms.set(gameId, {
        id: gameId,
        players: [],
        gameState: {
          status: 'waiting',
          currentPlayer: null,
          board: null
        }
      });
    }
    
    const room = gameRooms.get(gameId);
    const player = {
      id: socket.id,
      name: playerName,
      token: token
    };
    
    room.players.push(player);
    players.set(socket.id, { gameId, player });
    
    socket.join(gameId);
    
    // Notifier tous les joueurs de la room
    io.to(gameId).emit('playerJoined', {
      player: player,
      gameRoom: room
    });
    
    // Si 2 joueurs, démarrer la partie
    if (room.players.length === 2) {
      room.gameState.status = 'playing';
      room.gameState.currentPlayer = room.players[0].id;
      
      io.to(gameId).emit('gameStarted', {
        gameState: room.gameState,
        players: room.players
      });
    }
  });

  // Mouvement du joueur
  socket.on('playerMove', (data) => {
    const playerData = players.get(socket.id);
    if (!playerData) return;
    
    const { gameId } = playerData;
    const room = gameRooms.get(gameId);
    
    if (room && room.gameState.currentPlayer === socket.id) {
      // Traiter le mouvement
      console.log(`🎯 Mouvement reçu:`, data);
      
      // Changer de joueur actuel
      const currentIndex = room.players.findIndex(p => p.id === socket.id);
      const nextIndex = (currentIndex + 1) % room.players.length;
      room.gameState.currentPlayer = room.players[nextIndex].id;
      
      // Diffuser le mouvement à tous les joueurs de la partie
      io.to(gameId).emit('gameUpdate', {
        move: data,
        gameState: room.gameState,
        playerId: socket.id
      });
    }
  });

  // Chat en jeu
  socket.on('chatMessage', (data) => {
    const playerData = players.get(socket.id);
    if (!playerData) return;
    
    const { gameId } = playerData;
    const { message } = data;
    
    io.to(gameId).emit('chatMessage', {
      playerId: socket.id,
      playerName: playerData.player.name,
      message: message,
      timestamp: Date.now()
    });
  });

  // Déconnexion
  socket.on('disconnect', () => {
    console.log(`🚪 Joueur déconnecté: ${socket.id}`);
    
    const playerData = players.get(socket.id);
    if (playerData) {
      const { gameId } = playerData;
      const room = gameRooms.get(gameId);
      
      if (room) {
        // Retirer le joueur de la room
        room.players = room.players.filter(p => p.id !== socket.id);
        
        // Notifier les autres joueurs
        socket.to(gameId).emit('playerLeft', {
          playerId: socket.id,
          gameRoom: room
        });
        
        // Supprimer la room si vide
        if (room.players.length === 0) {
          gameRooms.delete(gameId);
        }
      }
      
      players.delete(socket.id);
    }
  });
});

const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
  console.log(`🚀 Serveur Socket.io démarré sur http://localhost:${PORT}`);
  console.log(`📡 CORS autorisé pour: http://localhost:5173`);
});