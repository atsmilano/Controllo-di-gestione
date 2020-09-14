<?php
	return array (
		'jquery' => 
		array (
			'all' => 
			array (
				'js_defs' => 
				array (
					'image-picker' => 
					array (
						'path' => '/themes/library/plugins/jquery.image-picker',
						'file' => 'jquery.image-picker.min.js',
						'index' => 200,
						'css_deps' => 
						array (
							'image-picker' => 
							array (
								'path' => '/themes/library/plugins/jquery.image-picker',
								'file' => 'jquery.image-picker.css'
							)
						)
					)
				)
			)
		),	
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
							'imagepicker' => 
							array (
								'path' => '/themes/responsive/ff/ffField/widgets/imagepicker',
								'file' => 'imagepicker.js',
								'js_deps' => 
								array (
									'jquery.image-picker' => NULL
								)
							)
						)
					)
				)
			)
		)
	);