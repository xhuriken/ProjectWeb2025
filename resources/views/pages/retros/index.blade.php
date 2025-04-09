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


    @foreach($retros as $retro)
        <div class="mb-8 border-b p-4">
            <h2 class="font-bold mb-2">Retro #{{ $retro->id }} - {{ $retro->title }}</h2>
            <!-- On donne un ID unique basé sur l'ID de la rétro -->
            <div id="kanban_{{ $retro->id }}" class="kanban-container" style="min-height: 300px;"></div>
        </div>
    @endforeach


    <script src="https://cdn.jsdelivr.net/npm/jkanban/dist/jkanban.min.js"></script>
    <script>
        window.allajax = "{{route("retros.allAjaxData")}}";
    </script>
</x-app-layout>
