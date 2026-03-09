<?php
namespace UIComponents;
class FieldRadio extends Field{
    protected $ffield;

    public function __construct($id, $label) {
        parent::__construct($id, $label);      
        $this->ffield->base_type = "Number";
        $this->ffield->extended_type = "Selection";
        $this->ffield->control_type = "radio";    
        $this->ffield->multi_pairs = array(
            array(new \ffData("1", "Number"), new \ffData("Si", "Text")),
            array(new \ffData("0", "Number"), new \ffData("No", "Text")),
        );           
    }
}