<?php
return array(
	"jquery" => array(
		"all" => array(
			"js_defs" => array(
				"plugins" => array(
					"empty" => true,
					"js_defs" => array(
						"colorpicker" => array(
							"path" => FF_THEME_DIR . "/library/plugins/jquery.colorpicker",
							"file" => "jquery.colorpicker.js",
							"css_loads" => array(
								".style" => array(
									"path" => FF_THEME_DIR . "/library/plugins/jquery.colorpicker/css",
									"file" => "colorpicker.css"
								)
							)
						)
					)
				)
			)
		)
	)
);
