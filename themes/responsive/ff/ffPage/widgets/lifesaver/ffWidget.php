<?php

class ffWidget_lifesaver extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";

	var $class			= "ffWidget_lifesaver";

	var $widget_deps	= array();
	
	var $libraries		= array();
	
    var $js_deps		= array(
			"ff.ffPage.lifeSaver" => null
		);
    var $css_deps 		= array(
    	);
	
	// PRIVATE VARS

	var $oPage			= null;
	var $source_path	= null;
	var $style_path		= null;

	var $tpl 			= null;

	var $processed_id	= array();

	function __construct(ffPage_base $oPage = null, $source_path = null, $style_path = null)
	{
		$this->get_defaults();

		$this->oPage = array(&$oPage);

		if ($source_path !== null)
			$this->source_path = $source_path;
		elseif ($oPage !== null)
			$this->source_path = $oPage->getThemePath();

		$this->style_path = $style_path;
		
		$lifesaver = get_session("ff.lifeSaver.clear");
		if (is_array($lifesaver) && count($lifesaver))
		{
			foreach ($lifesaver as $key => $value)
			{
				$oPage->tplAddJs("ff.ffPage.lifeSaver.clear." . uniqid(), array(
					"embed" => '
							ff.pluginAddInitLoad("ff.ffPage.lifeSaver", function () {
								ff.ffPage.lifeSaver.clear("' . $key . '", "' . $value . '");
							});
						'
					, "priority" => cm::LAYOUT_PRIORITY_LOW
				));
			}
			set_session("ff.lifeSaver.clear", array());
		}
		
	}

	function prepare_template($id)
	{
	}

	function process($id, $options, ffPage_base &$oPage)
	{
		$tpl_id = $options["tpl_id"];
		if (!strlen($tpl_id))
			$tpl_id = "main";

		if (!isset($this->tpl[$tpl_id]))
			$this->prepare_template($tpl_id);
		
		$elements = "";
		foreach ($options["elements"] as $key => $value)
		{
			if (strlen($elements)) $elements .= ", ";
			
			switch ($value)
			{
				case "comp":
					$elements .= '{"comp" : "' . $key . '"}';

					if (ffIsset($oPage->components, $key) && is_subclass_of($oPage->components[$key], "ffRecord_base"))
					{
						$oPage->components[$key]->addEvent("on_done_action", function ($obj, $action) {
							switch ($action)
							{
								case "update":
								case "delete":
									$lifesaver = get_session("ff.lifeSaver.clear");
									$keys = "";
									foreach ($obj->key_fields as $value => $field)
									{
										$keys .= "_" . $obj->key_fields[$value]->value->getValue(null, FF_SYSTEM_LOCALE);
									}
									$lifesaver[$obj->id] = $keys;
									set_session("ff.lifeSaver.clear", $lifesaver);
									break;
								case "insert":
									$lifesaver = get_session("ff.lifeSaver.clear");
									$lifesaver[$obj->id] = "";
									set_session("ff.lifeSaver.clear", $lifesaver);
									break;
							}
						}, ffEvent::PRIORITY_HIGH);
					}
					break;
				case "field":
					$elements .= '{"field" : "' . $key . '"}';
					break;
			}
		}

		$oPage->tplAddJs("ff.ffPage.lifeSaver.watch." . uniqid(), array(
			"embed" => '
					ff.pluginAddInitLoad("ff.ffPage.lifeSaver", function () {
						ff.ffPage.lifeSaver.ctxAdd({
							"id" : "' . $id . '"
							, "keys" : "' . $options["keys"] . '"
							, "path" : "' . $options["path"] . '"
							, "elements" : [
								' . $elements . '
							]
						});
					});
				'
			, "priority" => cm::LAYOUT_PRIORITY_LOW
		));
		
		return;
	}

	function get_component_headers($id)
	{
	}

	function get_component_footers($id)
	{
	}

	function process_headers()
	{
	}

	function process_footers()
	{
	}
}
