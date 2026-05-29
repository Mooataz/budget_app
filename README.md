# 💰 BudgetCollab — Application de Gestion Collaborative de Budget

## 📋 Description

**BudgetCollab** est une application web moderne permettant à plusieurs utilisateurs de gérer collaborativement leurs budgets, transactions et dépenses. Construite selon une **architecture MVC en 3 couches** (Frontend, Backend PHP, Base de données), elle intègre:

- ✅ Authentification sécurisée (hachage bcrypt, sessions PHP, CSRF)
- ✅ Gestion multi-utilisateurs avec rôles (Utilisateur / Administrateur)
- ✅ Budgets individuels et partagés entre plusieurs utilisateurs
- ✅ Transactions (revenus/dépenses) avec catégories
- ✅ Système d'alertes automatiques (seuils de 80% et 100%)
- ✅ Tableau de bord avec graphiques Chart.js
- ✅ Panel d'administration pour validation de comptes et gestion globale

## 🏗️ Architecture

```
budget_app/
├── public/
│   ├── index.php              # Front Controller (routeur)
│   ├── css/app.css            # Styles complets
│   └── js/app.js              # JavaScript côté client
├── src/
│   ├── core/
│   │   └── Core.php           # Session, Router, Validator, Response
│   ├── dao/
│   │   └── AllDAO.php         # Classes d'accès aux données
│   ├── services/
│   │   └── AllServices.php    # Logique métier (AuthService, BudgetService, etc.)
│   └── controllers/
│       └── AllControllers.php # Contrôleurs (Auth, Dashboard, etc.)
├── views/
│   ├── auth/                  # Pages login, register, profil
│   ├── dashboard/             # Tableau de bord
│   ├── transactions/          # Gestion des transactions
│   ├── budgets/               # Gestion des budgets
│   ├── categories/            # Catégories
│   ├── admin/                 # Panel admin
│   └── partials/              # Header, footer, 404
├── config/config.php          # Configuration globale
├── database/schema.sql        # Schéma MySQL
└── README.md
```

## 🗄️ Base de Données

**MySQL 8.0+** avec les tables:
- `users` — Utilisateurs (authentification, rôles, statut)
- `roles` — Rôles disponibles
- `budgets` — Budgets (individuel/partagé)
- `transactions` — Revenus et dépenses
- `categories` — Catégories par défaut et personnalisées
- `budget_categories` — Plafonds par catégorie
- `budget_members` — Appartenance aux budgets partagés
- `alertes` — Alertes de dépassement

## 🚀 Installation & Démarrage

### Prérequis
- PHP 8.0+
- MySQL 8.0+
- Apache / Nginx avec réécriture d'URLs

### 1️⃣ Cloner/Télécharger le projet
```bash
cd /var/www/html
git clone https://github.com/votrecompte/budget_app.git
cd budget_app
```

### 2️⃣ Configurer la base de données
```bash
# Adapter les données de connexion dans config/config.php
# Puis importer le schéma
mysql -u root -p budget_db < database/schema.sql
```

### 3️⃣ Configuration Apache (VirtualHost optionnel)
```apache
<VirtualHost *:80>
    ServerName budget.local
    DocumentRoot /var/www/html/budget_app/public
    
    <Directory /var/www/html/budget_app/public>
        AllowOverride All
        Require all granted
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
</VirtualHost>
```

### 4️⃣ Accéder à l'application
```
http://localhost/budget_app/public/login
ou
http://budget.local/login
```

**Compte admin par défaut:**
- Email: `admin@budget.local`
- Mot de passe: `Admin@1234`

## 📱 Fonctionnalités Principales

### Pour les Utilisateurs
- **Inscription & Authentification** — Créer un compte (validation admin requise)
- **Tableau de Bord** — Vue globale avec KPIs et graphiques
- **Transactions** — Ajouter/modifier/supprimer revenus et dépenses
- **Budgets** — Créer des budgets individuels ou partagés
- **Catégories** — Organiser les transactions par catégorie
- **Alertes** — Notifications automatiques si budget dépassé ou proche du seuil
- **Profil** — Gérer ses informations et mot de passe

