import * as THREE from 'three';

export function createCellule(x, z, color = "gray", size = 1) {

    const geometry = new THREE.BoxGeometry(size, 0.1, size);
    const material = new THREE.MeshBasicMaterial({ color: color });
    const cellule = new THREE.Mesh(geometry, material);

    cellule.position.set(x + size / 2, 0.05, z + size / 2);
    return cellule;
}
