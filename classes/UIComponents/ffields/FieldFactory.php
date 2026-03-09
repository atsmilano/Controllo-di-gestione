<?php
namespace UIComponents;

class FieldFactory
{
    public static function create ($id, $label, $type="Text") {        
        switch ($type) {
            case "Text":
                $field = new FieldText($id, $label); 
                break;
            case "Date":
                $field = new FieldDate($id, $label); 
                break;
            case "Combo":
                $field = new FieldCombo($id, $label); 
                break;
            case "Radio":
                $field = new FieldRadio($id, $label); 
                break;
            default:
                throw new \Exception("Tipo campo sconosciuto");
        }
        return  $field->getFField();
    }
}