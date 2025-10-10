import * as THREE from 'three';
import { createCellule } from './cellule.js';

export function createGrid(sizeGrid, divisionGrid) {
    return new THREE.GridHelper(sizeGrid, divisionGrid, "red", "#CFDBD5");
}

export function createColoredGrid(sizeGrid, divisionGrid, color = "#CFDBD5") {
    const cellules = [];

    const step = sizeGrid / divisionGrid;

    // Boucle pour rajouter de la couleur à chaque cellule de la map
    for (let i = -sizeGrid/2; i < sizeGrid/2; i += step) {
        for (let j = -sizeGrid/2; j < sizeGrid/2; j += step) {
            const cellule = createCellule(i, j, color, step);
            cellules.push(cellule);
        }
    }

    return cellules;
}
