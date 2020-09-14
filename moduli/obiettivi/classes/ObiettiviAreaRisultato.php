<?php
class ObiettiviAreaRisultato {
    public $id;
    public $descrizione;
    public $anno_introduzione;
    public $anno_termine;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						obiettivi_area_risultato.*
					FROM
						obiettivi_area_risultato
					WHERE
						obiettivi_area_risultato.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
                if ((int) $db->getField("anno_termine", "Text", true) !== 0) {
                    $this->anno_termine = $db->getField("anno_termine", "Text", true);
                } else {
                    $this->anno_termine = null;
                }
            } else
                throw new Exception("Impossibile creare l'oggetto ObiettivoAreaRisultato con ID = " . $id);
        }
    }

    public static function getAll() {
        $aree_risultato_obiettivo = array();

        $db = ffDB_Sql::factory();
        $sql = "SELECT obiettivi_area_risultato.*
                FROM obiettivi_area_risultato
                ORDER BY descrizione";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $area_risultato_obiettivo = new ObiettiviAreaRisultato();
                $area_risultato_obiettivo->id = $db->getField("ID", "Number", true);
                $area_risultato_obiettivo->descrizione = $db->getField("descrizione", "Text", true);
                $area_risultato_obiettivo->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
                if ((int) $db->getField("anno_termine", "Text", true) !== 0) {
                    $area_risultato_obiettivo->anno_termine = $db->getField("anno_termine", "Text", true);
                } else {
                    $area_risultato_obiettivo->anno_termine = null;
                }
                $aree_risultato_obiettivo[] = $area_risultato_obiettivo;
            } while ($db->nextRecord());
        }
        return $aree_risultato_obiettivo;
    }

    public static function getAttiviAnno(AnnoBudget $anno) {
        $aree_risultato_obiettivo_anno = array();
        foreach (ObiettiviAreaRisultato::getAll() as $area_risultato_obiettivo) {
            if ($area_risultato_obiettivo->anno_introduzione <= $anno->descrizione && ($area_risultato_obiettivo->anno_termine == null || $area_risultato_obiettivo->anno_termine >= $anno->descrizione)) {
                $aree_risultato_obiettivo_anno[] = $area_risultato_obiettivo;
            }
        }
        return $aree_risultato_obiettivo_anno;
    }

    public function canDelete() {
        $area_risultato = ObiettiviObiettivo::getAll(array("ID_area_risultato" => $this->id));

        return empty($area_risultato);
    }

    public function delete() {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM obiettivi_area_risultato
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ObiettiviAreaRisultato "
                . "con ID='" . $this->id . "' dal DB");
            }
            return true;
        }
        return false;
    }
}