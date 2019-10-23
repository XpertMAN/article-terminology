<div class="wrap"><h2>Управление терминами статей</h2>
<div class="clear"></div>
<div class="postbox">
<h3 class="hndle"><span>Импорт терминов</span></h3>
	<div class="inside">
			<div id="upload_response"></div>
			<input id="csv_uploader" type="file" name="sortpic" />
			<div class="clear10"></div>
			<button class="button-primary" id="upload"><?php _e('Загрузить список терминов') ?></button>
			<script language="JavaScript">
					jQuery(document).on('click', '#upload', function(e){
						e.preventDefault();
						var fd = new FormData();
						var file = jQuery(document).find('#csv_uploader');
						var individual_file = file[0].files[0];
						fd.append("file", individual_file);
						fd.append('action', 'fiu_upload_file');  
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							cache: false, 
							data: fd,
							contentType: false,
							processData: false,
							success: function(response) {
								console.log (response.status);
								if(response.status == 'success'){
									jQuery('#upload_response').show();
									jQuery('#upload_response').removeClass( 'responseError' ); 
									jQuery('#upload_response').addClass( 'responseSuccess' ); 
									jQuery('#upload_response').text( response.msg ); 
								}else if(response.status == 'error'){
									jQuery('#upload_response').show();
									jQuery('#upload_response').removeClass( 'responseSuccess' ); 
									jQuery('#upload_response').addClass( 'responseError' ); 
									jQuery('#upload_response').text( response.msg ); 
								}
							},
						});
					});
			</script>
	</div>
</div>
<div class="postbox">
<h3 class="hndle"><span>Экспорт терминов</span></h3>
	<div class="inside">
		<div id="download_response"></div>
		<a id="download_file" href="<?= plugin_dir_url(__FILE__) . 'export.txt' ?>" download="export.txt" /></a>
		<button class="button-primary" id="download"><?php _e('Выгрузить список терминов в CSV') ?></button>
			<script language="JavaScript">
					jQuery(document).on('click', '#download', function(e){
						e.preventDefault();
						var fd = new FormData();
						fd.append('action', 'fiu_download_file');  

						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							cache: false, 
							data: fd,
							contentType: false,
							processData: false,
							success: function(response) {
								console.log (response.status);
								if(response.status == 'success'){
									jQuery('#download_response').show();
									jQuery('#download_response').removeClass( 'responseError' ); 
									jQuery('#download_response').addClass( 'responseSuccess' ); 
									jQuery('#download_response').text( response.msg ); 
									var clickEvent = document.createEvent("MouseEvent");
									clickEvent.initMouseEvent("click", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null); 
									document.getElementById("download_file").dispatchEvent(clickEvent);									
								}else if(response.status == 'error'){
									jQuery('#download_response').show();
									jQuery('#download_response').removeClass( 'responseSuccess' ); 
									jQuery('#download_response').addClass( 'responseError' ); 
									jQuery('#download_response').text( response.msg ); 
								}
							},
						});
					});
			</script>

	</div>
</div>
<div class="postbox">
<h3 class="hndle"><span>Обработка терминов</span></h3>
	<div class="inside">
		<div id="parse_response"></div>
		<button class="button-primary" id="parse"><?php _e('Запустить поиск терминологии в контенте') ?></button>

		<script language="JavaScript">
					jQuery(document).on('click', '#parse', function(e){
						
						jQuery('#parse_response').show();
						jQuery('#parse_response').addClass( 'responseWaiting' ); 
						jQuery('#parse_response').text( 'Сканируем контент, подождите ...' ); 

						e.preventDefault();
						var fd = new FormData();
						fd.append('action', 'fiu_content_parse');  
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							cache: false, 
							data: fd,
							contentType: false,
							processData: false,
							success: function(response) {
								console.log (response.status);
								if(response.status == 'success'){
									jQuery('#parse_response').removeClass( 'responseWaiting' ); 
									jQuery('#parse_response').removeClass( 'responseError' ); 
									jQuery('#parse_response').addClass( 'responseSuccess' ); 
									jQuery('#parse_response').text( response.msg ); 
								}else if(response.status == 'error'){
									jQuery('#parse_response').removeClass( 'responseWaiting' ); 
									jQuery('#parse_response').removeClass( 'responseSuccess' ); 
									jQuery('#parse_response').addClass( 'responseError' ); 
									jQuery('#parse_response').text( response.msg ); 
								}
							},
						});
					});
			</script>

	</div>
</div>
</div>