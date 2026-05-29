# API Endpoints Documentation

## Authentication
- `GET /login` — Affiche le formulaire de connexion
- `POST /login` — Traite la connexion
- `GET /register` — Formulaire d'inscription
- `POST /register` — Traite l'inscription
- `GET /logout` — Déconnecte l'utilisateur

## Dashboard
- `GET /dashboard` — Tableau de bord personnel

## Transactions
- `GET /transactions` — Liste les transactions avec filtres
- `POST /transactions` — Crée une transaction
- `POST /transactions/{id}/update` — Modifie une transaction
- `POST /transactions/{id}/delete` — Supprime une transaction

## Budgets
- `GET /budgets` — Liste des budgets de l'utilisateur
- `GET /budgets/{id}` — Détail d'un budget
- `POST /budgets` — Crée un nouveau budget
- `POST /budgets/{id}/update` — Modifie un budget
- `POST /budgets/{id}/delete` — Supprime un budget
- `POST /budgets/{id}/inviter` — Invite un membre (email)
- `GET /budgets/rejoindre/{token}` — Accepte une invitation (lien)

## Catégories
- `GET /categories` — Liste les catégories
- `POST /categories` — Crée une catégorie
- `POST /categories/{id}/update` — Modifie une catégorie
- `POST /categories/{id}/delete` — Supprime une catégorie
- `GET /api/categories` — API : Liste JSON des catégories

## Alertes (API)
- `GET /api/alertes` — Liste les alertes non lues
- `POST /api/alertes/{id}/lue` — Marque une alerte comme lue
- `POST /api/alertes/tout-lire` — Marque toutes les alertes comme lues

## Administration (Admin only)
- `GET /admin` — Dashboard admin
- `POST /admin/comptes/{id}/valider` — Valide un compte en attente
- `POST /admin/comptes/{id}/suspendre` — Suspend un compte
- `POST /admin/comptes/{id}/supprimer` — Supprime un compte
- `POST /admin/comptes/{id}/role` — Change le rôle d'un utilisateur

## Profil
- `GET /profil` — Affiche le profil
- `POST /profil` — Modifie les infos personnelles
- `POST /profil/password` — Change le mot de passe

## Schéma des Paramètres

### Transaction
```json
{
  "type": "REVENU|DEPENSE",
  "montant": 50.00,
  "date_op": "2025-05-26",
  "categorie_id": 1,
  "budget_id": 5,
  "description": "Courses",
  "csrf_token": "xxxxx"
}
```

### Budget
```json
{
  "nom": "Budget Ménage",
  "plafond_global": 100000,
  "periode": "MENSUEL|HEBDOMADAIRE|PERSONNALISE",
  "date_debut": "2025-05-01",
  "date_fin": "2025-05-31",
  "type": "INDIVIDUEL|PARTAGE",
  "csrf_token": "xxxxx"
}
```

### Catégorie
```json
{
  "nom": "Loisirs",
  "icone": "gamepad",
  "csrf_token": "xxxxx"
}
```

## Réponses

### Succès (200, 201)
```json
{
  "ok": true,
  "message": "Opération réussie",
  "data": { /* données spécifiques */ }
}
```

### Erreur (4xx, 5xx)
```json
{
  "ok": false,
  "message": "Description de l'erreur",
  "errors": {
    "field": "Message d'erreur du champ"
  }
}
```

## Codes HTTP
- `200` — OK
- `201` — Created
- `400` — Bad Request
- `403` — Forbidden (accès refusé)
- `404` — Not Found
- `422` — Validation Error
- `500` — Server Error

## Authentification
Tous les endpoints (sauf /login, /register) nécessitent une session active.

Les administrateurs ont accès aux endpoints `/admin/*`.

Le token CSRF doit être inclus dans tous les formulaires POST.
