<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Models\Retro;
use App\Models\RetrosColumn;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class RetroController extends Controller
{
    /**
     * Display the page
     *
     * @return Factory|View|Application|object
     */
    public function index() {
        $retros = Retro::with('cohort')->get();

        $cohorts = Cohort::all();

        return view('pages.retros.index', compact('retros', 'cohorts'));
    }


    public function allRetrosAjaxData()
    {
        $retros = Retro::with(['cohort', 'columns.elements'])->get();
        $formattedRetros = $retros->map(function($retro){
            $boards = $retro->columns->map(function($col){
                $items = $col->elements->map(function($elem){
                    return [
                        'id'    => $elem->id,
                        'title' => $elem->title
                    ];
                });
                return [
                    'id'    => 'column_' . $col->id,
                    'title' => $col->title,
                    'item'  => $items->toArray()
                ];
            });

            return [
                'retro_id'    => $retro->id,
                'cohort_name' => $retro->cohort ? $retro->cohort->name : '',
                'retro_title' => $retro->title,
                'boards'      => $boards->toArray()
            ];
        });

        return response()->json($formattedRetros);
    }


    /**
     * Store new retro
     */
    public function ajaxStore(Request $request)
    {
        $request->validate([
            'cohort_id' => 'required|exists:cohorts,id',
            'title'     => 'required|string|max:255'
        ]);

        $retro = Retro::create([
            'cohort_id' => $request->cohort_id,
            'title'     => $request->title,
        ]);

        $defaultColumns = ['À faire', 'En cours', 'Terminé'];
        foreach ($defaultColumns as $colTitle) {
            RetrosColumn::create([
                'retro_id' => $retro->id,
                'title'    => $colTitle
            ]);
        }

        return response()->json([
            'success' => true,
            'retro_id' => $retro->id
        ]);
    }

    //need ajax for store new colomn and element !
}
