<?php
$modulo = Modulo::getCurrentModule();
$user = LoggedUser::getInstance();
$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

//recupero della richiesta
if (isset($_REQUEST["ID"])) {
    try {
        $richiesta = new FabbisognoFormazione\Richiesta($_REQUEST["ID"]);
        if ($richiesta->id_anno_budget !== $anno->id) {
            ffErrorHandler::raise("Errore nel passaggio dei parametri.");
        }
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: nessuna richiesta.");
}
//******************************************************************************
//privilegi utente
$privilegi_utente["view_richiesta"] = false;
$privilegi_utente["edit_richiesta"] = false;
$privilegi_utente["edit_segreteria_organizzativa"] = false;
$privilegi_utente["edit_resp_scientifico"] = false;
$privilegi_utente["chiusura"] = false;
$privilegi_utente["edit_data_chiusura"] = false;
$privilegi_utente["delete_richiesta"] = false;

//verifica privilegi utente
$personale = \FabbisognoFormazione\Personale::factoryFromMatricola($user->matricola_utente_selezionato);
$cdr_competenza_multipairs = array();

if ($user->hasPrivilege("fabbisogno_admin") || $user->hasPrivilege("fabbisogno_operatore_formazione")) {
    $privilegi_utente["edit_richiesta"] = true;
    $privilegi_utente["edit_segreteria_organizzativa"] = true;
    $privilegi_utente["edit_resp_scientifico"] = true;
    $privilegi_utente["chiusura"] = true;
    $privilegi_utente["delete_richiesta"] = true;
    $privilegi_utente["edit_data_chiusura"] = true;

    //cdr aziendali per i quali poter creare una scheda
    foreach (AnagraficaCdr::getAnagraficaInData($date) as $cdr_competenza) {
        $cdr_competenza_multipairs[] = array(new ffData($cdr_competenza->codice, "Text"),new ffData($cdr_competenza->codice." - ".$cdr_competenza->descrizione, "Text"));
    }
}
else if($personale->isReferenteCdrInData($date)) {
    //referente cdr
    //verifica su cdr di competenza in data
    $found = false;
    foreach ($personale->getCdrReferenzaAnno($date) as $cdr_competenza) {
        $cdr_competenza_multipairs[] = array(new ffData($cdr_competenza->codice, "Text"),new ffData($cdr_competenza->codice." - ".$cdr_competenza->descrizione, "Text"));
        if ($richiesta->codice_cdr == $cdr_competenza->codice) {
            $found = true;
            break;
        }
    }
    if ($richiesta !== null) {
        if ($found == true) {
            $privilegi_utente["edit_richiesta"] = true;
            $privilegi_utente["edit_segreteria_organizzativa"] = true;
            $privilegi_utente["edit_resp_scientifico"] = true;
            $privilegi_utente["chiusura"] = true;
        }
    }
    else {
        $privilegi_utente["edit_richiesta"] = true;
        $privilegi_utente["edit_segreteria_organizzativa"] = true;
        $privilegi_utente["edit_resp_scientifico"] = true;
        $privilegi_utente["chiusura"] = true;
    }
}
else if($personale->isResponsabileCdrReferenteInData($date)) {
    //resposnabile cdr referente
    //verifica su cdr di competenza in data
    $found = false;
    foreach ($personale->getCdrResponsbileReferenzaAnno($date) as $cdr_competenza) {
        $cdr_competenza_multipairs[] = array(new ffData($cdr_competenza->codice, "Text"),new ffData($cdr_competenza->codice." - ".$cdr_competenza->descrizione, "Text"));
        if ($richiesta->codice_cdr == $cdr_competenza->codice) {
            $found = true;
        }
    }
    if ($richiesta !== null) {
        if ($found == true) {
            $privilegi_utente["edit_richiesta"] = true;
            $privilegi_utente["edit_segreteria_organizzativa"] = true;
            $privilegi_utente["edit_resp_scientifico"] = true;
            $privilegi_utente["chiusura"] = true;
            $privilegi_utente["delete_richiesta"] = true;
        }
    }
    else {
        $privilegi_utente["edit_richiesta"] = true;
        $privilegi_utente["edit_segreteria_organizzativa"] = true;
        $privilegi_utente["edit_resp_scientifico"] = true;
        $privilegi_utente["chiusura"] = true;
    }
}

if ($privilegi_utente["edit_richiesta"]!==true && $richiesta !== null) {
    //resposnabile scientifico
    if ($richiesta->matricola_responsabile_scientifico == $personale->matricola) {
        $privilegi_utente["edit_richiesta"] = true;
        $privilegi_utente["edit_segreteria_organizzativa"] = true;
    }
    //referente segreteria
    else if ($richiesta->matricola_referente_segreteria == $personale->matricola){
        $privilegi_utente["edit_richiesta"] = true;
    }
    //responsabile cdr
    else {
        foreach ($personale->getCodiciCdrResponsabilitaAnno($anno) as $cdr_resp) {
            if ($richiesta->codice == $cdr_resp->codice) {
                $privilegi_utente["view_richiesta"] = true;
                break;
            }
        }
    }
}
if ($privilegi_utente["edit_richiesta"] == true) {
    $privilegi_utente["view_richiesta"] = true;
}
if ($privilegi_utente["view_richiesta"] == false) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla richiesta di fabbisogno.");
}
//******************************************************************************
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("scheda_fabbisogno_pdf.html", "main");

