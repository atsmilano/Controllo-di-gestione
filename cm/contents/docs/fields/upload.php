<?php
$db = ffDB_Sql::factory(); 
/** 
 * Inizializzazione dell'oggetto ffRecord, 
 * $oRecord è lo standard, ma può essere usato qualsiasi nome, 
 * purchè sia rispettata la coerenza in seguito 
*/
$oRecord = ffRecord::factory($cm->oPage);
/** 
 * ID dell'oggetto. 
 * Se questo oggetto è in relazione con un altro (modifica) è importante che questo campo coincida 
 * con il record_ID del campo originale 
 */
$oRecord->id = "UtentiModify";
/** 
 * resources è un array che viene popolato con gli ID degli oggetti su cui si sta lavorando
 */
$oRecord->resources[] = $oRecord->id;
/**
 * Impedisce che le modifiche effettuate in questa zona di test diventino effettive
 */
$oRecord->skip_action = true;
$oRecord->hide_all_controls = true;
/**
 * Titolo dell'oggetto record 
 */
//$oRecord->title = ffTemplate::_get_word_by_code("utenti_modify");
/**
 * Tabella da cui vengono prese e/o salvate le informazioni.
 * Può essere dichiarata una sola tabella, se i dati arrivano da più fonti la gestione
 * verrà delegata negli eventi
 */
$oRecord->src_table = "cm_mod_security_users";

/**
 * Aggiunta dei tab
 */
//$oRecord->tab = true;

$oRecord->auto_wrap = false;
/** 
 * Inizializzazione dell'oggetto ffField, 
 * elemento base di tutte le sovrastrutture del framework (grid, record e detail) 
 */
$oField = ffField::factory($cm->oPage);
/** 
 * ID del field, deve essere univoco all'interno di un oggetto 
 */
$oField->id = "ID";
/** 
 * Tipo del dato, 
 * se non espresso si sottointende Text 
 */
$oField->base_type = "Number";
/** 
 * Viene dichiarato il campo chiave (possono essere più di uno), 
 * non sono visibili all'interno della tabella (se si vuole vedere questo dato bisogna ridichiararlo sotto). 
 * Nel caso sia un record in modifica è importante abbia un nome coerente con quello del campo chiave in visualizzazione. 
*/
$oRecord->addKeyField($oField);

/**
 * Campi upload
 */
$oRecord->addContent('<h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Upload Semplice</h2>');

$oRecord->addContent('<div class="single-field">');

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";
$oField->widget = "uploadifive";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "avatar";<br />
		$oField->extended_type = "File";<br />
        $oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";<br />
        $oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code);
$oRecord->addContent('</div>');
/******************************************************************************************/

$oRecord->addContent('<h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Upload Semplice con label</h2>');

$oRecord->addContent('<div class="single-field">');

$oField = ffField::factory($cm->oPage);
$oField->id = "avatarLabel";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";
$oField->label = "Inserisci il tuo avatar";
$oField->widget = "uploadifive";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "avatarLabel";<br />
		$oField->extended_type = "File";<br />
        $oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";<br />
        $oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";<br />
        $oField->label = "Inserisci il tuo avatar";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code);
$oRecord->addContent('</div>');
/******************************************************************************************/

$oRecord->addContent('<h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Upload Semplice Senza elimina thumb (nn funziona)</h2>');

$oRecord->addContent('<div class="single-field">');

$oField = ffField::factory($cm->oPage);
$oField->id = "avatarLabelThumb";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";
$oField->label = "Inserisci il tuo avatar";
$oField->file_show_delete = false;
$oField->widget = "uploadifive";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "avatar";<br />
		$oField->extended_type = "File";<br />
        $oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";<br />
        $oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";<br />
        $oField->label = "Inserisci il tuo avatar";<br />
        $oField->file_show_delete = false;<br />
		        
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code);
$oRecord->addContent('</div>');
/******************************************************************************************/

$oRecord->addContent('<h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Upload Semplice con label e visualizza thumb filename</h2>');

$oRecord->addContent('<div class="single-field">');

$oField = ffField::factory($cm->oPage);
$oField->id = "avatarLabelFilename";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";
$oField->label = "Inserisci il tuo avatar";
$oField->file_show_filename = true;
$oField->widget = "uploadifive";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "avatarLabelFilename";<br />
		$oField->extended_type = "File";<br />
        $oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";<br />
        $oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";<br />
        $oField->label = "Inserisci il tuo avatar";<br />
        $oField->file_show_filename = true;<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code);
$oRecord->addContent('</div>');
/******************************************************************************************/


$oRecord->addContent('<h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Upload Semplice con label e nasconde preview(non funziona)</h2>');

$oRecord->addContent('<div class="single-field">');

$oField = ffField::factory($cm->oPage);
$oField->id = "avatarLabelnoPreview";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";
$oField->label = "Inserisci il tuo avatar";
$oField->file_show_preview = false;
$oField->widget = "uploadifive";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "avatarLabelFilename";<br />
		$oField->extended_type = "File";<br />
        $oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";<br />
        $oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";<br />
        $oField->label = "Inserisci il tuo avatar";<br />
        $oField->file_show_preview = false;<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code);
$oRecord->addContent('</div>');
/******************************************************************************************/


$oRecord->addContent('<h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Upload Semplice con label Multiplo</h2>');

$oRecord->addContent('<div class="single-field">');

$oField = ffField::factory($cm->oPage);
$oField->id = "avatarLabelMulti";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";
$oField->label = "Inserisci galleria immagini";
$oField->file_multi = true;
$oField->widget = "uploadifive";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "avatarLabelFilename";<br />
		$oField->extended_type = "File";<br />
        $oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";<br />
        $oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";<br />
        $oField->label = "Inserisci galleria immagini";<br />
        $oField->file_multi = true;<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code);
$oRecord->addContent('</div>');
/******************************************************************************************/

$oRecord->addContent('<h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Upload Semplice con label Multiplo con Edit</h2>');

$oRecord->addContent('<div class="single-field">');

$oField = ffField::factory($cm->oPage);
$oField->id = "avatarLabelMultiEditable";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";
$oField->label = "Inserisci galleria immagini";
$oField->file_multi = true;
$oField->widget = "uploadifive";
$oField->store_in_db = false;

//todo: da fare senza fanzy
if(!$dialog_loaded) { //todo: da sistemare
    $dialog_loaded = "showfilesManage";
    $cm->oPage->widgetLoad("dialog");
    $cm->oPage->widgets["dialog"]->process(
        $dialog_loaded
        , array(
            "title"          => $component->label
        , "tpl_id"        => null
        )
        , $cm->oPage
    );
}
//$oField->file_sortable = true;
$oField->file_show_edit = true;
//todo: da fare con mimetype e uploads max impostati


$oField->setWidthComponent(6);
$oRecord->addContent($oField);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "avatarLabelFilename";<br />
		$oField->extended_type = "File";<br />
        $oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";<br />
        $oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";<br />
        $oField->label = "Inserisci galleria immagini";<br />
        $oField->file_multi = true;<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code);
$oRecord->addContent('</div>');
/******************************************************************************************/


/*

        $oField->file_max_size = 1000000;<br />
		$oField->file_check_exist = true;<br />
		$oField->file_normalize = true;<br />
*/


$oField->file_full_path = true;


/**
 * Viene innestato l'oggetto $oRecord all'interno della pagina
 */
$cm->oPage->addContent('<style>.single-field{float: left;width: 100%;border-bottom: 1px solid #eee;padding-bottom: 40px;margin-bottom: 40px;}</style>');
$cm->oPage->addContent($oRecord);