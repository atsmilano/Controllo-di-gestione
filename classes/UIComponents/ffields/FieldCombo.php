<?php
namespace UIComponents;
class FieldCombo extends Field{
    protected $ffield;

    public function __construct($id, $label) {
        parent::__construct($id, $label);
        $this->ffield->base_type = "Number";  
        $this->ffield->extended_type = "Selection";               
    }
}