<?php
class PianoCdr extends Entity{
    protected static $tablename = "piano_cdr";    

    //restituisce array con tutti i piani dei cdr ordinati per data di definizione
    public static function getAll($where=array(), $order=array(array("fieldname"=>"data_definizione", "direction"=>"DESC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }

    //restituisce il piano attivo alla data selezionata, null se nessun piano attivo
    public static function getAttivoInData(TipoPianoCdr $tipo_piano, $date) {
        $calling_class = static::class;
        $piani_cdr = $calling_class::getAll();
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
       $calling_class = static::class;
       $piani_cdr_codice = array();
       $classes = array("Cdc","Cdr");
       //Per sicurezza viene verificato che il nome della classe sia uno dei due valori ammissibili
       if(!in_array($class, $classes)) {
           ffErrorHandler::raise("Classe inesistente");
       }

       foreach($calling_class::getAll() as $piano_cdr) {
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
        $sql = "DELETE FROM ".self::$tablename." WHERE ".self::$tablename.".ID = " . $db->toSql($this->id);
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto ".static::class." con ID = " . $this->id . " dal DB");
        }
    }
    
    //restituisce l'eventuale piano successivo a quello corrente, null se nessun piano
    public function getPianoSuccessivo (){        
        $piano_successivo = null;
        //vengono ciclati tutti i piani cdr (di default in ordine di data decrescente) dello stesso tipo del piano
        $last = null;        
        foreach (\PianoCdr::getAll(array("ID_tipo_piano_cdr"=>$this->id_tipo_piano_cdr)) as $piano_cdr) {           
            if ($piano_cdr->id == $this->id) {
                if ($last !== null) {
                    $piano_successivo = $last;
                }
                break;
            }
            $last = $piano_cdr;            
        }
        return $piano_successivo;
    }

    public function introduzionePiano() {
        $db = ffDB_Sql::factory();
        $sql = "
            UPDATE 
                ".self::$tablename."
            SET							
                data_introduzione = " . $db->toSql($this->data_definizione) ."
            WHERE 
                ".self::$tablename.".ID = " . $db->toSql($this->id) . "
        ";

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile introdurre il piano con id = " . $this->id . " nel DB");
        }
    }
    
    public function save() {
        $db = ffDB_Sql::factory();
        $sql = "
                INSERT INTO
                    ".self::$tablename."
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
