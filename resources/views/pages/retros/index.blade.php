<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Retrospectives') }}
            </span>
        </h1>
    </x-slot>
    <link rel="stylesheet" href="{{asset('jkanban/dist/jkanban.css')}}">

    {{--    il faut un conteneur qui fasse toute la taille de l'écran avec dedans des bouton ou form--}}
    {{--    Ajouté une retro (on dois l'associer a un cohort specifique paris ceux existant) --}}
    {{--    il va créer un conteneur kanban qui s'initialiseras automatiquement--}}
    {{--    Il faut stoqué tout les kanban, par cohort, et y stoqué les contenant (en cours fini etc..) et les élément dedans (faire ....)--}}
    {{--    Je pense qu'il faudrait le faire dynamiquement, plusieur élément a l'infini et plusieur contenant a l'infini (si jamais)--}}
    {{--    Table à créer : --}}
    {{--    retros (id, cohort_id)--}}
    {{--    retros_colomn (id, retros_id, content)--}}
    {{--    retros_element (id, retros_id, content)--}}
    {{--    Plus tard : une liste de tout les kanban existant--}}

{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jkanban/dist/jkanban.min.css">--}}
{{--    Pas de policies :/--}}
    @if(auth()->user()->school()->pivot->role != 'student')
    <div class="p-4 border mb-4 space-y-3 max-w-96">
        <h2 class="text-lg font-semibold">
            Créer une nouvelle Rétro
        </h2>

        <div class="flex-col space-x-2 flex gap-1.25">
            <select id="cohort_id"
                    class="border border-gray-300 p-2 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">-- Choisissez une promotion --</option>
                @foreach($cohorts as $cohort)
                    <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                @endforeach
            </select>

            <input type="text"
                   id="retro_title"
                   class="border border-gray-300 p-2 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 flex-1"
                   placeholder="Titre de la rétro" />

            <button id="createRetroBtn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded focus:outline-none">
                Créer
            </button>
        </div>
    </div>
    @endif
    @forelse($retros as $retro)
        <div class="mb-8 border-b p-4">
            <h2 class="font-bold mb-2">
                [{{ $retro->cohort->name ?? 'Sans Cohort' }}] - {{ $retro->title }}
            </h2>

            <div id="kanban_{{ $retro->id }}" class="kanban-container" data-retro="{{ $retro->id }}" style="min-height: 300px;"></div>

            <div class="mt-2 flex gap-2">
                <button onclick="addColumn({{ $retro->id }})"
                        class="bg-green-600 text-white px-3 py-1 rounded">
                    + Colonne
                </button>
            </div>
        </div>
    @empty
        <p class="text-gray-500 italic">Aucune rétro disponible pour le moment.</p>
    @endforelse


    <script src="{{asset("jkanban/dist/jkanban.js")}}"></script>
    <script>
        window.userRole = "{{auth()->user()->school()->pivot->role}}";
        window.allajax             = "{{ route('retros.allAjaxData')             }}";
        window.storeRetroUrl       = "{{ route('retros.ajaxStore')               }}";
        window.storeColumnUrl      = "{{ route('retros.ajaxStoreColumn')         }}";
        window.storeElementUrl     = "{{ route('retros.ajaxStoreElement')        }}";
        window.renameElementUrl    = "{{ route('retros.ajaxRenameElement')       }}";
        window.updateElementUrl    = "{{ route('retros.ajaxUpdateElementColumn') }}";
        window.deleteColumnUrl     = "{{ route('retros.ajaxDeleteColumn')        }}";
        window.deleteElementUrl    = "{{ route('retros.ajaxDeleteElement')       }}";
    </script>
</x-app-layout>
