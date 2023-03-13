<?php

namespace MappaturaCompetenze;

class MappaturaPeriodo extends \Entity
{
    protected static $tablename = "competenze_mappatura_periodo"; 
    
    private static $tipi_mappatura = array(
        array(
            "ID" => 1,
            "descrizione" => "Mappatura responsabile",            
            "chart_color" => "0, 153, 51",
        ),
        array(
            "ID" => 2,
            "descrizione" => "Autovalutazione",
            "chart_color" => "255, 99, 132",
        ),
        array(
            "ID" => 3,
            "descrizione" => "Mappatura da collaboratori",
            "chart_color" => "255, 128, 0",
        ),
        array(
            "ID" => 4,
            "descrizione" => "Mappatura tra pari",
            "chart_color" => "153, 51, 255",
        ),
    );
    
    public static function getTipiMappatura() {
        $classname = static::class;
        return $classname::$tipi_mappatura;
    }
    
    public static function getTipoMappaturaFromId($id) {
        $classname = static::class;
        return $classname::$tipi_mappatura[array_search($id, array_column($classname::$tipi_mappatura, 'ID'))];
    }
    
    public function visualizzabileUtente ($ruoli, $data_visualizzazione_valida) {
        $show_mappatura = false;                
        
        switch ($this->id_tipo_mappatura){
            //valori attesi sempre visibili
            case 0:                                    
                $show_mappatura = true;
            break;
            //responsabile
            case 1:
                if ($ruoli["amministratore"] || $ruoli["valutatore_responsabile"]) {
                    $show_mappatura = true;
                }
                else if ($ruoli["valutato"] && $data_visualizzazione_valida){
                    $show_mappatura = true;
                }                    
            break;
            //autovalutazione
            case 2:
                if ($ruoli["amministratore"] == true || $ruoli["valutatore_responsabile"] || $ruoli["valutato"]) {
                    $show_mappatura = true;
                }                
            break;
            //collaboratori
            case 3:
                if ($ruoli["amministratore"] == true || $ruoli["valutatore_collaboratore"] == true) {
                    $show_mappatura = true;
                }
                else if ($data_visualizzazione_valida){
                    if ($ruoli["valutatore_responsabile"] || $ruoli["valutato"]) {
                        $show_mappatura = true;
                    }
                }
            break;
            //pari
            case 4:
                if ($ruoli["amministratore"] == true || $ruoli["valutatore_pari"] == true) {
                    $show_mappatura = true;
                }
                else if ($data_visualizzazione_valida){
                    if ($ruoli["valutatore_responsabile"] || $ruoli["valutato"]) {
                        $show_mappatura = true;
                    }
                }
            break;
            default:
            break;
        }
        return $show_mappatura;
    }
}