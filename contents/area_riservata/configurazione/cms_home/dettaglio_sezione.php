<?php

$isEdit = false;
if (isset($_REQUEST["keys[ID]"])) {
    try {
        $sezione = new CmsHomeSezione($_REQUEST["keys[ID]"]);
        $isEdit = true;
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$anno = $cm->oPage->globals["anno"]["value"];

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "sezioni-home-modify";
$oRecord->title = $isEdit ? "Modifica sezione": "Nuova sezione";
$oRecord->resources[] = "sezioni-home";
$oRecord->src_table  = "cms_home_sezione";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento";
$oField->label = "Ordinamento";
$oField->base_type = "Number";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tipo";
$oField->label = "Tipo";
$oField->base_type = "Text";
if (!$isEdit) {
    $oField->label = "Tipo";
    $oField->extended_type = "Selection";
    $oField->control_type = "radio";
    $oField->multi_pairs = array (
        array(new ffData("H", "Text"), new ffData("HTML", "Text")),
        array(new ffData("A", "Text"), new ffData("ALLEGATO", "Text")),
    );    
    $oField->required = true;
}
else {
    $oField->data_type = "";
    $oField->default_value = new ffData($sezione->getTipoDescrizione(), "Text");
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->label = "Anno inizio";
$oField->base_type = "Number";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->label = "Anno fine";
$oField->base_type = "Number";
$oRecord->addContent($oField);

if ($isEdit) {
    $oRecord->addContent("<hr>");
    foreach ($sezione->getTipoField($cm, $anno, LoggedUser::Instance()) as $oField) {
        $oRecord->addContent($oField);
    }
    
    if ($sezione->isAllegato()) {
        $allegati = CmsHomeSezioneAllegato::getAll(['ID_sezione' => $sezione->id]);
        
        $html .= '
            <table id="allegati-ajax-table" class="table table-striped table-responsive">
                <thead>
                    <tr>
                        <th class="cel-1 text-nowrap ffField text active">Nome File</th>
                        <th class="cel-1 text-nowrap ffField text active">Elimina</th>
                    </tr>
                </thead>
                <tbody>
        ';
        
        if (count($allegati) == 0) {
            $html .= '<tr id="row_no_allegati"><td colspan="2">Nessun allegato caricato</td></tr>';
        }
        else {
            $allegati_helper = new AllegatoHelper();
            
            //START GRANT PERMISSIONS
            $allegati_permissions = $allegati_helper->defineAllegatiPermission($user);
            $allegati_permissions = $allegati_helper->definePermission($allegati, $allegati_permissions, true, true);
            $permission_cookie = $allegati_helper->encodePermissions($allegati_permissions);
            //Call before every output or will not work!!! IMPORTANT
            setcookie('p_2_#', $permission_cookie, time() + 600, '/');
            //END GRANT PERMISSIONS
            
            $key_allegato = 0;
            foreach ($allegati as $allegato) {
                $txt_elimina = $allegati_helper->getDeleteLink($allegato->filename_md5, "Elimina", true);
                $txt_download = $allegati_helper->getDownloadLink($allegato->filename_md5, $allegato->filename_plain);
                
                $html .= '<tr id="al-' . $key_allegato . '" >';
                $html .= "<td>" . $txt_download . "</td><td class='delete'>$txt_elimina</td>";
                $html .= '</tr>';
                
                $key_allegato++;
            }

            $cm->oPage->addContent("
                <div id='inactive_body'></div>
                <div id='conferma_delete_allegato'>
                    <h3>Conferma eliminazione allegato</h3>
                    <a id='conferma_si_eliminare' class='conferma_si confirm_link'>Conferma</a>
                    <a id='conferma_no_eliminare' class='conferma_no confirm_link'>Annulla</a>
                </div>
            ");
        }
        $html .= "</tbody>";
        $html .= "</table>";
        
        $oRecord->addContent($html);
    }
}

CoreHelper::refreshTabOnDialogClose($oRecord->id);

$oRecord->addEvent("on_do_action", "checkRelations");
$oRecord->addEvent("on_do_action", "deleteRelations");
$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {
    $id_sezione = $oRecord->key_fields["ID"]->value->getValue();
    if (isset($id_sezione) && $id_sezione != "") {
        $sezione = new CmsHomeSezione($id_sezione);
    }

    switch ($frmAction) {
        case "insert":
            if (!CmsHomeSezione::isValidRangeAnno(
                    $oRecord->form_fields["anno_inizio"]->value->getValue(),
                    $oRecord->form_fields["anno_fine"]->value->getValue()
                )) {
                CoreHelper::setError($oRecord, "L'anno termine deve essere maggiore o uguale dell'anno introduzione");
            }

            break;
        case "update":
            $anno_introduzione = $oRecord->form_fields["anno_inizio"]->value->getValue();
            $anno_termine = $oRecord->form_fields["anno_fine"]->value->getValue();
            if (!CmsHomeSezione::isValidRangeAnno($anno_introduzione, $anno_termine)) {
                CoreHelper::setError($oRecord, "L'anno termine deve essere maggiore o uguale dell'anno introduzione");
            }

            break;
    }
}

function deleteRelations($oRecord, $frmAction) {
    $id_sezione = $oRecord->key_fields["ID"]->value->getValue();
    if (isset($id_sezione) && $id_sezione != "") {
        $sezione = new CmsHomeSezione($id_sezione);
    }

    switch ($frmAction) {
        case "delete":
        case "confirmdelete":
            
            $sezione->delete();
            break;
    }
}