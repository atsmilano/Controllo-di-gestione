<?php
class ObiettiviPeriodoRendicontazione extends Entity{	
    protected static $tablename = "obiettivi_periodo_rendicontazione";

    //ritorna l'ultimo periodo di rendicontazione definito nell'anno (null se nessuno definito)
    public static function getUltimoDefinitoAnno (AnnoBudget $anno) {		
        //viene considerato l'ultimo periodo attivo nell'anno per la data selezionata			
        $periodi_rendicontazione = ObiettiviPeriodoRendicontazione::getAll(array("ID_anno_budget" => $anno->id));
        if (count($periodi_rendicontazione)>0)
                return end($periodi_rendicontazione);
        else
                return null;
    }
    
    //ritorna tutti i periodi di rendicontazione dell'anno passato come parametro
    public static function getPeriodiRendicontazioneAnno (AnnoBudget $anno) {
        $filters = array("ID_anno_budget" => $anno->id);
        return ObiettiviPeriodoRendicontazione::getAll($filters);
    }
    
    public function canDelete() {
        return (count(ObiettiviRendicontazione::getAll(array("ID_periodo_rendicontazione" => $this->id))) == 0);
    }
}