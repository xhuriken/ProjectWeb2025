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

    /**
     * this is testing gemini function. If it make error when '/test-gemini' is load : Gemini Didnt work
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Http\Client\ConnectionException
     */
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

        //Get all user (étudiant) in this cohort
        $users = $cohort->users->map(function($user) {
            return [
                'id'         => $user->id,
                'last_name'  => $user->last_name,
                'first_name' => $user->first_name,
                'average'    => $user->average ?? rand(10, 20),
            ];
        });

        //Get all groups where are this cohort
        $oldGroups = Group::where('cohort_id', $cohort->id)->with('users')->get();

        //create pairs of user
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

        //transform to string for the prompt
        $oldPairsText = "";
        foreach ($oldPairs as $pair) {
            $oldPairsText .= "({$pair['id_1']}, {$pair['id_2']}), ";
        }
        $oldPairsText = rtrim($oldPairsText, ", ");

        //Make the prompt
        $prompt = "
        Vous êtes un moteur de calcul ultra strict.

        Voici votre mission :

        1. Vous recevez ci-dessous la liste UNIQUE et DÉFINITIVE des étudiants pour la promotion \"{$cohort->name}\".
           Chaque étudiant est représenté par :
           - \\\"id\\\" (identifiant entier unique)
           - \\\"last_name\\\" (string)
           - \\\"first_name\\\" (string)
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
           - La moyenne de chaque groupe (\\\"average_group\\\") doit être calculée avec la formule exacte :
             (somme des averages du groupe) ÷ (nombre d'étudiants du groupe)
           - Le résultat doit être un nombre flottant avec 2 décimales de précision.

        Si une seule de ces règles est violée (ajout d'un élève inventé, mauvaise moyenne, élève manquant, etc.), le résultat sera invalide.

        ---

        Contrainte supplémentaire :

        - Vous devez utiliser l'historique des anciennes paires fournies ci-dessous.
        - Évitez au maximum que deux étudiants ayant déjà été dans le même groupe se retrouvent ensemble à nouveau.
        - Si ce n'est pas totalement évitable, minimisez le nombre de répétitions au maximum.

        Anciennes paires d'étudiants (id1, id2) :
        $oldPairsText

        ---

        Répondez uniquement avec un JSON strictement conforme à cette structure (sans aucun texte avant, après ou autour) :

        {
            \\\"promotion\\\": \\\"{$cohort->name}\\\",
              \\\"groups\\\": [
                {
                  \\\"number\\\": 1,
                  \\\"average_group\\\": <floating number>,
                  \\\"students\\\": [
                     { \\\"id\\\": <number>, \\\"last_name\\\": \\\"<string>\\\", \\\"first_name\\\": \\\"<string>\\\", \\\"average\\\": <float> }
                  ]
                },
            ...
          ]
        }

        Liste officielle des étudiants :

        " . json_encode($users->toArray(), JSON_PRETTY_PRINT);
        // add all users for the prompt

        //API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . config('services.gemini.api_key'), [
            'contents' => [
                [
                    //add prompt in the request
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);


        //Now, this is usefull (because gemini api work)
        if ($response->failed()) {
            \Log::error('Gemini API error', ['response' => $response->body()]);
            return response()->json(['message' => 'Erreur avec l\'API Gemini'], 500);
        }

        //Stock and send result !
        $result = $response->json();

        return response()->json([
            'groups' => $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}'
        ]);
    }

    public function saveGroups(Request $request)
    {
        //Get json in 'groups' and decode it
        $groupsData = $request->input('groups');
        if (is_string($groupsData)) {
            $groupsData = json_decode($groupsData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['message' => 'JSON invalide.'], 422);
            }
        }

        // Verify json (if all data are here)
        if (!isset($groupsData['promotion'], $groupsData['groups'])) {
            return response()->json(['message' => 'Format de données invalide.'], 422);
        }

        //Get corhort by name (I THINK PROMOTION MUST BE UNIQUE)
        $cohort = Cohort::where('name', $groupsData['promotion'])->first();
        if (!$cohort) {
            return response()->json(['message' => 'Cohorte introuvable.'], 404);
        }

        //Increase generation
        $newGeneration = ($cohort->groups()->max('generation') ?? 0) + 1;

        DB::beginTransaction();
        try {
            foreach ($groupsData['groups'] as $groupData) {
                $group = Group::create([
                    'cohort_id'      => $cohort->id,
                    'generation'     => $newGeneration,
                    'number'         => $groupData['number'],
                    'average_group' => $groupData['average_group'],
                ]);

                foreach ($groupData['students'] as $student) {
                    $group->users()->attach($student['id']);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Groupes sauvegardés avec succès !']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la sauvegarde des groupes', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Erreur lors de la sauvegarde.'], 500);
        }
    }


    /**
     * Delete group's generation
     * @param Cohort $cohort
     * @param $generation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteGeneration(Cohort $cohort, $generation)
    {
        DB::beginTransaction();

        try {
            $groups = $cohort->groups()->where('generation', $generation)->get();

            foreach ($groups as $group) {
                $group->users()->detach();
                $group->delete();
            }

            DB::commit();

            return back()->with('success', 'Génération ' . $generation . ' supprimée.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la suppression de la génération', ['exception' => $e]);
            return back()->with('error', 'Erreur lors de la suppression de la génération.');
        }
    }

}
