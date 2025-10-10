/**
 * MapVisitor - Composant pour visiter les cartes d'autres joueurs
 */
export default class MapVisitor {
    constructor(containerId, onMapLoad) {
        this.container = document.getElementById(containerId);
        this.onMapLoad = onMapLoad; // Callback pour charger la carte
        this.currentVisitedMap = null;
        this.init();
    }

    init() {
        this.render();
        this.attachEvents();
    }

    render() {
        this.container.innerHTML = `
            <div class="map-visitor-panel">
                <div class="visitor-header">
                    <h3>🗺️ Visiter une carte</h3>
                </div>

                <div class="visitor-search">
                    <input
                        type="text"
                        id="player-search-input"
                        placeholder="Entrez le pseudo du joueur..."
                        autocomplete="off"
                    />
                    <button id="visit-map-btn">Visiter</button>
                    <button id="return-home-btn" style="display: none;">Retour à ma carte</button>
                </div>

                <div id="visit-status" class="visit-status"></div>

                <div id="visitors-list" class="visitors-list" style="display: none;">
                    <h4>👥 Visiteurs sur cette carte</h4>
                    <ul id="visitors-items"></ul>
                </div>
            </div>
        `;
    }

    attachEvents() {
        const input = document.getElementById('player-search-input');
        const visitBtn = document.getElementById('visit-map-btn');
        const returnBtn = document.getElementById('return-home-btn');

        // Visite par clic sur bouton
        visitBtn.addEventListener('click', () => {
            const pseudo = input.value.trim();
            if (pseudo) {
                this.visitPlayerMap(pseudo);
            }
        });

        // Visite par touche Entrée
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const pseudo = input.value.trim();
                if (pseudo) {
                    this.visitPlayerMap(pseudo);
                }
            }
        });

        // Retour à la carte personnelle
        returnBtn.addEventListener('click', () => {
            this.returnToOwnMap();
        });
    }

    async visitPlayerMap(targetPseudo) {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            this.showStatus('❌ Vous devez être connecté', 'error');
            return;
        }

        this.showStatus('🔍 Recherche de la carte...', 'loading');

        try {
            // Récupérer la map du joueur depuis l'API
            const response = await fetch(`http://localhost:8000/api/map/by-pseudo/${targetPseudo}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                const error = await response.json();
                this.showStatus(`❌ ${error.error || 'Joueur introuvable'}`, 'error');
                return;
            }

            const data = await response.json();
            const { map, owner } = data;

            this.showStatus(`✅ Carte de ${owner.pseudo} chargée (XP: ${owner.xp})`, 'success');

            // Sauvegarder les infos de la visite
            this.currentVisitedMap = {
                mapId: map.id,
                mapName: map.name,
                owner: owner.pseudo,
                config: map.config
            };

            // Afficher le bouton retour
            document.getElementById('return-home-btn').style.display = 'inline-block';
            document.getElementById('visit-map-btn').style.display = 'none';

            // Callback pour charger la carte visuellement
            if (this.onMapLoad) {
                this.onMapLoad(map.config, owner.pseudo, map.id, map.name, map.color);
            }

        } catch (error) {
            console.error('Erreur visite carte:', error);
            this.showStatus('❌ Erreur lors de la visite', 'error');
        }
    }

    async returnToOwnMap() {
        const token = localStorage.getItem('auth_token');

        this.showStatus('🏠 Retour à votre carte...', 'loading');

        try {
            // Récupérer sa propre map
            const response = await fetch('http://localhost:8000/api/user/map', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            const data = await response.json();

            if (data.error) {
                this.showStatus('❌ ' + data.error, 'error');
                return;
            }

            this.currentVisitedMap = null;
            this.showStatus('✅ Vous êtes sur votre carte', 'success');

            // Masquer bouton retour
            document.getElementById('return-home-btn').style.display = 'none';
            document.getElementById('visit-map-btn').style.display = 'inline-block';
            document.getElementById('visitors-list').style.display = 'none';

            // Recharger sa propre carte
            if (this.onMapLoad) {
                this.onMapLoad(data.config, 'Ma carte', data.id, data.name, data.color);
            }

        } catch (error) {
            console.error('Erreur retour carte:', error);
            this.showStatus('❌ Erreur lors du retour', 'error');
        }
    }

    showStatus(message, type = 'info') {
        const statusDiv = document.getElementById('visit-status');
        statusDiv.textContent = message;
        statusDiv.className = `visit-status ${type}`;

        // Auto-hide après 5 secondes (sauf loading)
        if (type !== 'loading') {
            setTimeout(() => {
                statusDiv.textContent = '';
                statusDiv.className = 'visit-status';
            }, 5000);
        }
    }

    updateVisitorsList(visitors) {
        const visitorsList = document.getElementById('visitors-list');
        const visitorsItems = document.getElementById('visitors-items');

        if (!visitors || visitors.length === 0) {
            visitorsList.style.display = 'none';
            return;
        }

        visitorsList.style.display = 'block';
        visitorsItems.innerHTML = visitors.map(v =>
            `<li>👤 ${v.pseudo}</li>`
        ).join('');
    }

    isVisitingOtherMap() {
        return this.currentVisitedMap !== null;
    }

    getCurrentMapInfo() {
        return this.currentVisitedMap;
    }
}
