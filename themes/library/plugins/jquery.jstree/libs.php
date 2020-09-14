<?php
return array(
	"jquery" => array(
		"all" => array(
			"js_defs" => array(
				"plugins" => array(
					"empty" => true,
					"js_defs" => array(
						"jstree" => array(
							"path" => FF_THEME_DIR . "/themes/library/plugins/jquery.jstree",
							"file" => "jquery.jstree.js"
							/*, "css_loads" => array(
								".style" => array(
									"path" => FF_THEME_DIR . "/library/plugins/jquery.jstree/themes/default",
									"file" => "style.css"
								)
							)*/
							, "js_deps" => array(
								"jquery.plugins.cookie" => null
								, "jquery.plugins.hotkeys" => null
							)
						)
					)
				)
			)
		)
	)
);
