// Public Imports
import "../../scss/public/main.scss";

(function( $ ) {
	'use strict';

	$(document).ready(function() {
		
		$('body .choose-button-container .choose-button').on( "click", "a.button-choose", function(e) {
			e.preventDefault();
			let show = $(this).data('field');
			$(this).parent().parent().parent().find('.hidden-type').hide();
			$(`.hidden-type.${show}`).fadeIn(500);

		});

		//selecting all required elements
		$( '.inputfile' ).each( function(){
		var $input	 = $( this ),
			$label	 = $input.next( 'label' ),
			labelVal = $label.html();

		$input.on( 'change', function( e )
		{
			var fileName = '';

			if( this.files && this.files.length > 1 )
				fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
			else if( e.target.value )
				fileName = e.target.value.split( '\\' ).pop();

			if( fileName )
				$label.find( 'span' ).html( fileName );
			else
				$label.html( labelVal );
		});

		// Firefox bug fix
		$input
		.on( 'focus', function(){ $input.addClass( 'has-focus' ); })
		.on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
	});

});

	

})( jQuery );
