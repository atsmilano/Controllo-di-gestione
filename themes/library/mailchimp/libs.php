<?php
	return array(
		"library" => array(
			"all" => array(
				"js_defs" => array(
					'mailchimp' => array (
						"empty" => true
						, 'js_defs' => array (
							'validator' => array(
								'path' => '//s3.amazonaws.com/downloads.mailchimp.com/js',
								'file' => 'mc-validate.js'
							)
							, "subscribe" => array(
								"path" => "/themes/library/mailchimp"
								, "file" => "validator.subscribe.js"
								, "js_deps" => array(
									"library.mailchimp.validator" => null
								)
							)
						)
					)	
				)		
			)
		)
	);