$tpl->set_var("logo_stampa_filename", mod_security_get_logo());        
$logo_qualita = mod_security_get_logo_qualita(true);
if ($logo_qualita !== null) {
    $tpl->set_var("logo_stampa_qualita_filename", $logo_qualita);
    $tpl->parse("SectLogoQualita", false);
}
        
$si_no_multipairs = array(
                        array(new ffData(1, "Number"),new ffData("Si", "Text")),
                        array(new ffData(0, "Number"),new ffData("No", "Text")),
                    );

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
    $desc_area_riferimento= "";
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
//tipologia formativa    
try {
    $tipologia = new \FabbisognoFormazione\Tipologia($richiesta->id_tipologia);
    $desc_tipologia = $tipologia->descrizione;
} catch (Exception $ex) {
    $desc_tipologia = "";
}

/*$tpl->set_var("id", $richiesta->id);*/
$tpl->set_var("cdr", $cdr_richiesta->codice." - ".$cdr_richiesta->descrizione);
$tpl->set_var("responsabile_scientifico_desc", $responsabile_scientifico_desc);
$tpl->set_var("referente_desc", $referente_desc);
$tpl->set_var("mail_segreteria_organizzativa", $richiesta->mail_segreteria_organizzativa);
$tpl->set_var("telefono_segreteria_organizzativa", $richiesta->telefono_segreteria_organizzativa);
$tpl->set_var("titolo", $richiesta->titolo);
$tpl->set_var("destinatari_desc", $destinatari_desc);
$tpl->set_var("apertura_destinatari_esterni", $richiesta->apertura_destinatari_esterni == 1?"Si":"No");
$tpl->set_var("quota_iscrizione_destinatari_esterni", $richiesta->quota_iscrizione_destinatari_esterni == 1?"Si":"No");
$tpl->set_var("desc_classe_priorita", $desc_classe_priorita);
$tpl->set_var("formazione_obbligatoria", $richiesta->formazione_obbligatoria == 1?"Si":"No");
$tpl->set_var("descrizione", $richiesta->descrizione);
$tpl->set_var("desc_area_riferimento", $desc_area_riferimento);
$tpl->set_var("desc_obiettivo_riferimento", $desc_obiettivo_riferimento);
$tpl->set_var("obiettivi_formativi", $richiesta->obiettivi_formativi);
$tpl->set_var("desc_modalita_apprendimento", $desc_modalita_apprendimento);
$tpl->set_var("valutazione_ricaduta", $richiesta->valutazione_ricaduta == 1?"Si":"No");
$tpl->set_var("desc_trimestre_avvio", $desc_trimestre_avvio);
//docenti
$docenti_desc = "";
foreach (\FabbisognoFormazione\DocentiRichiesta::getAll(array("ID_richiesta"=>$richiesta->id)) as $docente_richiesta){
    $docente = new \FabbisognoFormazione\Docenti($docente_richiesta->id_docenti);
    $tpl->set_var("docente", $docente->descrizione);
    $tpl->parse("SectDocenti", false);
}
$tpl->set_var("n_posti", $richiesta->n_posti);
$tpl->set_var("n_ore", $richiesta->n_ore);
$tpl->set_var("n_edizioni", $richiesta->n_edizioni);
$tpl->set_var("costi_edizione", $richiesta->costi_edizione);
$tpl->set_var("accreditamento_ecm", $richiesta->accreditamento_ecm == 1?"Si":"No");
$tpl->set_var("desc_tipologia", $desc_tipologia);
$tpl->set_var("data_chiusura", $richiesta->data_chiusura);

error_reporting(0);
//libreria per la generazione dei pdf
require_once(FF_DISK_PATH.DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR.CURRENT_USE_MPDF_VERSION.DIRECTORY_SEPARATOR."mpdf.php");

//generazione pdf
$mpdf = new mPDF();
$module = Modulo::getCurrentModule();
$stylesheet = file_get_contents($module->module_theme_dir.DIRECTORY_SEPARATOR ."css".DIRECTORY_SEPARATOR."fabbisogno.css");
$mpdf->WriteHTML($stylesheet,1);
$mpdf->WriteHTML($tpl->rpparse("main", false),2);
$filename = "Scheda Fabbisogno";
$mpdf->Output($filename, "I");
die();