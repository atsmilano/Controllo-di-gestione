<?php
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "MainGrid";
$oGrid->title = "Domains";
$oGrid->source_SQL = "SELECT 
							" . CM_TABLE_PREFIX . "mod_security_domains.*
						FROM 
							" . CM_TABLE_PREFIX . "mod_security_domains
						[WHERE] [HAVING] [ORDER]";

$oGrid->order_default = "nome";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
{
	$oGrid->record_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/wizard/step1";
	$oGrid->addEvent("on_before_parse_row", "MainGrid_on_before_parse_row");
}
$oGrid->record_id = "ModSecDomains";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->label = "Name";
$oGrid->addContent($oField);

if (strlen(MOD_SEC_DOMAIN_COMPANY))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = MOD_SEC_DOMAIN_COMPANY;
	$oField->label = "Company";
	$oGrid->addContent($oField);
}

if (strlen(MOD_SEC_DOMAIN_CREATION))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "creation_date";
	$oField->label = "Data Creazione";
	$oField->base_type = "Date";
	$oGrid->addContent($oField);
}
if (strlen(MOD_SEC_DOMAIN_EXPIRATION))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "expiration_date";
	$oField->label = "Data Scadenza";
	$oField->base_type = "Date";
	$oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = "Status";
$oField->extended_type = "Selection";
$oField->multi_select_one = false;
$oField->multi_pairs = array(
								array( new ffData("0"),  new ffData("Disabled")),
								array( new ffData("1"),  new ffData("Enabled"))
							);
$oGrid->addContent($oField);

if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "link";
	$oField->label = "Connection";
	$oField->data_type = "";
	$oGrid->addContent($oField);
}

if (MOD_SEC_PACKAGES)
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_packages";
    $oField->label = "Tipo Pacchetto";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_select_one_label = "Non impostato";
    $oField->source_SQL = "SELECT
                                    ID
                                    , CASE
                                            WHEN `type` = '0' THEN CONCAT(`name`, ' (pubblico)')
                                            WHEN `type` = '1' THEN CONCAT(`name`, ' (privato)')
                                            ELSE `name`
                                    END
                                FROM
                                    " . CM_TABLE_PREFIX . "mod_security_packages
                                ORDER BY
                                    `order`
        ";
    $oGrid->addContent($oField);
}
// Campi di ricerca
$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->label = "Name";
$oField->src_operation 	= "[NAME] LIKE [VALUE]";
$oField->src_prefix 	= "%";
$oField->src_postfix 	= "%";
$oGrid->addSearchField($oField);

if (strlen(MOD_SEC_DOMAIN_COMPANY))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "company_name";
	$oField->label = "Company";
	$oField->src_operation 	= "[NAME] LIKE [VALUE]";
	$oField->src_prefix 	= "%";
	$oField->src_postfix 	= "%";
	$oGrid->addSearchField($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = "Status";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "All";
$oField->multi_pairs = array( 
								array( new ffData("0"),  new ffData("Disabled")),
								array( new ffData("1"),  new ffData("Enabled"))
							);
$oGrid->addSearchField($oField);

$cm->oPage->addContent($oGrid);

function MainGrid_on_before_parse_row($oGrid)
{
	$rc = mod_security_get_db_by_domain($oGrid->key_fields["ID"]->value->getValue());
	if ($rc === false)
		$oGrid->grid_fields["link"]->setValue("offline");
	else
		$oGrid->grid_fields["link"]->setValue("online");
}