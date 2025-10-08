import apiService from '../services/api.js';

/**
 * Vérifie si l'utilisateur est authentifié si nécessaire
 * @param {string} redirectUrl - URL de redirection si non authentifié
 */
export async function checkAuthentication(redirectUrl = 'src/Template/Login.html') {
    console.log("🔒 Vérification de l'authentification...");

    //  Vérifier si un token existe
    if (!apiService.isAuthenticated()){
        console.log('❌ Utilisateur non authentifié, redirection vers la page de login');
        window.location.href = redirectUrl;
        return false;
    }

    // Vérifier si le token est valide en testant l'API
    try {
        console.log('🔄 Vérification du token avec l\'API...');
        await apiService.getCurrentUser();
        console.log('✅ Token valide, utilisateur authentifié');
        return true;
    } catch (error) {
        console.error('❌ Token invalide ou erreur API:', error);
        // Nettoyer le token invalide
        apiService.token = null;
        localStorage.removeItem('auth_token');
        // Rediriger vers la page de login
        window.location.href = redirectUrl;
        return false;
    }
}

/**
 * Protège une page en vérifiant l'authentification au chargement
 */
export function protectPage() {
    document.addEventListener('DOMContentLoaded', async () => {
        await checkAuthentication();
    });
}