### Pour les Administrateurs
- **Validation de Comptes** — Approuver les nouvelles inscriptions
- **Gestion des Rôles** — Promouvoir utilisateurs en administrateurs
- **Supervision** — Voir tous les budgets partagés et statistiques globales
- **Suppression de Comptes** — Supprimer des utilisateurs si nécessaire

## 🔐 Sécurité

- ✅ **Authentification** — Sessions PHP sécurisées avec régénération de tokens
- ✅ **Mots de Passe** — Hachage bcrypt avec coût 12
- ✅ **CSRF Protection** — Tokens vérifié à chaque formulaire
- ✅ **SQL Injection** — Requêtes paramétrées (PDO prepared statements)
- ✅ **XSS Protection** — Échappement HTML (`htmlspecialchars`) par défaut
- ✅ **Validation** — Côté serveur stricte (Validator class)

## 🔄 Flux Principaux

### 1. Inscription & Validation
1. Visiteur s'inscrit → compte créé avec statut `EN_ATTENTE`
2. Email envoyé à l'admin pour validation
3. Admin valide → statut passe à `ACTIF`
4. Utilisateur peut se connecter

### 2. Ajout d'une Transaction (DSS-03)
1. Utilisateur complète le formulaire (montant, date, catégorie, budget)
2. Backend valide les données
3. Transaction enregistrée en BD
4. Solde budget recalculé automatiquement
5. Si seuil atteint → Alerte générée
6. Tous les membres du budget reçoivent une notif

### 3. Budget Collaboratif (DSS-04)
1. Créateur crée un budget partagé et invite des membres (emails)
2. Chaque membre reçoit un email avec un lien d'invitation
3. Membre clique → accepte et rejoins le budget
4. Tous les membres voient les transactions en temps réel

## 📊 Technologies Utilisées

**Backend:**
- PHP 8.0+ (OOP, PDO, Sessions)
- MySQL 8.0+ (InnoDB, FK constraints)

**Frontend:**
- HTML5 + CSS3 (Dark mode, Responsive Design)
- Vanilla JavaScript (ES6+)
- Chart.js (Graphiques)
- Lucide Icons (Iconographie)

**Architecture:**
- MVC Pattern (Modèle-Vue-Contrôleur)
- DAO Pattern (Data Access Objects)
- Service Layer (Logique métier)
- Front Controller (Routeur centralisé)

## 📖 Exemple de Requête

### Ajouter une transaction (POST /transactions)
```javascript
const formData = new FormData();
formData.append('type', 'DEPENSE');
formData.append('montant', '50.00');
formData.append('date_op', '2025-05-26');
formData.append('categorie_id', '1');
formData.append('budget_id', '5');
formData.append('description', 'Courses du marché');
formData.append('csrf_token', 'xxxxx');

const res = await fetch('/budget_app/public/index.php/transactions', {
  method: 'POST',
  body: formData
});
const json = await res.json();
console.log(json.ok); // true
console.log(json.taux); // 45.2 (% du budget)
```

## 🧪 Tests

Quelques scénarios à tester:

1. ✅ Inscription → Validation admin → Connexion
2. ✅ Créer un budget individuel → Ajouter des transactions
3. ✅ Créer un budget partagé → Inviter un membre → Ajouter une transaction
4. ✅ Vérifier les alertes (80% et 100%)
5. ✅ Consulter le tableau de bord et les graphiques
6. ✅ Panel admin — valider/suspendre des comptes

## 📝 Améliorations Possibles

- [ ] Notifications email (PHPMailer SMTP)
- [ ] Export PDF/CSV des transactions
- [ ] Prévisions budgétaires (ML simple)
- [ ] App mobile responsive complète
- [ ] Webhook pour notifications temps réel (WebSockets)
- [ ] API REST publique avec JWT
- [ ] Bi-authentification (2FA)
- [ ] Comparaison multi-périodes

## 📄 Licence

MIT License - Libre d'utilisation

## 👥 Auteur

Projet développé à titre pédagogique pour illustrer une **architecture MVC complète** en PHP.

---

**Questions ?** Consultez les diagrammes UML du projet ou le cahier des charges détaillé.
