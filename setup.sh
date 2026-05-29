#!/bin/bash
# setup.sh — Script de mise en place rapide

echo "🚀 Configuration rapide de BudgetCollab..."

# Vérifier les dépendances
echo "📋 Vérification des dépendances..."

if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé"
    exit 1
fi
echo "✅ PHP $(php -v | head -n1)"

if ! command -v mysql &> /dev/null; then
    echo "⚠️  MySQL n'est pas dans PATH, mais ça peut être OK"
fi

# Créer les répertoires manquants
echo "📁 Création des répertoires..."
mkdir -p public/uploads
mkdir -p public/cache
mkdir -p database/backups
echo "✅ Répertoires créés"

# Permissions
echo "🔐 Ajustement des permissions..."
chmod 755 public
chmod 644 public/css/*.css
chmod 644 public/js/*.js
chmod 755 database
echo "✅ Permissions ajustées"

# Configuration
if [ ! -f config/config.php ]; then
    echo "⚠️  config/config.php n'existe pas - créez-le en copiant config/config.php.example"
fi

echo ""
echo "======================================"
echo "✨ Configuration complète !"
echo "======================================"
echo ""
echo "Prochaines étapes:"
echo "1. Adaptez config/config.php avec vos credentials MySQL"
echo "2. Créez la BD: mysql -u root -p < database/schema.sql"
echo "3. Accédez à: http://localhost/budget_app/public/login"
echo ""
echo "Compte admin par défaut:"
echo "  Email: admin@budget.local"
echo "  Mot de passe: Admin@1234"
echo ""
