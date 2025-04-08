<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Promotions') }}
            </span>
        </h1>
    </x-slot>

    <!-- begin: grid -->
    <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5 items-stretch">
        <div class="lg:col-span-2">
            <div class="grid">
                <div class="card card-grid h-full min-w-full">
                    <div class="card-header">
                        <h3 class="card-title">Mes promotions</h3>
                    </div>
                    <div class="card-body">
                        <div data-datatable="true" data-datatable-page-size="5">
                            <div class="scrollable-x-auto">
                                <table class="table table-border" data-datatable-table="true">
                                    <thead>
                                    <tr>
                                        <th class="min-w-[280px]">
                                            <span class="sort asc">
                                                 <span class="sort-label">Promotion</span>
                                                 <span class="sort-icon"></span>
                                            </span>
                                        </th>
                                        <th class="min-w-[135px]">
                                            <span class="sort">
                                                <span class="sort-label">Année</span>
                                                <span class="sort-icon"></span>
                                            </span>
                                        </th>
                                        <th class="min-w-[135px]">
                                            <span class="sort">
                                                <span class="sort-label">Etudiants</span>
                                                <span class="sort-icon"></span>
                                            </span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cohorts as $cohort)
                                            <tr>
                                                <td>
                                                    <div class="flex flex-col gap-2">
                                                        <a class="leading-none font-medium text-sm text-gray-900 hover:text-primary"
                                                           href="{{ route('cohort.show', 1) }}">
                                                            {{$cohort->name}}
                                                        </a>
                                                        <span class="text-2sm text-gray-700 font-normal leading-3">
                                                        {{$cohort->description}}
                                                    </span>
                                                    </div>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($cohort->start_date)->format('Y') }} - {{ \Carbon\Carbon::parse($cohort->end_date)->format('Y') }}</td>

                                                <td>{{$cohort->usersCount()}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-gray-600 text-2sm font-medium">
                                <div class="flex items-center gap-2 order-2 md:order-1">
                                    Show
                                    <select class="select select-sm w-16" data-datatable-size="true" name="perpage"></select>
                                    per page
                                </div>
                                <div class="flex items-center gap-4 order-1 md:order-2">
                                    <span data-datatable-info="true"></span>
                                    <div class="pagination" data-datatable-pagination="true"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="lg:col-span-1">
            <div class="card h-full">
                <div class="card-header">
                    <h3 class="card-title">
                        Ajouter une promotion
                    </h3>
                </div>
                <div class="card-body flex flex-col gap-5">
                    <x-forms.input name="name" :label="__('Nom')" />

                    <x-forms.input name="description" :label="__('Description')" />

                    <x-forms.input type="date" name="year" :label="__('Début de l\'année')" placeholder="" />

                    <x-forms.input type="date" name="year" :label="__('Fin de l\'année')" placeholder="" />

                    <x-forms.primary-button>
                        {{ __('Valider') }}
                    </x-forms.primary-button>
                </div>
            </div>
        </div>
        <div class="card mt-5">
            <div class="card-header">
                <h3 class="card-title">Créer des groupes</h3>
            </div>
            <div class="card-body flex flex-col gap-5">
                <form id="generateGroupsForm">
                    <input type="hidden" name="cohort_id" value="{{ $cohort->id }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-forms.input name="nombre_groupes" label="Nombre de groupes" type="number" min="1" required/>
                        <x-forms.input name="nombre_par_groupe" label="Nombre d'élèves par groupe" type="number" min="1" required/>
                    </div>

                    <div class="flex justify-end mt-4">
                        <x-forms.primary-button type="submit">
                            Générer les groupes
                        </x-forms.primary-button>
                    </div>
                </form>
            </div>
        </div>
        <div id="generated-groups" class="mt-10 hidden">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-bold">Groupes générés :</h2>
                <div class="flex gap-4">
                    <button id="saveGroups" class="btn btn-primary">Garder</button>
                    <button id="regenerateGroups" class="btn btn-secondary">Régénérer</button>
                </div>
            </div>
            <div id="groupsTable" class="grid grid-cols-1 md:grid-cols-2 gap-6"></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let currentGroupsJson = null;

        document.getElementById('generateGroupsForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const cohortId = this.querySelector('input[name="cohort_id"]').value;
            const nombreGroupes = this.querySelector('input[name="nombre_groupes"]').value;
            const nombreParGroupe = this.querySelector('input[name="nombre_par_groupe"]').value;

            if (!nombreGroupes || !nombreParGroupe) {
                Swal.fire('Erreur', 'Veuillez remplir tous les champs.', 'error');
                return;
            }

            try {
                Swal.fire({
                    title: 'Génération des groupes en cours...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const response = await fetch(`/cohort/${cohortId}/generate-groups`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nombre_groupes: nombreGroupes,
                        nombre_par_groupe: nombreParGroupe
                    })
                });

                const result = await response.json();
                if (response.ok) {
                    let cleanJson = result.groups
                        .replace(/^```json\s*/i, '') // Supprime le début ```json
                        .replace(/^```\s*/i, '')     // Supprime aussi si jamais il reste ```
                        .replace(/```$/, '');        // Supprime la fin ``` si besoin

                    currentGroupsJson = JSON.parse(cleanJson);
                    renderGroups(currentGroupsJson);
                    Swal.close();
                }
                else {
                    Swal.fire('Erreur', result.message || 'Erreur inconnue.', 'error');
                }

            } catch (error) {
                console.error(error);
                Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
            }
        });

        function renderGroups(data) {
            const groupsTable = document.getElementById('groupsTable');
            groupsTable.innerHTML = ''; // Nettoie tout

            data.groupes.forEach(groupe => {
                const groupDiv = document.createElement('div');
                groupDiv.className = 'border p-4 rounded shadow';

                let html = `
            <h3 class="font-bold mb-2">Groupe ${groupe.numero} (Moyenne : ${groupe.moyenne_groupe.toFixed(2)})</h3>
            <table class="w-full text-sm text-left">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Note moyenne</th>
                    </tr>
                </thead>
                <tbody>
        `;

                groupe.etudiants.forEach(etudiant => {
                    html += `
                <tr>
                    <td>${etudiant.nom}</td>
                    <td>${etudiant.prenom}</td>
                    <td>${etudiant.average}</td>
                </tr>
            `;
                });

                html += `
                </tbody>
            </table>
        `;

                groupDiv.innerHTML = html;
                groupsTable.appendChild(groupDiv);
            });

            document.getElementById('generated-groups').classList.remove('hidden');
        }

        // Gérer le bouton Régénérer
        document.getElementById('regenerateGroups').addEventListener('click', () => {
            document.getElementById('generated-groups').classList.add('hidden');
            document.getElementById('groupsTable').innerHTML = '';
            currentGroupsJson = null;
        });

        // Gérer le bouton Garder
        document.getElementById('saveGroups').addEventListener('click', async () => {
            if (!currentGroupsJson) {
                Swal.fire('Erreur', 'Aucun groupe à sauvegarder.', 'error');
                return;
            }

            try {
                Swal.fire({
                    title: 'Sauvegarde en cours...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const response = await fetch('/cohort/save-groups', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        groups: currentGroupsJson
                    })
                });

                const result = await response.json();
                if (response.ok) {
                    Swal.fire('Succès', 'Groupes sauvegardés avec succès!', 'success');
                } else {
                    Swal.fire('Erreur', result.message || 'Erreur inconnue.', 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Erreur', 'Erreur pendant la sauvegarde.', 'error');
            }
        });
    </script>

    <!-- end: grid -->
</x-app-layout>
