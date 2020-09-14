<?php
return array(
	"jquery" => array(
		"all" => array(
			"js_defs" => array(
				"plugins" => array(
					"empty" => true,
					"js_defs" => array(
						"printelement" => array(
							"path" => FF_THEME_DIR . "/library/plugins/jquery.printelement",
							"file" => "jquery.printelement.js"
							, "js_defs" => array(
								"observe" => array(
									"path" => FF_THEME_DIR . "/library/plugins/jquery.printelement"
									, "file" => "jquery.printelement.observe.js"
								)
							)
						)
					)
				)
			)
		)
	)
);
