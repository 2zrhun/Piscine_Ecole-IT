// Composant BuildingBar : affiche la galerie des buildings
export default class BuildingBar {
    constructor(containerId, onSelect) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = containerId;
            document.body.appendChild(this.container);
        }
    this.container.classList.add('building-bar');
    // Style pour positionner la barre en haut à gauche
    this.container.style.position = 'fixed';
    this.container.style.top = '20px';
    this.container.style.left = '20px';
    this.container.style.zIndex = '1000';
    this.container.style.background = 'rgba(255,255,255,0.9)';
    this.container.style.padding = '10px';
    this.container.style.borderRadius = '10px';
    this.container.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
    this.container.style.display = 'flex';
    this.container.style.gap = '10px';
    this.container.style.alignItems = 'center';
    this.container.style.maxWidth = '400px';
    this.container.style.overflowX = 'auto';
        this.onSelect = onSelect;
        this.loadBuildings();
    }

    loadBuildings() {
    fetch('http://localhost:8000/gallery/buildings/images')
            .then(response => response.json())
            .then(buildings => {
                this.container.innerHTML = '';
                buildings.forEach(building => {
                    const img = document.createElement('img');
                    img.src = building.image;
                    img.alt = building.name;
                    img.title = building.name;
                    img.className = 'building-bar-img';
                    img.style.width = '80px';
                    img.style.height = '80px';
                    img.style.objectFit = 'cover';
                    img.style.margin = '5px';
                    img.style.borderRadius = '8px';
                    img.style.cursor = 'pointer';
                    img.addEventListener('click', () => {
                        if (this.onSelect) {
                            this.onSelect(building);
                        }
                    });
                    this.container.appendChild(img);
                });
            })
            .catch(err => {
                this.container.innerHTML = '<p style="color:red">Erreur chargement buildings: ' + err + '</p>';
            });
    }
}
