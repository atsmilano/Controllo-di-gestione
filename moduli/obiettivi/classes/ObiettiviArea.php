<?php
class ObiettiviArea {
    public $id;
    public $descrizione;
    public $anno_introduzione;
    public $anno_termine;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						obiettivi_area.*
					FROM
						obiettivi_area
					WHERE
						obiettivi_area.ID = " . $db->toSql($id)
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
                throw new Exception("Impossibile creare l'oggetto ObiettivoArea con ID = " . $id);
        }
    }

    public static function getAll() {
        $aree_obiettivo = array();

        $db = ffDB_Sql::factory();
        $sql = "SELECT obiettivi_area.*
                FROM obiettivi_area
                ORDER BY descrizione";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $area_obiettivo = new ObiettiviArea();
                $area_obiettivo->id = $db->getField("ID", "Number", true);
                $area_obiettivo->descrizione = $db->getField("descrizione", "Text", true);
                $area_obiettivo->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
                if ((int) $db->getField("anno_termine", "Text", true) !== 0) {
                    $area_obiettivo->anno_termine = $db->getField("anno_termine", "Text", true);
                } else {
                    $area_obiettivo->anno_termine = null;
                }
                $aree_obiettivo[] = $area_obiettivo;
            } while ($db->nextRecord());
        }
        return $aree_obiettivo;
    }

    public static function getAttiviAnno(AnnoBudget $anno) {
        $aree_obiettivo_anno = array();
        foreach (ObiettiviArea::getAll() as $area_obiettivo) {
            if ($area_obiettivo->anno_introduzione <= $anno->descrizione && ($area_obiettivo->anno_termine == null || $area_obiettivo->anno_termine >= $anno->descrizione)) {
                $aree_obiettivo_anno[] = $area_obiettivo;
            }
        }
        return $aree_obiettivo_anno;
    }

    public function canDelete() {
        $area = ObiettiviObiettivo::getAll(array("ID_area" => $this->id));

        return empty($area);
    }

    public function delete() {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM obiettivi_area
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ObiettiviArea "
                . "con ID='" . $this->id . "' dal DB");
            }

            return true;
        }

        return false;
    }
}