$().ready(function() {
	$('.button').button();
});

function submit(obj) {
	$(obj).closest('form').submit();
}
