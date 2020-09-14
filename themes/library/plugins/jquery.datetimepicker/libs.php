<?php
return array(
	"jquery" => array(
		"all" => array(
			"js_defs" => array(
				"plugins" => array(
					"empty" => true,
					"js_defs" => array(
						"datetimepicker" => array(
							"type" => "slider",
							"path" => FF_THEME_DIR . "/library/plugins/jquery.datetimepicker",
							"file" => "jquery.datetimepicker.full.js",
							"css_loads" => array(
								".style" => array(
									"path" => FF_THEME_DIR . "/library/plugins/jquery.datetimepicker",
									"file" => "jquery.datetimepicker.css"
								)
							)
						)
					)
				)
			)
		)
	)
);
