<?php
class ValutazioniTotaleCategoria extends Entity {
    protected static $tablename = "valutazioni_totale_categoria";

    public static function factoryFromTotaleCategoria($id_totale, $id_categoria) {
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT * 
            FROM ".self::$tablename."
            WHERE 
              ".self::$tablename.".ID_totale = ".$db->toSql($id_totale)." AND
              ".self::$tablename.".ID_categoria = ".$db->toSql($id_categoria)."
        ";

        $db->query($sql);

        if ($db->nextRecord()) {
            do {
                $totale_categoria = new ValutazioniTotaleCategoria();
                $totale_categoria->id = $db->getField("ID", "Number", true);
                $totale_categoria->id_totale = $db->getField("ID_totale", "Number", true);
                $totale_categoria->id_categoria = $db->getField("ID_categoria", "Number", true);

                return $totale_categoria;
            } while ($db->nextRecord());
        }
        throw new Exception("Impossibile creare l'oggetto ".static::class." con id_totale = " . $id_totale . " e id_categoria = " . $id_categoria);
    }

    public function insert() {
        $db = ffDb_Sql::factory();
        $sql = "
                INSERT INTO ".self::$tablename."(
                    ID_totale, 
                    ID_categoria
                )
                VALUES (
                    ".$db->toSql($this->id_totale).", 
                    ".$db->toSql($this->id_categoria)."
                )
            ";

        if ($db->execute($sql)){
            return $db->getInsertID(true);
        }

        throw new Exception("Impossibile inserire l'oggetto ".static::class." nel DB.");
    }

    public function canDelete() {
        $categoria = new ValutazioniCategoria($this->id_categoria);
        if(!$categoria->canDelete()) {
            return false;
        }

        return true;
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
}