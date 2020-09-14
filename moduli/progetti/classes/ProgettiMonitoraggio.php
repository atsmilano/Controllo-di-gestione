<?php
class ProgettiMonitoraggio {
    public $id;
    public $id_progetto;
    public $numero_monitoraggio;
    public $id_tipologia_monitoraggio;
    public $descrizione_fase;
    public $costi_sostenuti;
    public $descrizione_utilizzo_risorse;
    public $note_rispetto_risorse_previste;
    public $note_rispetto_tempistiche;
    public $note_replicabilita_progetto;
    public $extend;
    public $time_modifica;
    public $record_attivo;
    
    public function __construct($id = null) {        
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT * 
            FROM progetti_monitoraggio pm 
            WHERE pm.ID = ".$db->toSql($id);        
        $db->query($sql);                
        if ($db->nextRecord()){
            $this->id = $db->getField("ID", "Number", true);
            $this->id_progetto = $db->getField("ID_progetto", "Number", true);
            $this->numero_monitoraggio = $db->getField("numero_monitoraggio", "Number", true);
            $this->id_tipologia_monitoraggio = $db->getField("ID_tipologia_monitoraggio", "Number", true);
            $this->descrizione_fase = $db->getField("descrizione_fase", "Text", true);
            $this->costi_sostenuti = $db->getField("costi_sostenuti", "Number", true);
            $this->descrizione_utilizzo_risorse = $db->getField("descrizione_utilizzo_risorse", "Text", true);
            $this->note_rispetto_risorse_previste = $db->getField("note_rispetto_risorse_previste", "Text", true);
            $this->note_rispetto_tempistiche = $db->getField("note_rispetto_tempistiche", "Text", true);
            $this->note_replicabilita_progetto = $db->getField("note_replicabilita_progetto", "Text", true);
            $this->extend = $db->getField("extend", "Text", true);            
            $this->time_modifica = $db->getField("time_modifica", "Date", true);
            $this->record_attivo = $db->getField("record_attivo", "Number", true);
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
            SELECT progetti_monitoraggio.*
            FROM progetti_monitoraggio
            " . $where . "
            ORDER BY progetti_monitoraggio.ID
        ";

        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $monitoraggio = new ProgettiMonitoraggio();

                $monitoraggio->id = $db->getField("ID", "Number", true);
                $monitoraggio->id_progetto = $db->getField("ID_progetto", "Number", true);
                $monitoraggio->numero_monitoraggio = $db->getField("numero_monitoraggio", "Number", true);
                $monitoraggio->id_tipologia_monitoraggio = $db->getField("ID_tipologia_monitoraggio", "Number", true);
                $monitoraggio->descrizione_fase = $db->getField("descrizione_fase", "Text", true);
                $monitoraggio->costi_sostenuti = $db->getField("costi_sostenuti", "Number", true);
                $monitoraggio->descrizione_utilizzo_risorse = $db->getField("descrizione_utilizzo_risorse", "Text", true);
                $monitoraggio->note_rispetto_risorse_previste = $db->getField("note_rispetto_risorse_previste", "Text", true);
                $monitoraggio->note_rispetto_tempistiche = $db->getField("note_rispetto_tempistiche", "Text", true);
                $monitoraggio->note_replicabilita_progetto = $db->getField("note_replicabilita_progetto", "Text", true);
                $monitoraggio->extend = $db->getField("extend", "Text", true);                
                $monitoraggio->time_modifica = $db->getField("time_modifica", "Date", true);
                $monitoraggio->record_attivo = $db->getField("record_attivo", "Number", true);

                $results_list[] = $monitoraggio;
            } while($db->nextRecord());
        }

        return $results_list;
    }

    public static function getNextNumeroMonitoraggio($id_progetto) {
        $db = ffDB_Sql::factory();
        $query = "
            SELECT MAX(pm.numero_monitoraggio) AS 'current_value'
            FROM progetti_monitoraggio pm
            WHERE pm.record_attivo = ".$db->toSql(1)."
                AND pm.ID_progetto = ".$db->toSql($id_progetto)."
	    ";

        $db->query($query);
        if ($db->nextRecord()) {
            return ($db->getField("current_value", "Number", true) + 1);
        }
        return 1;
    }

    public static function isUnicoMonitoraggioFinale($filters = array()) {
        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= "AND ". $field ." = ". $db->toSql($value);
        }
        $sql = "
            SELECT progetti_monitoraggio.*
            FROM progetti_monitoraggio
            " . $where . "
            ORDER BY progetti_monitoraggio.ID
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            return true;
        }
        return false;
    }

    public static function lastInsertedID($filters = array()) {
        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= "AND ". $field ." = ". $db->toSql($value);
        }
        $sql = "
            SELECT MAX(ID) AS 'last_id'
            FROM progetti_monitoraggio
            " . $where . "
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            return $db->getField("last_id", "Number", true);
        }
        throw new Exception("Impossibile recuperare l'ultimo ID inserito per ProgettiMonitoraggio");
    }
}