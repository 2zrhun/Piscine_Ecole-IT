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
// Stockage des joueurs dans les cartes (mapId -> liste de joueurs)
const mapVisitors = new Map();

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

  // ========== SYSTÈME DE VISITE DE CARTE ==========

  // Rejoindre la carte d'un joueur
  socket.on('visitMap', (data) => {
    const { mapId, mapName, ownerPseudo, visitorPseudo, token } = data;

    console.log(`🗺️  ${visitorPseudo} visite la carte de ${ownerPseudo} (Map ID: ${mapId})`);

    // Créer la room de la carte si elle n'existe pas
    const roomId = `map_${mapId}`;
    if (!mapVisitors.has(roomId)) {
      mapVisitors.set(roomId, {
        mapId: mapId,
        mapName: mapName,
        owner: ownerPseudo,
        visitors: []
      });
    }

    const mapRoom = mapVisitors.get(roomId);
    const visitor = {
      socketId: socket.id,
      pseudo: visitorPseudo,
      token: token,
      joinedAt: Date.now()
    };

    // Ajouter le visiteur
    mapRoom.visitors.push(visitor);

    // Stocker l'info du joueur
    players.set(socket.id, {
      currentMap: roomId,
      pseudo: visitorPseudo,
      isVisitor: ownerPseudo !== visitorPseudo
    });

    // Rejoindre la room Socket.io
    socket.join(roomId);

    // Notifier tous les joueurs présents sur cette carte qu'un nouveau visiteur a rejoint
    io.to(roomId).emit('playerJoinedMap', {
      visitor: visitor,
      totalVisitors: mapRoom.visitors.length,
      mapInfo: {
        mapId: mapId,
        mapName: mapName,
        owner: ownerPseudo
      }
    });

    // Envoyer la liste COMPLÈTE des visiteurs à TOUS les joueurs de la carte (y compris le nouveau)
    io.to(roomId).emit('currentVisitors', {
      visitors: mapRoom.visitors,
      mapInfo: {
        mapId: mapId,
        mapName: mapName,
        owner: ownerPseudo
      }
    });

    console.log(`📊 Carte "${mapName}" : ${mapRoom.visitors.length} visiteur(s)`, mapRoom.visitors.map(v => v.pseudo));
  });

  // Quitter une carte
  socket.on('leaveMap', () => {
    const playerData = players.get(socket.id);
    if (!playerData) return;

    const { currentMap, pseudo } = playerData;
    if (!currentMap) return;

    const mapRoom = mapVisitors.get(currentMap);
    if (mapRoom) {
      // Retirer le visiteur
      mapRoom.visitors = mapRoom.visitors.filter(v => v.socketId !== socket.id);

      // Notifier les autres qu'un joueur est parti
      socket.to(currentMap).emit('playerLeftMap', {
        socketId: socket.id,
        pseudo: pseudo,
        remainingVisitors: mapRoom.visitors.length
      });

      // Envoyer la liste mise à jour des visiteurs à tous ceux qui restent
      io.to(currentMap).emit('currentVisitors', {
        visitors: mapRoom.visitors,
        mapInfo: {
          mapId: mapRoom.mapId,
          mapName: mapRoom.mapName,
          owner: mapRoom.owner
        }
      });

      // Quitter la room
      socket.leave(currentMap);

      console.log(`🚪 ${pseudo} a quitté "${mapRoom.mapName}" (${mapRoom.visitors.length} visiteur(s) restant(s))`);

      // Supprimer la room si vide
      if (mapRoom.visitors.length === 0) {
        mapVisitors.delete(currentMap);
        console.log(`🗑️  Carte "${mapRoom.mapName}" fermée (aucun visiteur)`);
      }
    }

    players.delete(socket.id);
  });

  // Synchronisation des modifications de carte en temps réel
  socket.on('mapUpdate', (data) => {
    const playerData = players.get(socket.id);
    if (!playerData) return;

    const { currentMap, pseudo } = playerData;
    const { action, buildingData, position } = data;

    console.log(`🔨 ${pseudo} modifie la carte: ${action}`);

    // Diffuser la modification à tous les visiteurs de la carte
    socket.to(currentMap).emit('mapChanged', {
      action: action,
      buildingData: buildingData,
      position: position,
      updatedBy: pseudo,
      timestamp: Date.now()
    });
  });

  // Déconnexion
  socket.on('disconnect', () => {
    console.log(`🚪 Joueur déconnecté: ${socket.id}`);

    const playerData = players.get(socket.id);
    if (playerData) {
      // Gestion des anciennes rooms de jeu
      const { gameId } = playerData;
      if (gameId) {
        const room = gameRooms.get(gameId);
        if (room) {
          room.players = room.players.filter(p => p.id !== socket.id);
          socket.to(gameId).emit('playerLeft', {
            playerId: socket.id,
            gameRoom: room
          });
          if (room.players.length === 0) {
            gameRooms.delete(gameId);
          }
        }
      }

      // Gestion des visites de carte
      const { currentMap, pseudo } = playerData;
      if (currentMap) {
        const mapRoom = mapVisitors.get(currentMap);
        if (mapRoom) {
          mapRoom.visitors = mapRoom.visitors.filter(v => v.socketId !== socket.id);

          // Notifier les autres qu'un joueur est parti
          socket.to(currentMap).emit('playerLeftMap', {
            socketId: socket.id,
            pseudo: pseudo,
            remainingVisitors: mapRoom.visitors.length
          });

          // Envoyer la liste mise à jour des visiteurs à tous ceux qui restent
          io.to(currentMap).emit('currentVisitors', {
            visitors: mapRoom.visitors,
            mapInfo: {
              mapId: mapRoom.mapId,
              mapName: mapRoom.mapName,
              owner: mapRoom.owner
            }
          });

          console.log(`🚪 ${pseudo} déconnecté de "${mapRoom.mapName}" (${mapRoom.visitors.length} visiteur(s) restant(s))`);

          if (mapRoom.visitors.length === 0) {
            mapVisitors.delete(currentMap);
            console.log(`🗑️  Carte "${mapRoom.mapName}" fermée (aucun visiteur)`);
          }
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