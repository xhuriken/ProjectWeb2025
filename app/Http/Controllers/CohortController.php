<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Models\Group;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
class CohortController extends Controller
{
    /**
     * Display all available cohorts
     * @return Factory|View|Application|object
     */
    public function index() {
        return view('pages.cohorts.index', [
            'cohorts' => Cohort::all()
        ]);

    }


    /**
     * Display a specific cohort
     * @param Cohort $cohort
     * @return Application|Factory|object|View
     */
    public function show(Cohort $cohort) {

        return view('pages.cohorts.show', [
            'cohort' => $cohort,
            'users' => $cohort->users->filter()
        ]);
    }

    public function testGemini()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . config('services.gemini.api_key'), [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Explain how AI works']
                    ]
                ]
            ]
        ]);

        if ($response->failed()) {
            \Log::error('Gemini API error', ['response' => $response->body()]);
            return response()->json(['message' => 'API failed'], 500);
        }

        return response()->json($response->json());
    }





    public function generateGroups(Request $request, Cohort $cohort)
    {
        $request->validate([
            'nombre_groupes' => 'required|integer|min:1',
            'nombre_par_groupe' => 'required|integer|min:1',
        ]);

        // Récupérer les étudiants de la promotion
        $users = $cohort->users->map(function($user) {
            return [
                'id' => $user->id,
                'nom' => $user->last_name,
                'prenom' => $user->first_name,
                'average' => $user->average ?? rand(10, 20),
            ];
        });

        $oldGroups = Group::where('cohort_id', $cohort->id)->with('users')->get();

        $oldPairs = [];
        foreach ($oldGroups as $group) {
            $usersInGroup = $group->users;
            foreach ($usersInGroup as $i => $userA) {
                for ($j = $i + 1; $j < count($usersInGroup); $j++) {
                    $userB = $usersInGroup[$j];
                    $oldPairs[] = [
                        'id_1' => $userA->id,
                        'id_2' => $userB->id
                    ];
                }
            }
        }

        $oldPairsText = "";
        foreach ($oldPairs as $pair) {
            $oldPairsText .= "({$pair['id_1']}, {$pair['id_2']}), ";
        }
        $oldPairsText = rtrim($oldPairsText, ", ");

        $prompt = "
Vous êtes un moteur de calcul ultra strict.

Voici votre mission :

1. Vous recevez ci-dessous la liste UNIQUE et DÉFINITIVE des étudiants pour la promotion \"{$cohort->name}\".
   Chaque étudiant est représenté par :
   - \\\"id\\\" (identifiant entier unique)
   - \\\"nom\\\" (string)
   - \\\"prenom\\\" (string)
   - \\\"average\\\" (moyenne sur 20, float)

2. Vous devez OBLIGATOIREMENT utiliser tous ces étudiants, exactement tels qu'ils sont fournis, sans inventer, modifier ou ignorer aucun d'entre eux.

3. Votre objectif :
   - Former exactement {$request->nombre_groupes} groupes.
   - Chaque groupe doit contenir idéalement {$request->nombre_par_groupe} étudiants.
   - Si le nombre exact n'est pas atteignable, répartir équitablement les élèves supplémentaires en respectant l'équilibre général.

4. Répartition par note moyenne :
   - Répartir les étudiants de façon à ce que la moyenne des \\\"average\\\" des étudiants de chaque groupe soit la plus homogène possible entre tous les groupes.
   - Les écarts entre les moyennes de groupes doivent être minimisés.

5. Calcul :
   - La moyenne de chaque groupe (\\\"moyenne_groupe\\\") doit être calculée avec la formule exacte :
     (somme des averages du groupe) ÷ (nombre d'étudiants du groupe)
   - Le résultat doit être un nombre flottant avec 2 décimales de précision.

⚠️ Si une seule de ces règles est violée (ajout d'un élève inventé, mauvaise moyenne, élève manquant, etc.), le résultat sera invalide.

---

⚠️ Contrainte supplémentaire :

- Vous devez utiliser l'historique des anciennes paires fournies ci-dessous.
- Évitez au maximum que deux étudiants ayant déjà été dans le même groupe se retrouvent ensemble à nouveau.
- Si ce n'est pas totalement évitable, minimisez le nombre de répétitions au maximum.

Anciennes paires d'étudiants (id1, id2) :
$oldPairsText

---

Répondez uniquement avec un JSON strictement conforme à cette structure (sans aucun texte avant, après ou autour) :

{
  \\\"promotion\\\": \\\"{$cohort->name}\\\",
  \\\"groupes\\\": [
    {
      \\\"numero\\\": 1,
      \\\"moyenne_groupe\\\": <nombre flottant>,
      \\\"etudiants\\\": [
         { \\\"id\\\": <number>, \\\"nom\\\": \\\"<string>\\\", \\\"prenom\\\": \\\"<string>\\\", \\\"average\\\": <float> }
      ]
    },
    ...
  ]
}

Liste officielle des étudiants :

" . json_encode($users->toArray(), JSON_PRETTY_PRINT);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . config('services.gemini.api_key'), [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->failed()) {
            \Log::error('Gemini API error', ['response' => $response->body()]);
            return response()->json(['message' => 'Erreur avec l\'API Gemini'], 500);
        }

        $result = $response->json();

        return response()->json([
            'groups' => $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}'
        ]);
    }




    public function saveGroups(Request $request)
    {
        $validated = $request->validate([
            'groups' => 'required|array',
        ]);

        $groupsData = $validated['groups'];

        if (!isset($groupsData['promotion']) || !isset($groupsData['groupes'])) {
            return response()->json(['message' => 'Format de données invalide.'], 422);
        }

        $cohort = Cohort::where('name', $groupsData['promotion'])->first();
        if (!$cohort) {
            return response()->json(['message' => 'Cohorte introuvable.'], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($groupsData['groupes'] as $groupe) {
                $group = Group::create([
                    'cohort_id' => $cohort->id,
                    'numero' => $groupe['numero'],
                    'moyenne_groupe' => $groupe['moyenne_groupe'],
                ]);

                foreach ($groupe['etudiants'] as $etudiant) {
                    $group->users()->attach($etudiant['id']);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Groupes sauvegardés avec succès !']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la sauvegarde des groupes', ['exception' => $e]);
            return response()->json(['message' => 'Erreur lors de la sauvegarde.'], 500);
        }
    }

}
