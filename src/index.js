import { createMap } from './script/Game/map.js';
import { createCamera } from './script/Game/camera.js';
import { createRenderer } from './script/Game/renderer.js';
import { createControls } from './script/Game/control.js';
import { createGrid } from './script/Game/grid.js';
import { createColoredGrid } from './script/Game/grid.js';
import { createPlane } from './script/Game/plane.js';
import { initLifebarWithUser } from './components/lifebar.js';
import { saveMapConfig } from './services/api.js';
import { getMapConfig } from './services/api.js';
import BuildingGallery from './components/BuildingGallery.js';

window.addEventListener('DOMContentLoaded', () => {
    // Affichage dynamique de la galerie des buildings
    const galleryBar = document.createElement('div');
    galleryBar.id = 'building-gallery-bar';
    galleryBar.style.display = 'flex';
    galleryBar.style.gap = '10px';
    galleryBar.style.margin = '20px 0';
    galleryBar.style.overflowX = 'auto';
    document.body.appendChild(galleryBar);

    fetch('http://localhost:8000/api/buildings/images')
        .then(response => response.json())
        .then(buildings => {
            buildings.forEach(building => {
                const img = document.createElement('img');
                img.src = building.image;
                img.alt = building.name;
                img.title = building.name;
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '8px';
                img.style.cursor = 'pointer';
                img.addEventListener('click', () => {
                    // Action au clic : charger le modèle 3D ou autre
                    if (building.file) {
                        loadBuildingModel(building.file);
                    }
                });
                galleryBar.appendChild(img);
            });
        })
        .catch(err => {
            const errorMsg = document.createElement('p');
            errorMsg.textContent = 'Erreur chargement des buildings: ' + err;
            errorMsg.style.color = 'red';
            galleryBar.appendChild(errorMsg);
        });
    // Récupérer le token depuis le localStorage
    const token = localStorage.getItem('auth_token');

    // Afficher la galerie
    const gallery = new BuildingGallery('gallery', function(building) {
        console.log('Building sélectionné:', building);
        loadBuildingModel(building.file);
    });

    // Récupérer directement la map de l'utilisateur connecté
    if (token) {
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
            // Reconstruire la map avec la config reçue
            setupMapFromConfig(config);
        })
        .catch(err => {
            alert('Erreur récupération map: ' + err);
        });
    } else {
        alert('Token manquant. Veuillez vous reconnecter.');
    }
});


function loadBuildingModel(fileName) {
    const loader = new THREE.GLTFLoader();
    loader.load(`/uploads/building/${fileName}`, function(gltf) {
        scene.add(gltf.scene);
    }, undefined, function(error) {
        console.error('Erreur chargement modèle 3D:', error);
    });
}

// Fonction pour reconstruire la map à partir de la config reçue
function setupMapFromConfig(config) {
    // Paramètres de la map
    const sizeGrid = config.grid.size;
    const divisionGrid = config.grid.division;

    // Création de la map
    const scene = createMap();
    const camera = createCamera();
    const renderer = createRenderer();
    const controls = createControls(camera, renderer);

    // Ajout de la grille et du plane
    scene.add(createGrid(sizeGrid, divisionGrid));
    const plane = createPlane(sizeGrid);
    scene.add(plane);

    // Ajouter la couleur de la maps
    const coloredCells = createColoredGrid(sizeGrid, divisionGrid);
    coloredCells.forEach(cell => scene.add(cell));

    // Ajouter les éléments personnalisés
    if (Array.isArray(config.elements)) {
        config.elements.forEach(el => {
            // Exemple: ajouter un cube ou une sphère selon le type
            // ... à compléter selon ta logique d'affichage
        });
    }

    // Créer le bloc d'affichage avec le pseudo de l'utilisateur connecté
    initLifebarWithUser(50, 100, 10);

    // Redimension
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    // Animation
    function animate() {
        requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }
    animate();
}


// ...existing code...
