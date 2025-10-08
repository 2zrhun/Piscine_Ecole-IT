/**
 * Service API pour gérer les communications avec le backend Symfony
 */
class ApiService {
    constructor() {
        // URL de base de l'API
        this.baseUrl = 'http://localhost:8000/api';
        // Récupération du token depuis le localStorage
        this.token = localStorage.getItem('auth_token');
    }

    /**
     * Configuration des headers HTTP par défaut
     */
    getHeaders(includeAuth = true) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        //  Ajout automatique du token d'authentification 
        if (includeAuth && this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        return headers;
    }

    /**
     * Méthode générique pour toutes les requêtes HTTP
     */

    async makeRequest(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: this.getHeaders(options.includeAuth !== false),
            ...options
        };

        try {
            console.log(`Requête ${config.method || 'GET'} vers:`, url);
            const response = await fetch(url, config);

            // Gestion des erreurs HTTP
            if (!response.ok) {
                const errorData = await response.json().catch(()=> ({}));
                throw new Error(errorData.message || `Erreur HTTP: ${response.status}`);
            }

            //  Traitement de la réponse JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                console.log('Reponse reçue:', data);
                return data;
            }

        return null;

    } catch (error) {
        console.error('Erreur API:', error);
        throw error;
    }
}

/**
 * Connexion utilisateur
 */
async login(credentials) {
    try {
        const response = await this.makeRequest('/login_check', {
           method: 'POST',
           body: JSON.stringify({
            username: credentials.email,
            password: credentials.password
           }),
           includeAuth: false
        });

        // Stockage du token si la connexion est réussit
        if (response && response.token) {
            this.token = response.token;
            localStorage.setItem('auth_token', this.token);
            console.log('Connexion réussie, token stocké');
        }
        return response;
    } catch (error) {
        throw new Error('Erreur de connexion: '+ error.message);
    }
}

/**
 * Inscription d'un nouvel utilisateur
 */
async register(userData) {
    try {
        return await this.makeRequest('/register', {
            method: 'POST',
            body: JSON.stringify(userData),
            includeAuth: false
        });
    } catch (error) {
        throw new Error('Erreur d\'inscription: ' + error.message);
    }
}

/**
 * Récupération des informations de l'utilisateur connecté
 */
async getCurrentUser() {
    try {
        return await this.makeRequest('/user/profile', {
            method: 'GET'
        });
    } catch (error) {
        throw new Error('Erreur de récupération du profil: ' + error.message);
    }
}

/**
 * Déconnexion
 */
logout() {
    this.token = null;
    localStorage.removeItem('auth_token');
    console.log('Utilisateur déconnecté');
}

/**
 * Vérification si connecté
 */
isAuthenticated() {
    return !!this.token;
}
}

// Instance globale exportée
const apiService = new ApiService();
export default apiService;