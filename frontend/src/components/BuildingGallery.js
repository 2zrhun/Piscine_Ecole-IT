export default class BuildingGallery {
  constructor(containerId, onBuildingSelect) {
    this.container = document.getElementById(containerId);
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = containerId;
      document.body.appendChild(this.container);
    }
    this.onBuildingSelect = onBuildingSelect;
    this.buildings = [];
    this.init();
  }

  async init() {
    const res = await fetch('http://localhost:8000/api/buildings');
    const data = await res.json();
    this.buildings = data['hydra:member'] || [];
    this.render();
  }

  render() {
    this.container.innerHTML = '';
    this.buildings.forEach(building => {
      const img = document.createElement('img');
        img.src = `http://localhost:8000/uploads/imageBuilding/${building.image}`;
      img.alt = building.name;
      img.style.cursor = 'pointer';
      img.style.width = '100px';
      img.style.margin = '10px';
      img.onclick = () => {
        if (this.onBuildingSelect) this.onBuildingSelect(building);
      };
      this.container.appendChild(img);
    });
  }
}