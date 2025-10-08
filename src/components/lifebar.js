/**
 * Crée un bloc d'affichage avec énergie et argent
 * @param {number} energy - La valeur d'énergie à afficher (par défaut 50)
 * @param {number} money - La valeur d'argent à afficher (par défaut 100)
 * @param {number} xp - La valeur d'expérience à afficher (par défaut 10)
 * @returns {HTMLElement} L'élément du bloc d'affichage
 */
export function createEnergyBlock(energy = 50, money = 100, xp = 10, pseudo = 'user') {
    // Créer le conteneur principal du bloc
    const statsBlock = document.createElement('div');

    // Ajouter le contenu : pseudo en haut + énergie + argent + expérience en bas
    statsBlock.innerHTML = `
        <div style="margin-bottom: 3px; font-size: 24px;">👾 ${pseudo}</div>
        <div>⚡ ${energy} | 💰 ${money} | 🎓 ${xp}</div>
    `;
    
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
export function updateStats(statsBlock, energy, money, xp, pseudo = 'user') {
    statsBlock.innerHTML = `
        <div style="margin-bottom: 3px; font-size: 12px;">👾 ${pseudo}</div>
        <div>⚡ ${energy} | 💰 ${money} | 🎓 ${xp}</div>
    `;
}

/**
 * Met à jour seulement l'énergie
 * @param {HTMLElement} statsBlock - Le bloc d'affichage à mettre à jour
 * @param {number} newEnergy - La nouvelle valeur d'énergie
 */
export function updateEnergyValue(statsBlock, newEnergy) {
    const statsLine = statsBlock.querySelector('div:last-child');
    const currentContent = statsLine.innerHTML;
    const moneyMatch = currentContent.match(/💰 (\d+)/);
    const xpMatch = currentContent.match(/🎓 (\d+)/);
    const money = moneyMatch ? moneyMatch[1] : '100';
    const xp = xpMatch ? xpMatch[1] : '10';
    const pseudo = statsBlock.querySelector('div:first-child').textContent.replace('👾 ', '');

    statsBlock.innerHTML = `
        <div style="margin-bottom: 3px; font-size: 12px;">👾 ${pseudo}</div>
        <div>⚡ ${newEnergy} | 💰 ${money} | 🎓 ${xp}</div>
    `;
}

/**
 * Met à jour seulement l'argent
 * @param {HTMLElement} statsBlock - Le bloc d'affichage à mettre à jour
 * @param {number} newMoney - La nouvelle valeur d'argent
 */
export function updateMoneyValue(statsBlock, newMoney) {
    const statsLine = statsBlock.querySelector('div:last-child');
    const currentContent = statsLine.innerHTML;
    const energyMatch = currentContent.match(/⚡ (\d+)/);
    const xpMatch = currentContent.match(/🎓 (\d+)/);
    const energy = energyMatch ? energyMatch[1] : '50';
    const xp = xpMatch ? xpMatch[1] : '10';
    const pseudo = statsBlock.querySelector('div:first-child').textContent.replace('👾 ', '');

    statsBlock.innerHTML = `
        <div style="margin-bottom: 3px; font-size: 12px;">👾 ${pseudo}</div>
        <div>⚡ ${energy} | 💰 ${newMoney} | 🎓 ${xp}</div>
    `;
}

/**
 * Met à jour seulement l'expérience
 * @param {HTMLElement} statsBlock - Le bloc d'affichage à mettre à jour
 * @param {number} newXp - La nouvelle valeur d'expérience
 */
export function updateXpValue(statsBlock, newXp) {
    const statsLine = statsBlock.querySelector('div:last-child');
    const currentContent = statsLine.innerHTML;
    const energyMatch = currentContent.match(/⚡ (\d+)/);
    const moneyMatch = currentContent.match(/💰 (\d+)/);
    const energy = energyMatch ? energyMatch[1] : '50';
    const money = moneyMatch ? moneyMatch[1] : '100';
    const pseudo = statsBlock.querySelector('div:first-child').textContent.replace('👾 ', '');

    statsBlock.innerHTML = `
        <div style="margin-bottom: 3px; font-size: 12px;">👾 ${pseudo}</div>
        <div>⚡ ${energy} | 💰 ${money} | 🎓 ${newXp}</div>
    `;
}

/**
 * Initialise la lifebar avec le pseudo de l'utilisateur connecté
 * @param {number} energy - La valeur d'énergie à afficher (par défaut 50)
 * @param {number} money - La valeur d'argent à afficher (par défaut 100)
 * @param {number} xp - La valeur d'expérience à afficher (par défaut 10)
 * @returns {Promise<HTMLElement>} L'élément du bloc d'affichage
 */
export async function initLifebarWithUser(energy = 50, money = 100, xp = 10) {
    // Import dynamique pour éviter les dépendances circulaires
    const { default: apiService } = await import('../services/api.js');
    
    let userPseudo = 'user'; // Valeur par défaut
    
    try {
        // Vérifier si l'utilisateur est connecté
        if (apiService.isAuthenticated()) {
            console.log('🎮 Récupération du profil utilisateur...');
            const user = await apiService.getCurrentUser();
            
            if (user && user.pseudo) {
                userPseudo = user.pseudo;
                console.log('✅ Pseudo récupéré:', userPseudo);
            } else {
                console.warn('⚠️ Pas de pseudo trouvé, utilisation de "user"');
            }
        } else {
            console.warn('⚠️ Utilisateur non connecté, utilisation de "user"');
        }
    } catch (error) {
        console.error('❌ Erreur lors de la récupération du pseudo:', error);
        // On continue avec le pseudo par défaut
    }
    
    // Créer la lifebar avec le pseudo récupéré
    return createEnergyBlock(energy, money, xp, userPseudo);
}