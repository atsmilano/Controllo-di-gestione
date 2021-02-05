<?php
class TipoPianoCdr extends Entity{	
    protected static $tablename = "tipo_piano_cdr";
            
    //restituisce array con tutti i tipi di piano cdr ordinati per priorità
    public static function getAll($where=array(), $order=array(array("fieldname"=>"priorita", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    } 
    
    //restituisce il tipo piano con priorità più alta
    public static function getPrioritaMassima (){
        $calling_class = static::class;
        $tipi_piano = $calling_class::getAll();
        if (count($tipi_piano) > 0){
            return $tipi_piano[0];
        }       
        else {
            return null;
        }
    }
    
    //restituisce true se l'oggetto può essere eliminato (nessuna relazione vincolante)
    public function canDelete() {
        $piani_cdr_tipo = PianoCdr::getAll(array("ID_tipo_piano_cdr" => $this->id));
        return empty($piani_cdr_tipo);
    }
}