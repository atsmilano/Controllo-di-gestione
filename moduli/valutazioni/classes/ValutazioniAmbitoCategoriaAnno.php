<?php
class ValutazioniAmbitoCategoriaAnno extends Entity {
    protected static $tablename = "valutazioni_ambito_categoria_anno";
    
    public static function factoryFromAmbitoCategoriaAnno($id_ambito, $id_categoria, $id_anno) {
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT * FROM valutazioni_ambito_categoria_anno
            WHERE 
              valutazioni_ambito_categoria_anno.ID_ambito = ".$db->toSql($id_ambito)." AND
              valutazioni_ambito_categoria_anno.ID_categoria = ".$db->toSql($id_categoria)." AND
              valutazioni_ambito_categoria_anno.ID_anno_budget = ".$db->toSql($id_anno)."
        ";

        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $ambito_categoria_anno = new ValutazioniAmbitoCategoriaAnno();
                $ambito_categoria_anno->id = $db->getField("ID", "Number", true);
                $ambito_categoria_anno->id_categoria = $db->getField("ID_categoria", "Number", true);
                $ambito_categoria_anno->id_ambito = $db->getField("ID_ambito", "Number", true);
                $ambito_categoria_anno->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $ambito_categoria_anno->peso = $db->getField("peso", "Number", true);
                $ambito_categoria_anno->metodo_valutazione = $db->getField("peso", "Number", true);

                return $ambito_categoria_anno;
            } while ($db->nextRecord());
        }
        throw new Exception("Impossibile creare l'oggetto ValutazioniAmbitoCategoriaAnno con id_totale = " . $id_categoria . " e id_ambito = " . $id_ambito);
    }

    public function isAmbitoCategoriaAssociatiPeriodiAnno() {
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT valutazioni_periodo_categoria_ambito.ID
            FROM valutazioni_periodo_categoria_ambito
            INNER JOIN valutazioni_periodo_categoria 
              ON(valutazioni_periodo_categoria.id = valutazioni_periodo_categoria_ambito.id_periodo_categoria)
            INNER JOIN valutazioni_periodo 
              ON (valutazioni_periodo.id = valutazioni_periodo_categoria.id_periodo)
            INNER JOIN anno_budget 
              ON (anno_budget.id = valutazioni_periodo.id_anno_budget)
            WHERE
              valutazioni_periodo_categoria.ID_categoria = ".$db->toSql($this->id_categoria)."
              AND valutazioni_periodo_categoria_ambito.ID_ambito = ".$db->toSql($this->id_ambito)."
              AND anno_budget.id = ".$db->toSql($this->id_anno_budget)."
        ";
        $db->query($sql);
        if($db->nextRecord()){
            return true;
        } else {
            return false;
        }
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
        $ambito = new ValutazioniAmbito($this->id_ambito);
        $categoria = new ValutazioniCategoria($this->id_categoria);
        $annoValutazione = new ValutazioniAnnoBudget($this->id_anno_budget);

        return $ambito->setPesoAnno($annoValutazione, $categoria, $this->peso, $this->metodo, $action);
    }

    public function delete($ignoreAssociationPeriodiAnno = false) {
        if($this->canDelete($ignoreAssociationPeriodiAnno)) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM valutazioni_ambito_categoria_anno
                WHERE valutazioni_ambito_categoria_anno.ID = ".$db->toSql($this->id)."
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ValutazioniAmbitoCategoriaAnno con ID='" . $this->id . "' dal DB");
            }
            return true;
        }
        return false;
    }
    
    public function canDelete($ignoreAssociationPeriodiAnno = false) {
        if(!$ignoreAssociationPeriodiAnno) {
            if($this->isAmbitoCategoriaAssociatiPeriodiAnno()) {
                return false;
            }
        }
        return ValutazioniHelper::canDeleteCategoriaAnno($this);
    }

    public static function relationsAfterAnnoFine($id_name, $id_value, $anno_fine) {
        $db = ffDB_Sql::factory();

        $sqlAmbitoCategoriaAnno = "
            SELECT valutazioni_ambito_categoria_anno.id
            FROM valutazioni_ambito_categoria_anno
            INNER JOIN anno_budget
              ON (valutazioni_ambito_categoria_anno.ID_anno_budget = anno_budget.ID AND anno_budget.descrizione > ".$db->toSql($anno_fine).")
            WHERE valutazioni_ambito_categoria_anno.".$id_name." = ".$db->toSql($id_value)."
        ";
        $db->query($sqlAmbitoCategoriaAnno);
        return $db->nextRecord();
    }
}