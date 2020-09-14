<?php
class PianoCdr {
    public $id;
    public $data_definizione;
    public $data_introduzione;
    public $id_tipo_piano_cdr;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT 
                    piano_cdr.*
                FROM
                    piano_cdr
                WHERE
                    piano_cdr.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->data_definizione = CoreHelper::getDateValueFromDB($db->getField("data_definizione", "Date", true));
                $this->data_introduzione = CoreHelper::getDateValueFromDB($db->getField("data_introduzione", "Date", true));
                $this->id_tipo_piano_cdr = $db->getField("ID_tipo_piano_cdr", "Number", true);
            } else {
                throw new Exception("Impossibile creare l'oggetto PianoCdr con ID = " . $id);
            }
        }
    }

    //restituisce array con tutti i piani dei cdr
    public static function getAll($filters = array()) {
        $piani_cdr = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value){
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        }

        $sql = "SELECT piano_cdr.*
                FROM piano_cdr
				" . $where . "
                ORDER BY piano_cdr.data_definizione DESC";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $piano_cdr = new PianoCdr();
                $piano_cdr->id = $db->getField("ID", "Number", true);
                $piano_cdr->data_definizione = CoreHelper::getDateValueFromDB($db->getField("data_definizione", "Date", true));
                $piano_cdr->data_introduzione = CoreHelper::getDateValueFromDB($db->getField("data_introduzione", "Date", true));
                $piano_cdr->id_tipo_piano_cdr = $db->getField("ID_tipo_piano_cdr", "Number", true);
                $piani_cdr[] = $piano_cdr;
            } while ($db->nextRecord());
        }
        return $piani_cdr;
    }

    //restituisce il piano attivo alla data selezionata, null se nessun piano attivo
    public static function getAttivoInData(TipoPianoCdr $tipo_piano, $date) {
        $piani_cdr = PianoCdr::getAll();
        foreach ($piani_cdr as $piano) {
            //i piani sono ordinati in ordine decrescente dalla getAll, 
            //viene quindi selezionato il primo piano del tipo passato come parametro con data introduzione minore o uguale a quella considerata		
            if ($piano->data_introduzione !== null && strtotime($piano->data_introduzione) <= strtotime($date) && $piano->id_tipo_piano_cdr == $tipo_piano->id) {
                return $piano;
            }
        }
        return null;
    }
    
    public static function getPianiCdrCodice($codice, $class) {
       $piani_cdr_codice = array();
       $classes = array("Cdc","Cdr");
       //Per sicurezza viene verificato che il nome della classe sia uno dei due valori ammissibili
       if(!in_array($class, $classes)) {
           ffErrorHandler::raise("Classe inesistente");
       }

       foreach(PianoCdr::getAll() as $piano_cdr) {
           try {
               $tmp = $class::factoryFromCodice($codice, $piano_cdr);
               $piani_cdr_codice[] = $piano_cdr;
           } catch(Exception $ex) {

           }
       }
       return $piani_cdr_codice;
   }

    //restituisce un array con i cdr appartenenti al piano
    //array vuoto se nessun cdr per il piano
    public function getCdr() {
        return Cdr::getAll(array("ID_piano_cdr" => $this->id));
    }

    //restituisce il cdr radice (un solo cdr con padre 0 previsto) del piano cdr
    public function getCdrRadice() {
        foreach (Cdr::getAll(array("ID_piano_cdr" => $this->id)) as $cdr) {
            if ($cdr->id_padre == 0) {
                return $cdr;
            }
        }
    }

    public function delete() {
        $db = ffDB_Sql::factory();
        $sql = "DELETE FROM piano_cdr WHERE piano_cdr.ID = " . $db->toSql($this->id);
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto PianoCdr con ID = " . $this->id . " dal DB");
        }
    }

    public function introduzionePiano() {
        $db = ffDB_Sql::factory();
        $sql = "
            UPDATE 
                piano_cdr
            SET							
                data_introduzione = " . $db->toSql($this->data_definizione) ."
            WHERE 
                piano_cdr.ID = " . $db->toSql($this->id) . "
        ";

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile introdurre il piano con id = " . $this->id . " nel DB");
        }
    }
    
    public function save() {
        $db = ffDB_Sql::factory();
        $sql = "
                INSERT INTO
                    piano_cdr
                    (ID_tipo_piano_cdr, data_definizione)
                VALUES
                    (" . (strlen($this->id_tipo_piano_cdr) ? $db->toSql($this->id_tipo_piano_cdr) : "null") . ",
                     " . (strlen($this->data_definizione) ? $db->toSql($this->data_definizione) : "null") . ")";

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile inserire il Piano CdR di tipo ".$this->id_tipo_piano_cdr." definito in data ".$this->data_definizione." nel DB");
        } else {
            return $db->getInsertID();
        }
    }        
}
