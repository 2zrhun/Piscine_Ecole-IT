import * as THREE from 'three';

export class PNJ {
    constructor(scene, routes, color = 'red', step = 1, speed = 0.05) {
        this.routes = routes;
        this.step = step;
        this.speed = speed;

        // Case de départ aléatoire
        const start = this.routes[Math.floor(Math.random() * this.routes.length)];

        const geometry = new THREE.SphereGeometry(step * 0.3, 16, 16);
        const material = new THREE.MeshStandardMaterial({ color });
        this.mesh = new THREE.Mesh(geometry, material);
        this.mesh.position.set(start.x, step * 0.3, start.z);
        scene.add(this.mesh);

        this.currentCell = start;
        this.previousCell = null;
        this.targetCell = null;
    }

    update() {
        if (!this.targetCell) {
            this.targetCell = this.getNextCell();
            if (!this.targetCell) return;
        }

        const targetPos = new THREE.Vector3(this.targetCell.x, this.mesh.position.y, this.targetCell.z);
        const dir = new THREE.Vector3().subVectors(targetPos, this.mesh.position);
        const dist = dir.length();

        if (dist < 0.05) {
            this.previousCell = this.currentCell;
            this.currentCell = this.targetCell;
            this.targetCell = this.getNextCell();
        } else {
            dir.normalize();
            this.mesh.position.add(dir.multiplyScalar(this.speed));
        }
    }

    getNextCell() {
        const neighbors = [
            { x: this.currentCell.x + this.step, z: this.currentCell.z },
            { x: this.currentCell.x - this.step, z: this.currentCell.z },
            { x: this.currentCell.x, z: this.currentCell.z + this.step },
            { x: this.currentCell.x, z: this.currentCell.z - this.step },
        ];
        let valid = neighbors.map(n =>
            this.routes.find(r => Math.abs(r.x - n.x) < this.step / 2 && Math.abs(r.z - n.z) < this.step / 2)
        ).filter(Boolean);

        if (this.previousCell) {
            valid = valid.filter(cell =>
                !(cell.x === this.previousCell.x && cell.z === this.previousCell.z)
            );
        }

        if (valid.length === 0) return this.previousCell || this.currentCell;

        return valid[Math.floor(Math.random() * valid.length)];
    }
}
