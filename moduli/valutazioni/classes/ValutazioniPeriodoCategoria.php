<?php
class ValutazioniPeriodoCategoria extends Entity {
    protected static $tablename = "valutazioni_periodo_categoria";

    public function canDelete() {
        //Controllo se eventuali entità ambito_categoria_periodo associate sono cancellabili
        $periodo_categoria_ambiti = ValutazioniPeriodoCategoriaAmbito::getAll(array("ID_periodo_categoria" => $this->id));
        foreach($periodo_categoria_ambiti as $periodo_categoria_ambito) {
            if(!$periodo_categoria_ambito->canDelete()) {
                return false;
            }
        }               
        if(count($periodo_categoria_ambiti) == 0) {
            return ValutazioniHelper::canDeleteCategoriaPeriodo($this);
        }
        return true;
    }

    public function delete($propaga = true) {
        //Controllo se l'istanza può essere cancellata
        if($this->canDelete()) {
            //Se propagazione, cancello le istanze collegate (solo quelle di primo livello)
            if($propaga) {
                $periodo_categoria_ambiti = ValutazioniPeriodoCategoriaAmbito::getAll(array("ID_periodo_categoria" => $this->id));
                foreach($periodo_categoria_ambiti as $periodo_categoria_ambito) {
                    if(!$periodo_categoria_ambito->delete()) {
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