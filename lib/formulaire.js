$(function() {
	
	$(".datepicker").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
    });
	
	$('#request').click(function(){
	    validateForm();   
	});
	
	function validateForm(){
		
		var mode = $('#request').val();
		var formID = $('#formID').val();
		var param = [];
		var values = [];
		var cpt = 0;
		//var status = true;

		$('#'+formID+ ' .field' ).each(function() {
			if (!($(this).attr('type') == 'radio' && !$(this).is(':checked'))) {
				param[cpt] = $(this).attr('id');
				values[cpt] = $(this).val();
				cpt++;
			}
		});

		/*$.ajax({
			  type: 'POST',
			  url: "./php/script.php",
			  data: {'formID': formID, 'param': param, 'values': values, 'mode': mode,
			  success: function() {alert("toto");},
			  async:false
		});*/
		
		$.post("./php/script.php", {'formID': formID, 'param': param, 'values': values, 'mode': mode}, function(data) {alert(data);},"text");
		/*
		if(status && mode == 'insert') {
         	$('#alert-success-insert').show();
       	}*/
	}
});

(function(factory) {
	if ( typeof define === "function" && define.amd ) {
		define([ "../jquery.ui.datepicker" ], factory );
	}else{
		factory( jQuery.datepicker );
	}
}(function(datepicker) {
	datepicker.regional['fr'] = {
		closeText: 'Fermer',
		prevText: 'Précédent',
		nextText: 'Suivant',
		currentText: 'Aujourd\'hui',
		monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
		monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
		dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
		dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
		dayNamesMin: ['D','L','M','M','J','V','S'],
		weekHeader: 'Sem.',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	datepicker.setDefaults(datepicker.regional['fr']);
	return datepicker.regional['fr'];
}));