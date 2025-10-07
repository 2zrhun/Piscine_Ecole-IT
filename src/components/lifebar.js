/**
 * Crée un bloc d'affichage avec énergie et argent
 * @param {number} energy - La valeur d'énergie à afficher (par défaut 50)
 * @param {number} money - La valeur d'argent à afficher (par défaut 100)
 * @param {number} xp - La valeur d'expérience à afficher (par défaut 10)
 * @returns {HTMLElement} L'élément du bloc d'affichage
 */
export function createEnergyBlock(energy = 50, money = 100, xp = 10) {
    // Créer le conteneur principal du bloc
    const statsBlock = document.createElement('div');

    // Ajouter le contenu : énergie + argent + expérience
    statsBlock.innerHTML = `⚡ ${energy} | 💰 ${money} | 🎓 ${xp}`;
    
    // Style simple
    statsBlock.style.cssText = `
        position: fixed;
        top: 10px;
        right: 10px;
        background: #fff;
        border: 1px solid #ccc;
        padding: 5px 10px;
        font-family: Arial, sans-serif;
        font-size: 14px;
    `;
    
    // Ajouter au body
    document.body.appendChild(statsBlock);
    
    return statsBlock;
}

/**
 * Met à jour les valeurs dans un bloc existant
 * @param {HTMLElement} statsBlock - Le bloc d'affichage à mettre à jour
 * @param {number} energy - La nouvelle valeur d'énergie
 * @param {number} money - La nouvelle valeur d'argent
 * @param {number} xp - La nouvelle valeur d'expérience
 */
export function updateStats(statsBlock, energy, money, xp) {
    statsBlock.innerHTML = `⚡ ${energy} | 💰 ${money} | 🎓 ${xp}`;
}

/**
 * Met à jour seulement l'énergie
 * @param {HTMLElement} statsBlock - Le bloc d'affichage à mettre à jour
 * @param {number} newEnergy - La nouvelle valeur d'énergie
 */
export function updateEnergyValue(statsBlock, newEnergy) {
    const currentContent = statsBlock.innerHTML;
    const moneyPart = currentContent.split('| 💰 ')[1] || '100';
    const xpPart = currentContent.split('| 🎓 ')[1] || '10';
    statsBlock.innerHTML = `⚡ ${newEnergy} | 💰 ${moneyPart} | 🎓 ${xpPart}`;
}

/**
 * Met à jour seulement l'argent
 * @param {HTMLElement} statsBlock - Le bloc d'affichage à mettre à jour
 * @param {number} newMoney - La nouvelle valeur d'argent
 */
export function updateMoneyValue(statsBlock, newMoney) {
    const currentContent = statsBlock.innerHTML;
    const energyPart = currentContent.split(' | 💰')[0] || '⚡ 50';
    statsBlock.innerHTML = `${energyPart} | 💰 ${newMoney}`;
}

/**
 * Met à jour seulement l'expérience
 * @param {HTMLElement} statsBlock - Le bloc d'affichage à mettre à jour
 * @param {number} newXp - La nouvelle valeur d'expérience
 */
export function updateXpValue(statsBlock, newXp) {
    const currentContent = statsBlock.innerHTML;
    const energyPart = currentContent.split(' | 💰')[0] || '⚡ 50';
    const moneyPart = currentContent.split(' | 💰')[1] || '💰 100';
    statsBlock.innerHTML = `${energyPart} | 💰 ${moneyPart} | 🎓 ${newXp}`;
}
