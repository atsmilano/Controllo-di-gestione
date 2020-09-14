<?php

class TipoContratto {
    public $id;
    public $descrizione;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						tipo_contratto.*
					FROM
						tipo_contratto
					WHERE
						tipo_contratto.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
            } else
                throw new Exception("Impossibile creare l'oggetto TipoContratto con ID = " . $id);
        }
    }

    //restituisce array con tutti i tipi contratto ordinati per descrizione
    public static function getAll($filters = array()) {
        $tipi_contratto = array();

        $db = ffDB_Sql::factory();

        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value)
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        
        $sql = "
            SELECT tipo_contratto.*
            FROM tipo_contratto
            " . $where . "
            ORDER BY descrizione ASC
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $tipo_contratto = new TipoContratto();
                $tipo_contratto->id = $db->getField("ID", "Number", true);
                $tipo_contratto->descrizione = $db->getField("descrizione", "Text", true);
                $tipi_contratto[] = $tipo_contratto;
            } while ($db->nextRecord());
        }
        return $tipi_contratto;
    }
}
