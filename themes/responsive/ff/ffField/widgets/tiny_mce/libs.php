<?php
	return array (
		'ff' => 
		array (
			'latest' => 
			array (
				'js_defs' => 
				array (
					'ffField' => 
					array (
						'js_defs' => 
						array (
							'tiny_mce' => 
							array (
								'path' => '/themes/responsive/ff/ffField/widgets/tiny_mce',
								'file' => 'tinymce.js',
								'js_deps' => 
								array (
									'tiny_mce' => NULL
								)
							)
						)
					)
				)
			)
		),
		'tiny_mce' => 
		array (
			'default' => 'latest',
			'latest' => 
			array (
				'path' => '/themes/library/tiny_mce',
				'file' => 'tiny_mce.js',
				'priority' => CM::LAYOUT_PRIORITY_NORMAL,
				//force loading after ff.js, needed for ckeditor-basepath
				'js_deps' => array (
					'tiny_mce-basepath' => false
					//with false, avoid to make this a deps into ffJS (because of static)
				)
			)
		)
		, 'tiny_mce-basepath' => 
		array (
			// cannot be inside ckeditor, in order to avoid reference loop
			'default' => 'latest',
			'latest' => 
			array (
				'embed' => "var TINYMCE_BASEPATH = 'http://' + window.location.hostname + ff.site_path + '/themes/library/tiny_mce/';",
				'priority' => CM::LAYOUT_PRIORITY_NORMAL,
				'js_deps' => 
				array (
					'ff' => NULL
				)
			)
		)
	);