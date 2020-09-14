<?php
class ProgettiProgettoPartnerInterni {
    public $id;
    public $id_progetto;
    public $codice_cdr;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null) {
        if ($id != null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT *
                FROM progetti_progetto_partner_interni pppi
                WHERE pppi.ID = ".$db->toSql($id)."
            ";
            
            $db->query($sql);

            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->id_progetto = $db->getField("ID_progetto", "Number", true);
                $this->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $this->extend = $db->getField("extend", "Text", true);
                $this->time_modifica = $db->getField("time_modifica", "Date", true);
                $this->record_attivo = $db->getField("record_attivo", "Number", true);
            }
            else {
                throw new Exception("Impossibile creare l'oggetto ProgettiProgettoPartnerInterni con ID = " . $id);
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
            SELECT progetti_progetto_partner_interni.*
            FROM progetti_progetto_partner_interni
            " . $where . "
            ORDER BY progetti_progetto_partner_interni.ID
        ";

        $db->query($sql);

        if ($db->nextRecord()) {
            do {
                $progetto_partner_interni = new ProgettiProgettoPartnerInterni();

                $progetto_partner_interni->id = $db->getField("ID", "Number", true);
                $progetto_partner_interni->id_progetto = $db->getField("ID_progetto", "Number", true);
                $progetto_partner_interni->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $progetto_partner_interni->extend = $db->getField("extend", "Text", true);
                $progetto_partner_interni->time_modifica = $db->getField("time_modifica", "Date", true);
                $progetto_partner_interni->record_attivo = $db->getField("record_attivo", "Number", true);

                $results_list[] = $progetto_partner_interni;
            } while($db->nextRecord());
        }

        return $results_list;
    }
}