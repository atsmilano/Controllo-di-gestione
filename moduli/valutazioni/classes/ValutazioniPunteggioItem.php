<?php
class ValutazioniPunteggioItem extends Entity {
    protected static $tablename = "valutazioni_punteggio_item";

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
        return count(ValutazioniValutazioneItem::getAll(array("ID_item" => $this->id_item))) == 0;
    }
}