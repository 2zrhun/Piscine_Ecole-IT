import * as THREE from 'three';

const windows = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer();
const textureLoader = new THREE.TextureLoader();

renderer.setClearColor(0xffffff, 1); // white background


renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);






let cube = new THREE.Mesh(
    new THREE.BoxGeometry(8,7,2),
    new THREE.MeshBasicMaterial({ map: textureLoader.load('/src/style/empire.png'), color: "white" })
);

cube.position.set(0, 0, -5);

windows.add(cube);
const cube1 = new THREE.Mesh(
    new THREE.BoxGeometry(),
    new THREE.MeshBasicMaterial({ color: "green" })
);

// Alternatively, you can use set():
cube1.position.set(2, 1, -5);

windows.add(cube1);

camera.position.z = 5;

// 5. Render loop
function animate() {
    requestAnimationFrame(animate);
    cube.rotation.x += 0.01; // optional rotation
    cube.rotation.y += 0.01;
    renderer.render(windows, camera);
}

animate();
