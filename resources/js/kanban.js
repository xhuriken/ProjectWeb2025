document.addEventListener("DOMContentLoaded", function() {

    fetch(window.allajax)
        .then(response => response.json())
        .then(retrosData => {
            retrosData.forEach(r => {
                let containerId = "kanban_" + r.retro_id;
                let containerDiv = document.getElementById(containerId);

                if (containerDiv) {
                    new jKanban({
                        element: "#" + containerId,
                        boards: r.boards
                    });
                } else {
                    console.warn("Pas de div correspondant à la rétro ID ", r.retro_id);
                }
            });
        })
        .catch(error => {
            console.error("Erreur lors de la récupération des kanbans:", error);
        });

    // var kabandiv = document.getElementById('myKanban');
    // if(kabandiv == null) return;
    //
    //
    // var kanban = new jKanban({
    //     element: '#myKanban',
    //     boards: [
    //         {
    //             'id'    : '_todo',
    //             'title' : 'À faire',
    //             'item'  : [
    //                 { 'title': 'Tâche 1' },
    //                 { 'title': 'Tâche 2' }
    //             ]
    //         },
    //         {
    //             'id'    : '_inprogress',
    //             'title' : 'En cours',
    //             'item'  : [
    //                 { 'title': 'Tâche 3' }
    //             ]
    //         },
    //         {
    //             'id'    : '_done',
    //             'title' : 'Terminé',
    //             'item'  : [
    //                 { 'title': 'Tâche 4' }
    //             ]
    //         }
    //     ]
    // });
});
