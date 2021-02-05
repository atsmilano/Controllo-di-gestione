<?php
class ValutazioniPeriodoCategoriaAmbito extends Entity {
    protected static $tablename = "valutazioni_periodo_categoria_ambito";

    public function canDelete() {
        $periodo_categoria = new ValutazioniPeriodoCategoria($this->id_periodo_categoria);
        return ValutazioniHelper::canDeleteCategoriaPeriodo($periodo_categoria);
    }

    public function delete() {
        //Controllo se l'istanza può essere cancellata
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