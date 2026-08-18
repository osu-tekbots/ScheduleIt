$(document).ready(function() {
    $('#userDatatable').DataTable({
        lengthMenu: [[25, 100, -1], [25, 100, 'All']],
        aaSorting: [[4, 'desc']]
    });

    $('#meetingDatatable').DataTable({
        lengthMenu: [[25, 100, -1], [25, 100, 'All']],
        aaSorting: [[4, 'desc']]
    });
    
    $('#findATimeDatatable').DataTable({
        lengthMenu: [[25, 100, -1], [25, 100, 'All']],
        aaSorting: [[3, 'desc']]
    });
});
