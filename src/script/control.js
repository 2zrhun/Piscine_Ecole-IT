import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';

export function createControls(camera, renderer) {
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    return controls;
}
