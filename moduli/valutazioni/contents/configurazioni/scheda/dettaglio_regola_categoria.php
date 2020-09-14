<?php
if (isset($_REQUEST["keys[ID_categoria]"])) {    
    $id_categoria = $_REQUEST["keys[ID_categoria]"];

    try {
        $categoria = new ValutazioniCategoria($id_categoria);        
        if (isset($_REQUEST["keys[ID_regola]"])) {    
            $isEdit = true;
            $id_regola = $_REQUEST["keys[ID_regola]"];

            try {
                $regola = new ValutazioniRegolaCategoria($id_regola);
                if ($regola->id_categoria !== $categoria->id) {
                    ffErrorHandler::raise("Errore nel passaggio dei parametri: categoria e regola non coerenti");
                }

            } catch (Exception $ex) {
                ffErrorHandler::raise($ex->getMessage());
            }
        }        
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

//record regole categoria
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "categoria-regola-modify";
$oRecord->title = "Regola per l'apertura delle schede";
$oRecord->src_table = "valutazioni_regola_categoria";
$oRecord->resources[] = "categoria-regola";     

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_regola";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_categoria";
$oField->base_type = "Number";
$oField->label = "Categoria";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                            array(
                                new ffData ($categoria->id, "Number"),
                                new ffData ($categoria->descrizione, "Text")
                            )
                        );
$oField->default_value = new ffData ($categoria->id, "Number");
$oField->control_type = "label";
$oRecord->addContent($oField);

//costruzione dell array degli attributi da utilizzare come parametri
$attributi_select = array();
foreach (ValutazioniRegolaCategoria::getAttributi() as $attributo) {
    $attributi_select[] = array(
                            new ffData ($attributo["ID"], "Number"),
                            new ffData ($attributo["descrizione"], "Text")
                            );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_attributo";
$oField->base_type = "Number";
$oField->label = "Attributo";
$oField->extended_type = "Selection";
$oField->widget = "activecomboex";
$oField->multi_pairs = $attributi_select;
$oField->actex_child = "valore";
$oField->required = true;
$oRecord->addContent($oField);

$valori_select = array();
foreach (ValutazioniRegolaCategoria::getValoriSelezionabili() as $valore_selezionabile){        
    $valori_select[] = array(
                            new ffData ($valore_selezionabile["ID_attributo"], "Number"),
                            new ffData ($valore_selezionabile["valore"], "Number"),
                            new ffData ($valore_selezionabile["descrizione"], "Text")
                            );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "valore";
$oField->base_type = "Number";
$oField->label = "Valore";
$oField->extended_type = "Selection";
$oField->widget = "activecomboex";
$oField->multi_pairs = $valori_select;
$oField->actex_father = "ID_attributo"; 
$oField->required = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);