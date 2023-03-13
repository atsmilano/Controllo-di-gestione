<?php
class TipoPianoCdr extends Entity{	
    protected static $tablename = "tipo_piano_cdr";
    protected static $relations = array(
                                    "relation" => array("target_class" => "PianoCdr",
                                                        "keys" => array(
                                                                        "ID_tipo_piano_cdr" => "ID",
                                                                        ),  
                                                        "allow_delete" => false,
                                                        "propagate_delete" => true,
                                                )
                                );
    
    //restituisce il tipo piano con priorità più alta
    public static function getPrioritaMassima (){
        $calling_class = static::class;
        $tipi_piano = $calling_class::getAll(array(), array(array("fieldname"=>"priorita", "direction"=>"ASC")));
        if (count($tipi_piano) > 0){
            return $tipi_piano[0];
        }       
        else {
            return null;
        }
    }
}