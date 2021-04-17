<?php

class UpFrontGalleryBlockOptions extends UpFrontBlockOptionsAPI {	
	
	public $tabs 		     = array();
	public $inputs 		     = array();
	public $tab_notices      = array();
	public $open_js_callback = array();
	
	public static function easing_effect($block) {
	
		/* we build the easing array */
		$easing_group = array( 'Quad', 'Cubic', 'Quart', 'Quint', 'Sine', 'Expo', 'Circ', 'Elastic', 'Back', 'Bounce' );
		
		$easing_effect = array();
		
		$easing_effect['swing'] = 'Swing';
			
		foreach ( $easing_group as $key => $value ) {
		
			$easing_in = 'ease In ' .  $value;
			$easing_out = 'ease Out ' .  $value;
			$easing_inout = 'ease In Out ' .  $value;
			
			$easing_effect[str_replace(' ', '', $easing_in)] = ucfirst($easing_in);
			$easing_effect[str_replace(' ', '', $easing_out)] = ucfirst($easing_out);
			$easing_effect[str_replace(' ', '', $easing_inout)] = ucfirst($easing_inout);
		
		}
		
		return $easing_effect;
	
	}
	
	
	public static function get_all_inputs($block) {
	
		return array(
			'setup'    => self::setup($block),
			'media'    => self::media($block),
			'grid'     => self::grid($block),
			'slider'   => self::slider($block),
			'lightbox' => self::lightbox($block),
			'overlay'  => self::overlay($block),
			'links'    => self::links($block),
			'filters'  => self::filters($block),
			'ordering' => self::ordering($block),
			'content'  => self::content($block)
		);
		
	}
	
	
	public static function settings($block = "") {
	
		$options = self::get_all_inputs($block);
		
		foreach ( $options as $key => $value ) {
		
			foreach ( $value as $options ) {
			
				if ( !isset($options['default']) )
					$options['default'] = '';
				
				$get_hw_setting = UpFrontGalleryBlock::get_setting($block, $options['name'], $options['default']);
				
				if ( $options['type'] == 'wysiwyg' )
					$settings[$options['name']] = html_entity_decode($get_hw_setting, ENT_QUOTES, 'UTF-8');
				
				else	
					$settings[$options['name']] = $get_hw_setting;
				
			}
		
		}
		
		return $settings;
		
	}
	

