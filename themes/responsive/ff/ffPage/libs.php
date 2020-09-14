<?php
return array(
	"ff" => array(
		"default" => "latest"
		, "latest" => array(
			"path" => "/themes/library/ff"
			, "file" => "ff.js"
			, "index" => 100
			, "async" => false
			, "css_defs" => array(
				"core" => array(
					"path" => "/themes/responsive/css"
					, "file" => "ff.css"
					, "priority" => cm::LAYOUT_PRIORITY_HIGH
					, "index" => 150
					, "css_loads" => array(
						".skin" => array(
							"path" => "/themes/responsive/css"
							, "file" => "ff-skin.css"
						)
					)
				)
                , "theme" => array(
                    "path" => "/modules/restricted/themes/responsive/css"
                    , "file" => "ff.modules.restricted.css"
                    , "priority" => cm::LAYOUT_PRIORITY_HIGH
                    , "index" => 100
                )
			)
			, "js_deps" => array(
				"jquery" => null
			)
			, "js_loads" => array(
				"ff.ffEvent" => null
				, "ff.ffEvents" => null
			)
			, "js_defs" => array(
				"ffEvent" => array(
					"path" => "/themes/library/ff"
					, "file" => "ffEvent.js"
					, "index" => 100
					, "async" => false
				)
				, "ffEvents" => array(
					"path" => "/themes/library/ff"
					, "file" => "ffEvents.js"
					, "index" => 100
					, "async" => false
				)
				, "history" => array(
					"path" => "/themes/library/ff"
					, "file" => "history.js"
					, "index" => 100
				)
				, "ajax" => array(
					"path" => "/themes/library/ff"
					, "file" => "ajax.js"
					, "index" => 100
					, "js_loads" => array(
						".defaults" => array(
							"embed" => 'ff.pluginAddInit("ff.ajax", function () {ff.ajax.defaults.display = false;});'
						)
					)
				)
				, "ffPage" => array(
					"path" => "/themes/responsive/ff/ffPage"
					, "file" => "ffPage.js"
					, "index" => 100
				)
			)
		)
	)
	, "jquery" => array(
		"default" => "1.11.2"
		, "1.11.2" => array(
			"path" => "/themes/library/jquery"
			, "file" => null
			, "index" => 200
			, "async" => false
			, "js_defs" => array(
				"plugins" => array(
					"empty" => true
				)
			)
		)
	)
	, "jquery-ui" => array(
		"default" => "1.11.3"
		, "1.11.3" => array(
			"path" => "/themes/library/jquery-ui"
			, "file" => null
			, "index" => 200
			, "js_deps" => array(
				"jquery" => null
			)
			, "js_defs" => array(
			)
			, "css_loads" => array(
				"jquery-ui.core" => null,
				"ff-jqueryui" => array(
					"path" => "/themes/responsive/css"
					, "file" => "ff-jqueryui.css"
				)
			)
			, "css_defs" => array(
				"accordion" => array(
					"file" => "base/accordion.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "autocomplete" => array(
					"file" => "base/autocomplete.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "button" => array(
					"file" => "base/button.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "core" => array(
					"file" => "base/core.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "datepicker" => array(
					"file" => "base/datepicker.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "dialog" => array(
					"file" => "base/dialog.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "draggable" => array(
					"file" => "base/draggable.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "menu" => array(
					"file" => "base/menu.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "progressbar" => array(
					"file" => "base/progressbar.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "resizable" => array(
					"file" => "base/resizable.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "selectable" => array(
					"file" => "base/selectable.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "selectmenu" => array(
					"file" => "base/selectmenu.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "slider" => array(
					"file" => "base/slider.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "sortable" => array(
					"file" => "base/sortable.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "spinner" => array(
					"file" => "base/spinner.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "tabs" => array(
					"file" => "base/tabs.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
				, "tooltip" => array(
					"file" => "base/tooltip.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "index" => 200
				)
			)
		)
	)
	, "fonticons" => array(
		"default" => "latest"
		, "latest" => array(
			"empty" => true
			, "css_defs" => array(
				"fontawesome" => array(
					"path" => "https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css"
					, "file" => "font-awesome.min.css"
					, "index" => 175
                    , "css_loads" => array(
                        ".ff" => array(
                            "path" => FF_THEME_DIR . "/" . CM_LOADED_THEME . "/css"
                            , "file" => "ff-fontawesome.css"
                        )
                    )
				)
				, "glyphicons" => array(
					"path" => "/themes/responsive/css"
					, "file" => "bootstrap-glyphicons.min.css"
					, "index" => 175

				)
			)
		)
	)
	, "bootstrap" => array(
		"default" => "latest"
		, "latest" => array(
			"empty" => true
			, "js_defs" => array(
				"core" => array(
					"path" => "https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/js"
					, "file" => "bootstrap.min.js"
					, "index" => 150
					, "js_deps" => array(
						"jquery" => null
					)
				)
			)
			, "css_defs" => array(
				"core" => array(
					"path" => "https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/css"
					, "file" => "bootstrap.min.css"
					, "index" => 150
					, "js_loads" => array(
						"bootstrap.core" => null
					)
					, "css_deps" => array(
						//"fonticons.fontawesome" => null
					)
					, "css_loads" => array(
						".ff" => array(
							"path" => FF_THEME_DIR . "/" . CM_LOADED_THEME . "/css"
							, "file" => "ff-bootstrap.css"
						)
					)
				)
			)
		)
	)
	, "foundation" => array(
		"default" => "latest"
		, "latest" => array(
			"empty" => true
			, "js_defs" => array(
				"core" => array(
					"path" => "https://cdn.jsdelivr.net/npm/foundation-sites@6.4.3/dist/js"
					, "file" => "foundation.min.js"
					, "index" => 150
					, "js_deps" => array(
						"jquery" => null
					)
					, "js_loads" => array(
						".init" => array(
							"embed" => "
								jQuery(function() {
									jQuery(document).foundation();
								});
							"
						)
					)
				)
			)
			, "css_defs" => array(
				"core" => array(
					"path" => "https://cdn.jsdelivr.net/npm/foundation-sites@6.4.3/dist/css"
					, "file" => "foundation.min.css"
					, "index" => 150
					, "js_loads" => array(
						"foundation.core" => null
					)
					, "css_deps" => array(
						//"fonticons.fontawesome" => null
					)
					, "css_loads" => array(
						".ff" =>  array(
							"path" => FF_THEME_DIR . "/" . CM_LOADED_THEME . "/css"
							, "file" => "ff-foundation.css"
						)
					)
				)
			)
		)
	)
	, "google" => array(
		"default" => "latest"
		, "latest" => array(
			"empty" => true
			, "js_defs" => array(
				"adsense" => array(
					"path" => "//pagead2.googlesyndication.com/pagead/js"
					, "file" => "adsbygoogle.js"
					, "priority" => cm::LAYOUT_PRIORITY_LOW
					, "js_loads" => array(
						".push" => array(
							"embed" => "
									jQuery(function() {
										var adsbygoogle = window.adsbygoogle || [];
										jQuery(\"ins.adsbygoogle\").each(function(){
											if (jQuery(this).attr(\"data-adsbygoogle-status\") !== \"done\") {
												adsbygoogle.push({});
											}
										});
									});
								"
						)
					)
				)
				, "jsapi" => array(
					"empty" => true
					, "js_defs" => array(
						"async" => array(
							"path" => "https://www.google.com"
							, "file" => "jsapi?callback=gloadinitcall"
							, "priority" => cm::LAYOUT_PRIORITY_LOW
							, "js_deps" => array(
								".initcall" => array(
									"embed" => "
											var gloadinit = [];
											function gloadinitcall(callback) {
												if (callback === undefined) {
													gloadinit.forEach(function(entry) {
														entry();
													});
													gloadinit = false;
												} else {
													if (gloadinit === false)
														callback();
													else
														gloadinit.push(callback);
												}
											}
										"
									, "exclude_compact" => true
								)	
							)
						)
						, "sync" => array(
							"path" => "https://www.google.com"
							, "file" => "jsapi"
							, "priority" => cm::LAYOUT_PRIORITY_LOW
							, "js_deps" => array(
								".initcall" => array(
									"embed" => "
											var gloadinit = [];
											function gloadinitcall(callback) {
												jQuery(window).ready(function(){callback();});
											}
										"
								)
							)
						)
					)
				)
				, "maps" => array(
					"empty" => true
					, "js_defs" => array(
						"async" => array(
							"path" => "https://maps.googleapis.com/maps/api"
							, "file" => "js?libraries=places&callback=gmapsinitcall"
							, "priority" => cm::LAYOUT_PRIORITY_LOW
							, "js_deps" => array(
								".initcall" => array(
									"embed" => "
											var gmapsinit = [];
											function gmapsinitcall(callback) {
												if (callback === undefined) {
													gmapsinit.forEach(function(entry) {
														entry();
													});
													gmapsinit = false;
												} else {
													if (gmapsinit === false)
														callback();
													else
														gmapsinit.push(callback);
												}
											}
										"
									, "exclude_compact" => true
								)
							)
						)
						, "sync" => array(
							"path" => "https://maps.googleapis.com/maps/api"
							, "file" => "js?libraries=places"
							, "priority" => cm::LAYOUT_PRIORITY_LOW
							, "js_deps" => array(
								".initcall" => array(
									"embed" => "
											var gmapsinit = [];
											function gmapsinitcall(callback) {
												jQuery(window).ready(function(){callback();});
											}
										"
								)
							)
						)
						, "markerclusterer" => array(
							"path" => FF_THEME_DIR . "/library/plugins/gmap3.markerclusterer",
							"file" => "markerclusterer.js"
						)
					)
				)
			)
		)
	)
	, "facebook" => array(
		"default" => "latest",
			"latest" => array(
				"path" => "http://connect.facebook.net/" . strtolower(substr(FF_LOCALE, 0, 2)) . "_" . strtoupper(substr(FF_LOCALE, 0, 2))
				, "file" => "all.js"
			)
	)
	, "library" => array(
		"default" => "latest",
		"latest" => array(
			"empty" => true
		)
	)
);