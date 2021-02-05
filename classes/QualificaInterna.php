<?php
class QualificaInterna extends Entity{
    protected static $tablename = "qualifica_interna";

    //restituisce array con tutte le qualifiche interne ordinati per descrizione
    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"ASC"))) {                        
        //metodo classe entity
        return parent::getAll($where, $order);        
    }
}