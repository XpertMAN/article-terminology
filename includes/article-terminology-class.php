<?php class ActiveTerminology {

	public static function activate() {
	
		// активация плагина

		ActiveTerminology::LoadStyles();
		ActiveTerminology::CreateShortcode();
		ActiveTerminology::Create_post_types_and_terms();  
		ActiveTerminology::CreateMetaBoxes();
		ActiveTerminology::CreatePages();
		ActiveTerminology::LoadAjax();
		ActiveTerminology::ModifyTheContent();
		ActiveTerminology::CatchPublish();
		ActiveTerminology::SEO();

		$abc_option_name = esc_attr( get_option('abc_option_name') ); if (!$abc_option_name) $abc_option_name = 'termabc';
		add_rewrite_endpoint($abc_option_name, EP_PERMALINK | EP_PAGES);    

	}

	public static function SEO() {


		function seo_letter_title ($title) {

				$abc_option_name = esc_attr( get_option('abc_option_name') ); if (!$abc_option_name) $abc_option_name = 'termabc';
				$lt_option_name = esc_attr( get_option('lt_option_name') ); 
				$termabc = get_query_var($abc_option_name); 	
				
				if ($termabc) { 
					$termabc = mb_strtoupper(ActiveTerminology::transliterate(strtolower($termabc), 'cyr')); 
					$title = str_replace ('%lt_letter%', $termabc, $lt_option_name);
				}

			return $title;
		
		}

		add_filter( 'aioseop_title', 'seo_letter_title', 10, 1 ); 


		function seo_letter_description ($description) {

				$abc_option_name = esc_attr( get_option('abc_option_name') ); if (!$abc_option_name) $abc_option_name = 'termabc';
				$ds_option_name = esc_attr( get_option('ds_option_name') ); 
				$termabc = get_query_var($abc_option_name); 	
				
				if ($termabc) { 
					$termabc = mb_strtoupper(ActiveTerminology::transliterate(strtolower($termabc), 'cyr')); 
					$description = str_replace ('%ds_letter%', $termabc, $ds_option_name);
				}

			return $description;
		
		}



		add_filter( 'aioseop_description', 'seo_letter_description', 10, 1 ); 
			

		add_filter( 'the_title', 'add_text_to_page_title' );
			
			function add_text_to_page_title( $title ) {

				global $wp;
				$abc_option_name = esc_attr( get_option('abc_option_name') ); if (!$abc_option_name) $abc_option_name = 'termabc';
				$h1_option_name = esc_attr( get_option('h1_option_name') ); if (!$h1_option_name) $h1_option_name = '%at_title% на букву %at_letter%';
				$termabc = get_query_var($abc_option_name); 
				if ((in_the_loop()) && (is_page())) {
				if ($termabc) { 
					$l =  "<span class='at-up'>" . ActiveTerminology::transliterate(strtolower($termabc), 'cyr') . "</span>";
					$ntitle = str_replace ('%at_title%', $title . "<span id='delimiter'></span>", $h1_option_name);
					$ntitle = str_replace ('%at_letter%', $l, $ntitle);
					$title = $ntitle;
				}
				}

				return $title;
		}





	}
	
	public static function deactivate() {
		// деактивация плагина
		// уточнить - вычищать за собой созданные метаданные, или оставить? 

	}
	
	public static function LoadStyles() {
		// enqueue styles and js 

		wp_register_style  ( 'article_terminology',  str_replace('includes/', '', plugin_dir_url(__FILE__)) . 'asset/css/at.css' );
		wp_enqueue_style   ( 'article_terminology' );
		wp_register_script ( 'article_terminology_js',  str_replace('includes/', '', plugin_dir_url(__FILE__)) . 'asset/vendor/FileSaver.js' );
		wp_enqueue_script  ( 'article_terminology_js' );
	}

	public static function LoadAjax() {
		
		// ajax doings 

		add_action('wp_ajax_fiu_content_parse', 'fiu_content_parse');
		add_action('wp_ajax_nopriv_fiu_content_parse', 'fiu_content_parse');

		function fiu_content_parse(){

				header('Content-type: application/json');
				$success_results = 0; 
				$post_list = get_posts(array( 'fields'=> 'ids', 'posts_per_page'  => -1, 'post_type'   => 'post' ));
				// var_dump ($post_list);

				foreach ( $post_list as $post_id ) {
				   if (ActiveTerminology::ScanTerm($post_id)) { $success_results++; }
				}
 
				if ($success_results > 0) 
				{
					$response_array['status'] = 'success';  
					$response_array['msg'] = $success_results . ' терминов обнаружено и привязано к постам';
					echo json_encode($response_array);
					die (); 
				}
				else 
				{
					$response_array['status'] = 'error';  
					$response_array['msg'] = 'Терминов в статьях не найдено';
					echo json_encode($response_array);
					die (); 
				}
		} // конец обработчика сканирования 

		add_action('wp_ajax_fiu_upload_file', 'fiu_upload_file');
		add_action('wp_ajax_nopriv_fiu_upload_file', 'fiu_upload_file');

		function fiu_upload_file(){
				header('Content-type: application/json');
				$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');

				if(!in_array($_FILES['file']['type'],$mimes)) 
					{ 
						$response_array['status'] = 'error';  
						$response_array['msg'] = 'Ошибка. Загружаемый файл не является файлом CSV!';  
						echo json_encode($response_array);
						die (); 
					}


				if ( 0 < $_FILES['file']['error'] ) 
						{ 
						$response_array['status'] = 'error';  
						$response_array['msg'] = 'Ошибка загрузки файла!';  
						echo json_encode($response_array);
						die (); 
					}


				$tmp = uniqid(rand(), true) . '.csv';
				move_uploaded_file($_FILES['file']['tmp_name'], '' . $tmp);

				if (($handle = fopen($tmp, "r")) !== FALSE) {
					while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
						$row++;
						$num = count($data);

						$csv_data[$row]['term_name'] = $data[2];
						$csv_data[$row]['term_tax'] = $data[1];
						$csv_data[$row]['term_priority'] = $data[0];

						for ($c = 3; $c < $num; $c++) {
						  $csv_data[$row]['term_lemmas'] .=	$data[$c] . ", ";
						}
						$csv_data[$row]['term_lemmas'] = rtrim( trim ($csv_data[$row]['term_lemmas']), ','); 
					}
					fclose($handle);
				}

				$terms_uploaded = 0; 

						
				if (is_array($csv_data)):
						foreach ($csv_data as $csv_data_term) 
						{

							// $response_array['msg'] = var_export ($csv_data_term); 
							$termTitle = $csv_data_term['term_name'];
							$termTax   = $csv_data_term['term_tax'];

							$termMeta = array('_at_lemmas' => $csv_data_term['term_lemmas'], '_at_priority' => $csv_data_term['term_priority']);
							$insertResult = ActiveTerminology::InsertTerm ($termTitle, $termTax, $termMeta);
							if ($insertResult) $terms_uploaded++; 
						
						}
				endif;

						//var_dump ($csv_data);
						$response_array['status'] = 'success';  
						$response_array['msg'] = $terms_uploaded . ' термин(а)ов успешно загружено!';  
						echo json_encode($response_array);
						die (); 


		} // конец обработчика загрузки 


		add_action('wp_ajax_fiu_download_file', 'fiu_download_file');
		add_action('wp_ajax_nopriv_fiu_download_file', 'fiu_download_file');

		function fiu_download_file(){

				header('Content-type: application/json');
				$export_array = ActiveTerminology::GetTermData(); 
				if ( sizeof($export_array) < 0 ) 
					{
						$response_array['status'] = 'error';  
						$response_array['msg'] = 'Нет терминов для выгрузки';
						echo json_encode($response_array);
						die (); 
					}

				$exportfile = plugin_dir_path(__FILE__) . 'export.txt';
				$fp = fopen( $exportfile, 'w');

				foreach ($export_array as $fields) { 
						// fputcsv($fp, $fields, ";");	
						fputs($fp, implode($fields, ';')."\n");
						}
				fclose($fp);


				$response_array['status'] = 'success';  
				$response_array['msg'] = 'Термины выгружены';  // . $exportfile;  
				echo json_encode($response_array);
				die (); 

		} // конец обработчика выгрузки		
		
		add_action('wp_ajax_at_terms_scan', 'at_terms_scan');
		add_action('wp_ajax_nopriv_at_terms_scan', 'at_terms_scan');

		function at_terms_scan(){

				$post_id = $_POST["post_id"];
				header('Content-type: application/json');
				if (ActiveTerminology::ScanTerm($post_id)) 
				{
					$response_array['status'] = 'success';  
					$response_array['msg'] = 'Термины созданы';
					echo json_encode($response_array);
					die (); 
				}
				else 
				{
					$response_array['status'] = 'error';  
					$response_array['msg'] = 'Не удалось создать список терминов или термины не обнаружены';
					echo json_encode($response_array);
					die (); 
				}

		} // конец обработчика сканера терминов в записи

	}


	public static function ScanTerm( $post_id ) {

		// content scanner

		$content_post = get_post( $post_id);
		$content = $content_post->post_content;
		$export_array = ActiveTerminology::GetTermData($retid = true); 
		$term_cnt = 0; 
		foreach ($export_array as $TermsData) 
		{ 
				$cnt = 0;
				$CurrentID = $TermsData[0];
				$CurrentPriority = $TermsData[1];

				foreach ($TermsData as $lemma) 
					{
						if (mb_strpos($content, trim($lemma)) !== false) 
							{  
								$found_terms[$CurrentID] = $CurrentPriority;
								break(1);
							}
					}
		}
		
		arsort($found_terms);
		$at_size_option_name = esc_attr( get_option('at_size_option_name') ); if (!$at_size_option_name) $at_size_option_name = 15;
		$found_terms = array_slice($found_terms, 0, $at_size_option_name);
		$found_terms_str = implode (",", array_keys($found_terms));

		if ($found_terms_str) 
			{ 
				update_post_meta($post_id, '_at_found_terms', $found_terms_str);
				return true;
			}
			else {
				return false;
			}
	
	}


	public static function CreatePages() {
	// создаем страницы 


		add_action('admin_menu', 'register_at_submenu_page');

		function register_at_submenu_page() {
			add_submenu_page( 'edit.php?post_type=article-term', 'Инструменты', 'Инструменты', 'edit_posts', 'at-submenu-page', 'at_submenu_page_callback' ); 
			add_submenu_page( 'edit.php?post_type=article-term', 'Настройки', 'Настройки', 'manage_options', 'at-submenu-options-page', 'at_submenu_options_page_callback' ); 
		}

		function at_submenu_page_callback() {
			require ( plugin_dir_path(__FILE__) . '/admin-backend.php');
		}

		function at_submenu_options_page_callback() {
			require ( plugin_dir_path(__FILE__) . '/admin-options.php');
		}

	}

	public static function CreateMetaBoxes() {
	// создаем метабоксы 


			add_action('add_meta_boxes', 'at_add_post_box');
			add_action( 'save_post', 'at_save_postdata' );


			function at_add_post_box(){
				$screens = array( 'post' );
				add_meta_box( 'at_action', 'Работа с терминами', 'at_term_box_callback', $screens, 'side' );
			}

			function at_term_box_callback( $post, $meta ){
				$screens = $meta['args'];
				wp_nonce_field( plugin_basename(__FILE__), 'at_term_noncename' );
				echo '<div id="term_action_response"></div>'; 
				echo '<button class="button-primary button-large" id="at_term_action" name="at_term_action" />Найти и обработать термины</button>';
				?>
				<script language="JavaScript">
					jQuery(document).on('click', '#at_term_action', function(e){
						e.preventDefault();
						var fd = new FormData();
						fd.append('post_id', '<?= $post->ID; ?>');  
						fd.append('action', 'at_terms_scan');  

						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							cache: false, 
							data: fd,
							contentType: false,
							processData: false,
							success: function(response) {
								console.log (response);
								if(response.status == 'success'){
									jQuery('#term_action_response').show();
									jQuery('#term_action_response').removeClass( 'responseError' ); 
									jQuery('#term_action_response').addClass( 'responseSuccess' ); 
									jQuery('#term_action_response').text( response.msg ); 
						
								}else if(response.status == 'error'){
									jQuery('#term_action_response').show();
									jQuery('#term_action_response').removeClass( 'responseSuccess' ); 
									jQuery('#term_action_response').addClass( 'responseError' ); 
									jQuery('#term_action_response').text( response.msg ); 
								}
							},
						});
					});
			</script>
			<?php 
			}



			add_action('add_meta_boxes', 'at_add_terms_box');
			add_action( 'save_post', 'at_save_termdata' );
			

			function at_add_terms_box(){
				$screens = array( 'article-term' );
				add_meta_box( 'at_lemmas', 'Список словоформ', 'at_meta_box_callback', $screens );
			}

			function at_meta_box_callback( $post, $meta ){
				$screens = $meta['args'];
				wp_nonce_field( plugin_basename(__FILE__), 'at_noncename' );
				echo '<p><label class="at-reader-text" for="at_lemmas">' . __("Список словоформ, перечисляйте через запятую", 'at_textdomain' ) . '</label>';
				echo '<textarea class="widefat" id="at_lemmas_list" name="at_lemmas" />'. get_post_meta( $post->ID, '_at_lemmas', true) .'</textarea>';
				echo '<p><label class="at-reader-text" for="at_priority">' . __("Приоритет термина", 'at_textdomain' ) . '</label>';
				echo '<input class="widefat" id="at_priority" name="at_priority" type="number" value="' . get_post_meta( $post->ID, '_at_priority', true) . '"/>';
			}

		
			function at_save_termdata( $post_id ) {

				if ( ! isset( $_POST['at_lemmas'] ) ) return;
				if ( ! wp_verify_nonce( $_POST['at_noncename'], plugin_basename(__FILE__) ) ) return;
				if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
				if( ! current_user_can( 'edit_post', $post_id ) ) return;
				
				$at_lemmas_data = sanitize_text_field( $_POST['at_lemmas'] );
				$at_lemmas_data = explode (",", $at_lemmas_data); 
				$at_lemmas_data = array_map('trim', $at_lemmas_data);
				$at_lemmas_data = implode (", ", $at_lemmas_data); 
				update_post_meta( $post_id, '_at_lemmas', $at_lemmas_data );

				$at_priority_data = (int) sanitize_text_field( $_POST['at_priority'] );
				if ( !$at_priority_data ) $at_priority_data = 0; 
				update_post_meta( $post_id, '_at_priority', $at_priority_data );

			}

	
	}

	public static function Create_post_types_and_terms() {
	// создаем тип записей "термины статей" и таксономию для него
	
		add_action( 'load-options-permalink.php', 'wpse30021_load_permalinks' );
		function wpse30021_load_permalinks()
		{
			if( isset( $_POST['wpse30021_cpt_base'] ) )
			{
				update_option( 'wpse30021_cpt_base', sanitize_title_with_dashes( $_POST['wpse30021_cpt_base'] ) );
			}
			
			add_settings_field( 'wpse30021_cpt_base', __( 'Пермалинк термина статьи' ), 'wpse30021_field_callback', 'permalink', 'optional' );
		}
		function wpse30021_field_callback()
		{
			$value = get_option( 'wpse30021_cpt_base' );	
			if (!$value) $value = 'article-term';
			echo '<input type="text" value="' . esc_attr( $value ) . '" name="wpse30021_cpt_base" id="wpse30021_cpt_base" class="regular-text" />';
		}

		$terms_category_labels = array(
			'name'              => _x( 'Категория термина', 'taxonomy general name' ),
			'singular_name'     => _x( 'Категория термина', 'taxonomy singular name' ),
			'search_items'      => __( 'Поиск категорий терминов' ),
			'all_items'         => __( 'Все категорий терминов' ),
			'edit_item'         => __( 'Редактировать категорию термина' ),
			'update_item'       => __( 'Обновить категорию термина' ),
			'add_new_item'      => __( 'Добавить категорию термина' ),
			'new_item_name'     => __( 'Новая категория термина' ),
			'menu_name'         => __( 'Категории терминов' ),
			'not_found'			=>  __('Не найдено категорий терминов'),
		);

		$terms_category_args = array(
			'hierarchical'      => true,
			'labels'            => $terms_category_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			// 'rewrite'           => array( 'slug' => 'article-term-category' ),
		);

		$term_labels = array(
			'name' => _x('Термины', 'post type general name'),
			'singular_name' => _x('Термин', 'post type singular name'),
			'add_new' => _x('Добавить термин', 'ad'),
			'add_new_item' => __('Добавить термин'),
			'edit_item' => __('Редактировать термин'),
			'new_item' => __('Новый термин'),
			'view_item' => __('Просмотреть термин'),
			'search_items' => __('Поиск по терминам'),
			'not_found' =>  __('Не найдено подходящих терминов'),
			'not_found_in_trash' => __('Нет терминов в корзине'), 
			'parent_item_colon' => ''
		  );

		$pml_value = get_option( 'wpse30021_cpt_base' );	
		if (!$pml_value) $pml_value = 'article-term';

		$term_args = array(
			'labels' => $term_labels,
			'public' => true,
			'menu_position' => 6,
			'publicy_queryable' => true,
			'capability_type' => 'post',
			'show_ui' => true,
			'query_var' => true,
			'has_archive' => false, 
			'rewrite' => array('slug' => $pml_value),
			'hierarchical' => true,
			'supports' => array('title','editor', 'page-attributes', 'thumbnail', 'custom-fields', 'post-formats'),
		);

		register_taxonomy( 'article-term-category', array( 'article-term-category' ), $terms_category_args );
		register_post_type('article-term', $term_args);
		register_taxonomy_for_object_type( 'article-term-category', 'article-term' );
		flush_rewrite_rules();
	
	}



	public static function InsertTerm ($title, $tax, $meta) {

			// добавляем термин
			$post_data = array(
					  'post_title'    => strip_tags( $title ),
					  'post_content'  => "",
					  'post_type'      => "article-term",
					  'post_status'   => 'draft',
					  'meta_input'    => $meta,
			);


			if ( !get_page_by_title($title, OBJECT, 'article-term') ) 
				{ 
					$post_id = wp_insert_post ( wp_slash ( $post_data )); 

					if ( is_wp_error($post_id) )
					{
						echo $post_id->get_error_message(); // можно в лог ошибку кинуть будет, если надо
						return false;
					}
					else 
					{ 
						wp_set_object_terms($post_id, $tax, 'article-term-category');
						return $post_id; 
					}

				} 
				else 
				{ 
					return false; 
				}


		} // eof 

