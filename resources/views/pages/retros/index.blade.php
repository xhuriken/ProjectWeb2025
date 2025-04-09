<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Retrospectives') }}
            </span>
        </h1>
    </x-slot>

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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jkanban/dist/jkanban.min.css">

    <div class="p-4 border-b mb-4">
        <h2 class="font-bold mb-2">Créer une nouvelle Rétro</h2>
        <select id="cohort_id" class="border p-1">
            <option value="">-- Choisissez un cohort --</option>
            @foreach($cohorts as $cohort)
                <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
            @endforeach
        </select>

        <input type="text" id="retro_title" class="border p-1" placeholder="Titre de la rétro" />

        <button id="createRetroBtn" class="bg-blue-500 text-white px-3 py-1 rounded">
            Créer
        </button>
    </div>

    @foreach($retros as $retro)
        <div class="mb-8 border-b p-4">
            <!-- Name -->
            <h2 class="font-bold mb-2">
{{--                Retro #{{ $retro->id }} - --}}
                [{{ $retro->cohort->name ?? 'Sans Cohort' }}] - {{ $retro->title }}
            </h2>

            <!-- Kanban div -->
            <div id="kanban_{{ $retro->id }}" class="kanban-container" style="min-height: 300px;"></div>

            <!-- Btn Add COlomn element -->
            <div class="mt-2 flex gap-2">
                <button onclick="addColumn({{ $retro->id }})"
                        class="bg-green-600 text-white px-3 py-1 rounded">
                    + Colonne
                </button>
                <button onclick="addElement({{ $retro->id }})"
                        class="bg-gray-700 text-white px-3 py-1 rounded">
                    + Élément
                </button>
            </div>
        </div>
    @endforeach
    <script src="https://cdn.jsdelivr.net/npm/jkanban/dist/jkanban.min.js"></script>
    <script>
        window.allajax = "{{ route('retros.allAjaxData') }}";
        window.storeRetroUrl = "{{ route('retros.ajaxStore') }}";
    </script>
</x-app-layout>
