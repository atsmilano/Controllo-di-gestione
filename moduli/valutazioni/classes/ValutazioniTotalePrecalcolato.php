<?php
class ValutazioniTotalePrecalcolato extends Entity {
    protected static $tablename = "valutazioni_totale_precalcolato";

    public function delete() {
        $db = ffDb_Sql::factory();
        $sql = "
                DELETE FROM valutazioni_totale_categoria
                WHERE valutazioni_totale_categoria.ID = ".$db->toSql($this->id)."
            ";

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto ValutazioniTotaleCategoria con ID='" . $this->id . "' dal DB");
        }

        return true;
    }
}