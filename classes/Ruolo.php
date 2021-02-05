<?php
class Ruolo extends Entity{
    protected static $tablename = "ruolo";

    //restituisce array con tutti i ruoli ordinati per descrizione
    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }
}
