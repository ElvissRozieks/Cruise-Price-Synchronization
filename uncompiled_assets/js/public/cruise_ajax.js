(function( $ ) {
	'use strict';

	$(document).ready(function() {

		google.maps.event.addDomListener(window, 'load', initialize);
			
			 
		function initialize() {
			var input = document.getElementById('location');
			var autocomplete = new google.maps.places.Autocomplete(input);
		}

		initialize();

		$('form.ajax').on('submit', function(e){
			e.preventDefault();
			let resume_data = $('input#resume_file').prop('files')[0];
			if(!resume_data){
				resume_data = '';
			}
			let cover_data = $('input#cover_letter_file').prop('files')[0];
			if(!cover_data){
				cover_data = '';
			}
			else{
				cover_data 
			}
			var form_data = new FormData();
			form_data.append('id', $('input#board_id').val());
			form_data.append('first_name', $('input#first_name').val());
			form_data.append('last_name', $('input#last_name').val());
			form_data.append('email', $('input#email').val());
			form_data.append('phone', $('input#phone').val());
			form_data.append('location', $('input#location').val());
			form_data.append('skypeid', $('input#skypeid').val());
			form_data.append('linkedin', $('input#linkedin').val());
			form_data.append('resume_text', $('textarea#resume_text').val());
			form_data.append('resume_file', resume_data);
			form_data.append('cover_letter_file', cover_data);
			form_data.append('cover_letter_text', $('textarea#cover_letter_text').val());
			form_data.append('website', $('input#website').val());
			form_data.append('salary', $('input#salary').val());
			form_data.append('know-about-us', $('input#know-about-us').val());
			form_data.append('gdpr', $('input#gdpr').val());
			form_data.append('mapped_url_token', $('input#mapped_url_token').val());
			form_data.append('action', 'submit_ajax_request');

			function validate() {

				let errorCheck = 1;

				$( "form.ajax .field .mandatory").each(function() {
					let combined = '';
					if(!$( this ).val()){
						$( this ).addClass('error');
						combined = $(this).data('combined');
						if(combined){
							let inputFile = $(`input[name='${combined}']`);
							inputFile.parent().addClass('error');
							let file_check = inputFile[0].files.length;
							if(file_check === 0) {
								errorCheck = 0;
							}
							else {
								inputFile.parent().removeClass('error');
								$( this ).removeClass('error');
							}
						}
						else {
							errorCheck = 0;
						}
						
					}
					else{
						$( this ).removeClass('error');
					}
				});

				let gdpr = $( "form.ajax .field input[type=checkbox]").prop('checked');

				if(!gdpr){
					$('.checkmark').addClass('error');
					errorCheck = 0;
				}
				else{
					$('.checkmark').removeClass('error');
				}

				return Boolean(errorCheck);
			}

			if(validate()){
	
				$.ajax({
					url: submit_ajax_obj.ajaxurl,
					type: 'POST',
					contentType: false,
					processData: false,
					data: form_data,
					success: function (response) {
						$(".form-wrapper .heading").text('Thank you');
						$(".form-wrapper form").html('<pre>'+response+'</pre>');
						$(".success_msg").css("display","block");
					},
					error: function(errorThrown){
						$(".error_msg").css("display","block"); 
					}
				});
			}
			else{
				scroll('#apply-form');
			}

		});  

	});

	

function scroll(towhere){
	var new_position = $(towhere).offset();
	$('html, body').stop().animate({ scrollTop: new_position.top - 300 }, 500);
}

})( jQuery );
