// create kanban constructor (i'm not sure why, but i had an error without)
const KanbanConstructor = window.jKanban;

// all kaban instance
const allKanbans = {};

// for onClick() in html;
window.addColumn  = addColumn;
window.addElement = addElement;

document.addEventListener('DOMContentLoaded', () => {
    if (!window.allajax) {
        //if whe are in another page
        return;
    }

    // Fetch all retros
    fetch(window.allajax)
        .then(res => res.json())
        .then(retrosData => {
            // for all retro
            retrosData.forEach(r => {
                // create container kanban_id with retro id
                const containerId = "kanban_" + r.retro_id;
                const containerEl = document.getElementById(containerId);

                // if he exist (all container are charged and create before DOMContentLoaded in blade)
                if (containerEl) {
                    const kanbanInstance = new KanbanConstructor({
                        element: "#" + containerId,
                        boards: r.boards || [],

                        // Add + at the top of the board (see Jkanban doc)
                        itemAddOptions: {
                            enabled: true,
                            content: '+',
                            class: 'kanban-title-button btn btn-default btn-xs',
                            footer: false
                        },

                        // callback for + btn pressed
                        buttonClick: function(el, boardId) {
                            // Extract the DB column ID from the board ID
                            const columnDbId = boardId.replace('column_', '');

                            // Ask text for this element
                            Swal.fire({
                                title: 'Nouvel élément',
                                input: 'text',
                                showCancelButton: true,
                                confirmButtonText: 'Ajouter',
                                cancelButtonText: 'Annuler'
                            }).then(result => {
                                if (result.isConfirmed && result.value) {
                                    // Send it
                                    fetch(window.storeElementUrl, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': window.csrf,
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            retro_id: r.retro_id,
                                            column_id: columnDbId,
                                            title: result.value
                                        })
                                    })
                                        .then(resp => resp.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Add the item to the board visually
                                                kanbanInstance.addElement(boardId, {
                                                    id: 'elem_' + data.element_id,
                                                    title: result.value
                                                });

                                            } else {
                                                // error :/
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Erreur',
                                                    text: data.error || 'inconnue'
                                                });
                                            }
                                        })
                                        .catch(err => console.error(err));
                                }
                            });
                        },

                        // Callback for drag and drop !
                        dropEl: function(el, target, source, sibling){
                            // item ID is stored in data-eid, like "elem_12"
                            const itemIdAttr = el.getAttribute('data-eid');
                            // security check (but now it working well, this is useless)
                            if(!itemIdAttr){
                                console.warn("Cannot find item ID in data-eid");
                                return;
                            }
                            const elementDbId = itemIdAttr.replace('elem_', '');

                            // Identify the new board column
                            const boardDiv = target.parentElement;
                            const boardId  = boardDiv.dataset.id;
                            const colDbId  = boardId.replace('column_', '');

                            // Save the new column for this item in the DB
                            fetch(window.updateElementUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': window.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    retro_id: r.retro_id,
                                    element_id: elementDbId,
                                    column_id: colDbId
                                })
                            })
                                .then(resp => resp.json())
                                .then(data => {
                                    // If something went wrong, display an error
                                    if(!data.success){
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Erreur de déplacement',
                                            text: data.error || 'inconnue'
                                        });
                                    }
                                })
                                .catch(err => console.error("Erreur updateElementColumn:", err));
                        }
                    });

                    // Store this Kanban instance in the dictionary
                    allKanbans[r.retro_id] = kanbanInstance;
                }
            });
        });

    // Listen for clicks on the "Create a new Retro" button
    const createBtn = document.getElementById("createRetroBtn");
    if (createBtn) {
        createBtn.addEventListener('click', () => {
            const cohortId   = document.getElementById("cohort_id").value;
            const retroTitle = document.getElementById("retro_title").value.trim();

            if(!cohortId || !retroTitle){
                Swal.fire({
                    icon: 'warning',
                    title: 'Champs manquants',
                    text: 'Merci de remplir le Cohort et le titre'
                });
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
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Rétro créée avec succès !',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            //TODO:
                            // Charge it in DOM
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: data.error || 'inconnue'
                        });
                    }
                })
                .catch(err => console.error(err));
        });
    }
});

/**
 *  Add a new column for a specific retro
 */
function addColumn(retroId) {
    const kanban = allKanbans[retroId];
    if (!kanban) {
        console.error("Kanban not found for retro ID=", retroId);
        return;
    }

    Swal.fire({
        title: 'Nouvelle colonne',
        text: 'Entrez le nom de la colonne',
        input: 'text',
        showCancelButton: true,
        confirmButtonText: 'Créer',
        cancelButtonText: 'Annuler'
    }).then((res) => {
        if (res.isConfirmed && res.value) {
            fetch(window.storeColumnUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    retro_id: retroId,
                    title: res.value
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const colDbId = data.column_id;
                        const newBoardId = 'column_' + colDbId;

                        kanban.addBoards([{
                            id: newBoardId,
                            title: res.value,
                            item: []
                        }]);

                        // Swal.fire({
                        //     icon: 'success',
                        //     title: 'Colonne ajoutée !',
                        //     timer: 2000,
                        //     showConfirmButton: false
                        // });
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: data.error || 'inconnue'
                        });
                    }
                })
                .catch(err => console.error(err));
        }
    });
}

/**
 * Add a new element to a specific retro (inused now, i made it with callback + brn)
 * @param retroId
 */
function addElement(retroId) {
    const kanban = allKanbans[retroId];
    if (!kanban) {
        console.error("Kanban not found for retro ID=", retroId);
        return;
    }

    // Get the current boards to list them in a select
    const boardsInfo = kanban.options.boards || [];
    const inputOptions = {};
    boardsInfo.forEach(b => {
        inputOptions[b.id] = b.title;
    });

    // Ask the user which board (column) they want
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
            const chosenBoardId = colResult.value;
            const colDbId = chosenBoardId.replace('column_', '');

            // Then ask for the item title
            Swal.fire({
                title: 'Titre de l’élément',
                input: 'text',
                showCancelButton: true,
                confirmButtonText: 'Ajouter',
                cancelButtonText: 'Annuler'
            }).then(itemResult => {
                if (itemResult.isConfirmed && itemResult.value) {
                    //save this item
                    fetch(window.storeElementUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': window.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            retro_id: retroId,
                            column_id: colDbId,
                            title: itemResult.value
                        })
                    })
                        .then(r => r.json())
                        .then(data => {
                            //Add new element
                            if (data.success) {
                                kanban.addElement(chosenBoardId, {
                                    id: 'elem_' + data.element_id,
                                    title: itemResult.value
                                });
                                //no alert here (it was annoying)
                            } else {
                                //if something gone wrong
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erreur',
                                    text: data.error || 'inconnue'
                                });
                            }
                        })
                        .catch(err => console.error(err));
                }
            });
        }
    });
}
