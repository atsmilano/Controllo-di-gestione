<?php
class ValutazioniItemCategoria extends Entity {
    protected static $tablename = "valutazioni_item_categoria";

    public static function factoryFromItemCategoria($id_item, $id_categoria) {
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT * FROM valutazioni_item_categoria
            WHERE 
              valutazioni_item_categoria.ID_item = ".$db->toSql($id_item)." AND
              valutazioni_item_categoria.ID_categoria = ".$db->toSql($id_categoria)."
        ";

        $db->query($sql);

        if ($db->nextRecord()) {
            do {
                $item_categoria = new ValutazioniItemCategoria();
                $item_categoria->id = $db->getField("ID", "Number", true);
                $item_categoria->id_item = $db->getField("ID_item", "Number", true);
                $item_categoria->id_categoria = $db->getField("ID_categoria", "Number", true);

                return $item_categoria;
            } while ($db->nextRecord());
        }
        throw new Exception("Impossibile creare l'oggetto ValutazioniItemCategoria con id_totale = " . $id_item . " e id_categoria = " . $id_categoria);
    }

    public function insert() {
        $db = ffDb_Sql::factory();
        $sql = "
                INSERT INTO valutazioni_item_categoria (
                    ID_item, 
                    ID_categoria
                )
                VALUES (
                    ".$db->toSql($this->id_item).", 
                    ".$db->toSql($this->id_categoria)."
                )
            ";

        if ($db->execute($sql)){
            return $db->getInsertID(true);
        }

        throw new Exception("Impossibile inserire l'oggetto ValutazioniItemCategoria nel DB.");
    }

    public function delete() {
        if($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM valutazioni_item_categoria
                WHERE valutazioni_item_categoria.ID = ".$db->toSql($this->id)."
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ValutazioniItemCategoria con ID='" . $this->id . "' dal DB");
            }
            return true;
        }
        return false;
    }

    public function canDelete() {
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT valutazioni_valutazione_periodica.ID
            FROM valutazioni_valutazione_periodica
            INNER JOIN valutazioni_valutazione_item
              ON (valutazioni_valutazione_item.ID_valutazione_periodica = valutazioni_valutazione_periodica.ID)
            WHERE valutazioni_valutazione_item.ID_item = ".$db->toSql($this->id_item)."
            AND valutazioni_valutazione_periodica.ID_categoria = ".$db->toSql($this->id_categoria)."  
        ";

        $db->query($sql);
        return !$db->nextRecord();
    }
}