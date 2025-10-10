# Intégration Continue Symfony (CI)

Ce projet utilise **GitHub Actions** pour automatiser les tests et vérifier la qualité du code sur chaque push ou pull request.  
Le workflow s’exécute automatiquement pour les branches `main` et `test`.

---

## 🚀 Objectif

Le but de ce pipeline CI est de :
- Vérifier que le projet Symfony se build correctement.
- Lancer les migrations sur une base MySQL temporaire.
- Exécuter les tests PHPUnit.
- Contrôler la sécurité des dépendances Composer.

---

## ⚙️ Déclenchement

Le workflow se déclenche automatiquement :
```yaml
on:
  push:
    branches: [ main, test ]
  pull_request:
    branches: [ main, test ]
```

---

## 🧱 Environnement

Le job tourne sur :
- **Ubuntu-latest**
- **PHP 8.3**
- **MySQL 8.0**

Le service MySQL est configuré avec :
```yaml
services:
  mysql:
    image: mysql:8.0
    env:
      MYSQL_DATABASE: citybuilder_db
      MYSQL_ROOT_PASSWORD: root
      MYSQL_USER: user
      MYSQL_PASSWORD: pass
```

---

## 🪜 Étapes principales

### 1. Checkout du code
```yaml
- name: Checkout code
  uses: actions/checkout@v4
```

### 2. Installation de PHP et des extensions nécessaires
```yaml
- name: Set up PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: 8.3
    extensions: mbstring, intl, pdo_mysql, zip
```

### 3. Installation des dépendances Composer
```yaml
- name: Install Composer dependencies
  run: cd API && composer install --no-interaction --prefer-dist
```

### 4. Audit de sécurité Composer
```yaml
- name: Composer Security Audit
  run: cd API && composer audit
```

### 5. Copie du fichier d’environnement de test
```yaml
- name: Copy .env.test
  run: cp API/.env.test API/.env
```

### 6. Attente du démarrage de MySQL
```yaml
- name: Wait for MySQL
  run: |
    for i in {1..30}; do
      nc -z 127.0.0.1 3306 && break
      sleep 1
    done
```

### 7. Préparation de la base de données
```yaml
- name: Prepare database
  run: |
      php API/bin/console doctrine:database:drop --force --if-exists --env=test
      php API/bin/console doctrine:database:create --env=test
```

### 8. Exécution des migrations
```yaml
- name: Run migrations
  run: php API/bin/console doctrine:migrations:migrate --no-interaction
```

### 9. Lancement des tests unitaires
```yaml
- name: Run tests
  run: php API/bin/phpunit
```

---

## 🧩 Exemple de résultat attendu

Si tout se passe bien, la CI affiche :
```
All tests passed!
Migrations executed successfully!
No vulnerabilities found in dependencies.
```

---

## 📈 Bilan actuel

⚠️ CI partiellement fonctionnelle  
✅ Compatible PHP 8.3  
⚠️ Certains jobs (migrations base de données / tests) nécessitent encore des ajustements  
⚠️ Pipeline en cours d’optimisation pour atteindre un état stable  

---

💡 **Conclusion**  
Cette version de CI/CD fournit une **base de travail solide**, mais certains aspects, notamment la gestion de la base MySQL et les tests automatisés, nécessitent encore des corrections.  
L’objectif est de rendre la CI **stable et fiable** pour chaque commit afin de faciliter le développement de l’application Symfony **CityBuilder**.

---

🧾 *Dernière mise à jour : octobre 2025 — Mainteneur : Younes Mouhri*

