<?php
namespace UIComponents;
class Field extends UIComponent{
    protected $ffield;

    public function __construct($id, $label) {
        parent::__construct("field-".$id);
                
        $cm = \cm::getInstance();
        $this->ffield = \ffField::factory($cm->oPage);
        $this->ffield->id = $id;
        $this->ffield->label = $label;                        
    }

    public function getFField() {        
        return $this->ffield;     
    }
}