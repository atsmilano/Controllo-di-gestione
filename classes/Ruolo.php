<?php
class Ruolo {
    public $id;
    public $descrizione;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						ruolo.*
					FROM
						ruolo
					WHERE
						ruolo.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
            } else {
                throw new Exception("Impossibile creare l'oggetto Ruolo con ID = " . $id);
            }
        }
    }

    //restituisce array con tutti i ruoli ordinati per descrizione
    public static function getAll($filters = array()) {
        $ruoli = array();

        $db = ffDB_Sql::factory();

        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value)
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";

        $sql = "
            SELECT ruolo.*
            FROM ruolo
            " . $where . "
            ORDER BY descrizione ASC
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $ruolo = new Ruolo();
                $ruolo->id = $db->getField("ID", "Number", true);
                $ruolo->descrizione = $db->getField("descrizione", "Text", true);
                $ruoli[] = $ruolo;
            } while ($db->nextRecord());
        }
        return $ruoli;
    }

}
