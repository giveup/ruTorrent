$(document).ready(function(){
	$('#plabel').text(theUILang.Labels);
	$('#flabel').text(theUILang.mnu_search);

	$('[ru-string]').each(function(){
		$(this).text(theUILang[$(this).attr('ru-string')])
	});
});
