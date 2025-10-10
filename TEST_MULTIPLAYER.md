# 🧪 Test du Système Multijoueur

## Scénario de test complet

### Étape 1 : Préparation (5 min)

#### 1.1 - Créer deux comptes utilisateurs

**Terminal 1 - Créer le joueur "Alice" :**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "alice@test.com",
    "password": "Test123!",
    "pseudo": "Alice",
    "mapName": "Ville d'Alice"
  }'
```

**Terminal 2 - Créer le joueur "Bob" :**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "bob@test.com",
    "password": "Test123!",
    "pseudo": "Bob",
    "mapName": "Ville de Bob"
  }'
```

#### 1.2 - Démarrer tous les serveurs

**Terminal 3 - Serveur Socket.io :**
```bash
cd game-server
npm start
```

**Terminal 4 - API Symfony :**
```bash
cd API
docker-compose up
```

**Terminal 5 - Frontend :**
```bash
npm run dev
```

---

### Étape 2 : Test de visite de carte (10 min)

#### 2.1 - Alice se connecte

1. Ouvrir `http://localhost:5173` dans le navigateur
2. Se connecter avec :
   - Email : `alice@test.com`
   - Mot de passe : `Test123!`
3. **Vérifier :**
   - ✅ La carte 3D d'Alice se charge
   - ✅ Le panneau "Visiter une carte" est visible en haut à droite
   - ✅ Console affiche : `🔌 Connecté au serveur Socket.io`
   - ✅ Console affiche : `✅ Carte de "Alice" chargée (50x50)`

#### 2.2 - Bob se connecte (fenêtre incognito)

1. Ouvrir une **fenêtre de navigation privée**
2. Aller sur `http://localhost:5173`
3. Se connecter avec :
   - Email : `bob@test.com`
   - Mot de passe : `Test123!`
4. **Vérifier :**
   - ✅ La carte 3D de Bob se charge
   - ✅ Console affiche : `✅ Carte de "Bob" chargée (50x50)`

#### 2.3 - Alice visite la carte de Bob

**Dans la fenêtre d'Alice :**

1. Taper "**Bob**" dans l'input du panneau
2. Cliquer sur "Visiter" ou appuyer sur Entrée
3. **Vérifier :**
   - ✅ Message : `✅ Carte de Bob chargée (XP: 10)`
   - ✅ La scène 3D change
   - ✅ Bouton "Retour à ma carte" apparaît
   - ✅ Console affiche : `🗺️ Chargement de la carte de Bob`

**Dans la fenêtre de Bob :**

1. **Vérifier dans la console :**
   - ✅ `👤 Nouveau visiteur: Alice`
2. **Vérifier dans le panneau :**
   - ✅ Liste des visiteurs affiche : `👤 Alice`

#### 2.4 - Alice retourne à sa carte

**Dans la fenêtre d'Alice :**

1. Cliquer sur "Retour à ma carte"
2. **Vérifier :**
   - ✅ Message : `✅ Vous êtes sur votre carte`
   - ✅ La carte d'Alice se recharge
   - ✅ Bouton "Visiter" réapparaît

**Dans la fenêtre de Bob :**

1. **Vérifier dans la console :**
   - ✅ `🚪 Visiteur parti: Alice`
2. **Vérifier :**
   - ✅ La liste des visiteurs est vide

---

### Étape 3 : Test multijoueur simultané (5 min)

#### 3.1 - Créer un troisième joueur "Charlie"

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "charlie@test.com",
    "password": "Test123!",
    "pseudo": "Charlie",
    "mapName": "Ville de Charlie"
  }'
```

#### 3.2 - Charlie visite Bob

1. Se connecter avec Charlie dans une 3ème fenêtre
2. Visiter "Bob"

**Dans la fenêtre de Bob :**
- ✅ Liste des visiteurs : `👤 Charlie`

#### 3.3 - Alice visite aussi Bob

**Maintenant Bob a 2 visiteurs simultanés**

**Dans la fenêtre de Bob :**
- ✅ Liste des visiteurs :
  - `👤 Alice`
  - `👤 Charlie`

**Console du serveur Socket.io :**
```
🗺️  Alice visite la carte de Bob (Map ID: 2)
📊 Carte "Ville de Bob" : 2 visiteur(s)
🗺️  Charlie visite la carte de Bob (Map ID: 2)
📊 Carte "Ville de Bob" : 3 visiteur(s)
```

---

### Étape 4 : Test de l'API directement (optionnel)

#### Récupérer la carte de Bob via l'API

**Se connecter pour obtenir un token :**
```bash
TOKEN=$(curl -X POST http://localhost:8000/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"username":"alice@test.com","password":"Test123!"}' \
  | jq -r '.token')
```

**Récupérer la carte de Bob :**
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/map/by-pseudo/Bob | jq
```

**Réponse attendue :**
```json
{
  "map": {
    "id": 2,
    "name": "Ville de Bob",
    "config": {
      "grid": {
        "size": 50,
        "division": 20
      },
      "elements": []
    },
    "createdAt": "2025-10-10T10:00:00+00:00"
  },
  "owner": {
    "pseudo": "Bob",
    "xp": 10
  }
}
```

---

## ✅ Checklist de validation

- [ ] Serveur Socket.io démarre sur port 3001
- [ ] API Symfony répond sur port 8000
- [ ] Frontend accessible sur port 5173
- [ ] Inscription de 2 utilisateurs réussie
- [ ] Connexion Socket.io établie (console navigateur)
- [ ] Visite de carte fonctionne
- [ ] Liste des visiteurs s'affiche
- [ ] Retour à sa carte fonctionne
- [ ] Plusieurs visiteurs simultanés possibles
- [ ] Déconnexion d'un visiteur notifie les autres

---

## 🐛 Problèmes connus

| Problème | Solution |
|----------|----------|
| Socket.io ne se connecte pas | Vérifier que le serveur tourne et que le port 3001 est libre |
| "Joueur introuvable" | Vérifier l'orthographe du pseudo (sensible à la casse) |
| Liste des visiteurs ne s'affiche pas | Ouvrir la console et vérifier les événements Socket.io |
| Carte ne se charge pas | Vérifier que la BDD contient bien une map pour cet utilisateur |

---

## 📊 Logs attendus

### Console navigateur (Alice)
```
🔌 Connecté au serveur Socket.io: abc123
✅ Carte de "Alice" chargée (50x50)
��️ Chargement de la carte de Bob
👤 Nouveau visiteur: Charlie
```

### Console serveur Socket.io
```
🚀 Serveur Socket.io démarré sur http://localhost:3001
🎮 Joueur connecté: abc123
🗺️  Alice visite la carte de Bob (Map ID: 2)
📊 Carte "Ville de Bob" : 2 visiteur(s)
🚪 Joueur déconnecté: abc123
```

### Console API Symfony
```
[info] Matched route "api_map_by_pseudo"
[info] Request: GET /api/map/by-pseudo/Bob
[info] Response: 200 OK
```

---

**Bon test ! 🧪**
