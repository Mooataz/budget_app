# Rapport de Soutenance

## BudgetCollab — Application Collaborative de Gestion de Budget

---

**Présenté par :** [Prénom NOM]

**Date :** Mai 2026

**Établissement :** [Nom de l'établissement]

**Formation :** [Intitulé de la formation]

---

# Table des matières

1. [Introduction](#1-introduction)
2. [Analyse des besoins](#2-analyse-des-besoins)
3. [Conception et architecture](#3-conception-et-architecture)
4. [Technologies utilisées](#4-technologies-utilisées)
5. [Fonctionnalités implémentées](#5-fonctionnalités-implémentées)
6. [Sécurité](#6-sécurité)
7. [Limitations et améliorations futures](#7-limitations-et-améliorations-futures)
8. [Conclusion](#8-conclusion)

---

# 1. Introduction

## 1.1 Contexte

Dans un environnement économique où la maîtrise des finances personnelles et collectives est devenue une nécessité, la gestion budgétaire occupe une place centrale dans la vie quotidienne. Les outils traditionnels (feuilles de calcul, relevés bancaires papier) présentent des limites en termes de collaboration, de réactivité et de visibilité. Les solutions du marché sont souvent trop coûteuses ou trop complexes pour une utilisation familiale ou en petit groupe.

C'est dans ce contexte qu'est né **BudgetCollab** : une application web collaborative de gestion de budget, conçue pour permettre à plusieurs utilisateurs de gérer ensemble leurs finances, qu'il s'agisse d'un budget familial, d'un projet associatif, ou d'une petite équipe.

## 1.2 Problématique

La gestion budgétaire collective pose plusieurs défis :
- Comment garantir que chaque membre visualise l'état des finances en temps réel ?
- Comment transmettre les alertes de dépassement à tous les concernés ?
- Comment gérer les permissions et rôles dans un environnement collaboratif sécurisé ?

Les applications bancaires classiques n'offrent pas de vue consolidée collaborative, et les tableurs partagés manquent d'automatisation et de sécurité.

## 1.3 Objectifs

- Développer une application web sécurisée pour la gestion collaborative de budgets
- Permettre le suivi en temps réel des transactions et soldes
- Offrir un système d'alertes automatiques aux seuils de 80 % et 100 %
- Faciliter la collaboration via invitations et budgets partagés
- Proposer une interface intuitive avec visualisations graphiques (Chart.js)
- Assurer un niveau de sécurité conforme aux standards du web

---

# 2. Analyse des besoins

## 2.1 Besoins fonctionnels

**Module d'authentification :** Création de compte sécurisée, connexion/déconnexion, validation des comptes par un administrateur.

**Gestion des transactions :** CRUD complet des revenus et dépenses, association à une catégorie et un budget, validation côté serveur.

**Gestion des budgets :** Création avec montant limite et période, budgets individuels ou partagés, barre de progression visuelle.

**Système d'alertes :** Déclenchement automatique à 80 % (vigilance) et 100 % (critique), notification aux membres.

**Tableau de bord :** KPIs (revenus, dépenses, solde, alertes), graphiques d'évolution et de répartition (Chart.js), filtres par période.

**Fonctionnalités collaboratives :** Invitation par email, acceptation/refus, budgets partagés avec rôles (propriétaire/membre).

**Panneau d'administration :** Validation des comptes, gestion des utilisateurs, statistiques globales.

## 2.2 Besoins non fonctionnels

**Sécurité :** Protection contre les injections SQL, XSS, CSRF ; hachage bcrypt des mots de passe ; sessions sécurisées.

**Performance :** Requêtes optimisées, temps de réponse < 2 secondes pour les opérations courantes.

**Maintenabilité :** Architecture MVC avec couches DAO et Services, code orienté objet.

**Utilisabilité :** Interface responsive, sombre (dark mode), icônes Lucide, navigation intuitive.

**Portabilité :** Fonctionne sur environnement LAMP/WAMP standard sans dépendances lourdes.

---

# 3. Conception et architecture

## 3.1 Architecture globale

BudgetCollab adopte une **architecture 3 tiers** couplée au **modèle MVC** :

**Tier 1 — Frontend (Présentation) :** Vues PHP, CSS, JavaScript, icônes Lucide. Système de templates avec inclusions partielles (header, footer, sidebar).

**Tier 2 — Backend (Logique applicative) :**
- *Contrôleurs :* Reçoivent les requêtes via le Front Controller, orchestrent les appels aux services.
- *Services :* Logique métier (TransactionService, BudgetService, AuthService, AlerteService, AdminService, DashboardService).
- *DAO :* Abstraction d'accès aux données avec requêtes SQL préparées (PDO).

**Tier 3 — Base de données (Persistance) :** MySQL 8+ avec InnoDB, clés étrangères, contraintes d'intégrité.

**Front Controller :** `public/index.php` — point d'entrée unique qui initialise la session, charge la configuration, et dispatche via le routeur.

## 3.2 Schéma de la base de données

| Table | Rôle |
|---|---|
| `users` | Utilisateurs (nom, email, mdp_hash, rôle, statut, tentatives) |
| `categories` | Catégories de transactions (nom, icône, est_defaut, user_id) |
| `budgets` | Budgets (nom, plafond_global, période, type INDIVIDUEL/PARTAGE) |
| `budget_categories` | Plafonds par catégorie pour chaque budget |
| `budget_members` | Membres des budgets partagés (rôle, statut, token invitation) |
| `transactions` | Revenus et dépenses (montant, type, date, description) |
| `alertes` | Alertes (niveau AVERTISSEMENT/CRITIQUE, message, lue) |

Toutes les tables utilisent InnoDB avec des clés étrangères et des contraintes `ON DELETE CASCADE`.

## 3.3 Architecture des classes

**Couche Core (noyau) :**
- `Database` — Singleton PDO
- `Session` — Gestion sécurisée des sessions
- `Router` — Mapping URL -> Contrôleur
- `Response` — Réponses JSON, redirections, contrôles d'accès
- `Validator` — Validation des entrées (required, email, numeric, date, inArray)

**Couche DAO :**
- `UtilisateurDAO`, `TransactionDAO`, `BudgetDAO`, `CategorieDAO`, `AlerteDAO`, `MembreBudgetDAO`

**Couche Service :**
- `AuthService` — Inscription, authentification, changement mot de passe
- `TransactionService` — Ajout, modification, suppression avec recalul des soldes
- `BudgetService` — Création, accès, invitation, calcul des taux
- `DashboardService` — Agrégation des données pour le tableau de bord
- `AlerteService` — Évaluation des seuils et notifications
- `AdminService` — Validation des comptes, statistiques

**Couche Contrôleur :**
- `AuthController`, `DashboardController`, `TransactionController`, `BudgetController`, `CategorieController`, `AlerteController`, `AdminController`

## 3.4 Routage

| Méthode | Route | Contrôleur |
|---|---|---|
| GET | `/` | DashboardController::index |
| GET/POST | `/login` | AuthController |
| GET/POST | `/register` | AuthController |
| GET | `/logout` | AuthController |
| GET/POST | `/transactions` | TransactionController |
| POST | `/transactions/{id}/update` | TransactionController::update |
| POST | `/transactions/{id}/delete` | TransactionController::destroy |
| GET/POST | `/budgets` | BudgetController |
| GET | `/budgets/{id}` | BudgetController::show |
| POST | `/budgets/{id}/update` | BudgetController::update |
| POST | `/budgets/{id}/delete` | BudgetController::destroy |
| POST | `/budgets/{id}/inviter` | BudgetController::inviter |
| GET | `/mes-invitations` | BudgetController::showInvitations |
| POST | `/invitations/{id}/accepter` | BudgetController::accepterInvitation |
| POST | `/invitations/{id}/refuser` | BudgetController::refuserInvitation |
| GET/POST | `/categories` | CategorieController |
| GET | `/api/categories` | CategorieController::list |
| GET | `/api/alertes` | AlerteController::list |
| POST | `/api/alertes/{id}/lue` | AlerteController::marquerLue |
| GET | `/api/invitations/count` | BudgetController::countInvitations |
| GET | `/admin` | AdminController::dashboard |
| POST | `/admin/comptes/{id}/valider` | AdminController::valider |
| POST | `/admin/comptes/{id}/suspendre` | AdminController::suspendre |
| POST | `/admin/comptes/{id}/supprimer` | AdminController::supprimer |
| GET/POST | `/profil` | AuthController |

---

# 4. Technologies utilisées

## 4.1 Langages et frameworks

**PHP 8+** — Orienté objet, sans framework lourd. Typage strict, PDO pour l'accès BD, password_hash pour la cryptographie.

**MySQL 8+** — InnoDB, clés étrangères, contraintes d'intégrité, charset utf8mb4.

**JavaScript (ES6+)** — Vanilla JS, Fetch API pour les appels AJAX, manipulation DOM.

**HTML5 + CSS3** — Design responsive, dark mode, variables CSS personnalisées.

## 4.2 Bibliothèques

**Chart.js 4.4** — Graphiques d'évolution (line) et de répartition (doughnut).

**Lucide Icons** — Iconographie vectorielle via balises SVG.

## 4.3 Environnement

**XAMPP** (Apache + MySQL + PHP) — Développement local.
**Git + GitHub** — Gestion de versions et déploiement.

---

# 5. Fonctionnalités implémentées

## 5.1 Authentification

L'inscription crée un compte avec statut `EN_ATTENTE`. Le mot de passe est haché avec bcrypt (coût 12). La connexion vérifie le statut ACTIF, régénère la session, et redirige selon le rôle. La déconnexion détruit la session. Tous les formulaires incluent un token CSRF.

## 5.2 Tableau de bord

Quatre cartes KPI affichent les revenus, dépenses, solde et alertes. Deux graphiques Chart.js montrent l'évolution temporelle (courbes) et la répartition par catégorie (donut). Un sélecteur de période (semaine/mois/année) filtre les données. Les budgets et transactions récentes sont affichés avec barres de progression.

## 5.3 Transactions

CRUD complet avec validation stricte (type, montant positif, date, catégorie, budget). Les transactions sont affichées dans un tableau trié par date avec code couleur (vert revenu / rouge dépense). L'ajout d'une transaction déclenche le recalcul automatique du solde du budget associé et l'évaluation des seuils d'alerte.

## 5.4 Budgets

Création avec nom, plafond, période (MENSUEL/HEBDOMADAIRE/PERSONNALISE), et type (INDIVIDUEL/PARTAGE). Barre de progression dynamique avec changement de couleur (< 80 % vert, 80-99 % orange, ≥ 100 % rouge). Page de détail avec onglets : transactions du budget, plafonds par catégorie, et membres (pour les budgets partagés).

## 5.5 Catégories

10 catégories par défaut (Alimentation, Transport, Logement, Santé, Loisirs, Études, Vêtements, Abonnements, Épargne, Autres). Création de catégories personnalisées avec sélection d'icône Lucide.

## 5.6 Alertes

Déclenchement automatique à chaque transaction :
- **< 80 %** : aucune alerte (suppression de l'alerte existante si le budget revient sous le seuil)
- **≥ 80 %** : alerte AVERTISSEMENT — "Budget à X% — Proche de la limite"
- **≥ 100 %** : alerte CRITIQUE — "Budget dépassé ! Consommation : X%"

Les alertes sont affichées dans le tableau de bord et accessibles via une icône cloche dans l'en-tête. Un badge indique le nombre d'alertes non lues.

## 5.7 Collaboratif

**Invitations :** Depuis la création d'un budget partagé (saisie d'emails) ou depuis la page de détail. L'invité reçoit une invitation en attente visible dans l'interface.

**Badge de notifications :** La sidebar et la barre supérieure affichent un badge avec le nombre d'invitations en attente. Les données sont chargées via `GET /api/invitations/count`.

**Page dédiée :** `GET /mes-invitations` liste toutes les invitations avec le nom du budget, l'inviteur et la date. Boutons Accepter/Refuser avec mise à jour en temps réel du compteur.

**Budgets partagés :** Les membres peuvent visualiser toutes les transactions du budget et en ajouter. Le propriétaire peut retirer des membres.

## 5.8 Administration

**Validation des comptes :** Liste des utilisateurs en attente avec boutons Valider/Refuser. La validation passe le statut à ACTIF.

**Gestion des utilisateurs :** Modification du rôle (UTILISATEUR/ADMINISTRATEUR), suspension, suppression.

**Statistiques globales :** KPIs (nombre d'utilisateurs, revenus totaux, dépenses totales, nombre de budgets).

---

# 6. Sécurité

| Mesure | Implémentation |
|---|---|
| **Injection SQL** | 100 % des requêtes avec PDO prepared statements + typage des paramètres |
| **XSS** | `htmlspecialchars()` avec `ENT_QUOTES` sur toutes les sorties utilisateur |
| **CSRF** | Token unique généré par `Session::generateCsrf()`, vérifié sur chaque formulaire POST |
| **Mots de passe** | `password_hash()` avec bcrypt, coût 12, `password_verify()` pour vérification |
| **Sessions** | `httponly`, `samesite=Lax`, régénération après connexion |
| **Validation entrées** | Classe `Validator` : required, email, numeric, date, inArray, minLength |

---

# 7. Limitations et améliorations futures

| Amélioration | Priorité | Description |
|---|---|---|
| **Service email** | Haute | PHPMailer pour notifications d'invitation, validation de compte, alertes |
| **Export PDF/CSV** | Moyenne | Export des transactions et rapports budgétaires |
| **Tests automatisés** | Haute | PHPUnit pour services et DAO, tests fonctionnels |
| **Objectifs financiers** | Moyenne | Module d'épargne avec suivi visuel de progression |
| **API REST** | Basse | Endpoints JSON sécurisés pour application mobile |
| **WebSockets** | Basse | Notifications temps réel sans rechargement |
| **2FA** | Basse | Authentification à deux facteurs (TOTP) |
| **Pagination** | Moyenne | Pagination avancée avec filtres combinés |
| **Comparaison multi-périodes** | Basse | Comparer mois/années dans les graphiques |
| **Filtre type transactions** | Faible | Filtre REVENU/DEPENSE dans la page transactions |

---

# 8. Conclusion

Le projet **BudgetCollab** a permis de concevoir et développer une application web complète de gestion collaborative de budget.

**Apports techniques :**
- Architecture MVC 3 tiers avec séparation stricte des responsabilités
- Motif DAO pour l'abstraction de la persistance
- Front Controller pour le routage centralisé
- Couche Service pour la logique métier
- PHP orienté objet sans framework lourd

**Sécurité :** Les bonnes pratiques ont été appliquées à chaque couche (requêtes préparées, échappement des sorties, CSRF, bcrypt, sessions sécurisées).

**Valeur ajoutée :** Les fonctionnalités collaboratives (invitations, budgets partagés, alertes automatiques, badges de notification) distinguent l'application des simples outils de suivi personnel.

**Perspectives :** Service email, export de données, tests automatisés, API REST, et application mobile constituent les axes d'évolution naturels.

BudgetCollab représente un travail abouti alliant rigueur architecturale, qualité de code, sécurité et expérience utilisateur, répondant à des cas d'usage réels et variés de gestion budgétaire collaborative.

---

*Document généré le 29 mai 2026*
