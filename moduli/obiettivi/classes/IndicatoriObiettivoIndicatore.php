<?php
class IndicatoriObiettivoIndicatore extends Entity{		
    protected static $tablename = "indicatori_obiettivo_indicatore";
           
    //ritorna il valore target dell'obiettivo_indicatore per il cdr passato come parametro se definito
    //restituisce il valore dell'obiettivo-indicatore nell'anno
    //se cdr == null restituisce valore aziendale
    //se cdr !== null restituisce valore target associato a cdr
    //  oppure il primo associato sui padri gerarchici
    //  oppure il valore aziendale
    public function getValoreTarget (Cdr $cdr=null, $cdr_origine=null){         
        //se si sta verificando il valore target per il cdr
        if ($cdr !== null) {          
            if ($cdr_origine == null) {
                $cdr_origine = $cdr;
            }
            //viene restituito il primo valore target restituito, in tabella dovrebbe esistere al più un valore target definito per indicatore-anno con codice cdr vuoto		
            foreach (IndicatoriValoreTargetObiettivoCdr::getAll(array("ID_obiettivo_indicatore" => $this->id, "codice_cdr" => $cdr->codice)) AS $valore_target_indicatore_anno) {
                return $valore_target_indicatore_anno->valore_target;
            }
            //se non è stato trovato nessun valore target, nel caso sia stato definito un cdr si cerca di recuperare il valore target del padre
            if ($cdr->id_padre !== 0) {
                $cdr_padre = new Cdr ($cdr->id_padre);
                return $this->getValoreTarget ($cdr_padre, $cdr_origine);
            }
        }       
        //se non sono stati trovati valori target sul ramo viene restituito quello aziendale
        if ($this->valore_target !== null) {
            return $this->valore_target;
        }        

        //nel caso in cui quello aziendale non sia definito per obiettivo_indicatore vengono restituiti i valori aziendali per l'indicatore
        //per il cdr selezionato (in ricorsione viene utilizzato il cdr dal quale è partito il ramo gerarchico)
        $indicatore = new IndicatoriIndicatore($this->id_indicatore);
        $obiettivo = new ObiettiviObiettivo($this->id_obiettivo);
        $valore_target_indicatore = $indicatore->getValoreTargetAnno(new AnnoBudget($obiettivo->id_anno_budget), $cdr_origine);
        return $valore_target_indicatore;                             
    }
    
    public function delete() {
        $db = ffDb_Sql::factory();
        $sql = "
            DELETE FROM indicatori_obiettivo_indicatore
            WHERE ID = ". $db->toSql($this->id);

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto ValutazioniCategoria con ID='" . $this->id . "' dal DB");
        }
    }
}