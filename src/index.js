import { createMap } from './script/Game/map.js';
import { createCamera } from './script/Game/camera.js';
import { createRenderer } from './script/Game/renderer.js';
import { createControls } from './script/Game/control.js';
import { createGrid } from './script/Game/grid.js';
import { createColoredGrid } from './script/Game/grid.js';
import { createPlane } from './script/Game/plane.js';
import { initLifebarWithUser } from './components/lifebar.js';
import BuildingGallery from './components/BuildingGallery.js';
import BuildingBar from './components/BuildingBar.js';
import MapVisitor from './components/MapVisitor.js';
import MapIndicator from './components/MapIndicator.js';
import socketService from './services/socketService.js';

// Variables globales pour la carte actuelle
let currentScene = null;
let currentCamera = null;
let currentRenderer = null;
let currentControls = null;
let currentUserPseudo = null;
let mapIndicator = null;

window.addEventListener('DOMContentLoaded', () => {
    // Récupérer le token depuis le localStorage
    const token = localStorage.getItem('auth_token');

    if (!token) {
        alert('Token manquant. Veuillez vous reconnecter.');
        return;
    }

    // Connecter Socket.io pour le multijoueur
    socketService.connect('http://localhost:3001');

    // Initialiser l'indicateur de carte
    mapIndicator = new MapIndicator();

    // Instanciation de la barre des buildings en haut à gauche
    new BuildingBar('building-bar', function(building) {
        console.log('Building sélectionné:', building);
        if (building.file) {
            loadBuildingModel(building.file);
        }
    });

    // Afficher la galerie
    new BuildingGallery('gallery', function(building) {
        console.log('Building sélectionné:', building);
        loadBuildingModel(building.file);
    });

    // Instanciation du panneau de visite de carte
    const mapVisitor = new MapVisitor('map-visitor', (config, ownerPseudo, mapId, mapName, mapColor) => {
        console.log(`🗺️ Chargement de la carte de ${ownerPseudo} avec la couleur ${mapColor || 'par défaut'}`);

        // Quitter la carte actuelle si on en visite une
        if (currentUserPseudo && socketService.isConnected()) {
            socketService.leaveMap();
        }

        // Charger la nouvelle carte avec sa couleur
        setupMapFromConfig(config, ownerPseudo, mapColor);

        // Mettre à jour l'indicateur de carte
        const isOwnMap = ownerPseudo === currentUserPseudo;
        mapIndicator.show(ownerPseudo, mapName, isOwnMap);

        // Rejoindre la carte via Socket.io
        if (socketService.isConnected()) {
            const visitorPseudo = currentUserPseudo || 'Visiteur';
            socketService.visitMap(mapId, mapName, ownerPseudo, visitorPseudo);
        }
    });

    // Configuration des événements Socket.io
    socketService.on('playerJoined', (data) => {
        console.log('👤 Nouveau visiteur a rejoint la carte:', data.visitor.pseudo);
        // Ne rien faire ici, on attend l'événement 'currentVisitors'
    });

    socketService.on('playerLeft', (data) => {
        console.log('🚪 Visiteur parti:', data.pseudo);
        // Ne rien faire ici, on attend l'événement 'currentVisitors'
    });

    socketService.on('currentVisitors', (data) => {
        console.log('📋 Mise à jour des visiteurs actuels:', data.visitors);
        mapVisitor.updateVisitorsList(data.visitors);
    });

    socketService.on('mapChanged', (data) => {
        console.log('🔨 Carte modifiée par:', data.updatedBy);
        // Ici vous pouvez ajouter la logique pour afficher les modifications en temps réel
    });

    // Récupérer la map de l'utilisateur connecté au démarrage
    fetch('http://localhost:8000/api/user/map', {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        const config = data.config;
        if (!config) {
            alert('Aucune configuration de map trouvée pour cet utilisateur.');
            return;
        }

        // Récupérer le pseudo de l'utilisateur
        return fetch('http://localhost:8000/api/user/profile', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(res => res.json())
        .then(userData => {
            currentUserPseudo = userData.pseudo || 'Joueur';

            // Charger sa propre carte avec sa couleur
            setupMapFromConfig(config, currentUserPseudo, data.color);

            // Afficher l'indicateur de sa propre carte
            mapIndicator.show(currentUserPseudo, data.name, true);

            // Rejoindre sa propre carte via Socket.io
            if (socketService.isConnected()) {
                socketService.visitMap(data.id, data.name, currentUserPseudo, currentUserPseudo);
            }
        });
    })
    .catch(err => {
        console.error('Erreur récupération map:', err);
        alert('Erreur récupération map: ' + err);
    });
});


function loadBuildingModel(fileName) {
    if (!currentScene) {
        console.error('❌ Scene non initialisée');
        return;
    }

    const loader = new THREE.GLTFLoader();
    loader.load(`/uploads/building/${fileName}`, function(gltf) {
        currentScene.add(gltf.scene);

        // Notifier les autres visiteurs de l'ajout
        if (socketService.isConnected()) {
            socketService.sendMapUpdate('addBuilding', { fileName }, gltf.scene.position);
        }
    }, undefined, function(error) {
        console.error('Erreur chargement modèle 3D:', error);
    });
}

// Fonction pour reconstruire la map à partir de la config reçue
function setupMapFromConfig(config, ownerPseudo = 'Joueur', mapColor = '#CFDBD5') {
    // Nettoyer l'ancienne scène si elle existe
    if (currentRenderer) {
        currentRenderer.domElement.remove();
    }

    // Paramètres de la map
    const sizeGrid = config.grid.size;
    const divisionGrid = config.grid.division;

    // Création de la map
    currentScene = createMap();
    currentCamera = createCamera();
    currentRenderer = createRenderer();
    currentControls = createControls(currentCamera, currentRenderer);

    // Ajout de la grille et du plane
    currentScene.add(createGrid(sizeGrid, divisionGrid));
    const plane = createPlane(sizeGrid);
    currentScene.add(plane);

    // Ajouter la couleur de la maps
    const coloredCells = createColoredGrid(sizeGrid, divisionGrid, mapColor);
    coloredCells.forEach(cell => currentScene.add(cell));

    // Ajouter les éléments personnalisés
    if (Array.isArray(config.elements)) {
        config.elements.forEach(() => {
            // Exemple: ajouter un cube ou une sphère selon le type
            // ... à compléter selon votre logique d'affichage
        });
    }

    // Créer le bloc d'affichage avec le pseudo du propriétaire de la carte
    initLifebarWithUser(50, 100, 10);

    console.log(`✅ Carte de "${ownerPseudo}" chargée (${sizeGrid}x${sizeGrid})`);

    // Redimension
    const resizeHandler = () => {
        currentCamera.aspect = window.innerWidth / window.innerHeight;
        currentCamera.updateProjectionMatrix();
        currentRenderer.setSize(window.innerWidth, window.innerHeight);
    };
    window.addEventListener('resize', resizeHandler);

    // Animation
    function animate() {
        requestAnimationFrame(animate);
        currentControls.update();
        currentRenderer.render(currentScene, currentCamera);
    }
    animate();
}


// ...existing code...
