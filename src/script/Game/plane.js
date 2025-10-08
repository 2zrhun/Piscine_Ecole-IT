import * as THREE from 'three';

export function createPlane(sizeGrid) {
    const planeGeom = new THREE.PlaneGeometry(sizeGrid, sizeGrid);
    const planeMat = new THREE.MeshBasicMaterial({ visible: false });
    const plane = new THREE.Mesh(planeGeom, planeMat);
    plane.rotation.x = -Math.PI / 2;
    return plane;
}
