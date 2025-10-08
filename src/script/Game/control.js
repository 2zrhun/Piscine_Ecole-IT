import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';

export function createControls(camera, renderer) {
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;

    // Permet de controller l'angle min a avoir pour empecher de regarder sous la maps
    controls.minPolarAngle = 0;
    controls.maxPolarAngle = Math.PI/2 - 0.3;

    // Limiter le zoom sur la map
    controls.minDistance = 10;
    controls.maxDistance = 50;

    return controls;
}