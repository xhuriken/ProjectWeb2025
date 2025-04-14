// Create a Kanban instance using jKanban and store all instances by retro ID.
const KanbanConstructor = window.jKanban;
const allKanbans = {};

// For onClick in blade
window.addColumn = addColumn;
window.addElement = addElement;

document.addEventListener('DOMContentLoaded', () => {
    if (!window.allajax) return;

    // Fetch all retros and initialize each Kanban.
    // For understand this, pls see https://github.com/riktar/jkanban
    fetch(window.allajax)
        .then(res => res.json())
        .then(retrosData => {
            retrosData.forEach(r => {
                const containerId = "kanban_" + r.retro_id;
                const containerEl = document.getElementById(containerId);
                if (containerEl) {
                    // Initialize jKanban for r retro
                    const kanbanInstance = new KanbanConstructor({
                        element: "#" + containerId,
                        gutter: '0px',
                        widthBoard: 'auto',
                        responsivePercentage: false,
                        dragItems: true,
                        dragBoards: false,
                        boards: r.boards.map(board => ({
                            id: board.id,
                            title: board.title,
                            item: []
                        })),
                        itemAddOptions: {
                            enabled: true,
                            content: '+ Ajouter une carte',
                            class: 'kanban-title-button w-full justify-start flex text-sm font-medium rounded-lg px-2 py-2 hover:bg-gray-300 duration-300',
                            footer: true
                        },
                        itemHandleOptions: {
                            enabled: false,
                            handleClass: "item_handle",
                            customCssHandler: "drag_handler",
                            customCssIconHandler: "drag_handler_icon",
                            customHandler: "<span class='item_handle'>+</span> %title%"
                        },

                        // Single click: edit card
                        click: function (el) {
                            const itemId = el.getAttribute('data-eid');
                            if (!itemId) return;
                            const elementDbId = itemId.replace('elem_', '');
                            // Get retro id from the container
                            let container = el.closest('.kanban-container');
                            while (container && !container.id.startsWith("kanban_")) {
                                container = container.parentElement;
                            }
                            if (!container) return;
                            const retroId = container.id.replace('kanban_', '');
                            editElement(el, elementDbId, retroId);
                        },
                        // Callback for item drop (drag and drop)
                        dropEl: function (el, target) {
                            let elementId = el.getAttribute('data-eid');
                            if (!elementId) return;
                            let pureId = parseInt(elementId.match(/\d+$/)[0]);
                            updateCardToDB(target.parentElement, pureId);
                        },
                        // Click on footer button to add a new card.
                        buttonClick: function (el, boardId) {
                            addElement(boardId, el);
                        },
                        propagationHandlers: []
                    });

                    allKanbans[r.retro_id] = kanbanInstance;

                    r.boards.forEach(board => {
                        board.item.forEach(card => {
                            kanbanInstance.addElement(board.id, {
                                id: 'elem_' + card.id,
                                title: card.title
                            });
                        });
                    });

                }
            });
        });

    // Global click listener for board deletion (admin only). (in future, professor who create this kanban)
    document.addEventListener('click', e => {
        if (window.userRole !== 'admin') return;
        if (e.target.classList.contains('ki-trash')) {
            const boardDiv = e.target.closest('.kanban-board');
            if (!boardDiv) return;
            const boardIdAttr = boardDiv.dataset.id; // e.g. "column_XX"
            const columnDbId  = boardIdAttr.replace('column_', '');

            // Use parent chain to get the correct container.
            const innerContainer = boardDiv.closest('.kanban-container');
            const containerDom = innerContainer.parentElement && innerContainer.parentElement.closest('.kanban-container');
            if (!containerDom) return;
            const retroId = containerDom.id.replace('kanban_', '');

            // Ajax call to delete the column.
            fetch(window.deleteColumnUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ column_id: columnDbId })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const kanban = allKanbans[retroId];
                        if (kanban) {
                            kanban.removeBoard(boardIdAttr);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Colonne supprimé !',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error || 'Unknown error'
                        });
                    }
                })
                .catch(err => console.error(err));
        }
    });

    // Listener for "Create a new Retro" button.
    const createBtn = document.getElementById("createRetroBtn");
    if (createBtn) {
        createBtn.addEventListener('click', e => {
            e.preventDefault();
            const cohortId = document.getElementById("cohort_id").value;
            const retroTitle = document.getElementById("retro_title").value.trim();
            if (!cohortId || !retroTitle) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Champs Manquant',
                    text: 'Veuillez compléter la promotion et le titre.'
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
                body: JSON.stringify({ cohort_id: cohortId, title: retroTitle })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Retro créé avec succès !',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error || 'Unknown error'
                        });
                    }
                })
                .catch(err => console.error(err));
        });
    }
});

