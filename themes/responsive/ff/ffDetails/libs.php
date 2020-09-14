<?php
	return array(
		"ff" => array(
			"latest" => array(
				"js_defs" => array(
					"ffDetails" => array(
						"path" => "/themes/responsive/ff/ffDetails"
						, "file" => "ff.ffDetails.js"
						, "index" => 100
						, "css_deps" => array(
							"ff.core" => null
						),
						"js_defs" => array(
							"sortable" => array(
								"path" => "/themes/responsive/ff/ffDetails"
								, "file" => "ff.ffDetails.sortable.js"
								, "index" => 100
								, "js_deps" => array(
								    "jquery.ui" => null
									, "ff.ajax" => null
								)
							)
						)
					)
				)
			)
		)
	);
