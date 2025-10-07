const form = document.getElementById('registerForm');
const errorMessage = document.getElementById('errorMessage');

form.addEventListener('submit', (e) => {
    e.preventDefault();

    const username = form.username.value.trim();
    const city = form.city.value.trim();
    const email = form.email.value.trim();
    const password = form.password.value.trim();
    const confirmPassword = form.confirmPassword.value.trim();

    errorMessage.textContent = "";

    if (password !== confirmPassword) {
        errorMessage.textContent = "⚠️ Les mots de passe ne correspondent pas.";
        return;
    }

    if (!username || !city || !email || !password) {
        errorMessage.textContent = "⚠️ Tous les champs doivent être remplis.";
        return;
    }

    localStorage.setItem('username', username);
    localStorage.setItem('city', city);
    localStorage.setItem('email', email);

    window.location.href = "./src/Template/Game.html";
});
