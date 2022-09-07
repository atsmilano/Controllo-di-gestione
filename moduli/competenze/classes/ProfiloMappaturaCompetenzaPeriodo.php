<?php

namespace MappaturaCompetenze;

class ProfiloMappaturaCompetenzaPeriodo extends \Entity
{
    protected static $tablename = "competenze_mappatura_competenza_periodo"; 
    
    private static $tipi_competenza = array(
        array(
            "ID" => 1,
            "descrizione" => "Trasversale",
        ),
        array(
            "ID" => 2,
            "descrizione" => "Specifica",
        ),
    );   
    
    public static function getTipiCompetenza() {
        $classname = static::class;
        return $classname::$tipi_competenza;
    }
}