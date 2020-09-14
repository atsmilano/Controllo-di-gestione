<?php

class ffWidget_dialog extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";

	var $class			= "ffWidget_dialog";

	var $widget_deps	= array();
	
	var $libraries		= array();
	
    var $js_deps		= array();
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
	}

	function prepare_template($id)
	{
		$this->tpl[$id] = ffTemplate::factory(__DIR__);
		$this->tpl[$id]->load_file($this->template_file, "main");

		$this->tpl[$id]->set_var("site_path", $this->oPage[0]->site_path);

		$this->tpl[$id]->set_var("source_path", $this->source_path);

        if ($this->style_path !== null)
			$this->tpl[$id]->set_var("style_path", $this->style_path);
		elseif ($this->oPage !== null)
			$this->tpl[$id]->set_var("style_path", $this->oPage[0]->getThemePath());
	}

	function process($id, $options, ffPage_base &$oPage)
	{
		$tpl_id = $options["tpl_id"];
		if (!strlen($tpl_id))
			$tpl_id = "main";

		if (!isset($this->tpl[$tpl_id]))
			$this->prepare_template($tpl_id);

		$this->tpl[$tpl_id]->set_var("id", $id);
		$this->tpl[$tpl_id]->set_var("name", $options["name"]);
		
		$framework_css = cm_getFrameworkCss();
		
		if (array_key_exists("id", $options))
			$this->tpl[$tpl_id]->set_var("id_tag", ' id="' . $options["id"] . '"');
		
		if (array_key_exists("addjs", $options))
			$this->tpl[$tpl_id]->set_var("addjs", $options["addjs"]);

		if($options["type"] == "jqueryui")
			$type = false;
		elseif($options["type"] == "tabs")
			$type = $options["type"];
		else
			$type = $framework_css["name"];			

		if($type) { //da fare le varie opzioni nella dialog
			$this->oPage[0]->tplAddJs("ff.ffPage.dialog-" . $type);
		} else {
			$this->oPage[0]->tplAddJs("ff.ffPage.dialog");
			$this->oPage[0]->tplAddCss("jquery-ui.theme");
			$this->oPage[0]->tplAddCss("jquery-ui.button");
			$this->oPage[0]->tplAddCss("jquery-ui.dialog");
			$this->oPage[0]->tplAddCss("jquery-ui.resizable");
			
			if (!(!ffIsset($options, "modal") || $options["modal"] === true))
				$parseParams["modal"] = '"modal" : true';

			if ($options["resizable"] === false) 
				$parseParams["resizable"] = '"resizable" : false';
			else
				$parseParams["resizable"] = '"resizable" : true';
			
			if (is_array($options["position"])) {
                $strPosition = "";
				foreach($options["position"] AS $position_value) {
					if(strlen($strPosition))
						$strPosition .= ",";
					$strPosition .= '' . $position_value . '';
				}
				$parseParams["position"] = '"position" : [' . $strPosition . ']';
			} elseif(strlen($options["position"])) {
				$parseParams["position"] = '"position" : "' . $options["position"] . '"';
			} else {
				$parseParams["position"] = '"position" : "center"';
			}
				
			if ($options["draggable"] === false)
				$parseParams["draggable"] = '"draggable" : false';
			else
				$parseParams["draggable"] = '"draggable" : true';

			if ($options["height"])
				$parseParams["height"] = '"height" : ' . $options["height"];
		}
		
		if($framework_css["name"]) {
			if(!is_numeric($options["width"])) {
				$options["dialogClass"] = cm_getClassByFrameworkCss("window-" . $options["width"], "dialog", $options["dialogClass"]);
				$options["width"] = "";
			} else {
				$options["dialogClass"] = cm_getClassByFrameworkCss("window", "dialog", $options["dialogClass"]);		
			}
		}
		if($options["callback"]) 
			$parseParams["callback"] = '"callback" : "' . $options["callback"] . '"';

		if($options["url"]) 
			$parseParams["url"] = '"url" : "' . $options["url"] . '"';

		if($options["doredirects"]) 
			$parseParams["doredirects"] = '"doredirects" : true';

		if($options["title"]) 
			$parseParams["title"] = '"title" : "' . $options["title"] . '"';

		if ($options["width"])
			$parseParams["width"] = '"width" : ' . $options["width"];
			
		if($options["dialogClass"]) {
			$parseParams["class"] = '"dialogClass" : "' . $options["dialogClass"] . '"';
		}
		
		$this->tpl[$tpl_id]->set_var("class", $options["class"]);
		
		if ($options["unique"])
			$parseParams["unique"] = '"unique" : true';

		if (is_array($options["params"]) && count($options["params"]))
		{
			$this->tpl[$tpl_id]->set_var("SectParam", "");
			$i = 0;
			foreach ($options["params"] as $key => $value)
			{
				$this->tpl[$tpl_id]->set_var("param_name", $key);
				$this->tpl[$tpl_id]->set_var("param_value", $value);
				$i++;
				if ($i < count($options["params"]))
					$this->tpl[$tpl_id]->set_var("param_colon", ",");
				else
					$this->tpl[$tpl_id]->set_var("param_colon", "");
				$this->tpl[$tpl_id]->parse("SectParam", true);
			}
			$this->tpl[$tpl_id]->parse("SectParams", false);
		}

		if(is_array($parseParams) && count($parseParams))
			$this->tpl[$tpl_id]->set_var("parse_params", "," . implode(",", $parseParams));

		if (!isset($this->processed_id[$id]))
		{
			$this->processed_id[$id] = true;
			$this->tpl[$tpl_id]->parse("SectIstance", true);
		}
		
		return $this->tpl[$tpl_id]->rpparse("SectControl", false);
	}

	function get_component_headers($id)
	{
		if (!isset($this->tpl[$id]))
			return;

		return $this->tpl[$id]->rpparse("SectHeaders", false);
	}

	function get_component_footers($id)
	{
		if (!isset($this->tpl[$id]))
			return;

		return $this->tpl[$id]->rpparse("SectFooters", false);
	}

	function process_headers()
	{
		if (!isset($this->tpl["main"]))
			return;

		return $this->tpl["main"]->rpparse("SectHeaders", false);
	}

	function process_footers()
	{
		if (!isset($this->tpl["main"]))
			return;

		return $this->tpl["main"]->rpparse("SectFooters", false);
	}
}
