<?php
class ObiettiviOrigine {
    public $id;
    public $descrizione;
    public $anno_introduzione;
    public $anno_termine;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						obiettivi_origine.*
					FROM
						obiettivi_origine
					WHERE
						obiettivi_origine.ID = " . $db->toSql($id)
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
                throw new Exception("Impossibile creare l'oggetto ObiettivoOrigine con ID = " . $id);
        }
    }

    public static function getAll() {
        $origini_obiettivo = array();

        $db = ffDB_Sql::factory();
        $sql = "SELECT obiettivi_origine.*
                FROM obiettivi_origine
                ORDER BY descrizione";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $obiettivo_origine = new ObiettiviOrigine();
                $obiettivo_origine->id = $db->getField("ID", "Number", true);
                $obiettivo_origine->descrizione = $db->getField("descrizione", "Text", true);
                $obiettivo_origine->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
                if ((int) $db->getField("anno_termine", "Text", true) !== 0) {
                    $obiettivo_origine->anno_termine = $db->getField("anno_termine", "Text", true);
                } else {
                    $obiettivo_origine->anno_termine = null;
                }
                $origini_obiettivo[] = $obiettivo_origine;
            } while ($db->nextRecord());
        }
        return $origini_obiettivo;
    }

    public static function getAttiviAnno(AnnoBudget $anno) {
        $origini_obiettivo_anno = array();
        foreach (ObiettiviOrigine::getAll() as $origine_obiettivo) {
            if ($origine_obiettivo->anno_introduzione <= $anno->descrizione && ($origine_obiettivo->anno_termine == null || $origine_obiettivo->anno_termine >= $anno->descrizione)) {
                $origini_obiettivo_anno[] = $origine_obiettivo;
            }
        }
        return $origini_obiettivo_anno;
    }

    public function canDelete() {
        $origine = ObiettiviObiettivo::getAll(array("ID_origine" => $this->id));

        return empty($origine);
    }

    public function delete() {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM obiettivi_origine
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ObiettiviOrigine "
                . "con ID='" . $this->id . "' dal DB");
            }
            return true;
        }
        return false;
    }
}