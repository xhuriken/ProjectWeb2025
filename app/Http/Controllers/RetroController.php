<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Models\Retro;
use App\Models\RetrosColumn;
use App\Models\RetrosElement;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

use App\Events\AddCardEvent;
use App\Events\DeleteCardEvent;
use App\Events\MoveCardEvent;
use App\Events\RenameCardEvent;
use App\Events\AddColumnEvent;
use App\Events\DeleteColumnEvent;


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

    /**
     * Get all Retros and return them (for displaying it in blade)
     * @return \Illuminate\Http\JsonResponse
     */
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
     * Store new retro (with default data)
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

        $defaultColumns = ["J'ai aimé", "Je n'ai pas aimé", "A améliorer", "Inès", "J'ai appris", "Autre.."];
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

    /**
     * Store a new retro element in colomn
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxStoreElement(Request $request)
    {
        $request->validate([
            'retro_id' => 'required|exists:retros,id',
            'column_id' => 'required|exists:retros_columns,id',
            'title' => 'required|string|max:255'
        ]);

        $elem = RetrosElement::create([
            'retro_id' => $request->retro_id,
            'retros_column_id' => $request->column_id,
            'title' => $request->title
        ]);

        // Pusher
        event(new AddCardEvent([
            'id' => $elem->id,
            'column_id' => $request->column_id,
            'title' => $request->title
        ]));
        \Log::info('Event AddCardEvent triggered');

        return response()->json([
            'success' => true,
            'element_id' => $elem->id
        ]);
    }

    /**
     * Store a new colomn in retros
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxStoreColumn(Request $request)
    {
        $request->validate([
            'retro_id' => 'required|exists:retros,id',
            'title'    => 'required|string|max:255'
        ]);

        $col = RetrosColumn::create([
            'retro_id' => $request->retro_id,
            'title'    => $request->title,
        ]);

        event(new AddColumnEvent([
            'id'    => $col->id,
            'title' => $col->title
        ]));

        return response()->json([
            'success'  => true,
            'column_id'=> $col->id
        ]);
    }

    /**
     * Update an element in a new column (for drag and drop)
     */
    public function ajaxUpdateElementColumn(Request $request)
    {
        $request->validate([
            'retro_id'   => 'required|exists:retros,id',
            'element_id' => 'required|exists:retros_elements,id',
            'column_id'  => 'required|exists:retros_columns,id',
        ]);

        $elem = RetrosElement::where('retro_id', $request->retro_id)
            ->findOrFail($request->element_id);

        $elem->retros_column_id = $request->column_id;
        $elem->save();

        event(new MoveCardEvent([
            'id'        => $elem->id,
            'column_id' => $request->column_id
        ]));

        return response()->json([
            'success' => true
        ]);
    }


    public function ajaxDeleteColumn(Request $request)
    {
        $request->validate([
            'column_id' => 'required|exists:retros_columns,id',
        ]);

        RetrosColumn::destroy($request->column_id);

        event(new DeleteColumnEvent([
            'id' => $request->column_id
        ]));
        return response()->json([
            'success' => true
        ]);
    }

    public function ajaxDeleteElement(Request $request)
    {
        $request->validate([
            'element_id' => 'required|exists:retros_elements,id',
        ]);


        $elem = RetrosElement::findOrFail($request->element_id);
        $columnId = $elem->retros_column_id;

        $elem->delete();

        event(new DeleteCardEvent([
            'id'        => $request->element_id,
            'column_id' => $columnId
        ]));

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Rename an element (title) in the DB
     */
    public function ajaxRenameElement(Request $request)
    {
        $request->validate([
            'retro_id'   => 'required|exists:retros,id',
            'element_id' => 'required|exists:retros_elements,id',
            'new_title'  => 'required|string|max:255'
        ]);

        $elem = RetrosElement::findOrFail($request->element_id);

        $elem->title = $request->new_title;
        $elem->save();

        event(new RenameCardEvent([
            'id'    => $request->element_id,
            'title' => $request->new_title
        ]));

        return response()->json([
            'success' => true
        ]);
    }


}