//		public static function InsertTerm ($title, $meta) {


	public static function ModifyTheContent () {

			add_filter( 'the_content', 'at_modify_the_content' );

			/*function generate_custom_title($title) {
				$new_title = 'this is my title';
				global $new_title;
				$title = $new_title;
				return $title;  
				}
			*/

			function at_modify_the_content( $content )
			{

				$_at_found_terms = get_post_meta(get_the_ID(), '_at_found_terms', true);
				if ($_at_found_terms) { 
				
				$at_arr = explode(",", $_at_found_terms);
				$args = array( 'post_type' => 'article-term', 'posts_per_page' => sizeof($at_arr), 'post__in' => $at_arr); 

					// var_dump($args);
					$query = new WP_Query( $args);

					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
							$link = str_replace (home_url(), "", get_the_permalink());
							$term_text .= '<a href="'.$link.'">'.get_the_title().'</a>, ';
						}
						$content .= '<span class="article-terms-content">Термины в статье: '. $term_text.'</span>'; 
					} 
				}
			return $content;
			} 
	}// eof
	
	public static function CatchPublish () {
	
		add_action( 'wp_insert_post', 'at_action_scan_publish', 10, 3 );
		function at_action_scan_publish( $post_ID, $post, $update ){
			ActiveTerminology::ScanTerm($post_ID);
		}
	
	} // eof	
	
	public static function GetTermData ($retid = false) {
				$args = array( 'post_type' => 'article-term', 'posts_per_page' => -1); 
				$query = new WP_Query( $args);
				if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
							$current_post_id = get_the_ID();
							$terms = get_the_terms( get_the_ID(), 'article-term-category' );
							$tax = $terms[0]->name; 
							$tax = trim($tax, '"');
							$_at_priority   = get_post_meta( $current_post_id, '_at_priority', true);
							$_at_lemmas     = get_post_meta( $current_post_id, '_at_lemmas', true);
							if ($retid) 
								{ 
									$str_to_export  =  $current_post_id . ";" . $_at_priority . ";" . get_the_title() . ";" . str_replace(",", ";", $_at_lemmas);
								}
								else 
								{
									$str_to_export  = $_at_priority . ";" . $tax . ";" . get_the_title() . ";" . str_replace(",", ";", $_at_lemmas);
								}
							$arr_to_export  = array_map('trim', explode(";", $str_to_export));
							$export_array[] = $arr_to_export; 
						}
						return $export_array; 
					}
					else 
					{
						return false;
					}
	} // eof	
	
	public static function CreateShortcode () {


			function at_terms_list_fn( $atts ){
				global $wp;
				$abc_option_name = esc_attr( get_option('abc_option_name') ); if (!$abc_option_name) $abc_option_name = 'termabc';
				$termabc = get_query_var($abc_option_name); 
				$url = home_url( $wp->request); $home = home_url(); 

				// if ($termabc) { $url_cut = substr($url, 0, strpos($url, 'termabc')); } else {  $url_cut =  $url;  }
				
				$url_cut = str_replace ($home, '', $url); 
				if ($termabc) $url_cut = substr($url_cut, 0, strpos($url_cut, $abc_option_name)); 
				// echo $url_cut;

				$args = array( 'post_type' => 'article-term', 'posts_per_page' => -1, 'orderby' => 'title',  'order'   => 'ASC'); 
				$query = new WP_Query( $args);

				if ( $query->have_posts() ) {
						if ($termabc) 
							{ 
								$url_cut = rtrim($url_cut, "/");
								// $term_abc_text .= '<a rel = "canonical" class="at_abc_letter" href="'.$url_cut.'">В начало</a>'; 
								// echo "<script>$(document).ready(function() { document.title = 'blah';  });</script>"; 
							} 

						while ( $query->have_posts() ) {
							$query->the_post();
							$firstCharacters[] = mb_strtoupper ( mb_substr(get_the_title(), 0, 1));



							// $term_abc_text .= '<span class="at_abc_letter"><a href="'.$url_cut.'/'.$abc_option_name.'/'.transliterate($firstCharacter,  'lat').'">'.$firstCharacter.'</a></span>';
						}
						$firstCharacters = array_unique ($firstCharacters); 
						sort($firstCharacters);
						
						//var_dump($firstCharacters); 

						$used_chars = array(); 
						foreach ( $firstCharacters as $firstCharacter )
						{
						if (!in_array (ActiveTerminology::getUnicode($firstCharacter, 2), $used_chars)) 
							{ 
								$used_chars[] = ActiveTerminology::getUnicode($firstCharacter, 2); 
								$len = strlen(ActiveTerminology::getUnicode($firstCharacter, 2));
								if ($len == 4) 
									{ 
									$firstCharactersMB[] = $firstCharacter; 
									}
							}
						}

						$firstCharacters = array_unique($firstCharactersMB);

						foreach ( $firstCharacters as $firstCharacter )
						{
							$termabc = get_query_var($abc_option_name); 
							if ($termabc == ActiveTerminology::transliterate($firstCharacter,  'lat')) {$current_class = "current"; } else {$current_class = "";}
							$letters[] = $firstCharacter;
							$term_abc_text .= '<p class="at_abc_letter '.$current_class .'"><a href="'.$url_cut.'/'.$abc_option_name.'/'. ActiveTerminology::transliterate($firstCharacter,  'lat').'">'.$firstCharacter.'</a></p>';
						}


				$ret .= '<div class="at_abc">'. $term_abc_text . '</div>';
				$ret = str_replace ('//', '/', $ret);

				
				if ($termabc) 
					{
						$ret .= "<ul class='at_ul'>";
						if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
							$firstCharacter = mb_strtolower( mb_substr(get_the_title(), 0, 1));
							if ($firstCharacter == ActiveTerminology::transliterate(strtolower($termabc), 'cyr')) 
							{
			

							$link = get_the_permalink(); 
							$link = str_replace($url_cut, '', $link); 
							$title = get_the_title();
							$title_cut = substr($title, 0, strpos($title, "<span id='delimiter'></span>")); 
							$ret .= '<li><a class="at_abc_letter_term" href="' .$link . '">' . $title_cut . '</a></li>';


							}
						}
						$ret .= '</ul>'; 
						//$ret = str_replace ('//', '/', $ret);
					} 
					}
					}
				 
				if (!$termabc) {
				
						if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
						
							$link = get_the_permalink(); 
							$link = str_replace($url_cut, '', $link); 
							$term_title = get_the_title();
//							$term_title = substr($title, 0, strpos($title, "<span id='delimiter'></span>")); 
							$term_title_letter = mb_strtoupper(mb_substr($term_title, 0, 1)); 
							$term_titles[$term_title_letter][] = $term_title; 
							 

						}}
						
						//echo "<pre>";
						//var_dump($term_titles);
						//echo "</pre>";
						
						foreach ($letters as $letter) { 
						$ret .= '<h3>'.$letter.'</h3>';
						$ret .= "<ul class='at_ul'>";
						foreach ($term_titles[$letter] as $term_title) {
						$ret .= '<li><a class="at_abc_letter_term" href="' .$link . '">' . $term_title . '</a></li>';
						}
						$ret .= '</ul>'; 
						}
				}


				 return $ret;
			}


			// [at_terms_list"]  // шорткод
			// add_rewrite_endpoint($abc_option_name, EP_PERMALINK | EP_PAGES);    

			add_shortcode( 'at_terms_list', 'at_terms_list_fn' );



			function at_terms_title_fn( $atts ){

					global $wp;
					$abc_option_name = esc_attr( get_option('abc_option_name') ); if (!$abc_option_name) $abc_option_name = 'termabc';
					$termabc = get_query_var($abc_option_name); 
					if ($termabc) return "на букву " . $termabc;

			}

			add_shortcode( 'at_terms_title', 'at_terms_title_fn' );




	} // eof


	public function transliterate($text, $mode) {
				$cyr = array(
				'ж',  'ч',  'щ',   'ш',  'ю',  'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я', 'э', 'ы',
				'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я', 'Э', 'Ы');
				$lat = array(
				'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q', '3','7',
				'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q', '3', '7');
				if($mode == 'lat') return str_replace($cyr, $lat, $text);
				if($mode == 'cyr') return str_replace($lat, $cyr, $text);
				return null;
			}


    public function getUnicode($symbol,$bytes = 1)
    {
        $offset = 0;
        $highChar = substr($symbol, $offset ,1);
        $ascii = ord($highChar);
        if ($bytes > 1) {
            $code = ($ascii) & ((1 << (7 - $bytes)) - 1);
            for ($i = 1;$i<$bytes;$i++) {
                $char = substr($symbol, $offset + $i, 1);
                $code =  ($code << 6) | (ord($char) & 0x3f);
            }
            $ascii = $code;
        }
        return $ascii;
    }

    public function getBytesNumber($symbol)
    {
        $ascii = ord($symbol);
        $bytesNumber = 1;
        if ($ascii > 0x7f) {
            switch ($ascii&0xf0) {
                case 0xfd:
                    $bytesNumber = 6;
                    break;
                case 0xf8:
                    $bytesNumber = 5;
                    break;
                case 0xf0:
                    $bytesNumber = 4;
                    break;
                case 0xe0:
                    $bytesNumber = 3;
                    break;
                case 0xd1:
                case 0xd0:
                    $bytesNumber = 2;
                    break;
            }
        }
        return $bytesNumber;
}


}
?>