// Import du service API
import apiService from '../../services/api.js';

const form = document.getElementById('registerForm');
const errorMessage = document.getElementById('errorMessage');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // 📋 Récupération des données du formulaire
    const username = form.username.value.trim();
    const city = form.city.value.trim();
    const email = form.email.value.trim();
    const password = form.password.value.trim();
    const confirmPassword = form.confirmPassword.value.trim();

    // 🧹 Nettoyage du message d'erreur
    errorMessage.textContent = "";

    // ✅ Validation côté client
    if (password !== confirmPassword) {
        errorMessage.textContent = "⚠️ Les mots de passe ne correspondent pas.";
        return;
    }

    if (!username || !city || !email || !password) {
        errorMessage.textContent = "⚠️ Tous les champs doivent être remplis.";
        return;
    }

    if (password.length < 6) {
        errorMessage.textContent = "⚠️ Le mot de passe doit contenir au moins 6 caractères.";
        return;
    }

    try {
        // 🔄 Affichage du loading
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.textContent = "⏳ Inscription en cours...";
        submitButton.disabled = true;

        // 🚀 Appel à l'API pour l'inscription
        const userData = {
            pseudo: username,    // Correspond à votre entité User
            email: email,
            password: password,
            mapName: city        // ← Le nom de la map = la ville saisie
            // ville: city       // À ajouter dans votre entité si nécessaire
        };

        console.log('📤 Envoi des données d\'inscription:', userData);
        
        const response = await apiService.register(userData);
        
        console.log('✅ Inscription réussie:', response);

        // 💾 Sauvegarde des infos utilisateur (optionnel, pour compatibilité)
        localStorage.setItem('username', username);
        localStorage.setItem('city', city);
        localStorage.setItem('email', email);

        // 🎉 Message de succès
        errorMessage.style.color = "green";
        errorMessage.textContent = "✅ Inscription réussie ! Redirection...";

        // ⏱️ Redirection après 1.5 seconde
        setTimeout(() => {
            window.location.href = "../../../index.html";
        }, 1500);

    } catch (error) {
        // ❌ Gestion des erreurs
        console.error('❌ Erreur lors de l\'inscription:', error);
        
        let errorText = "❌ Erreur lors de l'inscription.";
        
        if (error.message.includes('déjà utilisé') || error.message.includes('already')) {
            errorText = "❌ Cette adresse email est déjà utilisée.";
        } else if (error.message.includes('réseau') || error.message.includes('fetch')) {
            errorText = "❌ Erreur de connexion. Vérifiez que l'API est démarrée.";
        }
        
        errorMessage.style.color = "red";
        errorMessage.textContent = errorText;

        // 🔄 Restauration du bouton
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    }
});