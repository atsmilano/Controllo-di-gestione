<?php
class ProgettiProgettoFaseTempoRealizzazione {
    public $id;
    public $id_progetto;
    public $data_inizio_fase;
    public $data_fine_fase;
    public $descrizione_fase;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null) {
        if ($id != null) {
            $db = ffDb_Sql::factory();
            $sql = "
                SELECT *
                FROM progetti_progetto_fase_tempo_realizzazione ppftr
                WHERE ppftr.ID = ".$db->toSql($id)."
            ";
            
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->id_progetto = $db->getField("ID_progetto", "Number", true);
                $this->data_inizio_fase = CoreHelper::getDateValueFromDB($db->getField("data_inizio_fase", "Date", true));
                $this->data_fine_fase = CoreHelper::getDateValueFromDB($db->getField("data_fine_fase", "Date", true));
                $this->descrizione_fase = $db->getField("descrizione_fase", "Text", true);
                $this->extend = $db->getField("extend", "Text", true);
                $this->time_modifica = $db->getField("time_modifica", "Date", true);
                $this->record_attivo = $db->getField("record_attivo", "Number", true);
            }
            else {
                throw new Exception("Impossibile creare l'oggetto ProgettiProgettoFaseTempoRealizzazione con ID = " . $id);
            }
        }
    }

    public static function getAll($filters = array()) {
        $results_list = array();
        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= "AND ". $field ." = ". $db->toSql($value);
        }
        $sql = "
            SELECT ppftr.*
            FROM progetti_progetto_fase_tempo_realizzazione ppftr
            " . $where . "
            ORDER BY ppftr.ID
        ";

        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $progetto_fase_tempo_realizzazione = new ProgettiProgettoFaseTempoRealizzazione();

                $progetto_fase_tempo_realizzazione->id = $db->getField("ID", "Number", true);
                $progetto_fase_tempo_realizzazione->id_progetto = $db->getField("ID_progetto", "Number", true);
                $progetto_fase_tempo_realizzazione->data_inizio_fase = CoreHelper::getDateValueFromDB($db->getField("data_inizio_fase", "Date", true));
                $progetto_fase_tempo_realizzazione->data_fine_fase = CoreHelper::getDateValueFromDB($db->getField("data_fine_fase", "Date", true));
                $progetto_fase_tempo_realizzazione->descrizione_fase = $db->getField("descrizione_fase", "Text", true);
                $progetto_fase_tempo_realizzazione->extend = $db->getField("extend", "Text", true);
                $progetto_fase_tempo_realizzazione->time_modifica = $db->getField("time_modifica", "Date", true);
                $progetto_fase_tempo_realizzazione->record_attivo = $db->getField("record_attivo", "Number", true);

                $results_list[] = $progetto_fase_tempo_realizzazione;
            } while($db->nextRecord());
        }
        return $results_list;
    }    
}