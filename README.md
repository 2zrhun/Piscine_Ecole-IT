# City builder 3000
### La city du builder future 3000

# Front
Jeux Web **city builder**, développée avec **Three.js** et **WebGl**.

## 🚀 Fonctionnalités

- 🔐 Authentification JWT
- ✅ Parcourir une maps 3D
- 📊 Visualisation des statistiques du joueur : Pseudo / XP / Items
- 🔒 Bouton vers Panel Admin pour les utilisateurs avec le rôle Admin
- 🏗️ Bouton de construction, pour choisir est placer les bâtiments au choix
- 🏠 Visualisation des parcelles de construction, des routes, et le déplacement aléatoire des PNJ

  ---

## 🧱 Stack technique

- **Three.js 10.9.2**
- **WebGl**
- **JavaScript**
- **html / css**

---

## 📁 Arborescence principale

````
src/
├──   Template/ (Dossier fichier html)
│ |── Admin.html (Page Admin)
│ |── Game.html (Page du jeu)
│ └── Register.html (Page d'inscription)

├── components/
│ |── BuildingBar.js
│ |── BuildingGallery.js
│ └── lifebar.js

├── script/
│ |── Game/
│ | |── camera.js (Gérer la caméra)
│ | |── cellule.js (Gérer les différentes cellules de la maps)
│ | |── control.js (Gerer le déplacement de la caméra)
│ | |── grid.js (Création des case pour les base de la maps)
│ | |── map.js (Création de la maps)
│ | |── plane.js 
│ | |── renderer.js
│ | |── pnj.js (Gestion des PNJ)
│ | └── squareCreateBuilder.js (Création de la box pour le batiment)
│ |── register/
│ | └── redirect.js (rediriger apres l'inscription)
│ |── auth-guard.js
│ |── login.js (Gére la connexion)
│ └── register.js (Gére l'inscription)

├── service/
│ └── api.js # Liée l'api au front

├── style/
│ |── register.css
│ |── login.css
│ └── register.css

├── index.js (Importe tout les fonction du jeu et les fais comuniquer entre elles)
├── deployment.yml
├── Dockerfile
├── README.md
├── docker-compose.yml
├── index.html (Page de connection)
├── package-lock.json
└── package.json

````

## ⚙️ Installation & lancement

### Prérequis
- Three.js ≥ 10.9.2
- php ≥ 3

  ````
npm run dev
  ````
