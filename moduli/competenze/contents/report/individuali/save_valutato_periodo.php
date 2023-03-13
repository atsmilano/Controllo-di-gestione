<?php
use MappaturaCompetenze\ValutatoPeriodo;
use MappaturaCompetenze\Periodo;

$user = LoggedUser::getInstance();
if (!$user->hasPrivilege("competenze_admin")) {
    die("L'utente non ha i privilegi per il salvataggio dei dati");
}

if (isset($_REQUEST["id_periodo"]) && isset($_REQUEST["matricola_valutato"]) && isset($_REQUEST["note"])) {    
    $id_periodo = $_REQUEST["id_periodo"];
    $matricola_valutato = $_REQUEST["matricola_valutato"];
    $note = $_REQUEST["note"];
    $data_abilitazione_visualizzazione = $_REQUEST["data_abilitazione_visualizzazione"];
    
    $valutato_periodo = ValutatoPeriodo::getByFields(array(
                            "ID_periodo"=>$id_periodo, 
                            "matricola_valutato"=>$matricola_valutato));
    if ($valutato_periodo == null) {
        try {
            //verifica esistenza delle entità per controllo parametri
            $periodo = new Periodo($id_periodo);
            $personale = Personale::factoryFromMatricola($matricola_valutato);            
            $valutato_periodo = new ValutatoPeriodo();
            $valutato_periodo->id = null;
            $valutato_periodo->id_periodo = $periodo->id;
            $valutato_periodo->matricola_valutato = $personale->matricola;
        } catch (Exception $ex) {
            die("Errore nel passaggio dei parametri.");
        }
    }
    $valutato_periodo->note = $note;
    $valutato_periodo->data_abilitazione_visualizzazione = CoreHelper::formatUiDate($data_abilitazione_visualizzazione, "d/m/Y", "Y-m-d");    
    $valutato_periodo->save(array("ID", "ID_periodo", "matricola_valutato", "note", "data_abilitazione_visualizzazione"));
}
else {
    die("Errore nel passaggio dei parametri.");
}
die("Salvataggio effettuato correttamente");