import * as THREE from 'three';
import { createCellule } from './cellule.js';

export function createGrid(sizeGrid, divisionGrid) {
    return new THREE.GridHelper(sizeGrid, divisionGrid, "red", "blue");
}

export function createColoredGrid(sizeGrid, divisionGrid) {
    const cells = [];

    const step = sizeGrid / divisionGrid;

    for (let i = -sizeGrid/2; i < sizeGrid/2; i += step) {
        for (let j = -sizeGrid/2; j < sizeGrid/2; j += step) {
            const cell = createCellule(i, j, "yellow", step);
            cells.push(cell);
        }
    }

    return cells;
}
