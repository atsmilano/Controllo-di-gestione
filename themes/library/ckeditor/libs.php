<?php
	return array(
		"library" => array(
			"all" => array(
				"js_defs" => array(
					'ckeditor' => 
					array (
						'path' => '/themes/library/ckeditor',
						'file' => 'ckeditor.js',
						'priority' => CM::LAYOUT_PRIORITY_NORMAL,
						//force loading after ff.js, needed for ckeditor-basepath
						'js_defs' => 
						array (
							'adapters-jquery' => 
							array (
								'path' => '/themes/library/ckeditor',
								'file' => 'adapters/jquery.js',
								'priority' => CM::LAYOUT_PRIORITY_NORMAL,
							),
						),
						'js_deps' => 
						array (
							'.init' => array (
								'embed' => "var CKEDITOR_BASEPATH = document.location.protocol + '//' + window.location.hostname + ff.base_path + '/themes/library/ckeditor/';",
								'priority' => CM::LAYOUT_PRIORITY_NORMAL,
								'js_deps' => 
								array (
									'ff' => NULL
								)
							)
						)
					)
				)		
			)
		)
	);
