<?php
class ObiettiviParereAzioni {
    public $id;
    public $descrizione;
    public $anno_introduzione;
    public $anno_termine;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						obiettivi_parere_azioni.*
					FROM
						obiettivi_parere_azioni
					WHERE
						obiettivi_parere_azioni.ID = " . $db->toSql($id)
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
                throw new Exception("Impossibile creare l'oggetto ParereAzioni con ID = " . $id);
        }
    }

    public static function getAll() {
        $pareri_azioni = array();

        $db = ffDB_Sql::factory();
        $sql = "SELECT obiettivi_parere_azioni.*
                FROM obiettivi_parere_azioni
                ORDER BY descrizione";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $parere_azioni = new ObiettiviParereAzioni();
                $parere_azioni->id = $db->getField("ID", "Number", true);
                $parere_azioni->descrizione = $db->getField("descrizione", "Text", true);
                $parere_azioni->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
                if ((int) $db->getField("anno_termine", "Text", true) !== 0) {
                    $parere_azioni->anno_termine = $db->getField("anno_termine", "Text", true);
                } else {
                    $parere_azioni->anno_termine = null;
                }
                $pareri_azioni[] = $parere_azioni;
            } while ($db->nextRecord());
        }
        return $pareri_azioni;
    }

    public static function getAttiveAnno(AnnoBudget $anno) {
        $pareri_azioni = array();
        foreach (ObiettiviParereAzioni::getAll() as $parere_azioni) {
            if ($parere_azioni->anno_introduzione <= $anno->descrizione && ($parere_azioni->anno_termine == null || $parere_azioni->anno_termine >= $anno->descrizione)) {
                $pareri_azioni[] = $parere_azioni;
            }
        }
        return $pareri_azioni;
    }

    public function canDelete() {
        $parere_azioni = ObiettiviObiettivoCdr::getAll(array("ID_parere_azioni" => $this->id));

        return empty($parere_azioni);
    }

    public function delete() {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM obiettivi_parere_azioni
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ObiettiviParereAzioni "
                . "con ID='" . $this->id . "' dal DB");
            }

            return true;
        }

        return false;
    }
}