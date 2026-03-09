<?php
namespace UIComponents;
class UIComponent {
    protected $id;
    private $html = "";

    public function __construct ($id) {
        $this->id = $id;        
    }

    public function prependHtml($html) {
        $this->html = $html . $this->html;
    }    

    public function appendHtml($html) {        
        $this->html .= $html;
    }
   
    //enclose = true, crea il div con id del componente
    public function getHtml($enclose = true) {
        if ($enclose == true) {
            return "<div id=".$this->id.">".$this->html."</div>";
        }
        else {
            return $this->html;
        }        
    }
}