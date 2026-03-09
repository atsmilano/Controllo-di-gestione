<?php
namespace UIComponents;
class FieldText extends Field{
    protected $ffield;

    public function __construct($id, $label) {
        parent::__construct($id, $label);
        $this->ffield->base_type = "Text";                
    }
}