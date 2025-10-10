/**
 * MapIndicator - Affiche le nom de la carte actuellement visitée
 */
export default class MapIndicator {
    constructor() {
        this.indicator = null;
        this.createIndicator();
    }

    createIndicator() {
        // Créer l'élément d'indicateur s'il n'existe pas
        if (!this.indicator) {
            this.indicator = document.createElement('div');
            this.indicator.id = 'map-indicator';
            this.indicator.style.cssText = `
                position: fixed;
                top: 10px;
                left: 50%;
                transform: translateX(-50%);
                background: linear-gradient(135deg, rgba(76, 175, 80, 0.95), rgba(69, 160, 73, 0.95));
                color: white;
                padding: 12px 30px;
                border-radius: 25px;
                font-family: 'Arial', sans-serif;
                font-size: 18px;
                font-weight: bold;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                z-index: 1002;
                backdrop-filter: blur(10px);
                border: 2px solid rgba(255, 255, 255, 0.3);
                display: none;
                animation: slideDown 0.4s ease-out;
            `;
            document.body.appendChild(this.indicator);

            // Ajouter l'animation CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideDown {
                    from {
                        opacity: 0;
                        transform: translateX(-50%) translateY(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(-50%) translateY(0);
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Afficher l'indicateur avec le nom de la carte
     * @param {string} mapOwner - Le propriétaire de la carte
     * @param {string} mapName - Le nom de la carte
     * @param {boolean} isOwnMap - Est-ce la carte du joueur actuel ?
     */
    show(mapOwner, mapName, isOwnMap = false) {
        if (!this.indicator) return;

        if (isOwnMap) {
            this.indicator.innerHTML = `🏠 Ma carte : <span style="font-weight: normal;">${mapName}</span>`;
            this.indicator.style.background = 'linear-gradient(135deg, rgba(33, 150, 243, 0.95), rgba(25, 118, 210, 0.95))';
        } else {
            this.indicator.innerHTML = `🗺️ Carte de : <span style="font-weight: normal;">${mapOwner}</span>`;
            this.indicator.style.background = 'linear-gradient(135deg, rgba(76, 175, 80, 0.95), rgba(69, 160, 73, 0.95))';
        }

        this.indicator.style.display = 'block';
    }

    /**
     * Masquer l'indicateur
     */
    hide() {
        if (this.indicator) {
            this.indicator.style.display = 'none';
        }
    }

    /**
     * Mettre à jour l'indicateur
     * @param {string} mapOwner - Le propriétaire de la carte
     * @param {string} mapName - Le nom de la carte
     * @param {boolean} isOwnMap - Est-ce la carte du joueur actuel ?
     */
    update(mapOwner, mapName, isOwnMap = false) {
        this.show(mapOwner, mapName, isOwnMap);
    }
}
