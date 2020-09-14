<?php
$anno = $cm->oPage->globals["anno"]["value"];
$cdr_global = $cm->oPage->globals["cdr"]["value"];
$cdr = new CdrCostiRicavi($cdr_global->id);

//verifica privilegi utente
$user = LoggedUser::Instance();
if (!($user->hasPrivilege("costi_ricavi_view") || $user->hasPrivilege("costi_ricavi_edit"))) {
	ffErrorHandler::raise("Utente non abilitato alla visualizzazione dei costi e ricavi per il CDR.");
}

if (isset($_REQUEST["keys[ID_importo_periodo]"]) && strlen($_REQUEST["keys[ID_importo_periodo]"])) {
	$importo_periodo = new CostiRicaviImportoPeriodo($_REQUEST["keys[ID_importo_periodo]"]);
	$conto = new CostiRicaviConto($importo_periodo->id_conto);
	$fp = new CostiRicaviFp($conto->id_fp);
	$periodo = new CostiRicaviPeriodo($importo_periodo->id_periodo);
}
else if (isset($_REQUEST["keys[ID_conto]"] ) && isset($_REQUEST["keys[ID_periodo]"]) && strlen($_REQUEST["keys[ID_conto]"]) && strlen($_REQUEST["keys[ID_periodo]"])){
		$importo_periodo = null;
		$conto = new CostiRicaviConto(($_REQUEST["keys[ID_conto]"]));
		$fp = new CostiRicaviFp($conto->id_fp);
		$periodo = new CostiRicaviPeriodo($_REQUEST["keys[ID_periodo]"]);			
}
else {
	ffErrorHandler::raise("Errore nel passaggio dei parametri.");
}