	function modify_arguments($args = false) {

		$block = UpFrontBlocksData::get_block($args['block_id']);		
		
		/* we add the notices to each tabs */
		$inputs = self::get_all_inputs($block);
		
		foreach ( $inputs as $key => $value )
			$inputs[$key][$key . '-notice'] = self::tab_notice($key . '-notice');
			
		$this->tabs = array(
			'setup' => "Einrichten",
			'media' => "Medien",
			'grid' => "Raster",
			'slider' => "Slider",
			'lightbox' => "Lightbox",
			'overlay' => "Overlay",
			'links' => "Links",
			'filters' => "Filter",
			'ordering' => "Sortierung",
			'content' => "Inhalt"
		);
		
		$this->inputs = $inputs;
				
		$js_callback = '
			pur.blockOptionsApi.loadStyle( "' . UPFRONT_GALLERY_URL . 'admin/css/admin.css" );
			pur.blockOptionsApi.loadScript( "' . UPFRONT_GALLERY_URL . 'admin/js/admin.js", function() {

				upfront_gallery_js("' . $args['block_id'] . '");
				pur.blockOptionsApi.updateOptions("upfront_gallery_js", "' . $args['block_id'] . '");

			} );
		';

		$this->open_js_callback = upfront_load_block_options_assets( $args['block_id'], $js_callback );

	}
	
	
	public static function tab_notice($id) {
	
		return array(
			'name' => $id,
			'type' => 'raw-html',
			'html' => '<div class="pur-admin-notice"><a href="#" class="pur-close">x</a><p class="show-once"></p></div>'
		);
				
	}
	
	
	public static function header($id, $label) {
	
		return array(
			'name' => $id,
			'type' => 'raw-html',
			'html' => '<h3 class="pur-admin-header">' . $label . '</h3>'
		);
			
			
	}
	
	
	public static function wrapper($id, $open = false) {
	
		if ( $open )
			return array(
				'name' => 'wrap-' . $id . '-open',
				'type' => 'raw-html',
				'html' => '<div class="wrapper ' . $id . '">'
			);
		else
			return array(
				'name' => 'wrap-' . $id . '-close',
				'type' => 'raw-html',
				'html' => '</div>'
			);
			
	}
	
		
	public static function setup($block) {
	
		global $post;
		
		$page_info = UpFrontGalleryBlockDisplay::page_info($block);
		
		if (  $page_info['page-type'] == 'upfront_gallery' )
			$ablum_hide = array(
				'#sub-tab-media',
				'#input-readon-text', 
				'#input-enable-filters', 
				'#input-enable-ordering',
				'#input-img-enable-title-count',
				'#input-img-title-count-text'
			);
			
		else
			$ablum_hide = array(
				'#sub-tab-media',
				'#input-img-enable-title-count',
				'#input-img-title-count-text'
			);
									
		$settings = array(
			'open-wrap-view' => self::wrapper('view', true),
				'heading-view' => self::header('heading-view', 'Anzeigen &amp; Layout'),
					'view' => array(
						'type' => 'select',
						'name' => 'view',
						'label' => 'Ansicht',
						'default' => 'album',
						'options' => array(
							'albums' => 'Alben',
							'album' => 'Album',
							'media' => 'Medien'
						),
						'toggle' => array(
							'albums' => array(
								'hide' => array(
									'#sub-tab-media',
									'.wrapper.album-show-title'
								),
								'show' => array(
									'#input-img-enable-title-count',
									'#input-img-title-count-text'
								)
							),
							'album' => array(
								'hide' => $ablum_hide,
								'show' => array(
									'.wrapper.album-show-title'
								)
								
							),
							'media' => array(
								'show' => array(
									'#sub-tab-media',
									'.wrapper.img-show-title'
								),
								'hide' => array(
									'#sub-tab-grid',
									'#sub-tab-links',
									'#sub-tab-filters',
									'#input-enable-links',
									'#input-enable-lightbox',
									'#input-enable-filters',
									'#input-enable-ordering',
									'#input-layout',
									'#input-img-enable-title-count',
									'#input-img-title-count-text',
									'.wrapper.album-show-title',
									'.wrapper.nbr-images'
								),
								
							)
						),
						'tooltip' => 'Wähle aus, ob eine Liste von Alben oder ein bestimmtes Album angezeigt werden soll.'
					),
					'layout' => array(
						'type' => 'select',
						'name' => 'layout',
						'label' => 'Layout',
						'default' => 'grid',
						'options' => array(
							'grid' => 'Raster',
							'slider' => 'Slider'
						),
						'toggle' => array(
							'grid' => array(
								'show' => array(
									'#sub-tab-grid',
									'#input-enable-title-link',
									'.wrapper.img-show-title'
								),
								'hide' => array(
									'#sub-tab-slider'
								)
							),
							'slider' => array(
								'show' => array(
									'#sub-tab-slider'
								),
								'hide' => array(
									'#sub-tab-grid',
									'#input-enable-title-link',
									( $page_info['page-type'] == 'attachment' ? '' : '.wrapper.img-show-title' )
								)
							)
						),
						'tooltip' => 'Lege fest, ob das Layout ein Raster oder ein Slider sein soll.'
					),
			'close-wrap-view' => self::wrapper('view'),	
								
			'open-wrap-enable-overlay' => self::wrapper('enable-overlay', true),
				'heading-enable-overlay' => self::header('heading-enable-overlay', 'Features'),
					'enable-overlay' => array(
						'type' => 'checkbox',
						'name' => 'enable-overlay',
						'label' => 'Overlay',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#sub-tab-overlay'
								)
							),
							'false' => array(
								'hide' => array(
									'#sub-tab-overlay'
								)
							)
						),
						'tooltip' => 'Lege fest, ob die Hover-Überlagerung für Bilder aktiviert werden soll.'
					),
					'enable-lightbox' => array(
						'type' => 'checkbox',
						'name' => 'enable-lightbox',
						'label' => 'Lightbox',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#sub-tab-lightbox'
								)
							),
							'false' => array(
								'hide' => array(
									'#sub-tab-lightbox'
								)
							)
						),
						'tooltip' => 'Aktiviere die Lightbox-Vorschau in voller Größe für Bilder in Deiner Galerie.'
					),
					'enable-filters' => array(
						'type' => 'checkbox',
						'name' => 'enable-filters',
						'label' => 'Filter',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#sub-tab-filters'
								)
							),
							'false' => array(
								'hide' => array(
									'#sub-tab-filters'
								)
							)
						),
						'tooltip' => 'Lege fest, ob die Galeriefilter angewendet werden sollen oder nicht.'
					),
					'enable-ordering' => array(
						'type' => 'checkbox',
						'name' => 'enable-ordering',
						'label' => 'Sortierung',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#sub-tab-ordering'
								)
							),
							'false' => array(
								'hide' => array(
									'#sub-tab-ordering'
								)
							)
						),
						'tooltip' => 'Lege fest, ob die Galeriebestellung angewendet werden soll oder nicht.'
					),
					'enable-links' => array(
						'type' => 'checkbox',
						'name' => 'enable-links',
						'label' => 'Links',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#sub-tab-links'
								)
							),
							'false' => array(
								'hide' => array(
									'#sub-tab-links'
								)
							)
						),
						'tooltip' => 'Lege fest, ob die Galerie-Links aktiviert werden sollen.'
					),
			'close-wrap-enable-overlay' => self::wrapper('enable-overlay'),	
				
			'open-wrap-nbr-images' => self::wrapper('nbr-images', true),
				'heading-limit-images' => self::header('heading-limit-images', 'Limit'),
					'enable-limit-images' => array(
						'type' => 'checkbox',
						'name' => 'enable-limit-images',
						'label' => 'Bilder einschränken',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-nbr-images'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-nbr-images'
								)
							)
						),
						'tooltip' => 'Lege fest, ob Du die in Deiner Galerie angezeigten Bilder einschränken möchtest.'
					),
					'nbr-images' => array(
						'type' => 'integer',
						'name' => 'nbr-images',
						'label' => 'Anzahl der Bilder',
						'default' => 10,
						'tooltip' => 'Stelle die maximale Anzahl der anzuzeigenden Bilder ein.'
					),
			'close-wrap-nbr-images' => self::wrapper('nbr-images')
		);
		
		
		return $settings;
	
	}
	
	
	public static function media($block) {
	
				
		$settings = array(
			'open-wrap-img-nav' => self::wrapper('img-nav', true),
				'heading-img-nav' => self::header('heading-img-nav', 'Image Navigation'),
					'img-nav' => array(
						'type' => 'checkbox',
						'name' => 'img-nav',
						'label' => 'Weiter &amp; Vorherige Navigation',
						'default' => true,
						'tooltip' => 'Stelle ein, ob der nächste &amp; Die vorherige Navigation angezeigt werden.'
					),
					'img-nav-previous-text' => array(
						'type' => 'text',
						'name' => 'img-nav-previous-text',
						'label' => 'Vorheriger Schaltflächentext',
						'default' => 'Bisherige',
						'tooltip' => __('Stelle den vorherigen Schaltflächentext ein.', 'upfront-gallery'),
					),
					'img-nav-next-text' => array(
						'type' => 'text',
						'name' => 'img-nav-next-text',
						'label' => 'Nächster Schaltflächentext',
						'default' => 'Nächster',
						'tooltip' => 'Stelle den nächsten Schaltflächentext ein.'
					),
			'close-wrap-img-nav' => self::wrapper('img-nav')	
		);
		
		
		return $settings;
	
	}
	
	
	public static function grid($block) {
				
		$settings = array(
			'open-wrap-grid-col' => self::wrapper('grid-col', true),
				'heading-grid-col' => self::header('heading-grid-col', 'Grid'),
					'grid-col' => array(
						'type' => 'select',
						'name' => 'grid-col',
						'label' => __('Spalten','upfront-gallery'),
						'default' => 3,
						'options' => array(
							'1' => '1',
							'2' => '2',
							'3' => '3',
							'4' => '4',
							'5' => '5',
							'6' => '6',
							'7' => '7',
							'8' => '8'
						),
						'tooltip' => __('Lege die Anzahl der Spalten fest, die der Raster enthalten soll.','upfront-gallery'),
					),
					'grid-col-spacing' => array(
						'type' => 'slider',
						'name' => 'grid-col-spacing',
						'label' => 'Spaltenabstand',
						'default' => 3,
						'slider-min' => 0,
						'slider-max' => 25,
						'slider-interval' => 1,
						'unit' => '%',
						'tooltip' => 'Lege den Abstand zwischen Deinen Galerie-Spalten fest.'
					),
					'grid-row-spacing' => array(
						'type' => 'slider',
						'name' => 'grid-row-spacing',
						'label' => 'Zeilenabstand',
						'default' => 10,
						'slider-min' => 0,
						'slider-max' => 500,
						'slider-interval' => 1,
						'unit' => 'px',
						'tooltip' => "Stelle den Zeilenabstand in Pixel ein."
					),
			'close-wrap-grid-col' => self::wrapper('grid-col'),
			
			'open-wrap-img-enable-crop-height' => self::wrapper('img-enable-crop-height', true),
				'heading-img-enable-crop-height' => self::header('heading-img-enable-crop-height', 'Resize'),
					'img-enable-crop-height' => array(
						'type' => 'checkbox',
						'name' => 'img-enable-crop-height',
						'label' => 'Bild vertikal zuschneiden',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-img-crop-method'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-img-crop-method',
									'#input-img-crop-height'
								)
							)
						),
						'tooltip' => 'Lege fest, ob das Bild vertikal beschnitten werden soll.'
					),
					'img-crop-method' => array(
						'type' => 'select',
						'name' => 'img-crop-method',
						'label' => 'Bildhöhenmethode',
						'default' => 'auto-thumb',
						'options' => array(
							'auto-thumb' => 'Auto Thumbnail',
							'auto-crop' => 'Automatisch zuschneiden',
							'crop' => 'Manuelles zuschneiden'
						),
						'toggle' => array(
							'auto-thumb' => array(
								'hide' => array(
									'#input-img-crop-height'
								)
							),
							'auto-crop' => array(
								'hide' => array(
									'#input-img-crop-height'
								)
							),
							'crop' => array(
								'show' => array(
									'#input-img-crop-height'
								)
							)
						),
						'tooltip' => 'Die automatische Miniaturansicht ändert die Breite und Höhe basierend auf dem verfügbaren Platz in der Rasterspalte. Beim automatischen Zuschneiden wird die Höhe des kleinsten Bilds ermittelt und als Höhe für alle Bilder im Album verwendet. Mit der manuellen Zuschneidemethode kannst Du eine Höhe angeben, um die die Bilder zugeschnitten werden sollen.'
					),
					'img-crop-height' => array(
						'type' => 'slider',
						'name' => 'img-crop-height',
						'label' => 'Bildhöhe',
						'default' => 200,
						'slider-min' => 50,
						'slider-max' => 1500,
						'slider-interval' => 1,
						'unit' => 'px',
						'tooltip' => 'Stelle die Höhe Ihres Bildes ein.'
					),
					'img-enable-crop-width' => array(
						'type' => 'checkbox',
						'name' => 'img-enable-crop-width',
						'label' => 'Bild horizontal zuschneiden',
						'default' => true,
						'tooltip' => 'Lege fest, ob das Bild horizontal beschnitten werden soll.'
					),
			'close-wrap-img-enable-crop-height' => self::wrapper('img-enable-crop-height')
		);
		
		
		return $settings;
	
	}
	
	
	public static function slider($block) {
			
		$settings = array(
			'open-wrap-slider-nav' => self::wrapper('slider-nav', true),
				'heading-slider-nav' => self::header('heading-slider-nav', 'Main Slider'),
					'slider-nav' => array(
						'type' => 'checkbox',
						'name' => 'slider-nav',
						'label' => 'Weiter &amp; Vorherige Navigation',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-slider-nav-hover'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-slider-nav-hover'
								)
							)
						),
						'tooltip' => 'Lege fest, ob Du den Schieberegler für die nächste/vorherige Navigation anzeigen möchtest.'
					),
					'slider-nav-hover' => array(
						'type' => 'checkbox',
						'name' => 'slider-nav-hover',
						'label' => 'Nur beim Schweben anzeigen',
						'default' => false,
						'tooltip' => 'Lege fest, ob die Navigation nur angezeigt werden soll, wenn Du mit der Maus über das Hauptbild fährst.'
					),
					'slider-enable-loop' => array(
						'type' => 'checkbox',
						'name' => 'slider-enable-loop',
						'label' => 'Schleife',
						'default' => true,
						'tooltip' => 'Soll der Schieberegler auf eine Endlosschleife eingestellt werden?'
					),
					'slider-enable-slideshow' => array(
						'type' => 'checkbox',
						'name' => 'slider-enable-slideshow',
						'label' => 'Slideshow',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-slider-slideshow-speed'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-slider-slideshow-speed'
								)
							)
						),
						'tooltip' => 'Lege fest, ob der Slider wie eine Diashow wirken und automatisch abgespielt werden soll.'
					),
					'slider-slideshow-speed' => array(
						'type' => 'slider',
						'name' => 'slider-slideshow-speed',
						'label' => 'Geschwindigkeit der Diashow',
						'default' => 7000,
						'slider-min' => 2000,
						'slider-max' => 20000,
						'slider-interval' => 500,
						'unit' => 'ms',
						'tooltip' => 'Stelle die Geschwindigkeit für die Pause zwischen den Folien ein.'
					),
			'close-wrap-slider-nav' => self::wrapper('slider-nav'),
			
			'open-wrap-slider-height' => self::wrapper('slider-height', true),
				'heading-slider-height' => self::header('heading-slider-height', 'Resize'),
					'slider-height' => array(
						'type' => 'select',
						'name' => 'slider-height',
						'label' => 'Slider Höhenmethode',
						'default' => 'auto',
						'options' => array(
							'auto' => 'Auto Zuschneiden',
							'crop' => 'Manuelles Zuschneiden',
							'animate' => 'Animieren',
						),
						'toggle' => array(
							'auto' => array(
								'hide' => array(
									'#input-slider-crop-height'
								)
							),
							'crop' => array(
								'show' => array(
									'#input-slider-crop-height'
								)
							),
							'animate' => array(
								'hide' => array(
									'#input-slider-crop-height'
								)
							)
						),
						'tooltip' => 'Lege fest, ob das Bild basierend auf der Höhe des kürzesten Bilds im Album automatisch zugeschnitten oder manuell zugeschnitten werden soll, wobei Du eine bestimmte Höhe festlegst, auf die das Bild zugeschnitten werden soll. Oder zwischen den verschiedenen Bildhöhen animieren.'
					),
					'slider-crop-height' => array(
						'type' => 'slider',
						'name' => 'slider-crop-height',
						'label' => 'Slider Höhe',
						'default' => 200,
						'slider-min' => 50,
						'slider-max' => 1500,
						'slider-interval' => 1,
						'unit' => 'px',
						'tooltip' => 'Stelle die Höhe Deines Sliders ein.'
					),
			'close-wrap-img-enable-crop-height' => self::wrapper('img-enable-crop-height'),
			
			'open-wrap-slider-effect' => self::wrapper('slider-effect', true),
				'heading-slider-effect' => self::header('heading-slider-effect', 'Effects'),
					'slider-effect' => array(
						'type' => 'select',
						'name' => 'slider-effect',
						'label' => 'Effekt',
						'default' => 'fade',
						'options' => array(
							'fade' => 'Verblassen',
							'slide' => 'Gleiten'
						),
						'toggle' => array(
							'fade' => array(
								'hide' => array(
									'#input-slider-direction'
								)
							),
							'slide' => array(
								'show' => array(
									'#input-slider-direction'
								)
							)
						),
						'tooltip' => 'Stelle den Effekt ein, der für Deinen Album-Slider verwendet werden soll.'
					),
					'slider-direction' => array(
						'type' => 'select',
						'name' => 'slider-direction',
						'label' => 'Ausrichtung',
						'default' => 'horizontal',
						'options' => array(
							'horizontal' => 'Horizontal',
							'vertical' => 'Vertikal'
						),
						'tooltip' => 'Lege die Richtung für Deinen Album-Slider fest.'
					),
					'slider-speed' => array(
						'type' => 'slider',
						'name' => 'slider-speed',
						'label' => 'Slider Geschwindigkeit',
						'default' => 300,
						'slider-min' => 150,
						'slider-max' => 5000,
						'slider-interval' => 50,
						'unit' => 'ms',
						'tooltip' => 'Stelle die Geschwindigkeit Deines Album-Sliders ein.'
					),
					'slider-easing' => array(
						'type' => 'select',
						'name' => 'slider-easing',
						'label' => 'Easing',
						'default' => 'swing',
						'options' => self::easing_effect($block),
						'tooltip' => 'Lege fest, ob der Album-Slider den Beschleunigungseffekt verwenden soll.'
					),
			'close-wrap-slider-effect' => self::wrapper('slider-effect'),
			
			'open-wrap-slider-pager' => self::wrapper('slider-pager', true),
				'heading-slider-pager' => self::header('heading-slider-pager', 'Pagination'),
					'slider-pager' => array(
						'type' => 'checkbox',
						'name' => 'slider-pager',
						'label' => 'Paginierung',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-slider-enable-pager-thumbs'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-slider-enable-pager-thumbs'
								)
							)
						),
						'tooltip' => 'Lege fest, ob die Slider-Paginierung aktiviert werden soll.'
					),
					'slider-enable-pager-thumbs' => array(
						'type' => 'checkbox',
						'name' => 'slider-enable-pager-thumbs',
						'label' => 'Verwende Miniaturansichten',
						'default' => false,
						'toggle' => array(
							'false' => array(
								'hide' => array(
									'.wrapper.slider-pager-show-all'
								)
							),
							'true' => array(
								'show' => array(
									'.wrapper.slider-pager-show-all',
									'#input-slider-pager-show-all' /* used to fire that input toggle */
								)
							)
						),
						'tooltip' => 'Lege fest, ob Miniaturansichten für die Slider-Paginierung verwendet werden sollen.'
					),
			'close-wrap-slider-pager' => self::wrapper('slider-pager'),
			
			'open-wrap-slider-pager-show-all' => self::wrapper('slider-pager-show-all', true),
				'heading-slider-pager-show-all' => self::header('heading-slider-pager-show-all', 'Pagination Thumbnails'),
					'slider-pager-show-all' => array(
						'type' => 'checkbox',
						'name' => 'slider-pager-show-all',
						'label' => 'Alle Miniaturansichten anzeigen ',
						'default' => false,
						'toggle' => array(
							'false' => array(
								'show' => array(
									'#input-slider-thumb-count',
									'#input-slider-pager-nav',
									'#input-slider-pager-nav-hover'
								)
							),
							'true' => array(
								'hide' => array(
									'#input-slider-thumb-count',
									'#input-slider-pager-nav',
									'#input-slider-pager-nav-hover'
								)
							)
						),
						'tooltip' => 'Lege fest, ob alle Pager-Miniaturansichten angezeigt werden sollen. Wenn ja, wird die Karussellnavigation automatisch aktiviert, sodass sie horizontal zwischen den Miniaturansichten scrollen können. Wenn deaktiviert, wird nur die Anzahl der von Dir angegebenen Miniaturansichten angezeigt.'
					),
					'slider-thumb-count' => array(
						'type' => 'slider',
						'name' => 'slider-thumb-count',
						'label' => 'Anzahl der Miniaturansichten',
						'default' => 4,
						'slider-min' => 2,
						'slider-max' => 10,
						'slider-interval' => 1,
						'tooltip' => 'Lege die Anzahl der Miniaturansichten fest, die für Deine Slider-Paginierung verwendet werden sollen.'
					),
					'slider-pager-spacing' => array(
						'type' => 'slider',
						'name' => 'slider-pager-spacing',
						'label' => 'Containerabstand',
						'default' => 0,
						'slider-min' => 0,
						'slider-max' => 500,
						'slider-interval' => 2,
						'unit' => 'px',
						'tooltip' => 'Stelle den linken und rechten Abstand des Miniaturbildcontainers des Slider ein.'
					),
					'slider-thumb-spacing' => array(
						'type' => 'slider',
						'name' => 'slider-thumb-spacing',
						'label' => 'Miniaturansichten Abstand',
						'default' => 6,
						'slider-min' => 0,
						'slider-max' => 100,
						'slider-interval' => 2,
						'unit' => 'px',
						'tooltip' => 'Stelle den Abstand zwischen den Miniaturansichten der Schieberegler-Paginierung ein.'
					),
					'slider-enable-thumb-crop-height' => array(
						'type' => 'checkbox',
						'name' => 'slider-enable-thumb-crop-height',
						'label' => 'Miniaturbild vertikal zuschneiden',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-slider-thumb-crop-height'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-slider-thumb-crop-height'
								)
							)
						),
						'tooltip' => ''
					),
					'slider-thumb-crop-height' => array(
						'type' => 'slider',
						'name' => 'slider-thumb-crop-height',
						'label' => 'Miniaturbildhöhe',
						'default' => 50,
						'slider-min' => 5,
						'slider-max' => 500,
						'slider-interval' => 1,
						'unit' => 'px',
						'tooltip' => ''
					),
					'slider-pager-nav' => array(
						'type' => 'checkbox',
						'name' => 'slider-pager-nav',
						'label' => 'Weiter &amp; Vorherige Navigation',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-slider-pager-nav-hover'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-slider-pager-nav-hover'
								)
							)
						),
						'tooltip' => 'Lege fest, ob Du die nächste/vorherige Navigation anzeigen möchtest.'
					),
					'slider-pager-nav-hover' => array(
						'type' => 'checkbox',
						'name' => 'slider-pager-nav-hover',
						'label' => 'Nur beim Schweben anzeigen ',
						'default' => false,
						'tooltip' => ''
					),
				'close-wrap-slider-thumb-count' => self::wrapper('slider-thumb-count')
		);
		
		
		return $settings;
	
	}
		
	
	public static function lightbox($block) {
				
		$settings = array(
			'open-wrap-lightbox-enable-loop' => self::wrapper('lightbox-enable-loop', true),
				'heading-lightbox-enable-loop' => self::header('heading-lightbox-enable-loop', 'Lightbox'),
					'lightbox-enable-loop' => array(
						'type' => 'checkbox',
						'name' => 'lightbox-enable-loop',
						'label' => 'Schleife',
						'default' => true,
						'tooltip' => 'Lege fest, ob die Lightbox-Bildschleife aktiviert werden soll. Dies bedeutet, dass Deine Benutzer in der Lage sind, durch alle Bilder der Galerie zu navigieren, ohne die Vollbildansicht des Leuchtkastens öffnen und schließen zu müssen. '
					),
					'lightbox-show-title' => array(
						'type' => 'checkbox',
						'name' => 'lightbox-show-title',
						'label' => 'Titel',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-lightbox-title-position'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-lightbox-title-position'
								)
							)
						),
						'tooltip' => 'Lege fest, ob der Lightboxtitel angezeigt werden soll.'
					),
					'lightbox-title-position' => array(
						'type' => 'select',
						'name' => 'lightbox-title-position',
						'label' => 'Titelposition',
						'default' => 'over',
						'options' => array(
							'over' => 'Über Bild',
							'float' => 'Unter Bild'
						),
						'tooltip' => 'Lege die Position des Bildtitels in der Vorschau des Leuchtkastens in voller Größe fest.'
					),
			'close-wrap-lightbox-enable-loop' => self::wrapper('lightbox-enable-loop'),
			
			'open-wrap-lightbox-enable-resize' => self::wrapper('lightbox-enable-resize', true),
				'heading-lightbox-enable-resize' => self::header('heading-lightbox-enable-resize', 'Resize'),
					'lightbox-enable-resize' => array(
						'type' => 'checkbox',
						'name' => 'lightbox-enable-resize',
						'label' => 'Größe der Lightbox ändern',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-lightbox-height',
									'#input-lightbox-width'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-lightbox-height',
									'#input-lightbox-width'
								)
							)
						),
						'tooltip' => 'Stelle die Abmessungen der Lightbox ein. Bei false wird die Leuchtkastengröße automatisch berechnet.'
					),
					'lightbox-height' => array(
						'type' => 'slider',
						'name' => 'lightbox-height',
						'label' => 'Höhe',
						'default' => 1000,
						'slider-min' => 100,
						'slider-max' => 2000,
						'slider-interval' => 10,
						'unit' => 'px',
						'tooltip' => 'Stelle die Höhe Deiner Lightbox ein.'
					),
					'lightbox-width' => array(
						'type' => 'slider',
						'name' => 'lightbox-width',
						'label' => 'Breite',
						'default' => 1000,
						'slider-min' => 100,
						'slider-max' => 2000,
						'slider-interval' => 10,
						'unit' => 'px',
						'tooltip' => 'Stelle die Breite Deiner Lightbox ein.'
					),
			'close-wrap-img-enable-crop-height' => self::wrapper('img-enable-crop-height'),
			
			'open-wrap-lightbox-open-effect' => self::wrapper('lightbox-open-effect', true),
				'heading-lightbox-open-effect' => self::header('heading-lightbox-open-effect', 'Effects'),
					'lightbox-open-effect' => array(
						'type' => 'select',
						'name' => 'lightbox-open-effect',
						'label' => 'Öffnen Effekt',
						'default' => 'elastic',
						'options' => array(
							'fade' => 'Verblassen',
							'elastic' => 'Elastisch',
							'none' => 'Keine'
						),
						'tooltip' => 'Stelle den Effekt ein, der zum Öffnen der Lightbox verwendet werden soll'
					),
					'lightbox-close-effect' => array(
						'type' => 'select',
						'name' => 'lightbox-close-effect',
						'label' => 'Schließen Effekt',
						'default' => 'elastic',
						'options' => array(
							'fade' => 'Verblassen',
							'elastic' => 'Elastisch',
							'none' => 'Keine'
						),
						'tooltip' => 'Stelle den Effekt ein, der zum Schließen der Lightbox verwendet werden soll.'
					),
					'lightbox-easingin' => array(
						'type' => 'select',
						'name' => 'lightbox-easingin',
						'label' => 'Easing In',
						'default' => 'swing',
						'options' => self::easing_effect($block),
						'tooltip' => 'Lege fest, ob der Easing In-Effekt für die Lightbox aktiviert werden soll.'
					),
					'lightbox-easingout' => array(
						'type' => 'select',
						'name' => 'lightbox-easingout',
						'label' => 'Easing Out',
						'default' => 'swing',
						'options' => self::easing_effect($block),
						'tooltip' => 'Lege fest, ob der Easing Out-Effekt für die Lightbox aktiviert werden soll.'
					),
			'close-wrap-lightbox-title-position' => self::wrapper('lightbox-title-position')
		);
		
		return $settings;
	
	}
	
	
	public static function overlay($block) {	
		
		$settings = array(
			'open-wrap-overlay-content' => self::wrapper('overlay-content', true),
				'heading-overlay-content' => self::header('heading-overlay-content', 'Overlay'),
					'overlay-content' => array(
						'type' => 'multi-select',
						'name' => 'overlay-content',
						'label' => 'Overlay Inhalt',
						'default' => array('image'),
						'options' => array(
							'title'	=> 'Titel',
							'caption' => 'Beschreibung',
							'image'	=> 'Bild'
						),
						'tooltip' => 'Lege fest, ob der Titel oder die Beschriftung der Miniaturansicht in der Hover-Überlagerung angezeigt werden soll. Das Bild wird unter Modus-> Beschriftungscontainer-> Hintergrund-> Bild eingestellt'
					),
			'close-wrap-overlay-content' => self::wrapper('overlay-content'),
			
			'open-wrap-overlay-effect' => self::wrapper('overlay-effect', true),
				'heading-overlay-effect' => self::header('heading-overlay-effect', 'Effects'),
					'overlay-effect' => array(
						'type' => 'select',
						'name' => 'overlay-effect',
						'label' => 'Effekt',
						'default' => 'bottom',
						'options' => array(
							'fade' => 'Verblassen',
							'top' => 'Von oben schieben',
							'right' => 'Von rechts schieben',
							'bottom' => 'Von unten schieben',
							'left' => 'Von links schieben'
						),
						'tooltip' => 'Stelle den Überlagerungseffekt für Deine Albumbilder ein.'
					),
					'overlay-speed' => array(
						'type' => 'slider',
						'name' => 'overlay-speed',
						'label' => 'Geschwindigkeit',
						'default' => 300,
						'slider-min' => 150,
						'slider-max' => 2000,
						'slider-interval' => 1,
						'unit' => 'ms',
						'tooltip' => 'Stelle die Geschwindigkeit ein, mit der die Hover-Überlagerung angezeigt wird.'
					),
					'overlay-easing' => array(
						'type' => 'select',
						'name' => 'overlay-easing',
						'label' => 'Easing',
						'default' => 'easeOutQuad',
						'options' => self::easing_effect($block),
						'tooltip' => 'Sollte der Beschleunigungseffekt aktiviert sein, wenn Du den Mauszeiger über ein einzelnes Bild hältst?'
					),
					'overlay-invert' => array(
						'type' => 'checkbox',
						'name' => 'overlay-invert',
						'label' => 'Effekt umkehren',
						'default' => false,
						'tooltip' => 'Lege fest, ob die Überlagerung invertiert werden soll, wenn Du mit der Maus über eines der Albumbilder fährst.'
					),
			'close-wrap-overlay-effect' => self::wrapper('overlay-effect')
		);
		
		
		return $settings;
	
	}
	
	
	public static function links($block) {
	
		$settings = array(
			'open-wrap-readon-text' => self::wrapper('readon-text', true),
				'heading-readon-text' => self::header('heading-readon-text', 'Links'),
					'readon-text' => array(
						'type' => 'text',
						'name' => 'readon-text',
						'label' => 'Readon Text',
						'default' => 'Album anzeigen…',
						'tooltip' => 'Lege den Grundtext für Deine Galerie fest.'
					),
					'enable-title-link' => array(
						'type' => 'checkbox',
						'name' => 'enable-title-link',
						'label' => 'Link Titel',
						'default' => false,
						'tooltip' => 'Lege fest, ob der Galerietitel verknüpft werden soll.'
					),
					'enable-image-link' => array(
						'type' => 'checkbox',
						'name' => 'enable-image-link',
						'label' => 'Bild-Link',
						'default' => true,
						'tooltip' => 'Lege fest, ob die Bilder verknüpft werden sollen.'
					),
					'link-target' => array(
						'type' => 'checkbox',
						'name' => 'link-target',
						'label' => 'In neuem Fenster öffnen',
						'default' => false,
						'tooltip' => 'Lege fest, ob der Read-On-Link in einem neuen Fenster geöffnet werden soll.'
					),
					'link-behaviour' => array(
						'type' => 'multi-select',
						'name' => 'link-behaviour',
						'label' => 'Verbindungsverhalten',
						'default' => array('auto', 'custom'),
						'options' => array(
							'auto' => 'Link zum Artikel',
							'custom' => 'Benutzerdefinierter Link'
						),
						'tooltip' => 'Wenn Mit Element verknüpfen ausgewählt ist, wird automatisch eine Verknüpfung mit dem Albumelement aus der Albumansicht oder mit dem Bild aus der Albumansicht hergestellt. Wenn Benutzerdefinierter Link ausgewählt ist, wird der im CMS-Album oder Bild festgelegte benutzerdefinierte Link verwendet, wenn er nicht leer gelassen wird. Wenn beide Optionen ausgewählt sind, wird der benutzerdefinierte Link verwendet, wenn er festgelegt ist, oder der Fallback auf den Elementlink, wenn der benutzerdefinierte Link leer gelassen wird.'
					),
			'close-wrap-readon-text' => self::wrapper('readon-text'),
			
			'open-wrap-link-show-title-tag' => self::wrapper('link-show-title-tag', true),
				'heading-link-show-title-tag' => self::header('heading-link-show-title-tag', 'HTML Tags'),
					'link-show-title-tag' => array(
						'type' => 'checkbox',
						'name' => 'link-show-title-tag',
						'label' => 'Titel-Tag',
						'default' => true,
						'tooltip' => 'Lege fest, ob das Link-Titel-Tag hinzugefügt werden soll.'
					),
			'close-wrap-link-show-title-tag' => self::wrapper('link-show-title-tag')
		);
		
		
		return $settings;
	
	}
	
	
	public static function filters($block) {
	
		/* we build the category option for the multiselect */		
		$category_options 	     = array();
		$categories_select_query = get_terms('gallery_categories');
		
		foreach ( $categories_select_query as $category )
			$category_options[$category->term_id] = $category->name;
		
		/* we build the tag option for the multiselect */		
		$tag_options	  = array();
		$tag_select_query = get_terms('gallery_tags');
		
		foreach ( $tag_select_query as $tag )
			$tag_options[$tag->term_id] = $tag->name;
			
		/* we build the item option for the multiselect */		
		$item_options = array();
		
		$args = array(
		    'posts_per_page' => -1,
		    'post_type' => 'upfront_gallery',
		    'post_status' => 'publish',
		    'suppress_filters' => true );
		    
		$item_select_query = get_posts( $args );
		
		foreach ( $item_select_query as $item )
			$item_options[$item->ID] = $item->post_title;
		
		$settings = array(
			'open-wrap-limit-albums' => self::wrapper('limit-album', true),
				'heading-limit-albums' => self::header('heading-limit-albums', 'Limit'),
					'enable-limit-albums' => array(
						'type' => 'checkbox',
						'name' => 'enable-limit-albums',
						'label' => 'Begrenze Alben',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-nbr-albums'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-nbr-albums'
								)
							)
						),
						'tooltip' => 'Lege fest, ob Du die Anzahl der angezeigten Albenalben begrenzen möchten.'
					),
					'nbr-albums' => array(
						'type' => 'integer',
						'name' => 'nbr-albums',
						'label' => 'Anzahl der Alben',
						'default' => 10,
						'tooltip' => 'Lege die maximale Anzahl der anzuzeigenden Alben fest.'
					),
			'close-wrap-nbr-images' => self::wrapper('nbr-images'),
			
			'open-wrap-filters-enable-categories' => self::wrapper('filters-enable-categories', true),
				'heading-filters-enable-categories' => self::header('heading-filters-enable-categories', 'Filters'),
					'filters-enable-categories' => array(
						'type' => 'checkbox',
						'name' => 'filters-enable-categories',
						'label' => 'Nach Kategorie',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-filters-categories',
									'#input-filters-categories-mode'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-filters-categories',
									'#input-filters-categories-mode'
								)
							)
						),
						'tooltip' => 'Lege fest, ob die Filterkategorie aktiviert werden soll.'
					),
					'filters-categories' => array(
						'type' => 'multi-select',
						'name' => 'filters-categories',
						'label' => 'Kategorien',
						'default' => null,
						'options' => $category_options,
						'tooltip' => 'Lege fest, welche Kategorien Du filtern möchtest.'
					),
					'filters-categories-mode' => array(
						'type' => 'select',
						'name' => 'filters-categories-mode',
						'label' => 'Kategorienmodus',
						'default' => 'include',
						'options' => array(
							'include' => 'Einschließen',
							'exclude' => 'Ausschließen'
						),
						'tooltip' => 'Lege fest, ob Du die ausgewählten Kategorien ein- oder ausschließen möchtest.'
					),
					'filters-enable-tags' => array(
						'type' => 'checkbox',
						'name' => 'filters-enable-tags',
						'label' => 'Nach Tags',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-filters-tags',
									'#input-filters-tags-mode'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-filters-tags',
									'#input-filters-tags-mode'
								)
							)
						),
						'tooltip' => 'Lege fest, ob die Filter-Tags aktiviert werden sollen.'
					),
					'filters-tags' => array(
						'type' => 'multi-select',
						'name' => 'filters-tags',
						'label' => 'Tags',
						'default' => null,
						'options' => $tag_options,
						'tooltip' => 'Lege fest, welche Tags Du filtern möchtest.'
					),
					'filters-tags-mode' => array(
						'type' => 'select',
						'name' => 'filters-tags-mode',
						'label' => 'Tags Modus',
						'default' => 'include',
						'options' => array(
							'include' => 'Einschließen',
							'exclude' => 'Ausschließen'
						),
						'tooltip' => 'Lege fest, ob Du die ausgewählten Tags ein- oder ausschließen möchtest.'
					),
					'filters-enable-wp-items' => array(
						'type' => 'checkbox',
						'name' => 'filters-enable-wp-items',
						'label' => 'Nach Alben',
						'default' => false,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-filters-wp-items',
									'#input-filters-wp-items-mode'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-filters-wp-items',
									'#input-filters-wp-items-mode'
								)
							)
						),
						'tooltip' => 'Lege fest, ob das Filteralbum aktiviert werden soll.'
					),
					'filters-wp-items' => array(
						'type' => 'multi-select',
						'name' => 'filters-wp-items',
						'label' => 'Alben',
						'default' => null,
						'options' => $item_options,
						'tooltip' => 'Lege fest, welches Album Du filtern möchtest.'
					),
					'filters-wp-items-mode' => array(
						'type' => 'select',
						'name' => 'filters-wp-items-mode',
						'label' => 'Alben-Modus',
						'default' => 'include',
						'options' => array(
							'include' => 'Einschließen',
							'exclude' => 'Ausschließen'
						),
						'tooltip' => 'Lege fest, ob Du die ausgewählten Alben ein- oder ausschließen möchtest.'
					),
			'close-wrap-enable-filters' => self::wrapper('enable-filters')
		);
		
		
		return $settings;
	
	
	}
	
	public static function ordering($block) {
	
		$settings = array(
			'open-wrap-heading-order-by' => self::wrapper('heading-order-by', true),
				'heading-order-by' => self::header('heading-order-by', 'Ordering Albums'),
					'order-by' => array(
						'type' => 'select',
						'name' => 'order-by',
						'label' => 'Sortieren nach',
						'default' => 'id',
						'options' => array(
							'date' => 'Datum',
							'title' => 'Titel',
							'id' => 'ID',
							'rand' => 'Zufall'
						),
						'tooltip' => 'Lege die Reihenfolge Deiner Alben fest.'
					),
					'order' => array(
						'type' => 'select',
						'name' => 'order',
						'label' => 'Sortierung',
						'default' => 'desc',
						'options' => array(
							'desc' => 'Absteigend',
							'asc' => 'Aufsteigend',
						),
						'tooltip' => 'Lege fest, ob die Reihenfolge absteigend oder aufsteigend sein soll.'
					),
			'close-wrap-heading-order-by' => self::wrapper('heading-order-by'),
			
			'open-wrap-heading-order-images-by' => self::wrapper('heading-order-images-by', true),
				'heading-order-images-by' => self::header('heading-order-images-by', 'Ordering Images'),
					'order-images-by' => array(
						'type' => 'select',
						'name' => 'order-images-by',
						'label' => 'Sortieren nach',
						'default' => 'entry',
						'options' => array(
							'entry' => 'Manuell',
							'title' => 'Titel',
							'id' => 'ID',
							'rand' => 'Zufall'
						),
						'tooltip' => 'Stelle die Reihenfolge Deiner Bilder ein. Wenn die Reihenfolge auf Manuell eingestellt ist, werden die Bilder in der Reihenfolge angezeigt, in der Du sie in Ihrem Album festgelegt hast.'
					),
					'order-images' => array(
						'type' => 'select',
						'name' => 'order-images',
						'label' => 'Sortierung',
						'default' => 'desc',
						'options' => array(
							'desc' => 'Absteigend',
							'asc' => 'Aufsteigend',
						),
						'tooltip' => 'Lege fest, ob die Reihenfolge absteigend oder aufsteigend sein soll.'
					),
			'close-wrap-heading-order-images-by' => self::wrapper('heading-order-images-by')
		);
		
		
		return $settings;
	
	}
	
	
	public static function content($block) {
	
		$wysiwyg = version_compare('3.2.5', UPFRONT_VERSION, '>') ? 'textarea' : 'wysiwyg';

		$settings = array(
			'open-wrap-block-before' => self::wrapper('block-before', true),
				'heading-block-before' => self::header('heading-block-before', 'Block Content'),
					'block-before' => array(
						'type' => $wysiwyg,
						'name' => 'block-before',
						'label' => 'Vor dem Block',
						'default' => '',
						'tooltip' => 'Füge hier Dein eigenes benutzerdefiniertes HTML hinzu, das vor dem Blockinhalt ausgegeben wird.'
					),
					'block-title' => array(
						'type' => 'text',
						'name' => 'block-title',
						'label' => 'Block Titel',
						'default' => '',
						'tooltip' => 'Füge einen Namen für diesen Galerieblock hinzu.'
					),
					'block-title-type' => array(
						'type' => 'select',
						'name' => 'block-title-type',
						'label' => 'Blocktiteltyp',
						'default' => 'h1',
						'options' => array(
							'h1' => 'h1',
							'h2' => 'h2',
							'h3' => 'h3',
							'h4' => 'h4',
							'h5' => 'h5'
						),
						'tooltip' => 'Wähle aus, welche Überschriftenebene für den Blocknamen verwendet werden soll.'
					),
					'block-content' => array(
						'type' => $wysiwyg,
						'name' => 'block-content',
						'label' => 'Blockbeschreibung',
						'default' => '',
						'tooltip' => 'Füge eine Beschreibung für diesen Galerieblock hinzu.'
					),
					'block-footer' => array(
						'type' => $wysiwyg,
						'name' => 'block-footer',
						'label' => 'Footer Block',
						'default' => '',
						'tooltip' => 'Füge eine Fußzeile für diesen Galerieblock hinzu.'
					),
					'block-after' => array(
						'type' => $wysiwyg,
						'name' => 'block-after',
						'label' => 'Nach dem Block',
						'default' => '',
						'tooltip' => 'Füge hier Ihr eigenes benutzerdefiniertes HTML hinzu, das nach dem Blockinhalt ausgegeben wird.'
					),
			'close-wrap-block-before' => self::wrapper('block-before'),
			
			'open-wrap-album-show-title' => self::wrapper('album-show-title', true),
				'heading-album-show-title' => self::header('heading-album-show-title', 'Album Content'),
					'album-show-title' => array(
						'type' => 'checkbox',
						'name' => 'album-show-title',
						'label' => 'Album Titel',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-album-title-type'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-album-title-type'
								)
							)
						),
						'tooltip' => 'Lege einen Titel für Deine Galerie fest.'
					),
					'album-title-type' => array(
						'type' => 'select',
						'name' => 'album-title-type',
						'label' => 'Titel-Markup',
						'default' => 'h2',
						'options' => array(
							'h1' => 'h1',
							'h2' => 'h2',
							'h3' => 'h3',
							'h4' => 'h4',
							'h5' => 'h5'
						),
						'tooltip' => 'Wähle aus, welcher Überschriftentyp für den Galerietitel verwendet werden soll.'
					),
					'album-show-description' => array(
						'type' => 'checkbox',
						'name' => 'album-show-description',
						'label' => 'Albumbeschreibung',
						'default' => true,
						'tooltip' => 'Lege fest, ob die Albumbeschreibung angezeigt werden soll.'
					),
			'close-wrap-album-show-title' => self::wrapper('album-show-title'),
			
			'open-wrap-img-show-title' => self::wrapper('img-show-title', true),
				'heading-img-show-title' => self::header('heading-img-show-title', 'Image Content'),
					'img-show-title' => array(
						'type' => 'checkbox',
						'name' => 'img-show-title',
						'label' => 'Bildtitel',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-img-title-type',
									'#input-img-title-position'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-img-title-type',
									'#input-img-title-position'
								)
							)
						),
						'tooltip' => 'Lege fest, ob der Bildtitel angezeigt werden soll.'
					),
					'img-title-type' => array(
						'type' => 'select',
						'name' => 'img-title-type',
						'label' => 'Titel-Markup',
						'default' => 'h3',
						'options' => array(
							'h1' => 'h1',
							'h2' => 'h2',
							'h3' => 'h3',
							'h4' => 'h4',
							'h5' => 'h5'
						),
						'tooltip' => 'Wähle aus, welcher Überschriftentyp für den Bildtitel verwendet werden soll.'
					),
					'img-title-position' => array(
						'type' => 'select',
						'name' => 'img-title-position',
						'label' => 'Titelposition',
						'default' => 'below-image',
						'options' => array(
							'above-image' => 'Über dem Bild',
							'below-image' => 'Unter dem Bild'
						),
						'tooltip' => 'Stelle die Position des Bildtitels ein.'
					),
					'img-title-prettifier' => array(
						'type' => 'checkbox',
						'name' => 'img-title-prettifier',
						'label' => 'Titel verschönern',
						'default' => true,
						'tooltip' => 'Lege fest, ob Du den Bildtitel verschönern möchtest. Wenn diese Option aktiviert ist, werden die ersten Buchstaben der Wörter groß geschrieben und die Striche und Punkte durch ein Leerzeichen ersetzt.'
					),
					'img-show-description' => array(
						'type' => 'checkbox',
						'name' => 'img-show-description',
						'label' => 'Bildbeschreibung',
						'default' => false,
						'tooltip' => 'Lege fest, ob die Bildbeschreibung angezeigt werden soll.'
					),
					'img-enable-title-count' => array(
						'type' => 'checkbox',
						'name' => 'img-enable-title-count',
						'label' => 'Aktiviere die Bildanzahl',
						'default' => true,
						'toggle' => array(
							'true' => array(
								'show' => array(
									'#input-img-title-count-text'
								)
							),
							'false' => array(
								'hide' => array(
									'#input-img-title-count-text'
								)
							)
						),
						'tooltip' => 'Lege fest, ob die Anzahl der Bilder angezeigt werden soll.'
					),
					'img-title-count-text' => array(
						'type' => 'text',
						'name' => 'img-title-count-text',
						'label' => 'Suffix für die Bildanzahl',
						'default' => ' images',
						
						'tooltip' => 'Stelle den Text ein, der nach der Bildanzahl angezeigt wird.'
					),
			'close-wrap-img-show-title' => self::wrapper('img-show-title'),
			
			'open-wrap-img-show-title-tag' => self::wrapper('img-show-title-tag', true),
				'heading-img-show-title-tag' => self::header('heading-img-show-title-tag', 'HTML Tags'),
					'img-show-title-tag' => array(
						'type' => 'checkbox',
						'name' => 'img-show-title-tag',
						'label' => 'Bildtitel-Tag',
						'default' => true,
						'tooltip' => 'Lege fest, ob das Bildtitel-Tag hinzugefügt werden soll.'
					),
			'close-wrap-img-show-title-tag' => self::wrapper('img-show-title-tag')
		);
				
		return $settings;
	
	}
	

}