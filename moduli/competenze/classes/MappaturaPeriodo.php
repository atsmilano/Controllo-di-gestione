<?php

namespace MappaturaCompetenze;

class MappaturaPeriodo extends \Entity
{
    protected static $tablename = "competenze_mappatura_periodo"; 
    
    private static $tipi_mappatura = array(
        array(
            "ID" => 1,
            "descrizione" => "Mappatura dall'alto",
        ),
        array(
            "ID" => 2,
            "descrizione" => "Autovalutazione",
        ),
        array(
            "ID" => 3,
            "descrizione" => "Mappatura dal basso",
        ),
        array(
            "ID" => 4,
            "descrizione" => "Mappatura tra pari",
        ),
    );
    
    public static function getTipiMappatura() {
        $classname = static::class;
        return $classname::$tipi_mappatura;
    }
}