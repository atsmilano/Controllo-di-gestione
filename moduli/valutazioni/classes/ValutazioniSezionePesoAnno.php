<?php
class ValutazioniSezionePesoAnno extends Entity {
    protected static $tablename = "valutazioni_sezione_peso_anno";

    public static function factoryFromSezioneCategoriaAnno($id_sezione, $id_categoria, $id_anno) {
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT * FROM ".self::$tablename."
            WHERE 
              ".self::$tablename.".ID_categoria = ".$db->toSql($id_categoria)." AND
              ".self::$tablename.".ID_sezione = ".$db->toSql($id_sezione)." AND
              ".self::$tablename.".ID_anno_budget = ".$db->toSql($id_anno)."
        ";

        $db->query($sql);

        if ($db->nextRecord()) {
            do {
                $sezione_categoria_anno = new ValutazioniSezionePesoAnno();
                $sezione_categoria_anno->id = $db->getField("ID", "Number", true);
                $sezione_categoria_anno->id_categoria = $db->getField("ID_categoria", "Number", true);
                $sezione_categoria_anno->id_sezione = $db->getField("ID_ambito", "Number", true);
                $sezione_categoria_anno->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $sezione_categoria_anno->peso = $db->getField("peso", "Number", true);

                return $sezione_categoria_anno;
            } while ($db->nextRecord());
        }
        throw new Exception("Impossibile creare l'oggetto ".static::class." con id_totale = " . $id_categoria . " e id_sezione = " . $id_sezione);
    }

    public function update() {
        if($this->canUpdate()) {
            return $this->save("update");
        }

        return false;
    }

    public function canUpdate() {
        return $this->canDelete(true);
    }

    public function insert() {
        return $this->save("insert");
    }

    public function save($action) {
        $sezione = new ValutazioniSezione($this->id_sezione);
        $categoria = new ValutazioniCategoria($this->id_categoria);
        $annoValutazione = new ValutazioniAnnoBudget($this->id_anno_budget);

        return $sezione->setPesoAnno($annoValutazione, $categoria, $this->peso, $action);
    }

    public function delete() {
        if($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM ".self::$tablename."
                WHERE ".self::$tablename.".ID = ".$db->toSql($this->id)."
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ".static::class." con ID='" . $this->id . "' dal DB");
            }

            return true;
        }

        return false;
    }

    public function canDelete() {
        return ValutazioniHelper::canDeleteCategoriaAnno($this);
    }

    public static function relationsAfterAnnoFine($id_name, $id_value, $anno_fine) {
        $db = ffDB_Sql::factory();

        $sqlSezionePesoAnno = "
            SELECT ".self::$tablename.".id
            FROM ".self::$tablename."
            INNER JOIN anno_budget
              ON (".self::$tablename.".ID_anno_budget = anno_budget.ID AND anno_budget.descrizione > ".$db->toSql($anno_fine).")
            WHERE ".self::$tablename.".".$id_name." = ".$db->toSql($id_value)."
        ";

        $db->query($sqlSezionePesoAnno);

        return $db->nextRecord();
    }
}