<?php
return array(
	"jquery" => array(
		"all" => array(
			"js_defs" => array(
				"plugins" => array(
					"empty" => true,
					"js_defs" => array(
						"hui" => array(
							"path" => FF_THEME_DIR . "/library/plugins/jquery.hui",
							"file" => "hui.js",
							"css_loads" => array(
								".style" => array(
									"path" => FF_THEME_DIR . "/library/plugins/jquery.hui",
									"file" => "hui.css",
								)
							),
                            "js_defs" => array(
                                "modal" => array(
                                    "path" => FF_THEME_DIR . "/library/plugins/jquery.hui/modal",
                                    "file" => "hui.modal.js",
                                    "css_deps" => array(
                                        ".style" => array(
                                            "path" => FF_THEME_DIR . "/library/plugins/jquery.hui/modal",
                                            "file" => "hui.modal.css",
                                        )
                                    )
                                )
                            )
						)
					)
				)
			)
		)
	)
);
