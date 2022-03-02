<?php
class ValutazioniRegolaCategoria extends Entity{		
    protected static $tablename = "valutazioni_regola_categoria";
    
    private static $attributi = array(
        array(
            "ID" => 1,
            "descrizione" => "Dirigenza / comparto",
        ),
        array(
            "ID" => 2,             
            "descrizione" => "Incarico di funzione",
        ),
        array(
            "ID" => 3,
            "descrizione" => "Qualifica interna",      
        ),
        array(
            "ID" => 4,
            "descrizione" => "Responsabilità CDR",      
        ),
        array(
            "ID" => 5,
            "descrizione" => "Ruolo",        
        ),
        array(
            "ID" => 6,
            "descrizione" => "Tipo CDR di Responsabilità",
        ),
    ); 
    
    //ritorna un elenco di attributi selezionabili per le regole
    public static function getAttributi($id=null) {        
        $attributi = array();
        if($id == null) {
            $attributi = self::$attributi;
        }
        else {
            foreach (self::$attributi as $attributo) {
                if ($attributo["ID"] == $id) {
                    $attributi[] = $attributo;
                    break;
                }
            }
        }
        return $attributi;
    }
       
    //ritorna un array con i valori selezionabili per il campo
    public static function getValoriSelezionabili() {
        $valori = array();
        //viene creato l'array per l'associazione attributo-valori selezionabili
        //Dirigenza/comparto
        $valori[] = array("ID_attributo"=>"1", "valore"=>0,"descrizione"=>"Comparto");
        $valori[] = array("ID_attributo"=>"1", "valore"=>1,"descrizione"=>"Dirigenza");
        //Incarico di funzione
        $valori[] = array("ID_attributo"=>"2", "valore"=>0,"descrizione"=>"Nessun incarico di funzione");
        $valori[] = array("ID_attributo"=>"2", "valore"=>1,"descrizione"=>"Incarico di funzione");
        //qualifica interna
        foreach (QualificaInterna::getAll() as $qualifica_interna) {
            $valori[] = array("ID_attributo"=>"3", "valore"=>$qualifica_interna->id,"descrizione"=>$qualifica_interna->descrizione);
        }
        //responsabilità CDR
        $valori[] = array("ID_attributo"=>"4", "valore"=>0,"descrizione"=>"Nessuna responsabilità");
        $valori[] = array("ID_attributo"=>"4", "valore"=>1,"descrizione"=>"Responsabile CDR");
        //Ruolo
        foreach (Ruolo::getAll() as $ruolo) {
            $valori[] = array("ID_attributo"=>"5", "valore"=>$ruolo->id,"descrizione"=>$ruolo->descrizione);
        }
        //Tipo CDR
        foreach (TipoCdr::getAll() as $tipo_cdr) {
            $valori[] = array("ID_attributo"=>"6", "valore"=>$tipo_cdr->id,"descrizione"=>$tipo_cdr->abbreviazione." - ".$tipo_cdr->descrizione);
        }
           
        return $valori;
    }
    
    //verifica regola
    //restituisce true se la regola è verificata
    public function verificaRegola(ValutazioniPersonale $personale) {
        //se sono presenti anomalie in ValutazioniPersonale viene restituito sempre falso
        if (strlen ($this->anomalia)) {
            return null;
        }        
        switch ($this->id_attributo) {
            //dirigenza/comparto
            case 1:
                if ($personale->qualifica_interna->dirigente == (int)$this->valore) {
                    return true;
                }
            break;
            //Incarico di funzione
            case 2:
                if ($personale->carriera->posizione_organizzativa == (int)$this->valore) {
                    return true;
                }
            break;
            //Qualifica interna
            case 3:               
                if ($personale->qualifica_interna->id == (int)$this->valore) {
                    return true;
                }
            break;
            //Responsabilità CDR / Tipo CDR di responsabilità
            case 4:
            case 6:
                //recupero eventuale responsabilità                
                $anno_riferimento = new ValutazioniAnnoBudget($personale->periodo_riferimento->id_anno_budget);
                $codici_cdr_responsabilità = $personale->getCodiciCdrResponsabilitaAnno($anno_riferimento);
                if ($this->id_attributo == 4) {                                    
                    //con responsibilità
                    if (!empty($codici_cdr_responsabilità)) {
                        $resp = 1;                      
                    }
                    //senza responsabilità
                    else {
                        $resp = 0;
                    }                    
                    if ((int)$this->valore == $resp) {
                        return true;
                    }                   
                    return false;                                        
                }
                else {
                    //viene estratto il cdr con livello più alto (return di getLivelloGerarchico più basso, 0 livello radice)
                    //per poterne verificare la tipologia
                    $max_lvl = "ND";
                    $return = null;
                    $cdr_resp_max = null;
                    foreach ($codici_cdr_responsabilità as $codice_cdr_resp) {
                        $piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $personale->periodo_riferimento->data_fine);
                        try {
                            $cdr_resp = Cdr::factoryFromCodice($codice_cdr_resp, $piano_cdr);
                            //se il livello è quello di radice viene restituito senza continuare la verifica
                            if ($max_lvl == 0) {
                                $cdr_resp_max = $cdr_resp;
                                break;
                            }
                            $cdr_resp_lvl = $cdr_resp->getLivelloGerarchico();
                            if (($max_lvl == "ND") || ($cdr_resp_lvl < $max_lvl)) {
                                $max_lvl = $cdr_resp_lvl;
                                $cdr_resp_max = $cdr_resp;
                            }
                        } catch (Exception $ex) {

                        }                                                
                    }    
                    if ($cdr_resp_max !== null && $cdr_resp_max->id_tipo_cdr == $this->valore) {
                        return true;
                    }
                    return false;
                }
            break;
            //Ruolo
            case 5:
                if ($personale->qualifica_interna->id_ruolo == (int)$this->valore) {
                    return true;
                }
            break;
            //Errore
            default:
                return null;
            break;
        }        
        return false;
    }

    public function canDelete() {
        return true;
    }

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
}