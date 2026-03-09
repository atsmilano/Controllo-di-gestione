<?php
namespace UIComponents;
class Tab extends UIComponent{
    public function __construct($tab_id="tab", $tab_name="tab", $tab_link="#", $tab_params="", $hide_ret_url=false) {
        parent::__construct($tab_id);
               
        if ($hide_ret_url == true) {
            $ret_url = "";
        }
        else {                
            $ret_url = "ret_url=".urlencode($tab_link."?".$tab_params);
        }
        $this->appendHtml("<li><a id=".$this->id." href=".$tab_link.
        "?". $tab_params.(strlen($tab_params)?(substr($tab_params, -1)=="&"?"":"&"):"").$ret_url.">".$tab_name."</a></li>");
    }   

    public function getHtml() {               
        return parent::getHtml(false);     
    }
}