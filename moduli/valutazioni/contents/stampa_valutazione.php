<?php
//viene verificato che la valutazione sia autovalutazione o meno (se non viene passato il parametro viene effettuato un redirect
if (isset ($_REQUEST["keys[ID_valutazione]"]))
	$valutazione = new ValutazioniValutazionePeriodica($_REQUEST["keys[ID_valutazione]"]);
else
	ffRedirect($_GET["ret_url"]);

//*******UTENTE E PRIVILEGI************
$user = LoggedUser::Instance();
$privilegi_utente = $valutazione->getPrivilegiPersonale($user->matricola_utente_selezionato);
if ($user->hasPrivilege("valutazioni_admin"))
    $admin_user = true;
else
    $admin_user = false;
//verifica che l'utente abbia i privilegi per visualizzare la valutazione
if ($admin_user == false && $valutazione->isAutovalutazione() && $privilegi_utente["view_autovalutazione"] !== true)
	ffRedirect($_GET["ret_url"]);
else if ($admin_user == false && !$valutazione->isAutovalutazione() && $privilegi_utente["view_valutazione"] !== true)
	ffRedirect($_GET["ret_url"]);

error_reporting(0);
//libreria per la generazione dei pdf
require_once(FF_DISK_PATH.DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR.CURRENT_USE_MPDF_VERSION.DIRECTORY_SEPARATOR."mpdf.php");

//generazione pdf
$mpdf = new mPDF();
$module = Modulo::getCurrentModule();
$stylesheet = file_get_contents($module->module_theme_dir.DIRECTORY_SEPARATOR ."css".DIRECTORY_SEPARATOR."stampa.css");
$mpdf->WriteHTML($stylesheet,1);
$mpdf->WriteHTML($valutazione->generazioneHtmlStampa(),2);
if ($valutazione->isAutovalutazione()){	
    $auto = "auto";
}
else {
    $auto = "";
}
$filename = $auto."valutazione_".$valutato->cognome."_".$valutato->nome."_".$valutato->matricola."_".$anno_valutazione->descrizione.".pdf";
$mpdf->Output($filename, "I");
die();