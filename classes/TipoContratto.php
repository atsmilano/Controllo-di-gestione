<?php
class TipoContratto extends Entity{
    protected static $tablename = "tipo_contratto";

    //restituisce array con tutti i tipi contratto ordinati per descrizione
    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }
}
