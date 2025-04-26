// Call the dataTables jQuery plugin
$(document).ready(function() {
  if ($.fn.DataTable.isDataTable('#dataTable')) {
    $('#dataTable').DataTable().destroy();
  }
  $('#dataTable').DataTable();
});
