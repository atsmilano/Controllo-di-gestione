<?php
CoreHelper::includeJqueryUi();

//caricamento del dialog
$cm->oPage->widgetLoad("dialog");
$tmp = $cm->oPage->widgets["dialog"]->process(
    "cdr_action_dialog" // id del dialog
    , array( // proprietà  del dialog
        "name" => "cdr_action_dialog"
        , "title" => ""
        , "padre" => ""
        , "url" => ""
        , "callback" => "refreshTree(getUrlParameter('id_padre', that.dialog_params.get(id)['url']), that.dialog_params.get(id)['lastaction']);"
    )
    , $cm->oPage // oggetto pagina associato
);

$tmp2 = $cm->oPage->widgets["dialog"]->process(
    "piano_cdr_action_dialog" // id del dialog
    , array( // proprietà  del dialog
        "name" => "piano_cdr_action_dialog"
        , "title" => ""
        , "url" => ""
        , "callback" => "location.reload();",
    )
    , $cm->oPage // oggetto pagina associato
);

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory((__DIR__) . "/tpl");
$tpl->load_file("tree_selection.html", "main");

//**********************
//recupero dei parametri
//**************************************************************************
//tipo piano_cdr
if (isset ($_REQUEST["sel_tipo_piano_cdr"]))
    $id_tipo_piano_cdr = $_REQUEST["sel_tipo_piano_cdr"];
else
    $id_tipo_piano_cdr = 0;

$tipi_piano_cdr = TipoPianoCdr::getAll();
//se il parametro di selezione del piano risulta valido viene utilizzato
try {
	$tipo_piano_cdr_selezionato = new TipoPianoCdr($id_tipo_piano_cdr);		
}
//altrimenti viene selezionato il piano con priorità  più alta
catch (Exception $ex) {		
	if (count($tipi_piano_cdr)>0)
		$tipo_piano_cdr_selezionato = $tipi_piano_cdr[0];
	else
		mod_notifier_add_message_to_queue("Nessun tipo di piano cdr definito.", MOD_NOTIFIER_ERROR);
}

if (isset($tipo_piano_cdr_selezionato))
{
	//*********************
	//generazione selezione
	$tipi_piano_select = array();
	foreach ($tipi_piano_cdr as $tipo_piano)
	{
		$tipo_piano_select[] = array(
										new ffData ($tipo_piano->id, "Number"),
										new ffData ($tipo_piano->descrizione, "Text")
										);
	}
	//esiste sicuramente almeno un tipo piano (verifica effettuata sul recupero dei parametri)
	//selezione del tipo di piano
	$oField = ffField::factory($cm->oPage);
	$oField->id = "sel_tipo_piano_cdr";
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->multi_pairs = $tipo_piano_select;
        $oField->multi_select_one = false;
	$oField->properties["onchange"] = "submit();";
	$oField->setValue($tipo_piano_cdr_selezionato->id);
	$oField->parent_page = array(&$cm->oPage);
	$tpl->set_var("tipo_piano_cdr_sel", $oField->process());
	
	//**************************************************************************
	//piano_cdr
	$piani_cdr = PianoCdr::getAll(array("ID_tipo_piano_cdr"=>$tipo_piano_cdr_selezionato->id));
	
	if (isset ($_REQUEST["sel_piano_cdr"]))
		$piano_cdr = $_REQUEST["sel_piano_cdr"];
	else
		$piano_cdr = 0;
		
	//se il parametro di selezione del piano risulta valido viene utilizzato
	try {
		$piano_cdr_selezionato = new PianoCdr($piano_cdr);	
		if ($piano_cdr_selezionato->id_tipo_piano_cdr !== $tipo_piano_cdr_selezionato->id)
			$piano_cdr_selezionato = 0;
	} 
	//altrimenti viene selezionato il piano con priorità più alta
	catch (Exception $ex) {		
		$piano_cdr_selezionato = 0;
	}
	if ($piano_cdr_selezionato == 0)
	{
		
		if (count($piani_cdr)>0)							
			$piano_cdr_selezionato = $piani_cdr[0];																												
		else
			$tpl->parse("NoPianiCdr", false);
	}
	//solo nel caso sia stato selezionato un piano viene caricato l'albero dei cdr definito per quel piano	
	if ($piano_cdr_selezionato !== 0)
	{		
		if ($piano_cdr_selezionato->data_introduzione !== null)
			$descrizione = "introdotto il " . date("d/m/Y",strtotime($piano_cdr_selezionato->data_introduzione));
		else
			$descrizione = "temporaneo definito il " . date("d/m/Y",strtotime($piano_cdr_selezionato->data_definizione));
		$tpl->set_var("id_piano_cdr", $piano_cdr_selezionato->id);
		$tpl->set_var("descrizione_piano", $descrizione);
		$tpl->set_var("data_definizione_piano_cdr", date("d/m/Y",strtotime($piano_cdr_selezionato->data_definizione)));		
		$tpl->set_var("tipo_piano_cdr", $tipo_piano_cdr_selezionato->descrizione);
		$cdr_piano = $piano_cdr_selezionato->getCdr();
		$tpl->set_var("tot_cdr", count($cdr_piano));		
		$n_cdc = 0;
		foreach($cdr_piano as $cdr){
			$n_cdc += count($cdr->getCdc());
		}		
		$tpl->set_var("tot_cdc", $n_cdc);
		
		$tpl->parse("PianoCdrInfo", false);		
	}
	
	//Selezione dei piani dei cdr associati  alla tipologia specificata	
	$piano_cdr_select = array();
	foreach ($piani_cdr as $piano_cdr)
	{
		if ($piano_cdr->data_introduzione !== null)
			$descrizione = "Introdotto il " . date("d/m/Y",strtotime($piano_cdr->data_introduzione));
		else
			$descrizione = "Temporaneo definito il " . date("d/m/Y",strtotime($piano_cdr->data_definizione));
		$piano_cdr_select[] = array(
										new ffData ($piano_cdr->id, "Number"),
										new ffData ($descrizione, "Text")
										);
	}
	$oField = ffField::factory($cm->oPage);
	$oField->id = "sel_piano_cdr";
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->multi_pairs = $piano_cdr_select;
	$oField->properties["onchange"] = "submit();";
	$oField->setValue($piano_cdr_selezionato->id);
	$oField->parent_page = array(&$cm->oPage);
	$tpl->set_var("piano_cdr_sel", $oField->process());

	//**************************************************************************
	//Visualizzazione pulsanti di creazione dei piani dei cdr	
	$tpl->parse("PianiCdr", false);
}

$cm->oPage->addContent($tpl);