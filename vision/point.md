Jeudi 24/07
# Point sur : projet de gestion de comptes personnels


## Backend

## Nom du projet :

**Plateforme de Gestion de Comptes Personnels**

## Objectif :

Permettre aux utilisateurs de gérer leurs dépenses, suivre leurs budgets mensuels, organiser leurs variables financières (ex. : transport, scolarité), et bénéficier d’analyses personnalisées sur le long terme.

## Utilisateurs :

* **Utilisateur simple / Admin (Version 1)** : accès complet à la gestion des comptes.

## Structure générale actuelle (Version 1  en cours) :

### Authentification : ✅

* Système d’enregistrement et de connexion (par otp) ✅
* Mot de passe oublié ( otp ) ✅
* Rôles d’utilisateur (admin ou simple utilisateur) ✅


### Entités principales : ✅

#### 1. **Utilisateurs** ✅

* Chaque utilisateur dispose de ses propres tableaux et sous-tableaux.

#### 2. **Tableaux (ex : Budget mensuel)** :

* Chaque tableau contient des **lignes/variables**.

#### 3. **Variables** (lignes dans les tableaux) : ✅

* Représentent une dépense (ex. : Scolarité, Déjeuner)
* Peuvent être **fixes** ou **calculées dynamiquement** à partir de sous-variables.

#### 4. **Sous-tableaux** : ✅

* Permettent de structurer les variables complexes (ex. : Transport = Taxi matin + Bus soir)
* Les variables principales peuvent être **liées** à la somme de ces sous-variables.

#### 5. **Opérations** : ✅

* Liées à une variable (ou sous-variable)
* Contiennent le **montant**, une **description**, et la **date**
* Contribuent à calculer automatiquement la **dépense réelle**


## Colonnes de chaque variable : ✅

* **Budget prévu** (défini par l’utilisateur, optionnel)
* **Dépense réelle** (calculée automatiquement à partir des opérations)


## Technologie actuelle:

* **Backend** : Laravel 12
* **Base de données** : MySQL (via WAMP)


















## Organisation temporelle :

* Un utilisateur commence son suivi **à partir de son mois d’inscription**
* Les données sont organisées **par mois**
* Possibilité d’afficher les totaux par mois ou par tableau


## Tableau de bord :

* Petites **analyses visuelles** (totaux, comparaisons prévues/réelles)
* À enrichir dans la V2 avec des graphiques et recommandations

## Fonctionnalités bonus (prévision ou en cours) :

* **Création automatique de sous-tableaux liés**
* **Recalcul dynamique** des totaux dès qu’une opération est ajoutée
* **Gestion de catégories** ou tags pour classer les variables
* **Export PDF / Excel** pour les rapports mensuels (prévu)
* **Support multi-devices** avec interface responsive (via Bootstrap)


## Technologie actuelle:

* **Backend** : Laravel 12
* **Base de données** : MySQL (via WAMP)

---

## 📦 Version 2 (prévision) :

### 🔐 Système d’abonnement :

* Limites selon le type d’abonnement (ex. : nombre de variables, de tableaux, d’exports…)

### 🧠 Analyse intelligente :

* Recommandations basées sur 3 mois d’utilisation
* Alerte sur dérives de budget
* Suggestions d’économies ou d’optimisation

Souhaites-tu que je te génère un **schéma visuel** de cette structure ou un **tableau de synthèse** (ex. Excel ou Markdown) ?
Tu veux aussi que je t’aide à faire un README propre pour GitHub ou autre ?
v
# Vendredi 25/07
Point du jeudi      
    Résumé de la journée d’hier (22 juillet 2025)
