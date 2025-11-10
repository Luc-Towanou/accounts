<?php

namespace App\Http\Middleware;

use App\Models\Categorie;
use App\Models\MoisComptable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMoisComptable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request);
    // }
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        // dd( 'Vérification !');
        if ($user) {
            // $mois = MoisComptable::where('user_id', $user->id)
            //                     ->first();
            // // dd( 'Vérification !' . $mois);
            // if ($mois) {
            //     // dd( 'Vérification 2 !' . $mois);

                $this->ensureMoisComptableAvecHeritage($user);
            // }
        }

        return $next($request);
    }

    private function ensureMoisComptableAvecHeritage($user)
    {
        // dd( 'Vérification 3 !' . $user);
        $annee = now()->year;
        $moisNum  = now()->month;

        if ($moisNum === 1) $mois = 'janvier';
        if ($moisNum === 2) $mois = 'fevrier';
        if ($moisNum === 3) $mois = 'mars';
        if ($moisNum === 4) $mois = 'avril';
        if ($moisNum === 5) $mois = 'mai';
        if ($moisNum === 6) $mois = 'juin';
        if ($moisNum === 7) $mois = 'juillet';
        if ($moisNum === 8) $mois = 'aout';
        if ($moisNum === 9) $mois = 'septembre';
        if ($moisNum === 10) $mois = 'octobre';
        if ($moisNum === 11) $mois = 'novembre';
        if ($moisNum === 12) $mois = 'decembre';

        // Vérifier si le mois existe déjà
        $moisComptable = MoisComptable::firstOrCreate(
            [
                'user_id' => $user->id,
                'annee'   => $annee,
                'mois'    => $mois,
            ],
            [
                'date_debut'   => now()->startOfMonth()->startOfDay(),
                'date_fin'     => now()->endOfMonth()->endOfDay(),
            ]
        );

        // Si c’est un nouveau mois, hériter de la structure précédente
        // dd($moisComptable->wasRecentlyCreated);
        // dd([
        //     'recently_created' => $moisComptable->wasRecentlyCreated,
        //     'exists'           => $moisComptable->exists,
        //     'attributes'       => $moisComptable->getAttributes()
        // ]); 
        if ($moisComptable->wasRecentlyCreated) {
            // $dernierMois = MoisComptable::where('id', 20)
            $dernierMois = MoisComptable::where('user_id', $user->id)
                ->where(function ($q) use ($moisComptable) {
                    $q->where('annee', '<', $moisComptable->annee)
                      ->orWhere(function ($q2) use ($moisComptable) {
                          $q2->where('annee', $moisComptable->annee)
                             ->where('mois', '<', $moisComptable->mois);
                      });
                })
                ->orderBy('annee', 'desc')
                ->orderBy('mois', 'desc')
                ->first();
            // dd($dernierMois); 
            
            // if ($dernierMois) {
            //     foreach ($dernierMois->tableaux as $tableau) {
            //         $nouveauTableau = $moisComptable->tableaux()->create([
            //             'user_id'        => $user->id,
            //             'nom'            => $tableau->nom,
            //             'budget_prevu'   => $tableau->budget_prevu,
            //             'description'    => $tableau->description,
            //             'nature'         => $tableau->nature,
            //             // autres colonnes ...
            //         ]);

            //         foreach ($tableau->variables as $variable) {
            //             $nouvelleVariable = $nouveauTableau->variables()->create([
            //                 'nom'           => $variable->nom,
            //                 'user_id'       => $user->id,
            //                 'budget_prevu'  => $variable->budget_prevu,
            //                 'calcule'       => $variable->calcule,
            //                 'type'          => $variable->type,
            //                 'categorie_id'  => $variable->categorie_id,
            //             ]);

            //             foreach ($variable->sousVariables as $sousVar) {
            //                 $nouvelleSousVar = $nouvelleVariable->sousVariables()->create([
            //                     'user_id'       => $user->id,
            //                     'nom'           => $sousVar->nom,
            //                     'budget_prevu'  => $sousVar->budget_prevu,
            //                     'calcule'       => $sousVar->calcule,
            //                     'categorie_id'  => $sousVar->categorie_id,
            //                 ]);
            //             }

            //             // regles de calcules restent à implementer.


                        
            //         }
            //     }
            // }
            if ($dernierMois) {
            foreach ($dernierMois->categories()->whereNull('parent_id')->get() as $categorieParent) {
                // 🔹 Duplication de la catégorie principale (niveau 1)
                $nouvelleCategorie = Categorie::create([
                    'user_id'            => $user->id,
                    'mois_comptable_id'  => $moisComptable->id,
                    'nom'                => $categorieParent->nom,
                    'budget_prevu'       => $categorieParent->budget_prevu,
                    'description'        => $categorieParent->description,
                    'nature'             => $categorieParent->nature,
                    'niveau'             => 1,
                    'calcule'            => $categorieParent->calcule,
                    'statut_objet'       => 'actif',
                ]);

                // 🔹 Duplication des enfants (niveau 2)
                foreach ($categorieParent->children as $enfant) {
                    $nouvelEnfant = Categorie::create([
                        'user_id'            => $user->id,
                        'mois_comptable_id'  => $moisComptable->id,
                        'parent_id'          => $nouvelleCategorie->id,
                        'nom'                => $enfant->nom,
                        'budget_prevu'       => $enfant->budget_prevu,
                        'description'        => $enfant->description,
                        'nature'             => $enfant->nature,
                        'niveau'             => 2,
                        'calcule'            => $enfant->calcule,
                        'statut_objet'       => 'actif',
                    ]);

                    // 🔹 Duplication des petits-enfants (niveau 3)
                    foreach ($enfant->children as $petitEnfant) {
                        Categorie::create([
                            'user_id'            => $user->id,
                            'mois_comptable_id'  => $moisComptable->id,
                            'parent_id'          => $nouvelEnfant->id,
                            'nom'                => $petitEnfant->nom,
                            'budget_prevu'       => $petitEnfant->budget_prevu,
                            'description'        => $petitEnfant->description,
                            'nature'             => $petitEnfant->nature,
                            'niveau'             => 3,
                            'calcule'            => $petitEnfant->calcule,
                            'statut_objet'       => 'actif',
                        ]);
                    }

                    // 🧮 TODO: copier les règles de calcul plus tard si applicable
                }
            }
        }
        }

        return $moisComptable;
    }
}
