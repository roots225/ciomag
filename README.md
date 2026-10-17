# 🚀 Refonte de CIO MAG — Plateforme Média & Événements

Bienvenue sur le dépôt du projet de **refonte de la plateforme CIO MAG** (Le Magazine des Dirigeants IT & de la Transformation Digitale).

---

### 🌐 Démonstration en ligne

Vous pouvez découvrir et tester dès maintenant cette nouvelle version fonctionnelle à l'adresse suivante :  
👉 **[https://ciomag.quizzpro.net](https://ciomag.quizzpro.net)**

---

### ✨ Présentation de la nouvelle version

Pensée pour moderniser l'expérience de vos lecteurs et simplifier le travail de votre rédaction, cette solution tout-en-un intègre nativement de nombreuses fonctionnalités :

- **Gestion complète des événements et des participants** (inscriptions, billetterie et suivi pour vos forums, ATDA, webinaires, etc.)
- **Boutique en ligne intégrée** pour la vente de vos parutions et contenus numériques
- **Système de gestion des abonnements** souple et automatisé
- **Publication et diffusion automatiques** de vos nouveaux articles sur vos réseaux sociaux
- **Interface moderne, fluide, responsive et optimisée pour le référencement (SEO)**

> **Mise en production rapide :** La plateforme étant déjà développée et opérationnelle, sa mise en production peut se faire très rapidement, avec une reprise fluide de vos contenus existants.

---

### 🛠️ Stack Technique

- **Backend :** [Laravel 11](https://laravel.com) (PHP 8.3)
- **Frontend :** Tailwind CSS, Alpine.js, Vite
- **Données & Cache :** MySQL 8.0, Redis
- **Infrastructure :** Docker, Nginx, CI/CD GitHub Actions

---

### 💻 Installation & Démarrage local (Docker)

1. **Cloner le projet :**
   ```bash
   git clone <repo-url>
   cd ciomag
   ```

2. **Créer le fichier d'environnement :**
   ```bash
   cp .env.example .env
   ```

3. **Lancer les conteneurs Docker :**
   ```bash
   ./start.sh
   ```

4. **Accès à l'application :**
   - Site web : [http://localhost:8000](http://localhost:8000)
