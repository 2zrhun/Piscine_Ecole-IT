/**
 * Service Socket.io pour la synchronisation multijoueur en temps réel
 */
import { io } from 'socket.io-client';

class SocketService {
    constructor() {
        this.socket = null;
        this.connected = false;
        this.currentPseudo = null;
        this.listeners = {
            onPlayerJoined: null,
            onPlayerLeft: null,
            onMapChanged: null,
            onCurrentVisitors: null
        };
    }

    connect(serverUrl = 'http://localhost:3001') {
        if (this.socket && this.connected) {
            console.log('✅ Socket déjà connecté');
            return;
        }

        this.socket = io(serverUrl, {
            transports: ['websocket', 'polling'],
            withCredentials: true
        });

        this.socket.on('connect', () => {
            this.connected = true;
            console.log('🔌 Connecté au serveur Socket.io:', this.socket.id);
        });

        this.socket.on('disconnect', () => {
            this.connected = false;
            console.log('🔌 Déconnecté du serveur Socket.io');
        });

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Événement: nouveau visiteur rejoint la carte
        this.socket.on('playerJoinedMap', (data) => {
            console.log('👤 Nouveau visiteur:', data.visitor.pseudo);
            if (this.listeners.onPlayerJoined) {
                this.listeners.onPlayerJoined(data);
            }
        });

        // Événement: visiteur quitte la carte
        this.socket.on('playerLeftMap', (data) => {
            console.log('🚪 Visiteur parti:', data.pseudo);
            if (this.listeners.onPlayerLeft) {
                this.listeners.onPlayerLeft(data);
            }
        });

        // Événement: modification de la carte
        this.socket.on('mapChanged', (data) => {
            console.log('🔨 Carte modifiée par:', data.updatedBy);
            if (this.listeners.onMapChanged) {
                this.listeners.onMapChanged(data);
            }
        });

        // Événement: liste des visiteurs actuels
        this.socket.on('currentVisitors', (data) => {
            console.log('📋 Visiteurs actuels:', data.visitors.length);
            if (this.listeners.onCurrentVisitors) {
                this.listeners.onCurrentVisitors(data);
            }
        });
    }

    /**
     * Rejoindre la carte d'un joueur
     */
    visitMap(mapId, mapName, ownerPseudo, visitorPseudo) {
        if (!this.socket || !this.connected) {
            console.error('❌ Socket non connecté');
            return;
        }

        const token = localStorage.getItem('auth_token');
        this.currentPseudo = visitorPseudo;

        this.socket.emit('visitMap', {
            mapId: mapId,
            mapName: mapName,
            ownerPseudo: ownerPseudo,
            visitorPseudo: visitorPseudo,
            token: token
        });

        console.log(`🗺️  Visite de la carte "${mapName}" de ${ownerPseudo}`);
    }

    /**
     * Quitter la carte actuelle
     */
    leaveMap() {
        if (!this.socket || !this.connected) {
            console.error('❌ Socket non connecté');
            return;
        }

        this.socket.emit('leaveMap');
        console.log('🚪 Carte quittée');
    }

    /**
     * Envoyer une modification de carte
     */
    sendMapUpdate(action, buildingData = null, position = null) {
        if (!this.socket || !this.connected) {
            console.error('❌ Socket non connecté');
            return;
        }

        this.socket.emit('mapUpdate', {
            action: action,
            buildingData: buildingData,
            position: position
        });

        console.log(`📡 Modification envoyée: ${action}`);
    }

    /**
     * Enregistrer des callbacks pour les événements
     */
    on(event, callback) {
        switch (event) {
            case 'playerJoined':
                this.listeners.onPlayerJoined = callback;
                break;
            case 'playerLeft':
                this.listeners.onPlayerLeft = callback;
                break;
            case 'mapChanged':
                this.listeners.onMapChanged = callback;
                break;
            case 'currentVisitors':
                this.listeners.onCurrentVisitors = callback;
                break;
            default:
                console.warn(`⚠️ Événement inconnu: ${event}`);
        }
    }

    /**
     * Déconnexion propre
     */
    disconnect() {
        if (this.socket) {
            this.socket.disconnect();
            this.socket = null;
            this.connected = false;
            console.log('🔌 Socket déconnecté manuellement');
        }
    }

    isConnected() {
        return this.connected;
    }
}

// Export une instance singleton
export default new SocketService();