🔹 1. Clarification de la structure de base
Nous avons confirmé et consolidé la structure suivante :
Un tableau contient des variables
Une variable peut être : 
de type "simple" (avec opérations directes)
ou de type "sous-tableau" (elle contient alors des sous-variables)
Une sous-variable est liée à une variable de type "sous-tableau"
➕ Ajout d’un champ calculé booléen sur variables et sous_variables :
Si calculé = true → la valeur est dérivée automatiquement via une règle (regle_calcul)
Sinon → la valeur est basée sur les opérations saisies manuellement
🔹 2. Création ou mise à jour des migrations
Mise à jour des colonnes dans les migrations : 
calculé → booléen (default false)
regle_calcul → nullable string (optionnel)
Migration corrigée pour que operations puisse être liées à : 
une variable_id
ou une sous_variable_id
🔹 3. Réécriture de la fonction store() pour MoisComptable
Fonction améliorée pour :
Créer un mois avec ses tableaux
Créer les variables (simples ou sous-tableaux) avec leurs champs calculé, regle_calcul, etc.
Créer les sous-variables si présentes
Enregistrer les règles de calcul pour chaque entité calculée
🔹 4. Mise à jour du CRUD des tableaux, variables, sous-variables et opérations
Chacun de ces éléments a reçu :
Des méthodes store, update, destroy, index, show adaptées
Une logique prenant en compte : 
la présence ou non de calculé
la possibilité de définir une regle_calcul
🔹 5. Refonte complète de l’OperationObserver
Recalcul automatique des totaux dès qu’une opération est ajoutée, modifiée ou supprimée
Respect des règles suivantes : 
Si la variable ou sous-variable est calculé = true → ne pas faire de somme
Si elle est non-calculée → somme des opérations
En fin de chaque opération : recalcul de toutes les variables et sous-variables calculées via RegleCalculService
🔹 6. Création du RegleCalculService
Service central qui :
Interprète les expressions dynamiques telles que : = variable("Transport") + sous_variable("Goûter") - 1500 
Remplace dynamiquement les noms de variables/sous-variables par leurs valeurs réelles
Utilise eval() en sécurité (après filtrage de l'expression)
🔹 7. Mise à jour du contexte global du projet
Nous avons consolidé la logique globale pour une meilleure cohérence : 

Un tableau est une collection de variables, dont certaines sont des groupes (type sous-tableau) contenant à leur tour des sous-variables.
Chaque niveau peut avoir des totaux, fixes ou calculés dynamiquement.
🧭 Prochaine étape possible
Voici ce que tu pourrais attaquer maintenant :
Action possibleObjectif✅ Interface pour saisir les règles de calcul (Blade ou Vue)Aide à la création d'expressions✅ Commande artisan pour recalcul manuelphp artisan regles:recalculer✅ Tests unitaires du serviceSécuriser les calculs✅ Affichage dynamique des totauxIntégration avec la vue Blade✅ Validation du calculé = true → regle_calcul obligatoireRenforcement logique des formulaires 
Souhaites-tu que l’on démarre une commande artisan de recalcul, ou que l’on ajoute un recalcul direct dans l’interface utilisateur ?


Point sur : projet de gestion de comptes personnels --avancement --


## 1. Creation de l'observer d'opératopn 'OperationObserver'
        Qui suit toute modification faite sur la table opération et qui recalcule automatiquement les totaux de toutes les variables et sous-variables calculées.

##  2. creation du service de regle calcul 'RegleCalculService'
        Qui permet de calculer dynamiquement les valeurs des variables et sous-variables calculées.

        Les variable et sous variable de type 'calculé' sont des variables qui sont calculées dynamiquement en fonction d'autres variables ou sous-variables. 
        Exemple : Expression = variable("Transport") + sous_variable("Goûter") - 1500 
        La valeur de expression sera enregistré dans la table 'Regle_calcule' et associé à la variable que l'utilisateur a voulu sauvegardé sous le type 'calculé'. Ainsi au moment de l'affichage de cette variable dans la vue, la valeur de l'expression sera calculée dynamiquement en fonction de la valeur des variables "Transport" et "Goûter".
        Cela permet d'avoir des variable dont le résultat n'est pas juste la somme d'operations simple mais qui sont calculées dynamiquement en fonction d'autres variables ou sous-variables que l'utilisateur meme a créé.
        