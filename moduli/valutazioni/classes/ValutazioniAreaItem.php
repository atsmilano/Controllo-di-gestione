<?php
class ValutazioniAreaItem extends Entity{		
	protected static $tablename = "valutazioni_area_item";	

    public static function getAll($where=array(), $order=array(array("fieldname"=>"ordine_visualizzazione", "direction"=>"DESC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
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