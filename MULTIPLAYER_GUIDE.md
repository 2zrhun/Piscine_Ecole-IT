# 🎮 Guide du Système Multijoueur - Visite de Cartes

## 📋 Vue d'ensemble

Ce système permet aux joueurs de **visiter les cartes des autres joueurs en temps réel** via Socket.io. Chaque joueur possède sa propre carte personnalisée et peut se téléporter sur la carte d'un autre joueur simplement en entrant son pseudo.

---

## 🚀 Démarrage

### 1. Lancer le serveur Socket.io

```bash
cd game-server
npm install
npm start
```

Le serveur démarrera sur `http://localhost:3001`

### 2. Lancer l'API Symfony

```bash
cd API
docker-compose up -d
# ou
symfony serve
```

L'API sera accessible sur `http://localhost:8000`

### 3. Lancer le frontend

```bash
npm install
npm run dev
```

Le jeu sera accessible sur `http://localhost:5173`

---

## 🎯 Fonctionnalités

### ✅ **Visite de carte**
- Entrez le pseudo d'un joueur dans le panneau en haut à droite
- Cliquez sur "Visiter" ou appuyez sur Entrée
- La carte du joueur se charge automatiquement en 3D
- Vous rejoignez la "room" de cette carte via Socket.io

### 🏠 **Retour à sa carte**
- Cliquez sur "Retour à ma carte"
- Votre carte personnelle se recharge
- Vous quittez la carte visitée

### 👥 **Liste des visiteurs**
- Voir en temps réel qui visite la même carte que vous
- Les visiteurs apparaissent/disparaissent automatiquement

### 🔨 **Synchronisation en temps réel** (à venir)
- Les modifications sur une carte sont diffusées à tous les visiteurs
- Ajout de bâtiments visible par tous

---

## 🗺️ Architecture Technique

### **Backend (API Symfony)**

#### Route : `/api/map/by-pseudo/{pseudo}`
Récupère la carte d'un joueur par son pseudo.

**Exemple de requête :**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/map/by-pseudo/PlayerOne
```

**Réponse :**
```json
{
  "map": {
    "id": 1,
    "name": "Ma Ville",
    "config": {
      "grid": {
        "size": 50,
        "division": 20
      },
      "elements": []
    }
  },
  "owner": {
    "pseudo": "PlayerOne",
    "xp": 150
  }
}
```

### **Serveur Socket.io**

#### Événement : `visitMap`
Rejoindre la carte d'un joueur.

**Données envoyées :**
```javascript
socket.emit('visitMap', {
    mapId: 1,
    mapName: "Ma Ville",
    ownerPseudo: "PlayerOne",
    visitorPseudo: "Visitor123",
    token: "jwt_token_here"
});
```

#### Événement : `playerJoinedMap`
Notifie tous les visiteurs qu'un nouveau joueur a rejoint.

**Données reçues :**
```javascript
socket.on('playerJoinedMap', (data) => {
    console.log(data.visitor.pseudo); // "Visitor123"
    console.log(data.totalVisitors);  // 2
});
```

#### Événement : `playerLeftMap`
Un visiteur quitte la carte.

#### Événement : `mapUpdate`
Synchronisation des modifications (placement de bâtiments, etc.)

**Données envoyées :**
```javascript
socket.emit('mapUpdate', {
    action: 'addBuilding',
    buildingData: { fileName: 'house.glb' },
    position: { x: 10, y: 0, z: 5 }
});
```

### **Frontend**

#### Composant : `MapVisitor`
Interface utilisateur pour rechercher et visiter les cartes.

**Fichiers :**
- `/src/components/MapVisitor.js` - Logique du composant
- `/src/style/mapVisitor.css` - Styles CSS

#### Service : `socketService`
Gestion de la connexion Socket.io et des événements.

**Fichier :** `/src/services/socketService.js`

**Utilisation :**
```javascript
import socketService from './services/socketService.js';

// Connexion
socketService.connect('http://localhost:3001');

// Visiter une carte
socketService.visitMap(mapId, mapName, ownerPseudo, visitorPseudo);

// Quitter
socketService.leaveMap();

// Écouter les événements
socketService.on('playerJoined', (data) => {
    console.log('Nouveau visiteur:', data.visitor);
});
```

---

## 📊 Flux de données

### Scénario : "Alice visite la carte de Bob"

```
1. Alice entre "Bob" dans l'input
   └─> Frontend appelle GET /api/map/by-pseudo/Bob

2. L'API retourne la config de carte de Bob
   └─> Frontend charge la carte 3D

3. Frontend émet 'visitMap' via Socket.io
   └─> Serveur Socket.io ajoute Alice à la room "map_1"

4. Serveur notifie tous les visiteurs
   └─> Bob voit "Alice a rejoint votre carte"

5. Alice place un bâtiment
   └─> Frontend émet 'mapUpdate'
   └─> Serveur diffuse à tous les visiteurs de la room
   └─> Bob voit le bâtiment apparaître en temps réel
```

---

## 🔧 Configuration

### Ports utilisés
- **Frontend :** `5173` (Vite)
- **API Symfony :** `8000`
- **Socket.io :** `3001`
- **MySQL :** `3306`

### CORS
Le serveur Socket.io autorise uniquement `http://localhost:5173`.

Pour modifier, éditez [game-server/server.js](game-server/server.js#L11-L15) :

```javascript
const io = socketIo(server, {
  cors: {
    origin: "http://your-frontend-url.com",
    methods: ["GET", "POST"],
    credentials: true
  }
});
```

---

## 🐛 Debugging

### Vérifier la connexion Socket.io

**Ouvrez la console du navigateur :**
```javascript
// Doit afficher "🔌 Connecté au serveur Socket.io: xxx"
```

### Vérifier les rooms actives

**Côté serveur Socket.io :**
```bash
# Les logs afficheront
🗺️  Alice visite la carte de Bob (Map ID: 1)
📊 Carte "Ma Ville" : 2 visiteur(s)
```

### Erreurs courantes

| Erreur | Solution |
|--------|----------|
| `Socket non connecté` | Vérifier que le serveur Socket.io tourne sur port 3001 |
| `Aucune carte trouvée` | Vérifier que le pseudo existe en BDD |
| `Token manquant` | Se reconnecter pour obtenir un nouveau JWT |
| `CORS error` | Vérifier la config CORS dans server.js |

---

## 📝 TODO / Améliorations futures

- [ ] Validation JWT côté serveur Socket.io (ligne 35 de server.js)
- [ ] Système de permissions (propriétaire vs visiteur)
- [ ] Curseurs multijoueurs en temps réel
- [ ] Chat intégré dans le panneau
- [ ] Historique des visiteurs
- [ ] Notifications push quand quelqu'un visite votre carte
- [ ] Mode "carte publique" / "carte privée"
- [ ] Système de "favoris" de cartes

---

## 📚 Ressources

- [Documentation Socket.io](https://socket.io/docs/v4/)
- [API Platform](https://api-platform.com/)
- [Three.js](https://threejs.org/)

---

**Bon jeu ! 🎮**
