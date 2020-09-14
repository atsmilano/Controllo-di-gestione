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
$oRecord->tab = true;

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
 * Campi di base
 */
$group_field = "base";
$oRecord->addContent(null, true, $group_field);
$oRecord->groups[$group_field] = array(
	"title" => ucfirst($group_field)
);

$oRecord->addContent('<h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Campo Semplice</h2>', $group_field);

/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Stringa"; 	//ffTemplate::_get_word_by_code("name");
$oField->base_type = "Text";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		/* Required */<br />
		$oField->id = "name";<br />
		/* Optional */<br />
		$oField->label = "Stringa";<br />
		$oField->base_type = "Text";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "name2";
$oField->placeholder = "Placeholder senza label";
$oField->base_type = "Text";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "name2";<br />
		$oField->placeholder = "Placeholder senza label";<br />
		$oField->base_type = "Text";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "name5";
$oField->label = "Placeholder con label";
$oField->placeholder = "Placeholder con label";
$oField->base_type = "Text";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "name5";<br />
		$oField->label = "Placeholder con label";<br />
		$oField->placeholder = "Placeholder con label";	<br />
		$oField->base_type = "Text";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "name5bis";
$oField->label = "Placeholder con label";
$oField->description = " e descrizione aggiuntiva";
$oField->placeholder = "Placeholder con label";
$oField->base_type = "Text";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "name5bis";<br />
		$oField->label = "Placeholder con label";<br />
		$oField->description = " e descrizione aggiuntiva";<br />
		$oField->placeholder = "Placeholder con label";	<br />
		$oField->base_type = "Text";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "name6";
$oField->label = "Campo con valore di default";
$oField->default_value = new ffData("Valore di default", "Text");
$oField->base_type = "Text";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "name6";<br />
        $oField->label = "Campo con valore di default";<br />
        $oField->default_value = new ffData("Valore di default", "Text");<br />
        $oField->base_type = "Text";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "name7";
$oField->label = "Campo disattivato";
$oField->default_value = new ffData("Campo disattivato", "Text");
$oField->properties["disabled"] = "disabled";
$oField->base_type = "Text";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "name7";<br />
        $oField->label = "Campo disattivato";<br />
        $oField->properties["disabled"] = "disabled";<br />
        $oField->default_value = new ffData("Campo disattivato", "Text");<br />
        $oField->base_type = "Text";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);

/**
 * Tipi di dato
 */
$group_field = "tipo";
$oRecord->addContent(null, true, $group_field);
$oRecord->groups[$group_field] = array(
    "title" => ucfirst($group_field)
);

$oRecord->addContent('<hr /><h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Tipi di dato</h2>', $group_field);

$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "name3";
$oField->label = "Stringa";
$oField->base_type = "Text";
$oField->default_value = new ffData("Campo stringa", "Text");
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code> 
		$oField = ffField::factory($cm->oPage);<br />
		/* Required */<br />
		$oField->id = "name3";<br />
		/* Optional */<br />
		$oField->label = "Stringa";<br />
		$oField->base_type = "Text";<br />
        $oField->default_value = new ffData("Campo stringa", "Text");<br /> 
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "number";
$oField->label = "Numero"; 	//ffTemplate::_get_word_by_code("name");
$oField->base_type = "Number";
$oField->default_value = new ffData("4", "Number");
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "number";<br />
		$oField->label = "Numero";<br />
        $oField->base_type = "Number";<br />
        $oField->default_value = new ffData("4", "Number");<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "price";
$oField->base_type = "Number";
$oField->app_type = "Currency";
$oField->label = "Prezzo";
$oField->store_in_db = false;
$oField->fixed_post_content = '&euro;';
$oField->default_value = new ffData("10.10", "Number");
$oField->setWidthComponent(6);

$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "price";<br />
		$oField->label = "Prezzo";<br />
		$oField->base_type = "Currency";<br />
        $oField->fixed_post_content = "&euro;";<br /> 
        $oField->default_value = new ffData("10.10", "Number");<br />
		$oRecord->addContent($oField);
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "dataText";
$oField->base_type = "Date";
$oField->default_value = new ffData("2018-02-27", "Date");
$oField->label = "Data";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "dataText";<br />
		$oField->label = "Data";<br />
		$oField->base_type = "Date";<br />
		$oField->default_value = new ffData("2018-02-27", "Date");<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "timeText";
