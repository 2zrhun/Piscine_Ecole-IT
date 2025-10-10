# 🏙️ CityBuilder - Jeu de Construction de Ville Multijoueur

## 📋 Présentation

CityBuilder est une application web interactive de construction de ville en 3D avec mode multijoueur en temps réel. Le projet permet aux utilisateurs de créer et gérer leur propre ville virtuelle, placer des bâtiments sur une carte, et interagir avec d'autres joueurs dans un environnement partagé.

L'application combine une interface 3D immersive avec un système backend robuste et un serveur temps réel pour offrir une expérience collaborative fluide.

---

## 🎯 Fonctionnalités Principales

### Gameplay
- **Construction de ville 3D** : Placement interactif de bâtiments sur une carte
- **Galerie de bâtiments** : Large sélection d'objets 3D à construire
- **Gestion de ressources** : Système de points de vie et d'énergie pour chaque bâtiment
- **Mode multijoueur** : Interaction en temps réel avec d'autres joueurs
- **Visualisation de la carte** : Indicateur de position et navigation intuitive

### Système Utilisateur
- **Authentification sécurisée** : Système de connexion avec JWT
- **Profils personnalisés** : Gestion des utilisateurs et de leurs villes
- **Persistance des données** : Sauvegarde automatique des progrès

### Interface Utilisateur
- **Design responsive** : Interface adaptée à tous les écrans
- **Barres d'outils intuitives** : Accès rapide aux fonctionnalités
- **Indicateurs visuels** : Affichage clair de l'état des bâtiments
- **Système de notifications** : Retours en temps réel sur les actions

---

## 🛠️ Technologies Utilisées

### Frontend
- **Three.js** `v0.180.0` - Rendu 3D et gestion des scènes
- **JavaScript ES6+** - Logique applicative moderne
- **Vite** `v7.1.9` - Build tool et serveur de développement
- **Socket.io Client** `v4.8.1` - Communication temps réel
- **HTML5/CSS3** - Structure et stylisation

### Backend - API REST
- **Symfony** `v7.3` - Framework PHP moderne
- **API Platform** `v4.2.2` - Création d'API REST
- **Doctrine ORM** `v3.5.2` - Gestion de la base de données
- **Lexik JWT** `v3.1.1` - Authentification par tokens
- **Nelmio CORS** `v2.5` - Gestion des requêtes cross-origin
- **EasyAdmin Bundle** `v4.26.3` - Interface d'administration
- **Vich Uploader** `v2.8.1` - Gestion des uploads de fichiers

### Serveur Multijoueur
- **Node.js** - Environnement d'exécution JavaScript
- **Express** `v4.18.2` - Framework web
- **Socket.io** `v4.7.5` - Communication WebSocket temps réel
- **Axios** `v1.6.0` - Requêtes HTTP
- **CORS** `v2.8.5` - Configuration cross-origin

### Base de Données
- **MySQL** `v8.0` - Base de données relationnelle
- **Doctrine Migrations** - Gestion des versions de schéma

### DevOps et Infrastructure
- **Docker** - Conteneurisation des services
- **Docker Compose** - Orchestration multi-conteneurs
- **Apache** - Serveur web
- **phpMyAdmin** - Interface de gestion MySQL
- **GitHub Actions** - CI/CD

---

## 🏗️ Architecture

Le projet suit une architecture microservices avec trois composants principaux :

### 1. Client Web (Frontend)
Application web interactive gérant l'affichage 3D, l'interface utilisateur et la logique côté client.

### 2. API REST (Backend)
Service Symfony fournissant les endpoints pour :
- Gestion des utilisateurs et authentification
- CRUD des bâtiments et entités
- Stockage et récupération des données
- Administration du système

### 3. Serveur de Jeu (Game Server)
Serveur Node.js WebSocket gérant :
- Synchronisation temps réel entre joueurs
- État partagé du jeu
- Événements multijoueur
- Communication bidirectionnelle

---

## 📦 Structure du Projet

```
Piscine_Ecole-IT/
├── API/                    # Backend Symfony
│   ├── src/               # Code source PHP
│   ├── config/            # Configuration Symfony
│   ├── migrations/        # Migrations de base de données
│   └── public/            # Point d'entrée public
│
├── game-server/           # Serveur multijoueur
│   └── server.js          # Serveur WebSocket Node.js
│
├── src/                   # Frontend JavaScript
│   ├── components/        # Composants UI réutilisables
│   ├── services/          # Services et logique métier
│   ├── script/            # Scripts utilitaires
│   ├── style/             # Feuilles de style
│   └── Template/          # Templates HTML
│
├── docker-compose.yml     # Configuration Docker
├── Dockerfile             # Image Docker personnalisée
└── index.html             # Point d'entrée de l'application
```

---

## 🚀 Démarrage Rapide

### Prérequis
- Docker et Docker Compose
- Node.js (v18+)
- Git

### Installation

1. **Cloner le repository**
```bash
git clone https://github.com/2zrhun/Piscine_Ecole-IT.git
cd Piscine_Ecole-IT
```

2. **Lancer les services avec Docker**
```bash
docker-compose up -d
```

3. **Installer les dépendances frontend**
```bash
npm install
```

4. **Installer les dépendances du serveur de jeu**
```bash
cd game-server
npm install
```

5. **Lancer le serveur de jeu**
```bash
npm start
```

6. **Lancer le serveur de développement frontend**
```bash
npm run dev
```

### Accès aux services
- **Application web** : http://localhost:5173
- **API Backend** : http://localhost:8080
- **phpMyAdmin** : http://localhost:8050
- **Serveur de jeu** : ws://localhost:3000

---

## 🎮 Utilisation

1. **Créer un compte** ou se connecter via l'interface d'authentification
2. **Explorer la galerie** de bâtiments disponibles
3. **Placer des bâtiments** sur la carte en cliquant et glissant
4. **Gérer les ressources** de vos constructions
5. **Interagir** avec les autres joueurs en temps réel
6. **Consulter la minimap** pour naviguer sur votre ville

---

## 📚 Documentation Complémentaire

- [MULTIPLAYER_GUIDE.md](MULTIPLAYER_GUIDE.md) - Guide détaillé du système multijoueur
- [TEST_MULTIPLAYER.md](TEST_MULTIPLAYER.md) - Procédures de test multijoueur

---

## 🔧 Configuration

### Variables d'Environnement

#### API (Symfony)
Configurer le fichier [API/.env](API/.env) :
- `DATABASE_URL` : Connexion à la base de données
- `JWT_SECRET_KEY` : Clé secrète JWT
- `CORS_ALLOW_ORIGIN` : Origine autorisée pour CORS

#### Game Server
Le serveur écoute par défaut sur le port `3000` et accepte les connexions cross-origin.

---

## 🔗 Liens

- **Repository** : [https://github.com/2zrhun/Piscine_Ecole-IT](https://github.com/2zrhun/Piscine_Ecole-IT)
---

## 👨‍💻 Équipe de Développement

Projet développé dans le cadre de la formation à l'École IT - M1.
Back-end & WebSocket : DEFAUQUET Pierre
Infra & Deployment : AZARHOUN Hamza
CI/CD & Docker : MOUHRI Younes
Front & 3D : RIVIERE Geoffrey

---

## 🙏 Remerciements

Merci à tous les contributeurs et aux technologies open-source qui ont rendu ce projet possible.