// Edit a card (single click).
// IF THE TEXT IS EMPTY OR WITH ONLY SPACE DELETE THIS CARD
// devlog: it was so difficult to make dbl click event with click event before
function editElement(itemDom, elementDbId, retroId) {
    const oldText = itemDom.textContent.trim();
    Swal.fire({
        title: 'Modifier (Vide pour supprimer)',
        input: 'text',
        inputValue: oldText,
        showCancelButton: true,
        confirmButtonText: 'Appliquer',
        cancelButtonText: 'Annuler'
    }).then(res => {
        if (!res.isConfirmed) return;

        const newText = res.value.trim();

        if (newText === '') {
            // If is empty, delete the card
            Swal.fire({
                title: 'Supprimer cette carte?',
                text: 'Cette action est irréversible.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Supprimer',
                cancelButtonText: 'Annuler'
            }).then(confirmDelete => {
                if (confirmDelete.isConfirmed) {
                    fetch(window.deleteElementUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': window.csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            retro_id: retroId,
                            element_id: elementDbId
                        })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                const instance = allKanbans[retroId];
                                if (instance) {
                                    instance.removeElement(itemDom);
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.error || 'Unknown error'
                                });
                            }
                        })
                        .catch(err => console.error(err));
                }
            });

        } else {
            // Else we rename the card
            fetch(window.renameElementUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    retro_id: retroId,
                    element_id: elementDbId,
                    new_title: newText
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        itemDom.textContent = newText;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error (edit)',
                            text: data.error || 'Unknown error'
                        });
                    }
                })
                .catch(err => console.error(err));
        }
    });
}

// Add a new column to a retro.
function addColumn(retroId) {
    const kanban = allKanbans[retroId];
    if (!kanban) {
        console.error("No Kanban for retro ID:", retroId);
        return;
    }
    Swal.fire({
        title: 'Nouvelle Colonne',
        text: 'Entrez un nom :',
        input: 'text',
        showCancelButton: true,
        confirmButtonText: 'Créer',
        cancelButtonText: 'Annuler'
    }).then(res => {
        if (res.isConfirmed && res.value) {
            fetch(window.storeColumnUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ retro_id: retroId, title: res.value })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // const colDbId = data.column_id;
                        // const newBoardId = 'column_' + colDbId;
                        // kanban.addBoards([{ id: newBoardId, title: res.value, item: [] }]);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error || 'Unknown error'
                        });
                    }
                })
                .catch(err => console.error(err));
        }
    });
}

// Add a new card to a board.
function addElement(boardId, el) {
    const columnDbId = boardId.replace('column_', '');
    let foundRetroId = null;
    for (const rid in allKanbans) {
        const k = allKanbans[rid];
        const foundBoard = k.options.boards.find(b => b.id === boardId);
        if (foundBoard) {
            foundRetroId = rid;
            break;
        }
    }
    if (!foundRetroId) {
        console.warn("No retro found for boardId:", boardId);
        return;
    }
    Swal.fire({
        title: 'Nouvelle Carte',
        input: 'text',
        showCancelButton: true,
        confirmButtonText: 'Ajouter',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed && result.value) {
            fetch(window.storeElementUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    retro_id: foundRetroId,
                    column_id: columnDbId,
                    title: result.value
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // const instance = allKanbans[foundRetroId];
                        // instance.addElement(boardId, { id: 'elem_' + data.element_id, title: result.value });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error || 'Unknown error'
                        });
                    }
                })
                .catch(err => console.error(err));
        }
    });
}

// Update card movement in the DB.
function updateCardToDB(boardDiv, elementId) {
    const boardId = boardDiv.dataset.id;
    const colDbId = boardId.replace('column_', '');
    const innerContainer = boardDiv.closest('.kanban-container');
    const containerDom = innerContainer.parentElement && innerContainer.parentElement.closest('.kanban-container');
    if (!containerDom) {
        console.warn("No container found");
        return;
    }
    const retroId = containerDom.id.replace('kanban_', '');
    fetch(window.updateElementUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrf,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            retro_id: retroId,
            element_id: elementId,
            column_id: colDbId
        })
    })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Move error',
                    text: data.error || 'Unknown error'
                });
            }
        })
        .catch(err => console.error(err));
}