$oField->base_type = "Time";
$oField->default_value = new ffData("20:10:00", "Time");
$oField->label = "Orario";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "timeText";<br />
		$oField->label = "Orario";<br />
		$oField->base_type = "Time";<br />
		$oField->default_value = new ffData("20:10:00", "Time");<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "dataTimeText";
$oField->base_type = "DateTime";
$oField->default_value = new ffData("2018-02-27 14:00:00", "DateTime");
$oField->label = "Data";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "dataTimeText";<br />
		$oField->label = "Data";<br />
		$oField->base_type = "DateTime";<br />
		$oField->default_value = new ffData("2018-02-27 14:00:00", "Date");<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "dataTimestamp";
$oField->label = "Data timestamp";
$oField->base_type = "Timestamp";
$oField->extended_type = "Date";
$oField->default_value = new ffData("1519731799", "Timestamp");
$oField->app_type = "Date";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "dataTimestamp";<br />
		$oField->label = "Data timestamp";<br />
		$oField->base_type = "Timestamp";<br />
		$oField->default_value = new ffData("1519731799", "Timestamp");<br />
        $oField->extended_type = "Date";<br />
        $oField->app_type = "Date";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "textarea";
$oField->label = "Textarea";
$oField->base_type = "Text";
$oField->control_type = "textarea";
$oField->default_value = new ffData("Questo è un campo di tipo testuale", "Text");
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "textarea";<br />
		$oField->label = "Textarea";<br />
		$oField->base_type = "Text";<br />
        $oField->control_type = "textarea";<br />
        $oField->default_value = new ffData("Questo è un campo di tipo testuale", "Text");<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "password";
$oField->label = "Password";
$oField->extended_type = "Password";
$oField->crypt_method = "mysql_password";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "password";<br />
		$oField->label = "Password";<br />
		$oField->extended_type = "Password";<br />
        $oField->crypt_method = "mysql_password";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "boolean";
$oField->label = "Boolean";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("0", "Number");
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "boolean";<br />
		$oField->label = "Boolean";<br />
        $oField->base_type = "Number";<br />
        $oField->extended_type = "Boolean";<br />
        $oField->control_type = "checkbox";<br />
        $oField->checked_value = new ffData("1", "Number");<br />
        $oField->unchecked_value = new ffData("0", "Number");<br />
        $oField->default_value = new ffData("0", "Number");<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "booleanSelezionato";
$oField->label = "Boolean selezionato";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("1", "Number");
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "booleanSelezionato";<br />
		$oField->label = "Boolean Selezionato";<br />
        $oField->base_type = "Number";<br />
        $oField->extended_type = "Boolean";<br />
        $oField->control_type = "checkbox";<br />
        $oField->checked_value = new ffData("1", "Number");<br />
        $oField->unchecked_value = new ffData("0", "Number");<br />
        $oField->default_value = new ffData("1", "Number");<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/



/*
$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("module_question_title") . "*";
$oField->required = true;
$oField->display_label = false;
$oField->placeholder = true;
$oField->default_value = new ffData($_COOKIE["esperto"]["title"]);
$oField->encode_entities = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "question";
$oField->label = ffTemplate::_get_word_by_code("module_question_question");
$oField->base_type = "Text";
$oField->encode_entities = false;
$oRecord->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "question";
$oField->label = ffTemplate::_get_word_by_code("module_question_question");
$oField->base_type = "Text";
$oField->control_type = "textarea";
$oField->display_label = false;
$oField->placeholder = true;
$oField->fixed_post_content = '<div class="label-helper">' . ffTemplate::_get_word_by_code("new_question_text_helper") . '</div>';
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField);+/

/**
 * Campi upload
 */
$group_field = "upload";
$oRecord->addContent(null, true, $group_field);
$oRecord->groups[$group_field] = array(
	"title" => ucfirst($group_field)
);

$oRecord->addContent('<hr /><h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Upload Semplice</h2>', $group_field);

