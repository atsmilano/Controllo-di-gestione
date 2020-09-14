<?php
class ProgettiProgettoFinanziamento {
    public $id;
    public $id_progetto;
    public $importo;
    public $origine;
    public $descrizione;
    public $atto;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null) {
        if ($id != null) {
            $db = ffDb_Sql::factory();
            $sql = "
                SELECT *
                FROM progetti_progetto_finanziamento ppf
                WHERE ppf.ID = ".$db->toSql($id)."
            ";          
            $db->query($sql);

            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->id_progetto = $db->getField("ID_progetto", "Number", true);
                $this->importo = $db->getField("importo", "Number", true);
                $this->origine = $db->getField("origine", "Text", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->atto = $db->getField("atto", "Text", true);
                $this->extend = $db->getField("extend", "Text", true);
                $this->time_modifica = $db->getField("time_modifica", "Date", true);
                $this->record_attivo = $db->getField("record_attivo", "Number", true);
            }
            else {
                throw new Exception("Impossibile creare l'oggetto ProgettiProgettoFinanziamento con ID = " . $id);
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
            SELECT *
            FROM progetti_progetto_finanziamento
            " . $where . "
            ORDER BY progetti_progetto_finanziamento.ID
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $progetto_finanziamento = new ProgettiProgettoFinanziamento();

                $progetto_finanziamento->id = $db->getField("ID", "Number", true);
                $progetto_finanziamento->id_progetto = $db->getField("ID_progetto", "Number", true);
                $progetto_finanziamento->importo = $db->getField("importo", "Number", true);
                $progetto_finanziamento->origine = $db->getField("origine", "Text", true);
                $progetto_finanziamento->descrizione = $db->getField("descrizione", "Text", true);
                $progetto_finanziamento->atto = $db->getField("atto", "Text", true);
                $progetto_finanziamento->extend = $db->getField("extend", "Text", true);
                $progetto_finanziamento->time_modifica = $db->getField("time_modifica", "Date", true);
                $progetto_finanziamento->record_attivo = $db->getField("record_attivo", "Number", true);

                $results_list[] = $progetto_finanziamento;
            } while($db->nextRecord());
        }
        return $results_list;
    }
}