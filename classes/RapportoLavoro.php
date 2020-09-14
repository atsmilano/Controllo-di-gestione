<?php
class RapportoLavoro {
    public $id;
    public $codice;
    public $descrizione;
    public $part_time;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						rapporto_lavoro.*
					FROM
						rapporto_lavoro
					WHERE
						rapporto_lavoro.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->codice = $db->getField("codice", "Text", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->part_time = $db->getField("part_time", "Text", true);
            } else {
                throw new Exception("Impossibile creare l'oggetto RapportoLavoro con ID = " . $id);
            }
        }
    }
    
    //restituisce array con tutti i rapporti lavoro ordinati per descrizione
    public static function getAll($filters = array()) {
        $rapporti_lavoro = array();

        $db = ffDB_Sql::factory();

        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value) {
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        }
        $sql = "
            SELECT rapporto_lavoro.*
            FROM rapporto_lavoro
            " . $where . "
            ORDER BY descrizione ASC
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $rapporto_lavoro = new RapportoLavoro();
                $rapporto_lavoro->id = $db->getField("ID", "Number", true);
                $rapporto_lavoro->codice = $db->getField("codice", "Text", true);
                $rapporto_lavoro->descrizione = $db->getField("descrizione", "Text", true);
                $rapporto_lavoro->part_time = $db->getField("part_time", "Text", true);
                $rapporti_lavoro[] = $rapporto_lavoro;
            } while ($db->nextRecord());
        }
        return $rapporti_lavoro;
    }
}