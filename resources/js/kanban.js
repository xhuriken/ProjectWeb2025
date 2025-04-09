document.addEventListener("DOMContentLoaded", function() {
    var kabandiv = document.getElementById('myKanban');
    if(kabandiv == null) return;


    var kanban = new jKanban({
        element: '#myKanban',
        boards: [
            {
                'id'    : '_todo',
                'title' : 'À faire',
                'item'  : [
                    { 'title': 'Tâche 1' },
                    { 'title': 'Tâche 2' }
                ]
            },
            {
                'id'    : '_inprogress',
                'title' : 'En cours',
                'item'  : [
                    { 'title': 'Tâche 3' }
                ]
            },
            {
                'id'    : '_done',
                'title' : 'Terminé',
                'item'  : [
                    { 'title': 'Tâche 4' }
                ]
            }
        ]
    });
});
