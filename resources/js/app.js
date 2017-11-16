$('#confirm-modal').on('show.bs.modal', function (event) {
	const button = $(event.relatedTarget);
	const id = button.data('id');

	const form = $(this).find('#confirm-form');
	const destiny = form.data('destiny');
	form.attr('action', destiny + id);
});
