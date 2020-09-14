<?php
	return array(
		"library" => array(
			"all" => array(
				"js_defs" => array(
					'codemirror' => 
					array (
						'path' => '/themes/library/codemirror/lib',
						'file' => 'codemirror.js',
						'priority' => CM::LAYOUT_PRIORITY_NORMAL,
						//force loading after ff.js, needed for ckeditor-basepath
						'css_deps' => array(
							'codemirror' => array(
								'path' => '/themes/library/codemirror/lib',
								'file' => 'codemirror.css'
							)
						),
						'js_defs' => array(
							"addons" => array(
								"empty" => true
								, "js_defs" => array(
									'search-cursor' => 
									array(
										'path' => '/themes/library/codemirror/addon/search',
										'file' => 'searchcursor.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
									),
									'search' => 
									array(
										'path' => '/themes/library/codemirror/addon/search',
										'file' => 'search.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
									),
									'javascript' => 
									array(
										'path' => '/themes/library/codemirror/mode/javascript',
										'file' => 'javascript.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
									),
									'javascript-hint' => 
									array(
										'path' => '/themes/library/codemirror/addon/hint',
										'file' => 'javascript-hint.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
									),
									'css' => 
									array(
										'path' => '/themes/library/codemirror/mode/css',
										'file' => 'css.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
									),
									'css-hint' => 
									array(
										'path' => '/themes/library/codemirror/addon/hint',
										'file' => 'css-hint.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
									),
									'html' => 
									array(
										'path' => '/themes/library/codemirror/mode/xml',
										'file' => 'xml.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
									),
									'html-hint' => 
									array(
										'path' => '/themes/library/codemirror/addon/hint',
										'file' => 'html-hint.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL,
										'js_deps' => array(
											'library.codemirror.addons.xml-hint' => null
										)
									),									
									'xml-hint' => 
									array(
										'path' => '/themes/library/codemirror/addon/hint',
										'file' => 'xml-hint.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
									),									
									'show-hint' => 
									array(
										'path' => '/themes/library/codemirror/addon/hint',
										'file' => 'show-hint.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL,
										'css_deps' => array(
											'.style' => array(
												'path' => '/themes/library/codemirror/addon/hint',
												'file' => 'show-hint.css',
											)
										)
										
									),
									'dialog' => 
									array(
										'path' => '/themes/library/codemirror/addon/dialog',
										'file' => 'dialog.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
										, 'css_deps' => 
										array(
											'dialog' => 
											array(
												'path' => '/themes/library/codemirror/addon/dialog',
												'file' => 'dialog.css'
											)

										)
									),
									'closebrackets' => 
									array(
										'path' => '/themes/library/codemirror/addon/edit',
										'file' => 'closebrackets.js',
										'priority' => CM::LAYOUT_PRIORITY_NORMAL
									)
								)
							)
						),
						'js_deps' => array (
							'.init' => array (
								'embed' => "var CODEMIRROR_BASEPATH = 'http://' + window.location.hostname + ff.site_path + '/themes/library/codemirror/';",
								'priority' => CM::LAYOUT_PRIORITY_NORMAL,
								'js_deps' => 
								array (
									'ff' => NULL
								)
							)
						),
						'js_loads' => 
						array(
							".addons.search-cursor" => null,
							".addons.search" => null,
							".addons.show-hint" => null,
							".addons.dialog" => null,
							".addons.closebrackets" => null
						)
					)
				)		
			)
		)
	);