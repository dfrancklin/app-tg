$('#confirm-modal').on('show.bs.modal', function (event) {
	const button = $(event.relatedTarget);
	const id = button.data('id');
	const link = $(this).find('a');
	const destiny = link.data('destiny');
	link.attr('href', destiny + id);
});
