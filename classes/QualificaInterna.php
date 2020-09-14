<?php
class QualificaInterna {
    public $id;
    public $codice;
    public $descrizione;
    public $dirigente;
    public $id_ruolo;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						qualifica_interna.*
					FROM
						qualifica_interna
					WHERE
						qualifica_interna.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->codice = $db->getField("codice", "Text", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->dirigente = CoreHelper::getBooleanValueFromDB($db->getField("dirigente", "Number", true));
                $this->id_ruolo = $db->getField("ID_ruolo", "Number", true);
            } else {
                throw new Exception("Impossibile creare l'oggetto QualificaInterna con ID = " . $id);
            }
        }
    }

    //restituisce array con tutte le qualifiche interne ordinate per descrizione
    public static function getAll($filters = array()) {
        $db = ffDB_Sql::factory();
        $qualifiche_interne = array();
        
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value) {
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        }

        $sql = "
            SELECT qualifica_interna.*
            FROM qualifica_interna
            " . $where . "
            ORDER BY descrizione ASC
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $qualifica_interna = new QualificaInterna();
                $qualifica_interna->id = $db->getField("ID", "Number", true);
                $qualifica_interna->codice = $db->getField("codice", "Text", true);
                $qualifica_interna->descrizione = $db->getField("descrizione", "Text", true);
                $qualifica_interna->dirigente = $db->getField("dirigente", "Text", true);
                $qualifica_interna->id_ruolo = $db->getField("ID_ruolo", "Number", true);
                $qualifiche_interne[] = $qualifica_interna;
            } while ($db->nextRecord());
        }
        return $qualifiche_interne;
    }

}
