<?php
namespace UIComponents;
class FieldDate extends Field{
    protected $ffield;

    public function __construct($id, $label) {
        parent::__construct($id, $label);
        $this->ffield->base_type = "Date";  
        $this->ffield->widget = "datepicker";              
    }
}