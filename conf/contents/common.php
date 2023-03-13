<?php

//******************************************************************************************************************************************
//verifica sul percorso della pagina e privilegi accesso
//******************************************************************************************************************************************
$cm->modules["security"]["events"]->addEvent("mod_security_on_check_session", "getUser");

function getUser($oPage)
{
    $cm = cm::getInstance();
    //******************************************************************************************************************************************
    //gestione dei campi di selezione per parametri globali
    //******************************************************************************************************************************************
    //anno budget già gestito in conf/common.php
    $anno_selezionato = $cm->oPage->globals["anno"]["value"];
    //viene salvata come global la data dell'anno selezionato    
    if ($anno_selezionato->descrizione == date("Y")) {
        $data_riferimento = new DateTime("NOW");
    }
    else {
        $data_riferimento = new DateTime($anno_selezionato->descrizione . "-12-31");
    }
    $cm->oPage->register_globals("data_riferimento", $data_riferimento, false);
    unset($data_riferimento);
    //******************************************************************************************************************************************
    //***************************************
    //valorizzazione del campo tipo piano cdr
    $id_tipo_piano_selezionato = 0;
    if (isset($_REQUEST["tipo_piano_cdr"])) {
        $id_tipo_piano_selezionato = $_REQUEST["tipo_piano_cdr"];
    }

    $tipo_piano_selezionato = 0;
    try {
        //viene istanziato il piano cdr dal parametro in url
        $tipo_piano_selezionato = new TipoPianoCdr($id_tipo_piano_selezionato);
        $found = false;
        //se il parametro di selezione del piano risulta valido viene utilizzato se ha almeno un cdr associato di responsabiltà dell'utente
        foreach ($anno_selezionato->getTipiPianoCdrAttiviUtente() as $tipo_piano) {
            if ($tipo_piano->id == $tipo_piano_selezionato->id) {
                $found = true;
                break;
            }
        }
        if ($found == false) {
            $tipo_piano_selezionato = null;
        }
    } catch (Exception $ex) {
        //altrimenti viene selezionato il piano con priorità più alta (primo estratto da getall) con almeno un cdr di responsabilità dell'utente        
        $tipi_piano_cdr = $anno_selezionato->getTipiPianoCdrAttiviUtente();
        if (count($tipi_piano_cdr) > 0) {
            $tipo_piano_selezionato = $tipi_piano_cdr[0];
        }
    }
    $cm->oPage->register_globals("tipo_piano_cdr", $tipo_piano_selezionato, false);
    //***************************************
    //valorizzazione del campo cdr		
    $cdr = 0;
    if ($tipo_piano_selezionato !== 0) {
        //estrazione piano cdr
        $dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
        $date = $dateTimeObject->format("Y-m-d");
        $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_selezionato, $date);

        //******************************************************************************************************************************************
        //recupero utente e informazioni correlate
        //******************************************************************************************************************************************
        $user = LoggedUser::getInstance();
        //si prova ad istanziare il cdr dal parametro passato in url, in caso contrario si opta per preselezionare il cdr
        if (isset($_REQUEST["cdr"])) {
            try {
                $cdr = new Cdr($_REQUEST["cdr"]);
            } catch (Exception $ex) {
                
            }
        }
        //se non è stato selezionato nessun parametro e l'utente è resposnabile di qualche cdr viene preselezionato quello        
        $personale = Personale::factoryFromMatricola($user->matricola_utente_selezionato);

        //se non è stato selezionato un cdr oppure è selezionato un cdr non visibile                      
        $cdr_visibili_piano = $personale->getCdrVisibiliPianoCdr($piano_cdr, $dateTimeObject);

        $cm->oPage->register_globals("cdr_visibili", $cdr_visibili_piano, false);
        //se è selezionato un cdr ma non è visibile dall'utente
        if ($cdr !== 0) {
            $cdr_selezionato_visibile = false;
            foreach ($cdr_visibili_piano as $cdr_visibile) {
                if ($cdr_visibile["cdr"]->id == $cdr->id) {
                    $cdr_selezionato_visibile = true;
                    break;
                }
            }
            if ($cdr_selezionato_visibile == false) {
                $cdr = 0;
            }
        }
        //se non è selezionato nessun cdr visibile
        if ($cdr == 0 && count($cdr_visibili_piano) > 0) {
            $cdr = $cdr_visibili_piano[0]["cdr"];
        }
        //se è selezionato un cdr vengono recuperati i privilegi
        if ($cdr !== 0) {
            foreach ($cdr->getPrivileges($personale, $dateTimeObject) as $privilege) {
                $user->user_privileges[] = $privilege;
            }
        }
    }
    //useSql viene impostato a false perchè il cdr sicuramente utilizzerà il piano istanziato
    $cdr->useSql = false;
    $cm->oPage->register_globals("cdr", $cdr, false);
    //******************************************************************************************************************************************
    //generazione del menu
    //******************************************************************************************************************************************
    require(FF_DISK_PATH . "/conf/contents/menu_gen.php");
    //fine generazione menu*********************************************************************************************************************
    //verifica centralizzata per accesso a moduli
    $request_uri = $_SERVER["REQUEST_URI"];

    //chiamata a getCurrentModuleMenuItem per calcolo permessi
    $menu_item = \core\Modulo::getCurrentModuleMenuItem($request_uri);

    if (
            (
            $menu_item["hide"] == true || $menu_item == null || $menu_item["icon"] == MODULES_ICONHIDE
            ) && (
            $menu_item["path"] != "/area_riservata")
    ) {
        ffRedirect(FF_SITE_PATH . "/area_riservata?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
    }

    if (!$cm->oPage->isXHR()) {

        if (strpos($_SERVER["QUERY_STRING"], "?")) {
            $_SERVER["QUERY_STRING"] = str_replace("?", "", $_SERVER["QUERY_STRING"]);
            ffRedirect(FF_SITE_PATH . $cm->path_info . "?" . $_SERVER['QUERY_STRING']);
        }

        $i = 0;
        //Decodifica dell'url, finchè c'è un match con stringhe del tipo %2F, prosegue
        //massimo 5 esecuzioni per evitare timeout php, se vengono raggiunte le 5 esecuzioni:
        //utente reindirizzato alla home
        while (preg_match("/%\w{2}/", $request_uri)) {
            if ($i == 5) {
                ffRedirect(FF_SITE_PATH . "/area_riservata?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
            }
            $request_uri = urldecode($request_uri);
            $i++;
        }

        //Split della stringa sulla base di ret_url=  n.b. se ret_url non c'è il primo elemento è l'url!
        $urls = explode("ret_url=", $request_uri);

        //interessa solo l'ultima url più, perchè identifica da dove si proviene
        $url = $urls[count($urls) - 1];

        //chiamata a getCurrentModuleMenuItem per selezione voce di menu
        $modulo = \core\Modulo::getCurrentModuleMenuItem($url, false);

        $cm->oPage->addContent('
        <script>
            var key =  "' . $modulo["key"] . '";
            var classIdentifier = "' . $modulo["class"] . '";
            classIdentifier = "." + classIdentifier.split(\' \').join(\'.\');

            var menuSelector = ".sidemenu > UL";

            // Controllo particolare per voce di menù HOME
            if (key != "default") {
                $("a." + key).parent().find(".menu-caret").toggleClass("fa-rotate-90");
                $(menuSelector + " a." + key).click();
                $(classIdentifier).attr("style", "background-color: #000000");
            }
            else {
                $("a.home").attr("style", "background-color: #000000");
            }
        </script>
        ');
    }

    //vengono caricati i css del core
    $n = 0;
    foreach (glob(FF_DISK_PATH . FF_THEME_DIR . DIRECTORY_SEPARATOR . "ats" . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . "*.css") as $filename) {
        $css_path = explode(DIRECTORY_SEPARATOR, realpath($filename));
        $css_file = array_pop($css_path);

        $cm->oPage->tplAddCss("core_" . $n, array(
            "path" => FF_THEME_DIR . "/ats/css",
            "file" => $css_file,
        ));
        $n++;
    }


    $currentModule = \core\Modulo::getCurrentModule();
    \core\Modulo::loadCss($currentModule);
}

//******************************************************************************************************************************************
//generazione dei campi di selezione per parametri globali
//******************************************************************************************************************************************
$cm->oPage->addEvent("on_tpl_layer_loaded", "showSelectionFields");

function showSelectionFields(ffPage_base $oPage)
{
    if (defined("MOD_SECURITY_SESSION_STARTED")) {
        $user = LoggedUser::getInstance();
        if ($user->hasPrivilege("user_selection") || $user->hasPrivilege("delega_accesso")) {
            $dipendente_selection = true;
        }
        else {
            $dipendente_selection = false;
        }
        //******************************************
        //campo per la selezione del dipendente (solo amministratori)
        if ($dipendente_selection == true) {
            //costruzione array selezione dipendenti (con utente loggato come default)
            $dipendenti_select = array();
            $dipendenti_select[] = array(
                new ffData(0, "Text"),
                new ffData("Utente loggato", "Text"),
            );
            if ($user->hasPrivilege("user_selection")) {
                $dipendenti_selezionabili = Personale::getAll();
            }
            else if ($user->hasPrivilege("delega_accesso")) {
                foreach ($user->deleghe_accesso as $delega) {
                    $dipendenti_selezionabili[] = Personale::factoryFromMatricola($delega["matricola_utente"]);
                }
            }
            foreach ($dipendenti_selezionabili as $dipendente) {
                $dipendenti_select[] = array(
                    new ffData($dipendente->id, "Text"),
                    new ffData($dipendente->cognome . " " . $dipendente->nome . " (matr. " . $dipendente->matricola . ")", "Text"),
                );
            }

            $dipendente_selezionato = null;
            if ($oPage->globals["dipendente"]["value"] != null) {
                $dipendente_selezionato = $oPage->globals["dipendente"]["value"];
                //viene verificato per la descrizione (controlli formali già effettuati in LoggedUser) che l'utente selezionato sia fra quelli selezionabili per l'utente
                $found = false;
                foreach ($dipendenti_selezionabili as $dipendente) {
                    if ($dipendente_selezionato->matricola == $dipendente->matricola) {
                        $found = true;
                        break;
                    }
                }
                if ($found == true) {
                    $desc_dipendente = $dipendente_selezionato->cognome . " " . $dipendente_selezionato->nome . " (matr. " . $dipendente_selezionato->matricola . " )";
                }
            }
            if ($dipendente_selezionato == null) {
                $desc_dipendente = "Utente Loggato";
            }

            //visualizzazione ffield
            $oFieldDipendente = ffField::factory($oPage);
            $oFieldDipendente->id = "dipendente";
            $oFieldDipendente->base_type = "Text";
            $oFieldDipendente->extended_type = "Selection";
            $oFieldDipendente->multi_pairs = $dipendenti_select;
            $oFieldDipendente->setValue($dipendente_selezionato->id);
            $oFieldDipendente->multi_select_one = false;
        }

        //******************************************
        //campo per la selezione dell'anno di budget
        //recupero di tutti gli anni di budget da visualizzare    
        $anni_select = array();
        foreach (AnnoBudget::getAll() AS $anno_budget) {
            if ($anno_budget->attivo == 1)
                $anni_select[] = array(
                    new ffData($anno_budget->id, "Number"),
                    new ffData("Anno budget: " . $anno_budget->descrizione, "Text")
                );
        }

        //visualizzazione ffield
        $anno_budget = $oPage->globals["anno"]["value"];

        $oFieldAnno = ffField::factory($oPage);
        $oFieldAnno->id = "anno";
        $oFieldAnno->base_type = "Number";
        $oFieldAnno->extended_type = "Selection";
        $oFieldAnno->multi_pairs = $anni_select;
        $oFieldAnno->setValue($anno_budget->id);
        $oFieldAnno->multi_select_one = false;

        //******************************************
        //campo per la selezione del tipo di piano cdr
        //recupero di tutte le tipologie da visualizzare
        //vengono visualizzate esclusivamente le tipologie di piano con almeno un piano nell'anno (e almeno un cdr associato)
        $tipi_piano_select = array();
        foreach ($anno_budget->getTipiPianoCdrAttiviUtente() AS $tipo_piano) {
            $tipi_piano_select[] = array(
                new ffData($tipo_piano->id, "Number"),
                new ffData("Piano organizzativo: " . $tipo_piano->descrizione, "Text")
            );
        }
        $tipo_piano_selezionato = $oPage->globals["tipo_piano_cdr"]["value"];

        //visualizzazione ffield
        $oFieldPianoCdr = ffField::factory($oPage);
        $oFieldPianoCdr->id = "piano_cdr";
        $oFieldPianoCdr->base_type = "Number";
        $oFieldPianoCdr->extended_type = "Selection";
        $oFieldPianoCdr->multi_pairs = $tipi_piano_select;
        $oFieldPianoCdr->setValue($tipo_piano_selezionato->id);
        if (count($tipi_piano_select) == 0)
            $oFieldPianoCdr->multi_select_one_label = "Nessun piano attivo con cdr di responsabilità per l'anno selezionato.";
        else
            $oFieldPianoCdr->multi_select_one = false;
        if (count($tipi_piano_select) <= 1)
            $oFieldPianoCdr->control_type = "label";

        if ($tipo_piano_selezionato !== 0) {
            //**************************
            //campo per la selezione cdr
            //viene selezionato l'ultimo piano dell'anno nel caso di anni differenti da quello corrente
            //nel caso l'anno sia quello corrente viene utilizzato l'ultimo piano introdotto alla data odierna
            $date = $oPage->globals["data_riferimento"]["value"]->format("Y-m-d");
            $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_selezionato, $date);

            //se l'utente ha privilegio cdr_view_all e non ha selezionato un dipendente specifico verranno visualizzati tutti i cdr e i figli altrimenti solamente quelli di responsabilità
            $cdr_select = array();
            if ($user->hasPrivilege("cdr_view_all") == true && $dipendente_selezionato === null) {
                $cdr_visibili = $piano_cdr->getCdr();
            }
            else {
                $personale = Personale::factoryFromMatricola($user->matricola_utente_selezionato);
                $cdr_visibili = array();
                foreach ($oPage->globals["cdr_visibili"]["value"] as $cdr_visibile) {
                    $cdr_visibili[] = $cdr_visibile["cdr"];
                }
            }

            //array per la selezione nel field e ordinamento
            $tipi_cdr = array();
            $descrizioni_cdr = array();
            if (count($cdr_visibili) > 0) {
                foreach ($cdr_visibili as $cdr) {
                    $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
                    $cdr_select[] = array(new ffData($cdr->id, "Number"),
                        new ffData("Cdr: " . $cdr->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr->descrizione, "Text")
                    );
                    $descrizioni_cdr[] = $cdr->descrizione;
                    $tipi_cdr[] = $tipo_cdr->abbreviazione;
                }
                array_multisort($tipi_cdr, SORT_ASC, $descrizioni_cdr, SORT_ASC, $cdr_select);
            }
            else {
                $cdr_select[] = array(new ffData(0, "Number"),
                    new ffData("Nessu Cdr di responsabilità", "Text")
                );
            }

            //campo per la selezione dei cdr di responsabilità del piano dei cdr in vigore per l'anno selezionato
            $cdr_selezionato = $oPage->globals["cdr"]["value"];
            $oFieldCdr = ffField::factory($oPage);
            $oFieldCdr->id = "cdr";
            $oFieldCdr->label = "Cdr";
            $oFieldCdr->base_type = "Number";
            $oFieldCdr->extended_type = "Selection";
            $oFieldCdr->multi_pairs = $cdr_select;
            $oFieldCdr->setValue($cdr_selezionato->id);
            $oFieldCdr->multi_select_one = false;
            if (count($cdr_select) <= 1) {
                $oFieldCdr->control_type = "label";
            }

            if ($cdr_selezionato !== 0) {
                $tipo_cdr_selezionato = new TipoCdr($cdr_selezionato->id_tipo_cdr);
                $cdr_desc = CoreHelper::cutText($cdr_selezionato->codice . " - " . $tipo_cdr_selezionato->abbreviazione . " " . $cdr_selezionato->descrizione, 50) . "<br>";
            }
            else {
                $cdr_desc = "Nessun Cdr di responsabilità";
            }
        }

        //visualizzazione campi
        $oPage->addContent("<div id='selettore_main' class='dropdown'>								
								<a class='btn dropdown-toggle' id='dropdownMenuSelettoreMain' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
									<span class='caret'></span>
									<span id='selettore_main_descr'>");
        if ($dipendente_selection == true) {
            $oPage->addContent("<label>Dipendente:</label> " . $desc_dipendente);
        }
        $oPage->addContent("		
										<label>Anno:</label> " . $anno_budget->descrizione . "
										<label>Piano Cdr:</label> " . $tipo_piano_selezionato->descrizione . "
										<label>Cdr:</label> " . $cdr_desc . "	
									</span>
								</a>
								<ul class='dropdown-menu' aria-labelledby='dropdownMenuSelettoreMain'><li>");
        if ($dipendente_selection == true) {
            $oPage->addContent($oFieldDipendente->process());
        }
        $oPage->addContent($oFieldAnno->process());
        $oPage->addContent($oFieldPianoCdr->process());
        $oPage->addContent($oFieldCdr->process());
        $oPage->addContent("	</li></ul>
							</div>
							<script type='text/javascript'>
								$('#selettore_main .dropdown-menu').click(function(event){
															event.stopPropagation();
														});
														
								$('#dipendente, #anno, #piano_cdr, #cdr').change(function (){
									$('#inactive_body').show();
									$('#frmMain').attr('action', '" . FF_SITE_PATH . "/area_riservata/" . "');
									$('#frmMain').submit();
								});
						
							</script>
							");
    }
}

//Gestione dei log
ffRecord::addEvent("on_factory_done", "recordInitOperations");

function recordInitOperations($oRecord)
{
    $oRecord->addEvent("on_do_action", "WriteRecordLog", ffEvent::PRIORITY_HIGH);
}

function WriteRecordLog($oRecord, $frmAction)
{
    //vengono accodati tutti gli eventuali ID del record
    foreach ($oRecord->key_fields AS $field) {
        $record_ids = $field->value->getValue() . ", ";
    }

    $db = ffDB_Sql::factory();
    $sql = "INSERT INTO log_record_operation (date_time, username, src_table, operation, data_record_ID)
					VALUES(
								NOW(), 
								" . $db->toSql(mod_security_getUserInfo("username")) . ",
								" . $db->toSql($oRecord->src_table) . ",
								" . $db->toSql($frmAction) . ",
								" . $db->toSql($record_ids) . "
							)
			";
    if ($db->execute($sql) == false)
        ffErrorHandler::raise("Errore durante il salvataggio del log");
}
