<?php
$user = LoggedUser::getInstance();

//verifica privilegi utente
if ($user->hasPrivilege("fabbisogno_operatore_formazione") || $user->hasPrivilege("fabbisogno_admin")) {
    $view_all = true;
} 
else if ($user->hasPrivilege("fabbisogno_referente_cdr")
        || $user->hasPrivilege("fabbisogno_responsabile_cdr_referente")
        || $user->hasPrivilege("fabbisogno_responsabile_scientifico_anno") 
        || $user->hasPrivilege("fabbisogno_segreteria_organizzativa_anno")
        || $user->hasPrivilege("fabbisogno_responsabile_cdr")
        ) {    
    $view_all = false;    	
}
else {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere all'estrazione delle schede.");
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];	
$date = $cm->oPage->globals["data_riferimento"]["value"];
$cdr = $cm->oPage->globals["cdr"]["value"];

$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $date->format("Y-m-d"));

$cdr_radice_piano = $piano_cdr->getCdrRadice();
$cdr_anno = $cdr_radice_piano->getGerarchia();
    
if ($user->hasPrivilege("fabbisogno_referente_cdr")) {
    $personale = \FabbisognoFormazione\Personale::factoryFromMatricola($user->matricola_utente_selezionato);
    $cdr_richiesta_competenza_anno = $personale->getCdrReferenzaAnno($date);
}
if ($user->hasPrivilege("fabbisogno_responsabile_cdr_referente")) {
    if (!isset($personale)) {
        $personale = \FabbisognoFormazione\Personale::factoryFromMatricola($user->matricola_utente_selezionato);
    }
    $ramo_cdr_competenza_anno = $personale->getCdrResponsbileReferenzaAnno($date);
}

if (isset($_REQUEST["cdr_select"])) {
    if (count($cdr_anno) > 0) {
        foreach ($cdr_anno as $cdr_associato) {
            if ($cdr_associato["cdr"]->id == $_REQUEST["cdr_select"]) {   
                $gerarchia_cdr_selezionato = $cdr_associato["cdr"]->getGerarchia();
                break;
            }
        }        
    } else {
        ffErrorHandler::raise("Nessun CDR disponibile");
    }    
} 
else if (count($cdr_anno) > 0){
    $gerarchia_cdr_selezionato = $cdr_anno;
} else {
    ffErrorHandler::raise("Nessun CDR disponibile");
}

$intestazione = array(
    "ID",
    "Codice cdr",
    "Responsabile scientifico",
    "Referente segreteria",
    "Mail segreteria organizzativa",
    "Telefono segreteria organizzativa",
    "Titolo evento",
    "Destinatari",
    "Apertura a destinatari esterni",
    "Prevista quota iscrizione (per destinatari esterni)",
    "Classe di priorità",
    "Formazione obbligatoria (ex lege)",
    "Descrizione evento",
    "Area di riferimento Obiettivi Nazionali",
    "Obiettivo Nazionale",
    "Obiettivi formativi specifici",
    "Modalità di valutazione apprendimento",
    "Valutazione Ricaduta",
    "Trimestre di avvio",
    "Docenti",
    "Posti a disposizione",
    "Ore formative (Per ciascuna edizione)",
    "N° Edizioni",
    "Costo singola edizione",
    "Accreditamento ECM",
    "Tipologia formativa",
    "Data chiusura richiesta",
);
$fogli["Fabbisogno"] = array($intestazione);

$fabbisogni_anno = FabbisognoFormazione\Richiesta::getAll(array("ID_anno_budget"=>$anno->id));

