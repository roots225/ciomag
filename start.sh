#!/usr/bin/env bash
set -e

# 2. Vérification de Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Erreur: Docker n'est pas installé sur ce système."
    exit 1
fi

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "❌ Erreur: docker-compose n'est pas disponible."
    exit 1
fi

COMPOSE_CMD="docker compose"
if ! docker compose version &> /dev/null; then
    COMPOSE_CMD="docker-compose"
fi

# 3. Démarrage des conteneurs (Nginx, PHP-FPM 8.3, MySQL 8.0, Redis 7.0, Queue Worker)
echo "🐳 Lancement des conteneurs Docker..."
$COMPOSE_CMD up -d --build

# 5. Préparation des répertoires de stockage & permissions
echo "📁 Préparation des répertoires storage et cache..."
$COMPOSE_CMD exec -T app mkdir -p storage/app/public \
                                   storage/framework/cache/data \
                                   storage/framework/sessions \
                                   storage/framework/views \
                                   storage/logs \
                                   bootstrap/cache
$COMPOSE_CMD exec -T app chmod -R 777 storage bootstrap/cache

# 6. Dépendances PHP & Génération de Clé d'application
echo "📦 Installation / vérification des dépendances Composer..."
$COMPOSE_CMD exec -T app composer install --optimize-autoloader --no-interaction

echo "🔑 Génération de la clé d'application Laravel..."
$COMPOSE_CMD exec -T app php artisan key:generate --force

# 6. Exécution des migrations & seeders (Kits, Formules BNPL, Produits, Utilisateurs)
echo "🗄️ Exécution des migrations et chargement du catalogue CI..."
$COMPOSE_CMD exec -T app php artisan migrate:fresh --seed --force

# 7. Lien de stockage
$COMPOSE_CMD exec -T app php artisan storage:link || true