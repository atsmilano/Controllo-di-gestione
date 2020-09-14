<?php
class ProgettiDirezioneRiferimentoAnno{
    public $id;
    public $codice_cdr;
    public $id_anno_budget;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null){
        if ($id != null) {
            $db = ffDB_Sql::factory();

            $query = "
                SELECT *
                FROM progetti_direzione_riferimento_anno pdra 
                WHERE pdra.record_attivo = " . $db->toSql(1) . "  
                    AND pdra.ID = " . $db->toSql($id);

            $db->query($query);

            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $this->extend = $db->getField("extend", "Text", true);
                $this->time_modifica = $db->getField("time_modifica", "Datetime", true);
                $this->record_attivo = $db->getField("record_attivo", "Number", true);
            } else {
                throw new Exception("Impossibile creare l'oggetto ProgettiDirezioneRiferimentoAnno con ID = " . $id);
            }
        }
    }

    public static function getAll($filters = array()) {
        $result_lists = array();
        $db = ffDB_Sql::factory();
        $where = "WHERE 1 = 1 ";
        foreach ($filters as $db_field => $value) {
            $where .= "AND " . $db_field . " = " . $db->toSql($value);
        }
        $query = "
            SELECT *
            FROM progetti_direzione_riferimento_anno
            $where
        ";
        $db->query($query);

        if ($db->nextRecord()) {
            do {
                $direzione_riferimento_anno = new ProgettiDirezioneRiferimentoAnno();

                $direzione_riferimento_anno->id = $db->getField("ID", "Number", true);
                $direzione_riferimento_anno->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $direzione_riferimento_anno->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $direzione_riferimento_anno->extend = $db->getField("extend", "Text", true);
                $direzione_riferimento_anno->time_modifica = $db->getField("time_modifica", "Datetime", true);
                $direzione_riferimento_anno->record_attivo = $db->getField("record_attivo", "Number", true);

                $result_lists[] = $direzione_riferimento_anno;
            } while ($db->nextRecord());
        }
        return $result_lists;
    }
}