<?php
$cm->oPage->addBounceComponent("Step1");
$cm->oPage->addBounceComponent("Step3");

$transit_params = $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST) . "ret_url=" . rawurlencode($_REQUEST["ret_url"]) . "&key=" . $_REQUEST["key"];

// dati generici
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "Step2";
$oRecord->title = "Step2 - Dati Database";
$oRecord->buttons_options["insert"]["display"] = false;
$oRecord->buttons_options["cancel"]["display"] = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "db_host";
$oField->label = "Host";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "db_name";
$oField->label = "Name";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "db_user";
$oField->label = "User";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "db_pass";
$oField->label = "Pass";
$oRecord->addContent($oField);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "StepPrev";
$oBt->label = "<< " . ffTemplate::_get_word_by_code("StepPrev");
$oBt->action_type = "submit";
$oBt->form_action_url = "step1?" . $transit_params;
$oBt->frmAction = "step2";
$oBt->aspect = "link";
$oRecord->addActionButton($oBt);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "StepNext";
$oBt->label = ffTemplate::_get_word_by_code("StepNext") . " >>";
$oBt->action_type = "submit";
$oBt->form_action_url = "step3?" . $transit_params;
$oBt->frmAction = "step2";
$oBt->aspect = "link";
$oRecord->addActionButton($oBt);

$cm->oPage->addContent($oRecord);
