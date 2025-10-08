import apiService from '../services/api.js';

class RegisterManager {
    constructor() {
        this.initializeForm();
    }

    initializeForm() {
        const form = document.getElementById('registerForm');
        const errorMessage = document.getElementById('errorMessage');
        
        if (!form) {
            console.error('❌ Formulaire d\'inscription non trouvé !');
            return;
        }

        form.addEventListener('submit', this.handleSubmit.bind(this));
    }

    async handleSubmit(event) {
        event.preventDefault();
        
        const pseudo = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const city = document.getElementById('city').value;
        
        // Utiliser city comme mapName pour le backend
        const mapName = city;

        // Validation côté client
        if (!pseudo || !email || !password || !confirmPassword || !city) {
            this.showError('Tous les champs sont obligatoires');
            return;
        }

        if (password !== confirmPassword) {
            this.showError('Les mots de passe ne correspondent pas');
            return;
        }

        try {
            const result = await apiService.register({
                pseudo,
                email,
                password,
                mapName
            });
            console.log("✅ Inscription réussie:", result);
            
            // Rediriger vers la page de connexion
            window.location.href = '/index.html';
        } catch (error) {
            console.error("❌ Erreur d'inscription:", error);
            this.showError(error.message);
        }
    }

    showError(message) {
        const errorDiv = document.getElementById('errorMessage');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new RegisterManager();
    });
} else {
    new RegisterManager();
}