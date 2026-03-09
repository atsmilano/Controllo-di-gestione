<?php
namespace core;
class Storico extends \Entity{
    protected static $tablename;
    protected static $inizio_field = "data_inizio";
    protected static $termine_field = "data_termine";

    //restituisce tutti gli oggetti attivi in una data specifica
    //vengono passati i nomi degli attributi di data inizio e fine per l'oggetto passato
    public static function getAttiviInData(\DateTime $data_riferimento = null, $filters = array()) {
        $class_name = static::class;
        if ($data_riferimento == null) {
            $cm = \cm::getInstance();
            $data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
        }
        return \CoreHelper::getObjectsInData($class_name, $data_riferimento, $class_name::$inizio_field, $class_name::$termine_field, $filters = array());
        
    }           

    public function isAttivoInData (\DateTime $data_riferimento = null) {
        $class_name = static::class;
        if ($data_riferimento == null) {
            $cm = \cm::getInstance();
            $data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
        }
        foreach (\CoreHelper::getObjectsInData($class_name, $data_riferimento, $class_name::$inizio_field, $class_name::$termine_field, $filters = array()) as $storico) {
            if ($storico->id == $this->id) {
                return true;
            }
        }
        return false;
    }    
}