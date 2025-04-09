<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">{{ $cohort->name }}</span>
        </h1>
    </x-slot>

    <!-- begin: grid -->
    <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5 items-stretch">
        <!-- Liste des étudiants -->
        <div class="lg:col-span-2">
            <div class="card card-grid h-full min-w-full">
                <div class="card-header">
                    <h3 class="card-title">Étudiants de la promotion</h3>
                </div>
                <div class="card-body">
                    <div data-datatable="true" data-datatable-page-size="30">
                        <div class="scrollable-x-auto">
                            <table class="table table-border" data-datatable-table="true">
                                <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Date de naissance</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->last_name }}</td>
                                        <td>{{ $user->first_name }}</td>
                                        <td>{{ $user->birth_date ?? 'Pas définie' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer flex justify-between text-gray-600 text-2sm font-medium">
                            <div class="flex items-center gap-2">
                                Show
                                <select class="select select-sm w-16" data-datatable-size="true" name="perpage"></select>
                                per page
                            </div>
                            <div class="flex items-center gap-4">
                                <span data-datatable-info="true"></span>
                                <div class="pagination" data-datatable-pagination="true"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{--THIBAUD : Je dois faire ça ?--}}
        <div class="lg:col-span-1">
            <div class="card h-full">
                <div class="card-header">
                    <h3 class="card-title">
                        Ajouter un étudiant à la promotion
                    </h3>
                </div>
                <div class="card-body flex flex-col gap-5">
                    <x-forms.dropdown name="user_id" :label="__('Etudiant')">
                        <option value="1">Etudiant 1</option>
                    </x-forms.dropdown>

                    <x-forms.primary-button>
                        {{ __('Valider') }}
                    </x-forms.primary-button>
                </div>
            </div>
        </div>


        <!-- Create Group -->
        <div class="lg:col-span-1">
            <div class="card h-full">
                <div class="card-header">
                    <h3 class="card-title">Créer des groupes</h3>
                </div>
                <div class="card-body flex flex-col gap-5">
                    <form id="generateGroupsForm">
                        @csrf
                        <input type="hidden" name="cohort_id" value="{{ $cohort->id }}">
                        <x-forms.input name="nombre_groupes" label="Nombre de groupes" type="number" min="1" />
                        <x-forms.input name="nombre_par_groupe" label="Nombre d'élèves par groupe" type="number" min="1" />
                        <x-forms.primary-button type="submit" class="w-full">
                            Générer les groupes
                        </x-forms.primary-button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Generate Group -->
        <div class="col-span-3 mt-10 hidden" id="generated-groups">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-bold">Groupes générés</h2>
                <div class="flex gap-4">
                    <button id="saveGroups" class="btn btn-primary">Garder</button>
                    <button id="regenerateGroups" class="btn btn-secondary">Régénérer</button>
                </div>
            </div>
            <div id="groupsTable" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            </div>
        </div>


    </div>


    <!-- Already Exist Group (outside the grid for full width)-->
    <div class="mt-10 w-full">
        <h2 class="text-xl font-bold mb-4">Groupes existants de cette promotion :</h2>

        @forelse($cohort->groups->groupBy('generation') as $generation => $groups)
            <div class="card mb-8">
                <div class="card-header flex justify-between items-center">
                    <h3 class="text-lg font-bold">Génération {{ $generation }}</h3>
                    <form method="POST" action="{{ route('groups.deleteGeneration', ['cohort' => $cohort->id, 'generation' => $generation]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Confirmer la suppression de cette génération ?')" class="btn btn-danger">
                            Supprimer la génération
                        </button>
                    </form>
                </div>
                <div class="card-body overflow-x-auto">
                    <table class="table table-border w-full">
                        <thead>
                        <tr>
                            <th>Groupe</th>
                            <th>Moyenne</th>
                            <th>Étudiants</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($groups as $group)
                            <tr>
                                <td>Groupe {{ $group->number }}</td>
                                <td>{{ number_format($group->average_group, 2) }}</td>
                                <td>
                                    <ul class="list-disc list-inside">
                                        @foreach($group->users as $user)
                                            <li>{{ $user->last_name }} {{ $user->first_name }} ({{ $user->average ?? '-' }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500">Aucun groupe enregistré pour cette promotion.</div>
        @endforelse
    </div>
    <!-- end: grid -->

    <!-- Scripts -->
    <script>
        window.routes = {
            generateGroups: '/cohort/{{ $cohort->id }}/generate-groups',
            saveGroups: '/cohort/save-groups'
        };
        window.csrf = '{{ csrf_token() }}';
    </script>


</x-app-layout>
