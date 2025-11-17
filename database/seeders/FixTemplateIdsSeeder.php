<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class FixTemplateIdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         Log::info("=== Début du FixTemplateIdsSeeder ===");

        // 1️⃣ Récupérer toutes les catégories template
        $templates = Categorie::where('is_template', true)->get();

        // Mapping rapide pour accès par nom + niveau + nature
        $indexTemplates = [];
        foreach ($templates as $tpl) {
            $key = $this->makeKey($tpl);
            $indexTemplates[$key] = $tpl;
        }

        // 2️⃣ Récupérer toutes les catégories utilisateur (non templates)
        $copies = Categorie::where('is_template', false)->get();

        $updateCount = 0;
        $failedCount  = 0;

        foreach ($copies as $categorie) {

            // Si le template_id existe déjà, on skip
            if (!empty($categorie->template_id)) {
                continue;
            }

            // 🔍 Trouver template correspondant par clé (nom, niveau, nature)
            $key = $this->makeKey($categorie);

            if (!isset($indexTemplates[$key])) {
                $failedCount++;
                Log::warning("Aucun template trouvé pour catégorie #{$categorie->id} ({$categorie->nom})");
                continue;
            }

            $template = $indexTemplates[$key];

            // ⚠ Vérification structurelle : s’il a un parent, vérifier que le parent correspond aussi
            if ($categorie->parent_id) {
                $copieParent = $categorie->parent;

                if ($copieParent) {
                    $parentKey = $this->makeKey($copieParent);

                    // Le parent doit correspondre lui aussi
                    if (!isset($indexTemplates[$parentKey])) {
                        Log::warning("Parent template introuvable pour #{$categorie->id} — parent #{$copieParent->id}");
                        continue;
                    }

                    // Le template parent doit être cohérent
                    $templateParent = $indexTemplates[$parentKey];

                    if ($templateParent->id !== $template->parent_id) {
                        Log::warning("Template parent incohérent pour #{$categorie->id}");
                        continue;
                    }
                }
            }

            // --- Mise à jour ---
            $categorie->template_id = $template->id;
            $categorie->save();

            $updateCount++;
        }

        Log::info("Fix terminé : $updateCount templates mis à jour, $failedCount erreurs.");
        Log::info("=== Fin du FixTemplateIdsSeeder ===");
    }

    /**
     * Génère une clé unique basée sur :
     * - nom
     * - niveau
     * - nature
     */
    private function makeKey($cat)
    {
        return strtolower(trim($cat->nom)) . '|' . $cat->niveau . '|' . $cat->nature;
    }
}
