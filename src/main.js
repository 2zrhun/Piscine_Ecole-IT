import { createMap } from './script/map.js';
import { createCamera } from './script/camera.js';
import { createRenderer } from './script/renderer.js';
import { createControls } from './script/control.js';
import { createGrid } from './script/grid.js';
import { createPlane } from './script/plane.js';
import { setupRaycast } from './script/raycast.js';

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

// Gestion clic
setupRaycast(scene, camera, plane);

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
