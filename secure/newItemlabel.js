// JavaScript Document
	$( function () {
            languages='';
			$("#msgContainer").prepend("<div id=\"bodyContainer\" ></div>")
			productCode = "05000"; //test purposes
			plID = 0;
			$.ajax( {
				type: 'POST',
				url: 'itemLabel.ajax.php',
				data: {
					'productCode': productCode,
					'plID': plID
				},
				dataType: 'json',
				encode: true
			} ).done( function ( data ) {
				if ( data[ 'success' ] ) {				
		
					populateItemSelect( data['itemLabeldata'] );
					languages=data['languages'];
					
					
					
				} else if ( data[ 'errors' ] != null ) {
					var errorText = "";
					for ( i = 0; i < data[ 'errors' ].length; ++i ) {
						if ( i > 0 ) {
							errorText += "\n\n";
						}
						errorText += data[ 'errors' ][ i ];
					}
					showError('foutmelding',$data['errors'])				
				}
				
			} ).fail( function ( data ) {
				showError('foutmelding','An error occurred while communcating with the server, please try again.')
				
			} );

			function populateItemSelect( data ) {
				productOptions = []
				//$.each( data, function () {
					$.each( data, function ( obj, item ) {
						var newOption = document.createElement( "option" );
						newOption.text = item[ 'productCode' ];
						newOption.value = item[ 'articleNumber' ] + '_' + item[ 'productID' ] + '_' + item[ 'plID' ];

						articleSelect.appendChild( newOption );
					} )

				//} );

			}
			
   
			$( '.fileUpload' ).bind( 'change', function () {
				var fileName = $( ".fileInput", this ).val();
				fileName = /[^\\]*$/.exec( fileName )[ 0 ];
				$( '.fileSelected' ).html( fileName );
			} )


			$( ".inline" ).colorbox( {
				inline: true,
				width: 600,
				height: 500
			} );


			$( '#fldupload' ).change( function () {
				//--------/(\.|\/)(gif|jpe?g|png|txt)$/i

				if ( this.files && this.files[ 0 ] ) {
					var filetype = this.files[ 0 ].type;
					// only jpg, png, ai,pdf,psd,eps
					var validTypes = /(\.|\/)(jpg|png|pdf|psd|eps|ai|)$/;
					// /^file\/(jpg|png|pdf|psd|eps|ai|)$/;

					if ( !validTypes.test( filetype ) ) {

						$( '#fldupload' ).val( null );
						 showInfo('Informatief','This file type is not allowed,please choose from jpg|png|pdf|psd|ai or eps');
						$( '.fileUpload span' ).text('Select file');
					}
				}
			} );

			//save record to db
			$( "#labeldata" ).submit( function ( e ) {
				e.preventDefault();
				var formElement = document.getElementById( "labeldata" );
				var formData = new FormData( formElement );
				console.log( formData );
				//console.log(e)
				formData.append( 'file', $( '#fldupload' )[ 0 ] );
				formData.append( 'newLabel', 'insert' );
				$.ajax( {
					url: "itemLabel.ajax.php",
					type: "POST",
					data: formData,
					mimeTypes: "multipart/form-data",
					contentType: false,
					cache: false,
					processData: false,
					success: function ( data ) {
						showSuccess('Item label record is created successfully')						
						//form reset
						$('form[name="labeldata"]')[0].reset();

					},
					error: function () {
						showError('foutmelding',$data['errors'])
					}
				} );
			} );
		} );