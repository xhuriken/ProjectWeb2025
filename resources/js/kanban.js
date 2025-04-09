const KanbanConstructor = window.jKanban;
const allKanbans = {};

window.addColumn  = addColumn;
window.addElement = addElement;

document.addEventListener('DOMContentLoaded', () => {

    if (!window.allajax) {
        console.warn('window.allajax (URL AJAX) est introuvable.');
        return;
    }

    fetch(window.allajax)
        .then(response => response.json())
        .then(retrosData => {
            retrosData.forEach(r => {
                const containerId = "kanban_" + r.retro_id;
                const containerEl = document.getElementById(containerId);

                if (containerEl) {
                    const kanbanInstance = new KanbanConstructor({
                        element: "#" + containerId,
                        boards: r.boards || []
                    });

                    allKanbans[r.retro_id] = kanbanInstance;
                } else {
                    console.warn("Pas de conteneur pour rétro ID=", r.retro_id);
                }
            });
        })
        .catch(err => {
            console.error("Erreur lors de l'appel AJAX Kanban:", err);
        });



    const createBtn = document.getElementById("createRetroBtn");
    if(!createBtn) return;

    createBtn.addEventListener('click', function() {
        const cohortId = document.getElementById("cohort_id").value;
        const retroTitle = document.getElementById("retro_title").value.trim();

        if(!cohortId || !retroTitle) {
            alert("Merci de remplir le Cohort et le titre");
            return;
        }

        fetch(window.storeRetroUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cohort_id: cohortId,
                title: retroTitle
            })
        })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert("Rétro créée avec succès !");
                    location.reload();
                } else {
                    alert("Erreur: " + (data.error ?? "inconnue"));
                }
            })
            .catch(err => {
                console.error("Erreur lors de la création AJAX:", err);
                alert("Une erreur est survenue en AJAX");
            });
    });
});

/**
 *  addColumn to specific retro
 */
function addColumn(retroId) {
    const kanban = allKanbans[retroId];
    if (!kanban) {
        console.error("Kanban introuvable pour la rétro ID=", retroId);
        return;
    }

    Swal.fire({
        title: 'Nouvelle colonne',
        text: 'Entrez le nom de la colonne',
        input: 'text',
        showCancelButton: true,
        confirmButtonText: 'Créer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const newColId = "column_" + Date.now();

            kanban.addBoards([
                {
                    id: newColId,
                    title: result.value,
                    item: []
                }
            ]);
        }
    });
}


/**
 * Add element to specific retro
 * @param retroId
 */
function addElement(retroId) {
    const kanban = allKanbans[retroId];
    if (!kanban) {
        console.error("Kanban introuvable pour la rétro ID=", retroId);
        return;
    }

    const boardsInfo = kanban.options.boards || [];


    const inputOptions = {};
    boardsInfo.forEach(board => {
        inputOptions[board.id] = board.title;
    });

    Swal.fire({
        title: 'Nouvel élément',
        text: 'Choisissez la colonne :',
        input: 'select',
        inputOptions: inputOptions,
        inputPlaceholder: 'Sélectionnez une colonne',
        showCancelButton: true,
        confirmButtonText: 'Suivant',
        cancelButtonText: 'Annuler'
    }).then(colResult => {
        if (colResult.isConfirmed && colResult.value) {
            const chosenColId = colResult.value;

            Swal.fire({
                title: 'Titre de l’élément',
                input: 'text',
                showCancelButton: true,
                confirmButtonText: 'Ajouter',
                cancelButtonText: 'Annuler'
            }).then(itemResult => {
                if (itemResult.isConfirmed && itemResult.value) {
                    kanban.addElement(chosenColId, {
                        title: itemResult.value
                    });
                }
            });
        }
    });
}
