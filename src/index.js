import { createMap } from './script/Game/map.js';
import { createCamera } from './script/Game/camera.js';
import { createRenderer } from './script/Game/renderer.js';
import { createControls } from './script/Game/control.js';
import { createColoredGrid } from './script/Game/grid.js';
import { createPlane } from './script/Game/plane.js';
import { PNJ } from './script/Game/pnj.js';
import {createSquareBuilder} from "./script/Game/squareCreateBuilder";

const sizeGrid = 50;
const divisionGrid = 52;

// Création de la case pour la création des batiments
createSquareBuilder({
    size: 100,
    borderWidth: 3,
    borderColor: 'black',
    background: 'white',
    dropdownWidth: 220,
    dropdownHeight: 150,
    imageSrc: './src/assets/ImageBuilderSquare.png',
});

// Map
const scene = createMap();
const camera = createCamera();
const renderer = createRenderer();
const controls = createControls(camera, renderer);

const { cellules, gridValues } = createColoredGrid(sizeGrid, divisionGrid);
cellules.forEach(cell => scene.add(cell));

const plane = createPlane(sizeGrid);
scene.add(plane);

const routes = gridValues.filter(c => c.value === 1);

// Création des PNJ
const colors = ['red', 'blue', 'yellow', 'purple','red', 'blue', 'yellow', 'purple','red', 'blue', 'yellow', 'purple'];
const pnjs = colors.map(color => new PNJ(scene, routes, color, sizeGrid / divisionGrid, 0.02));

// Redimensionaa
window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
});

// Animation
function animate() {
    requestAnimationFrame(animate);
    controls.update();
    pnjs.forEach(p => p.update());
    renderer.render(scene, camera);
}
animate();
