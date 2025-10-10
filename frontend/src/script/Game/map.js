import * as THREE from 'three';

export function createMap() {
    const map = new THREE.Scene();
    map.background = new THREE.Color("columbia blue");

    const light = new THREE.DirectionalLight(0xffffff, 1);
    light.position.set(20, 20, 20);
    map.add(light);
    map.add(new THREE.AmbientLight(0x555555));

    return map;
}
