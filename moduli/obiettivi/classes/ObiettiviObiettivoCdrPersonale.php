<?php
class ObiettiviObiettivoCdrPersonale {
    public $id;
    public $id_obiettivo_cdr;
    public $matricola_personale;
    public $peso;
    public $data_accettazione;
    public $data_ultima_modifica;
    public $data_eliminazione;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT 
                    obiettivi_obiettivo_cdr_personale.*
                FROM
                    obiettivi_obiettivo_cdr_personale
                WHERE
                    obiettivi_obiettivo_cdr_personale.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->id_obiettivo_cdr = $db->getField("ID_obiettivo_cdr", "Number", true);
                $this->matricola_personale = $db->getField("matricola_personale", "Text", true);
                $this->peso = $db->getField("peso", "Number", true);
                //ultima_modifica
                $this->data_ultima_modifica = CoreHelper::getDateValueFromDB($db->getField("data_ultima_modifica", "Date", true));
                $this->data_accettazione = CoreHelper::getDateValueFromDB($db->getField("data_accettazione", "Date", true));
                $this->data_eliminazione = CoreHelper::getDateValueFromDB($db->getField("data_eliminazione", "Date", true));
            } else
                throw new Exception("Impossibile creare l'oggetto ObiettivoCdrPersonale con ID = " . $id);
        }
    }

    public static function getAll($filters = array()) {
        $obiettivi_cdr_personale = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value)
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";

        $sql = "SELECT obiettivi_obiettivo_cdr_personale.*
            FROM obiettivi_obiettivo_cdr_personale
            " . $where;
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $obiettivo_cdr_personale = new ObiettiviObiettivoCdrPersonale();
                $obiettivo_cdr_personale->id = $db->getField("ID", "Number", true);
                $obiettivo_cdr_personale->id_obiettivo_cdr = $db->getField("ID_obiettivo_cdr", "Number", true);
                $obiettivo_cdr_personale->matricola_personale = $db->getField("matricola_personale", "Text", true);
                $obiettivo_cdr_personale->peso = $db->getField("peso", "Text", true);
                //ultima_modifica
                $obiettivo_cdr_personale->data_ultima_modifica = CoreHelper::getDateValueFromDB($db->getField("data_ultima_modifica", "Date", true));
                $obiettivo_cdr_personale->data_accettazione = CoreHelper::getDateValueFromDB($db->getField("data_accettazione", "Date", true));
                $obiettivo_cdr_personale->data_eliminazione = CoreHelper::getDateValueFromDB($db->getField("data_eliminazione", "Date", true));

                $obiettivi_cdr_personale[] = $obiettivo_cdr_personale;
            } while ($db->nextRecord());
        }
        return $obiettivi_cdr_personale;
    }

    //salvataggio dell'oggetto su db
    public function save() {
        $db = ffDB_Sql::factory();
        //update
        if ($this->id == null) {
            //TODO ripristino del record eliminato logicamente in caso di corrispondenza invece che inserimento di un nuovo record
            $sql = "INSERT INTO obiettivi_obiettivo_cdr_personale 
                    (
                        ID_obiettivo_cdr,				
                        matricola_personale,
                        peso,						
                        data_accettazione,
                        data_ultima_modifica,
                        data_eliminazione
                    ) 
                    VALUES (
                        " . (strlen($this->id_obiettivo_cdr) ? $db->toSql($this->id_obiettivo_cdr) : "null") . ",						
                        " . (strlen($this->matricola_personale) ? $db->toSql($this->matricola_personale) : "null") . ",						
                        " . (strlen($this->peso) ? $db->toSql($this->peso) : "null") . ",	
                        " . (strlen($this->data_accettazione) ? $db->toSql($this->data_accettazione) : "null") . ",
                        " . (strlen($this->data_ultima_modifica) ? $db->toSql($this->data_ultima_modifica) : "null") . ",
                        " . (strlen($this->data_eliminazione) ? $db->toSql($this->data_eliminazione) : "null") . "
                    );";
        } else {
            $sql = "UPDATE obiettivi_obiettivo_cdr_personale
                SET					
                    ID_obiettivo_cdr = " . (strlen($this->id_obiettivo_cdr) ? $db->toSql($this->id_obiettivo_cdr) : "null") . ",						
                    matricola_personale = " . (strlen($this->matricola_personale) ? $db->toSql($this->matricola_personale) : "null") . ",						
                    peso = " . (strlen($this->peso) ? $db->toSql($this->peso) : "null") . ",	
                    data_accettazione =  " . (strlen($this->data_accettazione) ? $db->toSql($this->data_accettazione) : "null") . ",
                    data_ultima_modifica = " . (strlen($this->data_ultima_modifica) ? $db->toSql($this->data_ultima_modifica) : "null") . ",
                    data_eliminazione = " . (strlen($this->data_eliminazione) ? $db->toSql($this->data_eliminazione) : "null") . "
                WHERE 
                    obiettivi_obiettivo_cdr_personale.ID = " . $db->toSql($this->id) . "
                ";
        }
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile salvare l'oggetto ObiettivoCdrPersonale con ID = " . $this->id . " nel DB");
        }
    }

    //eliminazione logica
    public function logicalDelete($propagate = true) {       
        // Recupero le valutazioni associate
        if ($propagate) {
            $valutazioni_personale = ObiettiviValutazionePersonale::getAll(array("ID_obiettivo_cdr_personale" => $this->id));
        }
        
        $db = ffDB_Sql::factory();
        $sql = "
            UPDATE obiettivi_obiettivo_cdr_personale
            SET data_eliminazione = " . $db->toSql(date("Y-m-d H:i:s")) . "
            WHERE obiettivi_obiettivo_cdr_personale.ID = " . $db->toSql($this->id) . "
        ";

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare logicamente l'oggetto ObiettivoCdrPersonale con ID = " . $this->id . " nel DB");
        }
        else if ($propagate) {
            foreach($valutazioni_personale as $valutazione) {
                $valutazione->delete();
            }
        }        
        return true;
    }
}