<?php
class CdcPersonale extends Entity{
    protected static $tablename = "cdc_personale";
    
    //estrazione di tutte le affernze   
    public static function getAll($where=array(), $order=array(
                                                    array("fieldname"=>"matricola_personale", "direction"=>"ASC"),
                                                    array("fieldname"=>"percentuale", "direction"=>"DESC"),
                                                    array("fieldname"=>"codice_cdc", "direction"=>"ASC"))
                                                    ) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }
}