//PUSHER !!
const pusher = new Pusher('31eab710babcb78d914d', {
    cluster: 'eu'
});

const channel = pusher.subscribe('project-web');

channel.bind('add-card', function(data) {

    console.log('add-card event:', data);

    const card = data.card;

    const boardId = 'column_' + card.column_id;

    for (const retroId in allKanbans) {
        const kanbanInstance = allKanbans[retroId];

        const foundBoard = kanbanInstance.options.boards.find(b => b.id === boardId);

        if (foundBoard) {
            kanbanInstance.addElement(boardId, {
                id: 'elem_' + card.id,
                title: card.title
            });

            console.log('Carte ajouter mon frrr dans : ', boardId);
            break;
        }
    }

});

/*
  delete-card
  We remove the card from its board
*/
channel.bind('delete-card', function(data) {
    console.log('[Pusher] delete-card:', data);
    const card = data.card;
    if (!card) return;

    const boardId = 'column_' + card.column_id;
    for (const retroId in allKanbans) {
        const kanbanInstance = allKanbans[retroId];
        const foundBoard = kanbanInstance.options.boards.find(b => b.id === boardId);
        if (foundBoard) {
            const items = kanbanInstance.getBoardElements(boardId);
            for (const item of items) {
                if (item.getAttribute('data-eid') === 'elem_' + card.id) {
                    kanbanInstance.removeElement(item);
                    break;
                }
            }
        }
    }
});

/*
  rename-card
  We just update the card's text
*/
channel.bind('rename-card', function(data) {
    console.log('[Pusher] rename-card:', data);
    const card = data.card;
    if (!card) return;

    // We look in all boards, find the item, update its text
    for (const retroId in allKanbans) {
        const kanbanInstance = allKanbans[retroId];
        kanbanInstance.options.boards.forEach(board => {
            const boardId = board.id;
            const items = kanbanInstance.getBoardElements(boardId);
            for (const item of items) {
                if (item.getAttribute('data-eid') === 'elem_' + card.id) {
                    item.textContent = card.title;
                }
            }
        });
    }
});

/*
  move-card
  We remove the card from old board, then add it to new board
*/
channel.bind('move-card', function(data) {
    console.log('[Pusher] move-card:', data);
    const card = data.card;
    if (!card) return;

    for (const retroId in allKanbans) {
        const kanbanInstance = allKanbans[retroId];

        let foundItem = null;
        let oldBoardId = null;

        // Find the item in old board
        kanbanInstance.options.boards.forEach(board => {
            const boardId = board.id;
            const items = kanbanInstance.getBoardElements(boardId);
            for (const item of items) {
                if (item.getAttribute('data-eid') === 'elem_' + card.id) {
                    foundItem = item;
                    oldBoardId = boardId;
                }
            }
        });

        // Remove from old board and add to new one
        if (foundItem) {
            kanbanInstance.removeElement(foundItem);
            const newBoardId = 'column_' + card.column_id;
            kanbanInstance.addElement(newBoardId, {
                id: 'elem_' + card.id,
                title: foundItem.textContent.trim()
            });
            break;
        }
    }
});

/*
  add-column
  We create a new board in the Kanban
*/
channel.bind('add-column', function(data) {
    console.log('[Pusher] add-column:', data);
    const column = data.column;
    if (!column) return;

    // If needed, check retro id. For now, we add to all Kanbans
    for (const retroId in allKanbans) {
        const kanbanInstance = allKanbans[retroId];
        kanbanInstance.addBoards([{
            id: 'column_' + column.id,
            title: column.title,
            item: []
        }]);
    }
});

/*
  delete-column
  We remove the board from the Kanban
*/
channel.bind('delete-column', function(data) {
    console.log('[Pusher] delete-column:', data);
    const column = data.column;
    if (!column) return;

    for (const retroId in allKanbans) {
        const kanbanInstance = allKanbans[retroId];
        const boardId = 'column_' + column.id;
        kanbanInstance.removeBoard(boardId);
    }
});
