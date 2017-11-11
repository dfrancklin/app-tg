UploaderComponent.loadComponents();

$('#confirm-modal').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget);
	var id = button.data('id');

	$(this)
		.find('#confirm-form')
		.attr('action', '/products/delete/' + id);
});