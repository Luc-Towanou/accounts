## **1. Authentification et Gestion des Utilisateurs**

**Backend :**

* Inscription et login (via Laravel Sanctum déjà en place, vérifier tokens et sécurités). ✅
* Gestion des rôles (admin, utilisateur classique) et permissions.
* Gestion des profils utilisateurs (modification email, mot de passe, informations personnelles).
* Fonction de récupération et réinitialisation du mot de passe.✅
* Validation des données d’inscription et login.✅
* Vérification de la session et protection des routes API.

**Frontend :**

* Pages login, inscription, mot de passe oublié.                            ✅
* Formulaires interactifs avec validation client (JS ou Vue/React).         
* Page profil utilisateur avec édition des informations.
* Notifications d’erreurs/succès.

---

## **2. Gestion des Mois Comptables**

**Backend :**

* CRUD complet sur les mois comptables.
* Gestion de la date de début et fin.
* Lien avec les tableaux et variables.
* Recalcul automatique des totaux à chaque modification (déjà partiellement en place).
* Historique ou versionning des mois pour suivi.

**Frontend :**

* Tableau/listing des mois comptables.
* Boutons création, modification, suppression.
* Vue détaillée du mois avec tous les tableaux et sous-totaux.
* Filtrage et recherche par mois, année.

---

## **3. Gestion des Tableaux**

**Backend :**

* CRUD complet des tableaux.
* Gestion des sous-tableaux et variables simples.
* Calcul automatique des totaux des tableaux et sous-tableaux.
* Liaison avec les règles de calcul (pour les variables calculées).
* Gestion de la colonne “budget prévu” et “dépense réelle”.

**Frontend :**

* Vue liste de tous les tableaux.
* Création/édition/suppression via formulaire.
* Affichage dynamique des totaux et sous-totaux.
* Support drag-and-drop ou réorganisation des tableaux (optionnel pour UX pro).

---

## **4. Gestion des Variables et Sous-Variables**

**Backend :**

* CRUD complet pour variables simples et sous-variables.
* Gestion du type (simple / sous-tableau / calculé).
* Implémentation complète des règles de calcul (`regle_calcul`) avec parsing sécurisé.
* Recalcul en cascade des dépendances.
* Gestion des opérations liées aux sous-variables.

**Frontend :**

* Interface d’ajout/édition des variables et sous-variables.
* Formulaire de saisie de règles de calcul (éditeur friendly).
* Affichage des valeurs calculées et réelles.
* Notifications d’erreurs si la règle est invalide ou si dépendances manquent.

---

## **5. Gestion des Opérations (Dépenses / Revenus)**

**Backend :**

* CRUD complet des opérations.
* Liaison avec sous-variables et mois comptables.
* Recalcul automatique des totaux après ajout/modification/suppression.
* Historique des opérations.
* Possibilité d’ajouter un coefficient ou multiplier les valeurs (déjà évoqué dans les expressions).

**Frontend :**

* Table des opérations filtrables par mois, tableau, variable.
* Formulaire création/édition des opérations.
* Affichage instantané du recalcul des sous-totaux et totaux.
* Filtres par date, catégorie, montant.

---

## **6. Calcul automatique des valeurs**

**Backend :**

* Parser sécurisé pour les expressions (pas `eval`).
* Gestion des parenthèses, coefficients, opérateurs arithmétiques.
* Recalcul en cascade des variables calculées.
* Gestion des cycles ou dépendances multiples.
* Endpoint API pour recalculer une variable spécifique ou tout le mois.

**Frontend :**

* Affichage dynamique des résultats après saisie ou modification.
* Messages clairs sur les erreurs de calcul.
* Interface réactive type "spreadsheet" pour une meilleure UX.

---

## **7. Statistiques et Analyse**

**Backend :**

* Calcul des totaux par catégorie, tableau, sous-variable.
* Filtrage par mois ou intervalle de dates personnalisé.
* Requêtes pour les graphiques et rapports.
* Possibilité d’export CSV ou PDF.
* Indicateurs clés : budget vs réel, variation mensuelle.

**Frontend :**

* Dashboard interactif avec graphiques (Chart.js ou ApexCharts).
* Choix d’affichage par mois ou intervalle de dates.
* Tableaux synthétiques des totaux.
* Alertes visuelles pour dépassement du budget.

---

## **8. Export et Impression**

**Backend :**

* Export PDF des mois comptables complets.
* Export CSV des opérations, tableaux et variables.
* Gestion des dépendances et affichage hiérarchique correct dans PDF.
* Personnalisation du rendu (titre, logo, styles).

**Frontend :**

* Boutons export PDF et CSV.
* Aperçu avant impression.
* Choix du format (PDF A4, portrait/paysage, CSV simple).

---

## **9. Notifications et Alertes**

**Backend :**

* Notification sur dépassement budget.
* Alertes pour opérations manquantes ou mois incomplets.
* Possibilité d’envoyer des emails ou push notifications (optionnel).

**Frontend :**

* Notification visuelle (toast, modal).
* Alertes sur tableau ou dashboard.
* Filtrage des alertes par catégorie ou mois.

---

## **10. Administration et Paramétrage**

**Backend :**

* Gestion des rôles et permissions.
* Configuration globale (devise, langue, seuils de notification).
* Gestion des modèles de tableau ou sous-variable prédéfinis.
* Audit des modifications et logs.

**Frontend :**

* Interface admin pour gérer utilisateurs et paramètres.
* Gestion visuelle des rôles et droits.
* Tableau des logs et historique des modifications.

---

## **11. UX/UI et Frontend global**

* Structure responsive (Bootstrap ou Tailwind).
* Dashboard avec cartes synthétiques.
* Tables interactives (recherche, tri, filtre).
* Formulaires clairs et validés côté client.
* Feedback instantané après actions CRUD.
* Navigation fluide entre mois, tableaux, variables et opérations.
* Possibilité d’un thème sombre/claire (optionnel mais pro).

---

### **Résumé Prioritaire pour une Version Professionnelle**

1. Backend : complétion de toutes les logiques de calcul, règles, dépendances, opérations et export.
2. Frontend : création complète des interfaces CRUD, dashboard, visualisation et export.
3. UX : feedback instantané, responsive, alertes visuelles.
4. Statistiques et analyses pour valeur ajoutée (dashboards et graphiques).
5. Sécurité et contrôle des accès.
