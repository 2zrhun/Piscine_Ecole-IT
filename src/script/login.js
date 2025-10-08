import apiService from '../services/api.js';
console.log("🔥 Script login.js chargé avec succès !");
console.log("⏰ DOM ready state:", document.readyState);
console.log("📝 Formulaire disponible maintenant:", document.getElementById('loginForm'));

class LoginManager {
    constructor() {
        this.initializeForm();
    }

    initializeForm() {
        console.log("🔍 initializeForm() appelée");
        const form = document.getElementById('loginForm');
        const errorMessage = document.getElementById('errorMessage');
        
        console.log("📝 Formulaire trouvé:", form);
        console.log("⚠️ Element errorMessage trouvé:", errorMessage);

        if (form) {
            console.log("✅ Event listener ajouté au formulaire");
            form.addEventListener('submit', async (e) => {
                console.log("🚀 Formulaire soumis !");
                e.preventDefault();
                e.stopPropagation();
                console.log("🛑 preventDefault() et stopPropagation() appelés");
                
                try {
                    await this.handleLogin(errorMessage);
                } catch (error) {
                    console.error("💥 Erreur dans handleLogin:", error);
                }
            });
        } else {
            console.error("❌ Formulaire non trouvé !");
        }
    }

    async handleLogin(errorElement) {
    console.log("🔥 handleLogin() appelée !");
    
    // Récupération des données du formulaire
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    console.log("📧 Email:", email);
    console.log("🔒 Password:", password ? "***renseigné***" : "vide");

    // Nettoyage des messages d'erreur précédents
    errorElement.textContent = '';

    try {
        console.log("🌐 Tentative de connexion...");
        // Tentative de connexion
        const response = await apiService.login({
            email: email,
            password: password
        });

        console.log("📡 Réponse reçue:", response);

        if (response && response.token) {
            // Connexion réussie
            console.log('✅ Connexion réussie !');
            // Redirection vers la page principale ou tableau de bord
            window.location.href = '/src/Template/Game.html';
        }
    } catch (error) {
        // Affichage de l'erreur
        console.error("❌ Erreur de connexion:", error);
        errorElement.textContent = error.message;
    }
}
}

// Initialisation - une seule fois
if (document.readyState === 'loading') {
    console.log("⏳ DOM en cours de chargement, attente...");
    document.addEventListener('DOMContentLoaded', () => {
        console.log("🟢 DOM chargé, création de LoginManager");
        new LoginManager();
    });
} else {
    console.log("✅ DOM déjà chargé, création immédiate de LoginManager");
    new LoginManager();
}