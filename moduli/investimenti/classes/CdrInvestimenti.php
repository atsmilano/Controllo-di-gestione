<?php
class CdrInvestimenti extends Cdr{	
	public function isDirezioneRiferimentoAnno (AnnoBudget $anno) {        
		foreach (InvestimentiDirezioneRiferimentoAnno::getDirezioneRiferimentoAnno($anno) as $direzione_riferimento) {            
            if ($direzione_riferimento->codice == $this->codice) {
                return true;
            }
        }
        return false;
	}	

    //restituisce un oggetto Cdr rappresentante la direzione di riferimento del cdr 
	public function getCdrDirezioneRiferimentoAnno(AnnoBudget $anno){
        //verifica su elemento radice (dovrebbe essere sempre fra le direzioni strategiche
		if ($this->id_padre == 0){
			return $this;
		}
		else {
			$cdr = new CdrInvestimenti($this->id_padre);
		}
				
		//si itera di livello in livello fino al raggiungimento di un cdr direzione strategica				
		if ($cdr->isDirezioneRiferimentoAnno($anno)) {
			return $cdr;			
		}
		else {
			return $cdr->getCdrDirezioneRiferimentoAnno($anno);
		}										
	}   
    
    public function isCdrBilancioAnno (AnnoBudget $anno) {        
		foreach (InvestimentiCdrBilancioAnno::getCdrBilancioAnno($anno) as $cdr_bilancio) {
            if ($cdr_bilancio->codice == $this->codice) {
                return true;
            }
        }
        return false;
	}
    
    public function isDipartimentoAmministrativoAnno (AnnoBudget $anno) {   
        
		foreach (InvestimentiDipAmministrativoAnno::getCdrDipartimentoAmministrativoAnno($anno) as $cdr_dip_amm) {
            if ($cdr_dip_amm->codice == $this->codice) {
                return true;
            }
        }
        return false;
	}
    
    public function isAbilitatoInvestimentiAnno (AnnoBudget $anno) {               
        foreach (InvestimentiCdrAbilitatoAnno::getCdrAbilitatiAnno($anno) as $cdr_abilitato) {
            if ($cdr_abilitato->codice == $this->codice) {
                return true;
            }
        }
        return false;
    }
    
    public function getCategorieCompetenzaAnno (AnnoBudget $anno) {
        $categorie = array();
		foreach (InvestimentiCategoria::getAll() as $categoria) {
            if ($categoria->getCodiceUocCompetenteAnno($anno) == $this->codice) {
                $categorie[] = $categoria;
            }
        }
        return $categorie;
    }        
    
    public function getInvestimentiAnno(AnnoBudget $anno){
        return InvestimentiInvestimento::getAll(array(
                                                        "ID_anno_budget" => $anno->id,
                                                        "codice_cdr_creazione" => $this->codice,
                                                        ));        
    }
    
    public static function getCdrConRichiesteAnno(AnnoBudget $anno) {
        $cm = cm::getInstance();
        
        $cdr_richieste = array();
        $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
        $date = $data_riferimento->format("Y-m-d");
        
        //recupero del del cdc       
        $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
        $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
        
        foreach (InvestimentiInvestimento::getAll(array("ID_anno_budget" => $anno->id)) as $investimento) {
            $cdc = Cdc::factoryFromCodice($investimento->richiesta_codice_cdc, $piano_cdr);
            $cdr = new Cdr ($cdc->id_cdr);
            if (!in_array($cdr, $cdr_richieste)){
                $cdr_richieste[] = $cdr;
            }
        }
        return $cdr_richieste;
    }
}