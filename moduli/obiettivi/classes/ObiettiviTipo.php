<?php
class ObiettiviTipo {
    public $id;
    public $descrizione;
    public $anno_introduzione;
    public $anno_termine;
    public $class;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						obiettivi_tipo.*
					FROM
						obiettivi_tipo
					WHERE
						obiettivi_tipo.ID = " . $db->toSql($id)
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
                $this->class = $db->getField("class", "Text", true);
            } else
                throw new Exception("Impossibile creare l'oggetto ObiettivoTipo con ID = " . $id);
        }
    }

    public static function getAll() {
        $tipi_obiettivo = array();

        $db = ffDB_Sql::factory();
        $sql = "SELECT obiettivi_tipo.*
                FROM obiettivi_tipo
                ORDER BY descrizione";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $tipo_obiettivo = new ObiettiviTipo();
                $tipo_obiettivo->id = $db->getField("ID", "Number", true);
                $tipo_obiettivo->descrizione = $db->getField("descrizione", "Text", true);
                $tipo_obiettivo->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
                if ((int) $db->getField("anno_termine", "Text", true) !== 0) {
                    $tipo_obiettivo->anno_termine = $db->getField("anno_termine", "Text", true);
                } else {
                    $tipo_obiettivo->anno_termine = null;
                }
                $tipo_obiettivo->class = $db->getField("class", "Text", true);
                $tipi_obiettivo[] = $tipo_obiettivo;
            } while ($db->nextRecord());
        }
        return $tipi_obiettivo;
    }

    public static function getAttiviAnno(AnnoBudget $anno) {
        $tipi_obiettivo_anno = array();
        foreach (ObiettiviTipo::getAll() as $tipo_obiettivo) {
            if ($tipo_obiettivo->anno_introduzione <= $anno->descrizione && ($tipo_obiettivo->anno_termine == null || $tipo_obiettivo->anno_termine >= $anno->descrizione)) {
                $tipi_obiettivo_anno[] = $tipo_obiettivo;
            }
        }
        return $tipi_obiettivo_anno;
    }

    public function canDelete() {
        $tipo = ObiettiviObiettivo::getAll(array("ID_tipo" => $this->id));

        return empty($tipo);
    }

    public function delete() {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM obiettivi_tipo
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ObiettiviTipo "
                . "con ID='" . $this->id . "' dal DB");
            }

            return true;
        }

        return false;
    }

}