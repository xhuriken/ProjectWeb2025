let currentGroupsJson = null;

document.addEventListener("DOMContentLoaded", (event) => {
    form = document.getElementById('generateGroupsForm');
    if(form == null) return;
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        //prevent default et la page scroll encore en haut
        //en plus c'est pas un <a> c'est un btn submit

        // const cohortId = this.querySelector('input[name="cohort_id"]').value;
        const groupNumber = this.querySelector('input[name="nombre_groupes"]').value;
        const usersNumber = this.querySelector('input[name="nombre_par_groupe"]').value;

        // let SweetAlert take care of empty fields
        if (!groupNumber || !usersNumber) {
            Swal.fire('Erreur', 'Veuillez remplir tous les champs.', 'error');
            return;
        }

        //loader SweetAlert
        Swal.fire({
            title: 'Génération des groupes en cours...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await fetch(window.routes.generateGroups, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrf
            },
            body: JSON.stringify({
                nombre_groupes: groupNumber,
                nombre_par_groupe: usersNumber
            })
        });

        const result = await response.json();
        if (response.ok) {
            let cleanJson = result.groups
                .replace(/^```json\s*/i, '')
                .replace(/^```/, '')
                .replace(/```$/, '');

            currentGroupsJson = JSON.parse(cleanJson);
            renderGroups(currentGroupsJson);
            Swal.close();
        } else {
            Swal.fire('Erreur', result.message || 'Erreur inconnue.', 'error');
        }

    });

    //
    // Display group generate (not saved) in temp table.
    //
    function renderGroups(data) {
        const groupsTable = document.getElementById('groupsTable');
        groupsTable.innerHTML = '';

        data.groups.forEach(group => {
            const groupDiv = document.createElement('div');
            groupDiv.className = 'border p-4 rounded shadow';

            //build the html

            let html = `
                        <h3 class="font-bold mb-2">Groupe ${group.number} (Moyenne : ${group.average_group.toFixed(2)})</h3>
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

            group.students.forEach(student => {
                html += `
                            <tr>
                                <td>${student.last_name}</td>
                                <td>${student.first_name}</td>
                                <td>${student.average}</td>
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

    //
    // Regenerate group
    //
    document.getElementById('regenerateGroups').addEventListener('click', async () => {
        //clear temp table
        document.getElementById('groupsTable').innerHTML = '';
        //clear current group json
        currentGroupsJson = null;
        ///clear
        document.getElementById('generated-groups').classList.add('hidden');

        const form = document.getElementById('generateGroupsForm');
        form.dispatchEvent(new Event('submit', { cancelable: true }));
    });



    //
    // Save group generate btn
    //
    document.getElementById('saveGroups').addEventListener('click', async () => {

        //If everithing fine, this is useless
        //Verify if we press the btn without group generate
        if (!currentGroupsJson) {
            Swal.fire('Erreur', 'Aucun groupe à sauvegarder.', 'error');
            return;
        }


        Swal.fire({
            title: 'Sauvegarde en cours...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        //save Fetch (route are set in cohort > show.blade.php)
        const response = await fetch(window.routes.saveGroups, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrf
            },
            body: JSON.stringify({
                groups: currentGroupsJson
            })
        });

        const result = await response.json();
        if (response.ok) {
            Swal.fire('Succès', 'Groupes sauvegardés avec succès !', 'success');
        } else {
            Swal.fire('Erreur', result.message || 'Erreur inconnue.', 'error');
        }
    });
});
