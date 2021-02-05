<?php
class RapportoLavoro extends Entity{
    protected static $tablename = "rapporto_lavoro";

    //restituisce array con tutti i rapporti lavoro ordinati per descrizione
    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"ASC"))) {        
        //metodo classe entity
        return parent::getAll($where, $order);        
    }
}