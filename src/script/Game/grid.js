import * as THREE from 'three';
import { createCellule } from './cellule.js';

export function createColoredGrid(sizeGrid, divisionGrid) {
    const cellules = [];
    const gridValues = [];
    const step = sizeGrid / divisionGrid;

    const routeSize = 2;
    const greenParcelle = 15;
    const tracage = routeSize + greenParcelle;

    let horizontalIndex = 0;
    for (let i = -sizeGrid / 2; i < sizeGrid / 2; i += step) {
        let verticalIndex = 0;
        for (let j = -sizeGrid / 2; j < sizeGrid / 2; j += step) {
            const posRow = horizontalIndex % tracage;
            const posCol = verticalIndex % tracage;
            const isRoute = (posRow < routeSize || posCol < routeSize);
            const color = isRoute ? 'gray' : 'green';

            const cellule = createCellule(i, j, color, step);
            cellule.userData.value = isRoute ? 1 : 2;

            cellules.push(cellule);

            gridValues.push({ x: i + step / 2, z: j + step / 2, value: cellule.userData.value });

            verticalIndex++;
        }
        horizontalIndex++;
    }

    return { cellules, gridValues };
}