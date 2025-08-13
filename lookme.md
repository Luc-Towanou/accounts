# Backend laravel 12

## Modelisation 

###  class 

Diagramme de classes en format texte UML simplifié, avec trois sections par classe (nom, attributs, méthodes), encapsulation indiquée (+ public, - privé). Les relations sont listées à la fin.



Utilisateur

-------------------------------
|        Utilisateur          |
-------------------------------
| +id: int                    |
| +nom: string                |
| +email: string              |
| +mot_de_passe: string       |
| +role: string               |
-------------------------------
| +s_enregistrer()            |
| +se_connecter()             |
| +accéderDashboard()         |
-------------------------------


MoisComptable

-------------------------------
|       MoisComptable         |
-------------------------------
| +id: int                    |
| +utilisateur_id: int        |
| +mois: string               |
| +annee: int                 |
-------------------------------
| +démarrerComptabilité()     |
-------------------------------


Tableau

-------------------------------
|          Tableau            |
-------------------------------
| +id: int                    |
| +mois_id: int               |
| +nom: string                |
| +description: string        |
| +budget_prevu: float        |
| +depense_reelle: float      |
-------------------------------
| +ajouterVariable()          |
| +calculerDépenses()         |
-------------------------------


SousTableau

-------------------------------
|        SousTableau          |
-------------------------------
| +id: int                    |
| +tableau_id: int            |
| +nom: string                |
| +description: string        |
-------------------------------
| +ajouterVariable()          |
| +totaliserDépenses()        |
-------------------------------


Variable

-------------------------------
|          Variable           |
-------------------------------
| +id: int                    |
| +tableau_id: int            |
| +sous_tableau_id: int       |
| +nom: string                |
| +type: string               |
| +valeur_prevue: float       |
| +valeur_reelle: float       |
-------------------------------
| +ajouterOperation()         |
| +mettreAJourValeur()        |
-------------------------------


RegleCalcul

-------------------------------
|         RegleCalcul         |
-------------------------------
| +id: int                    |
| +variable_id: int           |
| +expression: string         |
-------------------------------
| +evaluer()                  |
-------------------------------


Operation

-------------------------------
|          Operation          |
-------------------------------
| +id: int                    |
| +variable_id: int           |
| +montant: float             |
| +date_operation: date       |
| +commentaire: string        |
-------------------------------
| +enregistrer()              |
-------------------------------


Relations entre les classes :

Utilisateur "1" ------------> "*" MoisComptable
MoisComptable "1" ----------> "*" Tableau
Tableau "1" ----------------> "*" SousTableau
Tableau "1" ----------------> "*" Variable
SousTableau "1" ------------> "*" Variable
Variable "1" ---------------> "*" Operation
Variable "1" ---------------> "0..1" RegleCalcul

Souhaites-tu que je te génère cette version sous forme de document .txt ou .pdf ?

## migrations

    - user
    - mois_comptable
    - tableaux
    - sous_tableaux
    - variables
    - opérations
    - règles_cacul 

    
    

## installations

###  api / sanctum

php :  
composer install:api


### scramble 
**ugvuyuyv**