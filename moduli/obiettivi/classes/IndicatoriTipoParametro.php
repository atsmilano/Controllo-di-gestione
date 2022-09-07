<?php
class IndicatoriTipoParametro {		
    public $id;
    public $nome;    
    
    //le tipologie di parametro vengono gestite a livello di programmazione, si sceglie quindi di definirle nella classe piuttosto che tramite db
    //costruzione dell'array delle tipologie di parametro
    private static $tipi_parametro = array(
                                        array("id" => 1, "nome" => "Testo"),
                                        array("id" => 2, "nome" => "Numero"),
                                    );
    
    public function __construct($id = null) {
        if ($id !== null) {         
            $key = array_search($id, array_column(self::$tipi_parametro, 'id'));             
            if ($key !== false) {
                $this->id = self::$tipi_parametro[$key]["id"];
                $this->nome = self::$tipi_parametro[$key]["nome"];
            }
            else {
                throw new Exception("Impossibile creare l'oggetto IndicatoriTipoParametro con ID = ".$id);
            }
        }
    }        
    
    public static function getAll() {
        $tipi_parametro = array();
        foreach (self::$tipi_parametro as $tipo_par) {
            $tipo_parametro = new IndicatoriTipoParametro;
            $tipo_parametro->id = $tipo_par["id"];
            $tipo_parametro->nome = $tipo_par["nome"];
            
            $tipi_parametro[] = $tipo_parametro;
        }                
        return $tipi_parametro;
    }
    
    //modifica l'oggetto oField per definirlo in base alla tipologia di campo e lo restituisce    
    public function configureField(ffField_html $oField) {               
        //viene visualizzato il campo in maniera differente a seconda della tipologia specificata
        switch ($this->id) {
            case 1:                
                $oField->base_type = "Number";                
            break;
            case 2:
                $oField->base_type = "Text";                
            break;
        }                                                                
        
        return $oField;
    }
}