$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar";
$oField->container_class = "avatar_uploadifive";
$oField->label = ffTemplate::_get_word_by_code("uploadifive");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/docs/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/docs";
$oField->file_max_size = 1000000;
$oField->file_show_filename = true;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/avatar/[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->widget = "uploadifive";
$oField->store_in_db = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/

/**
 * Campi combo
 */

$group_field = "combo";
$oRecord->addContent(null, true, $group_field);
$oRecord->groups[$group_field] = array(
	"title" => ucfirst($group_field)
);
$oRecord->addContent('<hr /><h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Campo di selezione (Selection)</h2>', $group_field);

$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id="utenti";

$oField->extended_type = "Selection"; //Questa istruzione dirà che questo è un campo speciale, che nel nostro caso si chiamerà "Selection"
/*
 * Nella source_SQL va inserita una SELECT che selezionerà solamente due campi
 * Il primo sarà quello salvato nel database
 * Il secondo sarà quello visualizzato dall'utente
 */
$oField->source_SQL = "  SELECT ID, username FROM cm_mod_security_users
                          WHERE 1 
                          [AND] [WHERE] [HAVING] [ORDER]";

/*
 * Questa istruzione imposta un campo extra che avrà, di default, valore nullo.
 * Di base è impostata a true
 */
$oField->multi_select_one = true;

/*
 * Questa istruzione imposta la scritta che vogliamo sul campo extra.
 * Di base è "Selezionare Un Elemento.."
 */
$oField->multi_select_one_label = "Selezionare Un Elemento..";

/*
 * Questa istruzione istruzione imposta un valore per il campo extra
 * N.B. Il valore deve essere di tipo ffData!
 * Il valore di defaul è null
 */
$oField->multi_select_one_val = new ffData(0);

$oField->base_type = "Text";
$oField->label = "Seleziona utente:";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage); <br />
        $oField->id="utenti"; <br />
        <br />
        /*Questa istruzione dirà che questo è un campo più complesso, che nel nostro caso si chiamerà "Selection"*/ <br />
        $oField->extended_type = "Selection"; <br /> 
        <br />
        /* <br />
         * Nella source_SQL va inserita una SELECT che selezionerà solamente due campi <br />
         * Il primo sarà quello salvato nel database <br />
         * Il secondo sarà quello visualizzato dall\'utente <br />
         */ <br />
        $oField->source_SQL = "  SELECT ID, username FROM cm_mod_security_users <br />
                                  WHERE 1 <br />
                                  [AND] [WHERE] [HAVING] [ORDER]"; <br />
        <br />
        /* <br />
         * Questa istruzione imposta un campo extra che avrà, di default, valore nullo. <br />
         * Di base è impostata a true <br />
         */ <br />
        $oField->multi_select_one = true; <br />
        <br />
        /* <br />
         * Questa istruzione imposta la scritta che vogliamo sul campo extra. <br />
         * Di base è "Selezionare Un Elemento.." <br />
         */ <br />
        $oField->multi_select_one_label = "Selezionare Un Elemento.."; <br />
        
        /* <br />
         * Questa istruzione istruzione imposta un valore per il campo extra <br />
         * N.B. Il valore deve essere di tipo ffData! <br />
         * Il valore di defaul è null <br />
         */ <br />
        $oField->multi_select_one_val = new ffData(0); <br />
        <br />
        $oField->base_type = "Text"; <br />
        $oField->label = "Seleziona utente:"; <br />
        $oRecord->addContent($oField); <br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/

/**
 * Campi con widget
 */
$group_field = "widgets";
$oRecord->addContent(null, true, $group_field);
$oRecord->groups[$group_field] = array(
	"title" => ucfirst($group_field)
);
$oRecord->addContent('<hr /><h2 class="' . cm_getClassByFrameworkCss(array(12), "col") . '">Widgets</h2>', $group_field);

$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "actexSimple";
$oField->label = "Combo Async (actex)";
$oField->base_type = "Number";
$oField->widget = "actex";
$oField->source_SQL = "SELECT cm_mod_security_users.ID
                        , cm_mod_security_users.username
                    FROM cm_mod_security_users
                    WHERE 1
                    ORDER BY cm_mod_security_users.username
                    [LIMIT]";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
        $oField->id = "actexSimple";<br />
        $oField->label = "actexSimple";<br />
        $oField->base_type = "Number";<br />
        $oField->widget = "actex";<br />
        $oField->source_SQL = "SELECT cm_mod_security_users.ID<br />
                                , cm_mod_security_users.username<br />
                            FROM cm_mod_security_users<br />
                            WHERE 1<br />
                            ORDER BY cm_mod_security_users.username<br />
                            [LIMIT]";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "datePicker";
