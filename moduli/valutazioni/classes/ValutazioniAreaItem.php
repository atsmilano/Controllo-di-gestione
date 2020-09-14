<?php
class ValutazioniAreaItem {		
	public $id;
	public $descrizione;		
	public $id_ambito;
	public $ordine_visualizzazione;
	
	public function __construct($id=null){
		$db = ffDb_Sql::factory();

		if($id != null) {
            $sql = "
				SELECT 
					*
				FROM
					valutazioni_area_item
				WHERE
					valutazioni_area_item.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord())
            {
                $this->id = $db->getField("ID", "Number", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->id_ambito = $db->getField("ID_ambito", "Number", true);
                $this->ordine_visualizzazione = $db->getField("ordine_visualizzazione", "Number", true);
            }
            else
                throw new Exception("Impossibile creare l'oggetto ValutazioniAreaItem con ID = ".$id);
        }
	}

	public static function getAll($filters = array()) {
        $aree_item = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value){
            $where .= "AND ".$field."=".$db->toSql($value)." ";
        }

        $sql = "SELECT valutazioni_area_item.*
                FROM valutazioni_area_item
				" . $where ."
				ORDER BY valutazioni_area_item.ordine_visualizzazione DESC";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $areaItem = new ValutazioniAreaItem();

                $areaItem->id = $db->getField("ID", "Number", true);
                $areaItem->descrizione = $db->getField("descrizione", "Text", true);
                $areaItem->id_ambito = $db->getField("ID_ambito", "Number", true);
                $areaItem->ordine_visualizzazione = $db->getField("ordine_visualizzazione", "Number", true);
                $aree_item[] = $areaItem;
            }while ($db->nextRecord());
        }
        return $aree_item;
    }

    public function canDelete() {
        $valutazioni_item = ValutazioniItem::getAll(array("ID_area_item" => $this->id));
        foreach($valutazioni_item as $valutazione_item) {
            if (!$valutazione_item->canDelete()) {
                return false;
            }
        }
        return true;
    }

    public function delete($propaga = true) {
        if($this->canDelete()) {
            if($propaga) {
                $valutazioni_item = ValutazioniItem::getAll(array("ID_area_item" => $this->id));
                foreach($valutazioni_item as $valutazione_item) {
                    if(!$valutazione_item->delete()) {
                        return false;
                    }
                }
            }

            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM valutazioni_area_item
                WHERE valutazioni_area_item.ID = ".$db->toSql($this->id)."
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ValutazioniAreaItem con ID='" . $this->id . "' dal DB");
            }
            return true;
        }
        return false;
    }
}