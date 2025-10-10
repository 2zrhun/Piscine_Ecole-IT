import { createMap } from './script/Game/map.js';
import { createCamera } from './script/Game/camera.js';
import { createRenderer } from './script/Game/renderer.js';
import { createControls } from './script/Game/control.js';
import { createGrid } from './script/Game/grid.js';
import { createColoredGrid } from './script/Game/grid.js';
import { createPlane } from './script/Game/plane.js';
import { initLifebarWithUser } from './components/lifebar.js';

// Paramètres de la map
const sizeGrid = 50;
const divisionGrid = 20;

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
