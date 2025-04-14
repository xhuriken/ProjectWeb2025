// Create a Kanban instance using jKanban and store all instances by retro ID.
const KanbanConstructor = window.jKanban;
const allKanbans = {};

// for dbl click and single click
// make timer

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
                        boards: r.boards || [],
                        itemAddOptions: {
                            enabled: true,
                            content: '+ Add card',
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

                    // // Double click: delete card.
                    // containerEl.addEventListener('dblclick', ev => {
                    //     const itemDom = ev.target.closest('.kanban-item');
                    //     if (!itemDom) return;
                    //     const itemId = itemDom.getAttribute('data-eid');
                    //     if (!itemId) return;
                    //     const elementDbId = itemId.replace('elem_', '');
                    //     deleteElement(itemDom, elementDbId, r.retro_id);
                    // });
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
                            title: 'Column deleted',
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
                    title: 'Missing fields',
                    text: 'Please fill in Cohort and Title'
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
                            title: 'Retro created successfully!',
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
        title: 'Edit card',
        input: 'text',
        inputValue: oldText,
        showCancelButton: true,
        confirmButtonText: 'Save',
        cancelButtonText: 'Cancel'
    }).then(res => {
        if (!res.isConfirmed) return;

        const newText = res.value.trim();

        if (newText === '') {
            // If is empty, delete the card
            Swal.fire({
                title: 'Delete this card?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
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
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Card deleted',
                                    timer: 1500,
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Card updated',
                            timer: 1500,
                            showConfirmButton: false
                        });
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
        title: 'New column',
        text: 'Enter column name',
        input: 'text',
        showCancelButton: true,
        confirmButtonText: 'Create',
        cancelButtonText: 'Cancel'
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
                        const colDbId = data.column_id;
                        const newBoardId = 'column_' + colDbId;
                        kanban.addBoards([{ id: newBoardId, title: res.value, item: [] }]);
                        Swal.fire({
                            icon: 'success',
                            title: 'Column added',
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
        title: 'New card',
        input: 'text',
        showCancelButton: true,
        confirmButtonText: 'Add',
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
                        const instance = allKanbans[foundRetroId];
                        instance.addElement(boardId, { id: 'elem_' + data.element_id, title: result.value });
                        Swal.fire({
                            icon: 'success',
                            title: 'Card added',
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
