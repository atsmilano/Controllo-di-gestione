<?php
class IndicatoriParametroIndicatore extends Entity{		
    protected static $tablename = "indicatori_parametro_indicatore";
    
    //viene restituito l'eventuale valore salvato per la rendicontazione considerata
    public function getValoreParametroIndicatoreRendicontazioneInserito(ObiettiviRendicontazione $rendicontazione) {
        $filters = array(
                        "ID_rendicontazione"=> $rendicontazione->id,
                        "ID_parametro_indicatore"=> $this->id,
                        );
        $valori_parametro_indicatore_rendicontazione = IndicatoriValoreParametroIndicatoreRendicontazione::getAll($filters);        
        //la selezione restituirà eventualmente un unico valore univoco
        if (count($valori_parametro_indicatore_rendicontazione) > 0) {
            return $valori_parametro_indicatore_rendicontazione[0];
        }
        else {
            return null;
        }
    }
    
    //viene restituito il valore effettivo e rilevato (in caso di valore importato modificabile) del parametro per la rendicontazione 
    //(importato o inserito dall'utente in base alle politiche di gestione)    
    public function getValoreParametroIndicatoreRendicontazione(ObiettiviPeriodoRendicontazione $periodo_rendicontazione, ObiettiviObiettivoCdr $obiettivo_cdr) {
        $valore = null;
        $parametro = new IndicatoriParametro($this->id_parametro);                        
        $rendicontazione = ObiettiviRendicontazione::factoryFromObiettivoCdrPeriodo($obiettivo_cdr, $periodo_rendicontazione);  
        //viene in primis verificato che ci sia un parametro importato
        $parametro_rilevato = $parametro->getValoreParametroIndicatoreRilevatoPeriodoRendicontazione($periodo_rendicontazione, $obiettivo_cdr->codice_cdr);       
        if ($parametro_rilevato !== null) {
            //viene verificato che il parametro sia modificabile o meno
            //nel caso in cui sia modificabile il valore importato viene utilizzato solo come suggerimento
            if ($parametro_rilevato->modificabile == true) {
                if ($rendicontazione !== null) {
                    $valore["utilizzato"] = $this->getValoreParametroIndicatoreRendicontazioneInserito($rendicontazione)->valore;
                }
                else {
                    $valore["utilizzato"] = $parametro_rilevato->valore;
                }
                $valore["modificabile"] = true;                
            }
            else {
                $valore["utilizzato"] = $parametro_rilevato->valore;                
                $valore["modificabile"] = false;
            }
            $valore["rilevato"] = $parametro_rilevato->valore;            
        }
        //se non esiste parametro importato viene recuperato l'eventuale parametro inserito
        else {
            if ($rendicontazione !== null) {
                $valore["utilizzato"] = $this->getValoreParametroIndicatoreRendicontazioneInserito($rendicontazione)->valore;
            }
            else {
                $valore["utilizzato"] = null;
            }           
            $valore["rilevato"] = null;
            $valore["modificabile"] = true;
        }
        return $valore;
    }
            
    //salvataggio del valore del parametro per la rendicontazione considerata
    public function setValoreParametroIndicatoreRendicontazione(ObiettiviRendicontazione $rendicontazione, $value) {
        //se esiste già un valore salvato viene aggiornato altrimenti viene inserito
        $valore_parametro_indicatore_rendicontazione = IndicatoriParametroIndicatore::getValoreParametroIndicatoreRendicontazioneInserito($rendicontazione);        
        if ($valore_parametro_indicatore_rendicontazione == null) {
            $valore_parametro_indicatore_rendicontazione = new IndicatoriValoreParametroIndicatoreRendicontazione();    
            $valore_parametro_indicatore_rendicontazione->id_rendicontazione = $rendicontazione->id;
            $valore_parametro_indicatore_rendicontazione->id_parametro_indicatore = $this->id;
        }
        $valore_parametro_indicatore_rendicontazione->valore = $value;
        
        $valore_parametro_indicatore_rendicontazione->save();
    }
}