if (count($fabbisogni_anno)) {
    foreach ($fabbisogni_anno as $richiesta) {   
        $show = false;
        foreach($gerarchia_cdr_selezionato as $cdr_gerarchia_selezionata) {
            if ($richiesta->codice_cdr == $cdr_gerarchia_selezionata["cdr"]->codice){
                $show = true;
                break;
            }
        }
        if ($show == true) {
            //cdr
            $cdr_richiesta = AnagraficaCdr::factoryFromCodice($richiesta->codice_cdr, $date);
            //responsabile scientifico
            if (strlen($richiesta->matricola_responsabile_scientifico) ) {
                $responsabile_scientifico = Personale::factoryFromMatricola($richiesta->matricola_responsabile_scientifico);
                $responsabile_scientifico_desc = $responsabile_scientifico->cognome." ".$responsabile_scientifico->nome." (matr.".$responsabile_scientifico->matricola.")";
            }
            else {
                $responsabile_scientifico_desc = "Non definito";
            }
            //referente segreteria
            if (strlen($richiesta->matricola_referente_segreteria)) {
                $referente_segreteria = Personale::factoryFromMatricola($richiesta->matricola_referente_segreteria);
                $referente_desc = $referente_segreteria->cognome." ".$referente_segreteria->nome." (matr.".$referente_segreteria->matricola.")";
            }
            else {
                $referente_desc = "Non definito";
            }    
            //destinatari
            try {
                $destinatari = new \FabbisognoFormazione\Destinatari($richiesta->id_destinatari);
                $destinatari_desc = $destinatari->descrizione;
            } catch (Exception $ex) {
                $destinatari_desc = "";
            }            
            //Classe di priorità
            try {
                $classe_priorita = new \FabbisognoFormazione\ClassePriorita($richiesta->id_classe_priorita);
                $desc_classe_priorita = $classe_priorita->descrizione;
            } catch (Exception $ex) {
                $desc_classe_priorita = "";
            }            
            //area di riferimento
            try {
                $area_riferimento = new \FabbisognoFormazione\AreaRiferimento($richiesta->id_area_riferimento);
                $desc_area_riferimento = $area_riferimento->descrizione;
            } catch (Exception $ex) {
                $desc_area_riferimento = "";
            }            
            //obiettivo di riferimento
            try {
                $obiettivo_riferimento = new \FabbisognoFormazione\ObiettivoRiferimento($richiesta->id_obiettivo_riferimento);
                $desc_obiettivo_riferimento = $obiettivo_riferimento->codice.". ".$obiettivo_riferimento->descrizione;
            } catch (Exception $ex) {
                $desc_obiettivo_riferimento = "";
            }            
            //modalità_valutazione_apprendimento
            try {
                $modalita_apprendimento = new \FabbisognoFormazione\ModalitaValutazioneApprendimento($richiesta->id_modalita_valutazione_apprendimento);
                $desc_modalita_apprendimento = $modalita_apprendimento->descrizione;
            } catch (Exception $ex) {
                $desc_modalita_apprendimento = "";
            }            
            //trimestre di avvio
            try {
                $trimestre_avvio = new \FabbisognoFormazione\TrimestreAvvio($richiesta->id_trimestre_avvio);
                $desc_trimestre_avvio = $trimestre_avvio->descrizione;
            } catch (Exception $ex) {
                $desc_trimestre_avvio = "";
            }            
            //docenti
            $docenti_desc = "";
            foreach (\FabbisognoFormazione\DocentiRichiesta::getAll(array("ID_richiesta"=>$richiesta->id)) as $docente_richiesta){
                $docente = new \FabbisognoFormazione\Docenti($docente_richiesta->id_docenti);
                if(strlen($docenti_desc)) {
                    $docenti_desc .= "\n";
                }
                $docenti_desc .= $docente->descrizione;
            }
            //tipologia formativa   
            try {
                $tipologia = new \FabbisognoFormazione\Tipologia($richiesta->id_tipologia);
                $desc_tipologia = $tipologia->descrizione;
            } catch (Exception $ex) {
                $desc_tipologia = "";
            }            

            $record = array(
                $richiesta->id,
                $cdr_richiesta->codice." - ".$cdr_richiesta->descrizione,
                $responsabile_scientifico_desc,
                $referente_desc,
                $richiesta->mail_segreteria_organizzativa,
                $richiesta->telefono_segreteria_organizzativa,
                $richiesta->titolo,
                $destinatari_desc,
                $richiesta->apertura_destinatari_esterni == 1?"Si":"No",
                $richiesta->quota_iscrizione_destinatari_esterni == 1?"Si":"No",
                $desc_classe_priorita,
                $richiesta->formazione_obbligatoria == 1?"Si":"No",
                $richiesta->descrizione,
                $desc_area_riferimento,
                $desc_obiettivo_riferimento,
                $richiesta->obiettivi_formativi,
                $desc_modalita_apprendimento,
                $richiesta->valutazione_ricaduta == 1?"Si":"No",
                $desc_trimestre_avvio,
                $docenti_desc,
                $richiesta->n_posti,
                $richiesta->n_ore,
                $richiesta->n_edizioni,
                $richiesta->costi_edizione,
                $richiesta->accreditamento_ecm == 1?"Si":"No",
                $desc_tipologia,
                $richiesta->data_chiusura,
            );
            if ($view_all == true) {            
                $fogli["Fabbisogno"][] = $record;
            }
            else {     
                $found = false;
                if ($user->hasPrivilege("fabbisogno_referente_cdr")) {                    
                    foreach ($cdr_richiesta_competenza_anno as $cdr_richiesta_competenza) {  
                        if ($richiesta->codice_cdr == $cdr_richiesta_competenza->codice) {                               
                            $fogli["Fabbisogno"][] = $record;
                            $found = true;
                            break;
                        }
                    }
                }
                if ($found == false && $user->hasPrivilege("fabbisogno_responsabile_cdr_referente")) {                    
                    foreach ($ramo_cdr_competenza_anno as $cdr_richiesta_competenza) {                                                
                        if ($richiesta->codice_cdr == $cdr_richiesta_competenza->codice) {                
                            $fogli["Fabbisogno"][] = $record;
                            $found = true;
                            break;
                        }
                    }
                }
                //per responsabile scientifico e per segreteria organizzativa le verifiche sono ridondanti ma vengono introdotte per robustezza
                if ($found == false && $user->hasPrivilege("fabbisogno_responsabile_scientifico_anno") && $richiesta->matricola_responsabile_scientifico == $user->matricola_utente_selezionato) {                    
                    $fogli["Fabbisogno"][] = $record;
                    $found = true;
                }
                if ($found == false && $user->hasPrivilege("fabbisogno_segreteria_organizzativa_anno") && $richiesta->matricola_referente_segreteria == $user->matricola_utente_selezionato) {
                    $fogli["Fabbisogno"][] = $record;
                    $found = true;
                }
                //verifica su esistenza $cdr_richiesta ridondante (privilegio fabbisogno_responsabile_cdr garantito se cdr selezionato) ma introdotta per robustezza
                if ($found == false && $user->hasPrivilege("fabbisogno_responsabile_cdr") && $cdr_richiesta!== null && $richiesta->codice_cdr == $cdr->codice) {
                    $fogli["Fabbisogno"][] = $record;
                    $found = true;
                }
            }
        }
    }
}
//estrazione in excel
CoreHelper::simpleExcelWriter("Fabbisogni di competenza anno ".$anno->descrizione, $fogli);