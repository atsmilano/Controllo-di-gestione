<?php
class ObiettiviValutazionePersonale {
    public $id;
    public $id_periodo_rendicontazione;
    public $id_obiettivo_cdr_personale;
    public $perc_raggiungimento;
    public $time_ultimo_aggiornamento;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT obiettivi_valutazione_personale.*
                FROM obiettivi_valutazione_personale
                WHERE obiettivi_valutazione_personale.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->id_periodo_rendicontazione = $db->getField("ID_periodo_rendicontazione", "Number", true);
                $this->id_obiettivo_cdr_personale = $db->getField("ID_obiettivo_cdr_personale", "Number", true);
                $this->perc_raggiungimento = $db->getField("perc_raggiungimento", "Number", true);
                $this->time_ultimo_aggiornamento = CoreHelper::getDateValueFromDB($db->getField("time_ultimo_aggiornamento", "Date", true));
            } else
                throw new Exception("Impossibile creare l'oggetto ObiettivoValutazionePersonale con ID = " . $id);
        }
    }

    public static function factoryFromObiettivoCdrPersonalePeriodo(ObiettiviObiettivoCdrPersonale $obiettivo_cdr_personale, ObiettiviPeriodoRendicontazione $periodo) {
        $filters = array(
            "ID_periodo_rendicontazione" => $periodo->id,
            "ID_obiettivo_cdr_personale" => $obiettivo_cdr_personale->id,
        );
        $valutazione = ObiettiviValutazionePersonale::getAll($filters);
        //verrà selezionata al più una valutazione
        if (count($valutazione) > 0) {
            return $valutazione[0];
        } else {
            return null;
        }
    }

    public static function getAll($filters = array()) {
        $valutazioni = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value) {
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        }
        $sql = "SELECT obiettivi_valutazione_personale.*
                FROM obiettivi_valutazione_personale                
				" . $where . "
				ORDER BY obiettivi_valutazione_personale.ID_periodo_rendicontazione ASC, obiettivi_valutazione_personale.ID_obiettivo_cdr_personale ASC";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $valutazione = new ObiettiviValutazionePersonale();

                $valutazione->id = $db->getField("ID", "Number", true);
                $valutazione->id_periodo_rendicontazione = $db->getField("ID_periodo_rendicontazione", "Number", true);
                $valutazione->id_obiettivo_cdr_personale = $db->getField("ID_obiettivo_cdr_personale", "Number", true);
                $valutazione->perc_raggiungimento = $db->getField("perc_raggiungimento", "Number", true);
                $valutazione->time_ultimo_aggiornamento = CoreHelper::getDateValueFromDB($db->getField("time_ultimo_aggiornamento", "Date", true));

                $valutazioni[] = $valutazione;
            } while ($db->nextRecord());
        }
        return $valutazioni;
    }

    //salvataggio su db
    public function save() {
        $db = ffDB_Sql::factory();
        if ($this->id == null) {
            $sql = "
                INSERT INTO obiettivi_valutazione_personale 
                (
                    id_periodo_rendicontazione,				
                    id_obiettivo_cdr_personale,
                    perc_raggiungimento,						
                    time_ultimo_aggiornamento
                ) 
                VALUES (
                    " . (strlen($this->id_periodo_rendicontazione) ? $db->toSql($this->id_periodo_rendicontazione) : "null") . ",
                    " . (strlen($this->id_obiettivo_cdr_personale) ? $db->toSql($this->id_obiettivo_cdr_personale) : "null") . ",
                    " . (strlen($this->perc_raggiungimento) ? $db->toSql($this->perc_raggiungimento) : "null") . ",
                    " . (strlen($this->time_ultimo_aggiornamento) ? $db->toSql($this->time_ultimo_aggiornamento) : "null") . "
                );
            ";
        } else {
            $sql = "
                UPDATE obiettivi_valutazione_personale
                SET					
                    id_periodo_rendicontazione = " . (strlen($this->id_periodo_rendicontazione) ? $db->toSql($this->id_periodo_rendicontazione) : "null") . ",				
                    id_obiettivo_cdr_personale = " . (strlen($this->id_obiettivo_cdr_personale) ? $db->toSql($this->id_obiettivo_cdr_personale) : "null") . ",
                    perc_raggiungimento = " . (strlen($this->perc_raggiungimento) ? $db->toSql($this->perc_raggiungimento) : "null") . ",						
                    time_ultimo_aggiornamento = " . (strlen($this->time_ultimo_aggiornamento) ? $db->toSql($this->time_ultimo_aggiornamento) : "null") . "
                WHERE 
                    obiettivi_valutazione_personale.ID = " . $db->toSql($this->id) . "
            ";
        }
        if (!$db->execute($sql))
            throw new Exception("Impossibile salvare l'oggetto ObiettivoValutazionePersonale con ID = " . $this->id . " nel DB");
    }
    
    public function delete() {
        $db = ffDB_Sql::factory();
        $sql = "DELETE FROM obiettivi_valutazione_personale"
            . " WHERE ID = ".$db->toSql($this->id);
        
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto "
                . "ObiettivoValutazionePersonale con ID = " . $this->id . " nel DB");
        }
    }
}