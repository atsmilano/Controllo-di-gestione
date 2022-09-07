<?php

class ValutazioniItem extends Entity
{

    protected static $tablename = "valutazioni_item";

    public static function getAll($where = array(), $order = array(array("fieldname" => "ordine_visualizzazione", "direction" => "DESC")))
    {
        //metodo classe entity
        return parent::getAll($where, $order);
    }

    //viene restituito un array con le categorie associate all'item
    public function getCategorieAssociate()
    {
        $categorie = array();
        $db = ffDb_Sql::factory();
        $sql = "
				SELECT 
					ID_categoria
				FROM
					valutazioni_item_categoria
				WHERE
					valutazioni_item_categoria.ID_item = " . $db->toSql($this->id)
        ;
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $categoria = new ValutazioniCategoria($db->getField("ID_categoria", "Number", true));
                $categorie[$categoria->id] = $categoria;
            } while ($db->nextRecord());
        }
        return $categorie;
    }

    public function setCategoriaAssociata($id_categoria, $isInsert)
    {
        $db = ffDb_Sql::factory();

        if ($isInsert) {
            $sql = "
                INSERT INTO valutazioni_item_categoria(
                    ID_item, 
                    ID_categoria
                )
                VALUES (
                    " . $db->toSql($this->id) . ", 
                    " . $db->toSql($id_categoria) . "
                )
            ";
        }
        else {
            $sql = "
                DELETE FROM valutazioni_item_categoria
                WHERE 
                    ID_item = " . $db->toSql($this->id) . " AND
                    ID_categoria = " . $db->toSql($id_categoria);
        }
        if ($db->execute($sql)) {
            return $db->getInsertID(true);
        }
        ffErrorHandler::raise("Associazione/Disassociazione item " . $this->id . " con la categoria " . $id_categoria . " non riuscita");
    }

    //viene restituito un array con i punteggi dell'item
    public function getPunteggi()
    {
        $punteggi = array();
        $db = ffDb_Sql::factory();
        $sql = "
				SELECT 
					ID
				FROM
					valutazioni_punteggio_item
				WHERE
					valutazioni_punteggio_item.ID_item = " . $db->toSql($this->id) . "
				ORDER BY
					valutazioni_punteggio_item.punteggio ASC
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $punteggi[] = new ValutazioniPunteggioItem($db->getField("ID", "Number", true));
            } while ($db->nextRecord());
        }
        return $punteggi;
    }

    //viene restituito il punteggio massimo per l'item, null se nessun punteggio
    public function getPunteggioMassimo()
    {
        $db = ffDb_Sql::factory();
        $sql = "
				SELECT 
					valutazioni_punteggio_item.punteggio
				FROM
					valutazioni_punteggio_item
				WHERE
					valutazioni_punteggio_item.ID_item = " . $db->toSql($this->id) . "
				ORDER BY
					valutazioni_punteggio_item.punteggio DESC                
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            return $db->getField("punteggio", "Number", true);
        }
        return null;
    }

    function delete($propaga = true)
    {
        if ($this->canDelete()) {
            if ($propaga && !$this->checkOrDeleteRelations("delete")) {
                return false;
            }

            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM " . self::$tablename . "
                WHERE " . self::$tablename . ".ID = " . $db->toSql($this->id) . "
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto " . static::class . " con ID='" . $this->id . "' dal DB");
            }

            return true;
        }

        return false;
    }

    function canDelete()
    {
        return
                count(ValutazioniValutazioneItem::getAll(array("ID_item" => $this->id))) == 0 &&
                $this->checkOrDeleteRelations("canDelete");
    }

    private function checkOrDeleteRelations($function)
    {
        $punteggi_item = ValutazioniPunteggioItem::getAll(array("ID_item" => $this->id));
        foreach ($punteggi_item as $punteggio_item) {
            if (!$punteggio_item->$function()) {
                return false;
            }
        }

        $item_categorie = ValutazioniItemCategoria::getAll(array("ID_item" => $this->id));
        foreach ($item_categorie as $item_categoria) {
            if (!$item_categoria->$function()) {
                return false;
            }
        }
        return true;
    }

}