$oField->label = "Date Picker";
$oField->base_type = "Date";
$oField->extended_type = "Date";
$oField->widget = "datepicker";
$oField->default_value = new ffData("2017-02-27", "Date");
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
        $oField->id = "datePicker";<br />
        $oField->label = "Date Picker";<br />
        $oField->base_type = "Date";<br />
        $oField->extended_type = "Date";<br />
        $oField->widget = "datepicker";<br />
        $oField->default_value = new ffData("2017-02-27", "Date");<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "dateChooser";
$oField->base_type = "Timestamp";
$oField->label = "Date Chooser";
$oField->widget = "datechooser";
$oField->datechooser_type_date = array("min" => - 1, "max" => 2);
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
        $oField->id = "dateChooser";<br />
        $oField->base_type = "Timestamp";<br />
        $oField->label = "Date Chooser";<br />
        $oField->widget = "datechooser";<br />
        $oField->datechooser_type_date = array("min" => - 1, "max" => 2);<br />
        $oField->extended_type = "Date";<br />
        $oField->app_type = "Date";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "actexAutocomplete";
$oField->label = "Autocomplete";
$oField->widget = "actex";
$oField->actex_autocomp = true;
$oField->autocomplete_minLength = 0;
$oField->autocomplete_combo = true;
$oField->autocomplete_compare = "CONCAT(cm_mod_security_users.username, ' (', cm_mod_security_users.email, ')')";
$oField->autocomplete_operation = "LIKE [%[VALUE]%]";
$oField->source_SQL = "SELECT cm_mod_security_users.ID
                          , CONCAT(cm_mod_security_users.username, ' (', cm_mod_security_users.email, ')') AS name_surname
                        FROM cm_mod_security_users
                        WHERE 1
                        [AND][WHERE][HAVING]
                        ORDER BY name_surname";
$oField->actex_update_from_db = true;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
        $oField->id = "actexAutocomplete";<br />
        $oField->label = "Autocomplete";<br />
        $oField->widget = "actex";<br />
        $oField->actex_autocomp = true;<br />
        $oField->autocomplete_minLength = 0;<br />
        $oField->autocomplete_combo = true;<br />
        $oField->autocomplete_compare = "CONCAT(cm_mod_security_users.username, \' (\', cm_mod_security_users.email, \')\')";<br />
        $oField->autocomplete_operation = "LIKE [%[VALUE]%]";<br />
        $oField->source_SQL = "SELECT cm_mod_security_users.ID<br />
                                  , CONCAT(cm_mod_security_users.username, \' (\', cm_mod_security_users.email, \')\') AS name_surname<br />
                                FROM cm_mod_security_users<br />
                                WHERE 1<br />
                                [AND][WHERE][HAVING]<br />
                                ORDER BY name_surname";<br />
        $oField->actex_update_from_db = true;<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/
$oRecord->addContent('<div class="single-field">', $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "ckeditor";
$oField->label = "ckEditor"; 	//ffTemplate::_get_word_by_code("name");
$oField->base_type = "Text";
$oField->widget = "ckeditor";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$code = '<div class="' . cm_getClassByFrameworkCss(array(6), "col") . '">
	<code>
		$oField = ffField::factory($cm->oPage);<br />
		$oField->id = "ckeditor";<br />
		$oField->label = "ckEditor";<br />
		$oField->base_type = "Text";<br />
		$oField->widget = "ckeditor";<br />
		$oRecord->addContent($oField);<br />
	</code>
</div>';
$oRecord->addContent($code, $group_field);
$oRecord->addContent('</div>', $group_field);
/******************************************************************************************/




/** 
 * Viene innestato l'oggetto $oRecord all'interno della pagina 
 */
$cm->oPage->addContent('<style>.single-field{float: left;width: 100%;border-bottom: 1px solid #eee;padding-bottom: 40px;margin-bottom: 40px;}</style>');
$cm->oPage->addContent($oRecord);