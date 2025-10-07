import * as THREE from 'three';

export function setupRaycast(map, camera, plane) {
    const raycaster = new THREE.Raycaster();
    const souris = new THREE.Vector2();

    window.addEventListener('click', (e) => {
        souris.x = (e.clientX / window.innerWidth) * 2 - 1;
        souris.y = -(e.clientY / window.innerHeight) * 2 + 1;

        raycaster.setFromCamera(souris, camera);
        const intersects = raycaster.intersectObject(plane);
        if (intersects.length > 0) {
            const point = intersects[0].point;
            const cubeGeom = new THREE.BoxGeometry(1, 1, 1);
            const cubeMat = new THREE.MeshLambertMaterial({ color: "orange" });
            const cube = new THREE.Mesh(cubeGeom, cubeMat);
            cube.position.set(Math.round(point.x), 0.5, Math.round(point.z));
            map.add(cube);
        }
    });
}