//nomi dei campi in base al periodo
//label dei campi
if ($periodo->id_tipo_periodo == 1) {
	$campo_1_importo_label = "Budget anno precedente";
	$campo1_legenda = "Si riferisce all&acute;assegnazione di budget relativa all&acute;anno precedente, effettuata sulla base della previsione stimata dal CDR all&acute;avvio dell&acute;anno di budget e del Bilancio Preventivo Annuale.";
	$campo_2_importo_label = "Prechiusura anno precedente";
	$campo2_legenda = "Previsione di prechiusura effettuata dal Responsabile di CDR per l&acute;annualit&aacute; precedente.";
	$campo_3_importo_label = "Previsione " . $anno->descrizione;
	$campo3_legenda = "Previsione che il Responsabile di CDR dovr&aacute; effettuare per l&acute;anno di Budget sulla base delle informazioni a disposizione e delle attivit&aacute; programmate (la previsione verr&aacute; utilizzata per la predisposizione del Bilancio Preventivo).";
}
else if ($periodo->id_tipo_periodo == 2) {
	$campo_1_importo_label = "Budget";
	$campo1_legenda = "Si riferisce all&acute;assegnazione di budget definita sulla base della previsione effettuata dal CDR all&acute;avvio dell&acuteanno di budget e del Bilancio Preventivo Annuale.";
	$campo_2_importo_label = "Consuntivo rilevato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
	$campo2_legenda = "Si riferisce al consuntivo estratto da ERP dal Controllo di Gestione per il periodo di riferimento.";
	$campo_3_importo_label = "Consuntivo stimato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
	$campo3_legenda = "Stima che il Responsabile di CDR dovr&aacute; effettuare relativamente al consuntivo del periodo di riferimento (effettuata per competenza sulla base dei dati extra-contabili in possesso e di quelli presenti in ERP).";
	$campo_4_importo_label = "Prechiusura al 31/12/" . $anno->descrizione . " - Assestamento " . $anno->descrizione;
	$campo4_legenda = "Previsione di Prechiusura, da confrontare con il Budget, che il Responsabile di CDR dovr&aacute; effettuare relativamente all&acute;intera annualit&aacute; sulla base delle informazioni a disposizione e delle attività programmate.";
}
else if ($periodo->id_tipo_periodo == 3) {
	$campo_1_importo_label = "Budget";
	$campo1_legenda = "Si riferisce all&acute;assegnazione di budget definita sulla base della previsione effettuata dal CDR all&acute;avvio dell&acuteanno di budget e del Bilancio Preventivo Annuale.";
	$campo_2_importo_label = "Consuntivo rilevato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
	$campo2_legenda = "Si riferisce al consuntivo estratto da ERP dal Controllo di Gestione per il periodo di riferimento.";
	$campo_3_importo_label = "Consuntivo stimato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
	$campo3_legenda = "Stima che il Responsabile di CDR dovr&aacute; effettuare relativamente al consuntivo del periodo di riferimento (effettuata per competenza sulla base dei dati extra-contabili in possesso e di quelli presenti in ERP).";
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "costi-ricavi-conto-modify";
$oRecord->resources[] = "costi-ricavi-conto";
$oRecord->src_table = "costi_ricavi_importo_periodo";
$oRecord->allow_delete = false;
//privilegi sul record
$edit = false;
//l'obiettivo sarà modificabile solamente da chi ha i privilegi di modifica e se non è stata superata la data di scadenza
if (($user->hasPrivilege("costi_ricavi_edit") || $user->hasPrivilege("costi_ricavi_admin")) && (strtotime(date("Y-m-d")) <= strtotime($periodo->data_scadenza))){
	$edit = true;
}
if ($edit == true) {
	$oRecord->allow_insert = true;	
	$oRecord->allow_update = true;	
}
else{
	$oRecord->allow_insert = false;	
	$oRecord->allow_update = false;	
}

$oRecord->title = "Valutazione periodica";
if ($edit == true) {
	$oRecord->title .= " (modificabile)";
}
else {
	$oRecord->title .= " (NON modificabile)";
}
 
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_importo_periodo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oRecord->addKeyField($oField);

$html_legenda = "	<table id='legenda_costi_ricavi_conto'>
						<thead>
							<tr>
								<th>Voce</th>
								<th>Significato</th>
							</tr>
						</thead>
						<tbody>
				";

$oField = ffField::factory($cm->oPage);
$oField->id = "campo_1";
$oField->base_type = "Number";	
$oField->label = $campo_1_importo_label;
if (!$user->hasPrivilege("costi_ricavi_admin") || $edit == false){
	$oField->control_type = "label";
	$oField->store_in_db = false;
	$oField->default_value = new ffData(0, "Number");
}
else{
	$oField->required = true;
}
$oRecord->addContent($oField);
$html_legenda .= "<tr><td>".$campo_1_importo_label."</td><td>".$campo1_legenda."</td></tr>";

$oField = ffField::factory($cm->oPage);
$oField->id = "campo_2";
$oField->base_type = "Number";	
$oField->label = $campo_2_importo_label;
if (!$user->hasPrivilege("costi_ricavi_admin") || $edit == false){
	$oField->control_type = "label";
	$oField->store_in_db = false;
	$oField->default_value = new ffData(0, "Number");
}
else{
	$oField->required = true;
}
$oRecord->addContent($oField);
$html_legenda .= "<tr><td>".$campo_2_importo_label."</td><td>".$campo2_legenda."</td></tr>";

$oField = ffField::factory($cm->oPage);
$oField->id = "campo_3";
$oField->base_type = "Number";	
$oField->label = $campo_3_importo_label;
if (!$user->hasPrivilege("costi_ricavi_edit") || $edit == false){
	$oField->control_type = "label";
	$oField->store_in_db = false;
	$oField->default_value = new ffData(0, "Number");
}
else{
	$oField->required = true;
}
$oRecord->addContent($oField);
$html_legenda .= "<tr><td>".$campo_3_importo_label."</td><td>".$campo3_legenda."</td></tr>";

if ($periodo->id_tipo_periodo == 2) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "campo_4";
	$oField->base_type = "Number";	
	$oField->label = $campo_4_importo_label;
	if (!$user->hasPrivilege("costi_ricavi_edit") || $edit == false){
		$oField->control_type = "label";
		$oField->store_in_db = false;
		$oField->default_value = new ffData(0, "Number");
	}
	else{
		$oField->required = true;
	}
	$oRecord->addContent($oField);	
	$html_legenda .= "<tr><td>".$campo_4_importo_label."</td><td>".$campo4_legenda."</td></tr>";
}

//campi aggiuntivi per la gestione dell'id fp_cdr e dell'id_inserimento_periodo
if ($importo_periodo == null){
	$oRecord->insert_additional_fields["ID_conto"] = new ffData($conto->id, "Number");
	$oRecord->insert_additional_fields["ID_periodo"] = new ffData($periodo->id, "Number");			
}	

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

//descrizione fp periodo
$cm->oPage->addContent("<div id='dettaglio_conto_descrizione_periodo'>"
							. "<h4>Fattore produttivo</h4><p>" . $fp->codice . " - " . $fp->descrizione . "</p>"
							. "<h4>Conto</h4><p>" . $conto->codice . " - " . $conto->descrizione . "</p>"
							. "<h4>Periodo</h4><p>" . $periodo->descrizione . " - anno: " . $anno->descrizione . " (ultima data utile per la rendicontazione: " . date("d/m/Y", strtotime($periodo->data_scadenza)) . ")</p>"
						. "</div>");
//legenda
$html_legenda .= "													
						</tbody>							
					</table>
				";
$cm->oPage->addContent($html_legenda);