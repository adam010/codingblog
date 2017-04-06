<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title>Naamloos document</title>


	<link href="labels_style.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://file.myfontastic.com/PVjN7PfkPE7zjAAtr7zUwi/icons.css">
	<link rel="stylesheet" href="../basic.css" type="text/css">
	<link rel="stylesheet" href="../js/basic.css" type="text/css">
	<script src="https://code.jquery.com/jquery-3.2.0.js"></script>
	<script src="jquery.colorbox-min.js"></script>
	<script src="../js/basic.js"></script>
	<script type=”text/javascript” src=”http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js”></script>

	<script>
		$( function () {

			productCode = "05000";
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
					console.log( data );
					populateItemSelect( data )
				} else if ( data[ 'errors' ] != null ) {
					var errorText = "";
					for ( i = 0; i < data[ 'errors' ].length; ++i ) {
						if ( i > 0 ) {
							errorText += "\n\n";
						}
						errorText += data[ 'errors' ][ i ];
					}					
					console.log( data );

				} else {
					/// flashmessage("data retreival failed!")
					console.log( data );

				}
			} ).fail( function ( data ) {
				//alert( "Nothing loaded." );
				 ///flashmessage("data retrieval failed!")
				console.log( data );
			} );

			function populateItemSelect( data ) {
				productOptions = []
				$.each( data, function () {
					$.each( this, function ( obj, item ) {
						var newOption = document.createElement( "option" );
						newOption.text = item[ 'productCode' ];
						newOption.value = item[ 'articleNumber' ] + '_' + item[ 'productID' ] + '_' + item[ 'plID' ];

						articleSelect.appendChild( newOption );
					} )

				} );

			}
			
			$('.fileUpload').bind('change', function(){
               var fileName = $(".fileInput", this).val();
               fileName = /[^\\]*$/.exec(fileName)[0];
               $('.fileSelected').html(fileName);
			})



			$( ".inline" ).colorbox( {
				inline: true,
				width: 600,
				height: 500
			} );
			

			$('#fldupload').change(function(){ 
				//--------/(\.|\/)(gif|jpe?g|png|txt)$/i
					
				if (this.files && this.files[0]) {
				   var filetype = this.files[0].type; 
				// only jpg, png, ai,pdf,psd,eps
				var validTypes =  /(\.|\/)(jpg|png|pdf|psd|eps|ai|)$/;
					// /^file\/(jpg|png|pdf|psd|eps|ai|)$/;

				if (!validTypes.test(filetype)) {
				  
					$('#fldupload').val(null); 
				  alert('This file type is unsupported.');
				}
			  }		
			});
			
			//save to db

			$( "#labeldata" ).submit( function ( e ) {
				e.preventDefault();
				var formElement = document.getElementById( "labeldata" );
				var formData = new FormData( formElement );
				console.log( formData );
				//console.log(e)
				formData.append( 'file', $( '#fldupload' )[ 0 ] );
				formData.append('newLabel','insert');
				$.ajax( {
					url: "itemLabel.ajax.php",
					type: "POST",
					data: formData,
					mimeTypes: "multipart/form-data",
					contentType: false,
					cache: false,
					processData: false,
					success: function ( data ) {
						///alert( data );
						//form reset + reload

					},
					error: function () {
						//alert( " not okey" );
					}
				} );
			} );
		} );
	</script>
</head>


<body>

	<div id="Labelfrm">
		<form method="post" enctype="multipart/form-data" id='labeldata'>
			<table width="1138" border="0" id="datatable">
				<tbody>
					<tr>
						<th width="199" align="center" nowrap="nowrap" id="artcol">Artikel</th>
						<th width="166" align="center" nowrap="nowrap">Type</th>
						<th width="228" align="center" nowrap="nowrap">Language</th>

						<th width="385" align="center" nowrap="nowrap">Select new file to upload</th>
						<th width="78" align="center" nowrap="nowrap">Version date&nbsp;</th>
						<th width="27" align="center" nowrap="nowrap">&nbsp;</th>
						<th width="25" align="center" nowrap="nowrap">&nbsp;</th>
					</tr>
					<tr>
						<td><div class="select selecttype">
							<div class="select__arrow"></div>
							<select name="articleSelect" id="articleSelect" class="article-select" required>
								<option value="" selected>Please select an item</option>
							</select>
							</div>
						</td>
						<td align="left">
						<div class="select selecttype" >
							<div class="select__arrow"></div>						
							<select name="fldlabeltype" id="fldlabeltype" class="label-select " required >
								<option selected>Select an Option</option>
								<option>Frontlabel</option>
								<option>Backlabel</option>
							</select>
							</div>
						</td>
						<td>
						
						<input name="fldlang" type="text" id="fldlang" maxlength="255 " required >
							<div class="icon icon-language"></div>
						</td>
						<td align="left " nowrap="nowrap ">
							<div class="fileUpload " style="display:inline-block;">

								
               <label class="fileUpload">
                 <input type="file" name="fldupload" id="fldupload" class="fileInput" required>
				   </label>
							</div>
							<!--div class="icon icon-plus" style="float:right;display:inline-block"></div-->


						</td>
						<td align="left"><input name="fldversion" type="text" id="fldversion" value=<?php echo date( "Y-m-d");?> size="5" required /></td>
						<td align="left" style="width:25px">
							<a class='inline' href="#inline_content">
								<div class="icon icon-notes"></div>
							</a>
						</td>
						<td align="left" style="width:30px">
							<button type="submit" name="submit" id="submit" class="icon icon-plus submitBtn"> </button>
							<!--input class="icon icon-plus submitBtn" type="submit" name="submit" id="submit"   style="float:left;display:inline-block" title="submit form"-->

						</td>
					</tr>
					<tr>
					  <td colspan="7" style="text-align: center"><div id="okmsg"></div></td>
				  </tr>


				</tbody>

			</table>
			<div class="newnotes">
				<div id='inline_content' style='padding:10px; background:#fff;'>
					<textarea id="fldnotes" name="fldnotes" style="width:100%;height: 470px"></textarea>
			  </div>
		  </div>
		</form>
	</div>
	
</body>

</html>