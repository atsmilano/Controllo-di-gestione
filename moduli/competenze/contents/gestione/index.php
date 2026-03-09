<?php
$module = core\Modulo::getCurrentModule();
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));
$user = LoggedUser::getInstance();

$tabs_ui = new UIComponents\Tabs ($tabs_id);
$url_dir = FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione//";
if ($user->hasPrivilege("competenze_admin")) {
    $tab_ui = new \UIComponents\Tab ("competenze_trasversali",
                                    "Competenze trasversali",
                                    $url_dir."competenze_trasversali",
                                    $tab_params);
    $tabs_ui->addTab($tab_ui);
    $tab_ui = new \UIComponents\Tab ("valori",
                                    "Valori",
                                    $url_dir."valori",
                                    $tab_params);
    $tabs_ui->addTab($tab_ui);
    $tab_ui = new \UIComponents\Tab ("periodi",
                                    "Periodi",
                                    $url_dir."periodi",
                                    $tab_params);
    $tabs_ui->addTab($tab_ui);
}
if ($user->hasPrivilege("competenze_cdr_gestione") || $user->hasPrivilege("competenze_admin")) {
    $tab_ui = new \UIComponents\Tab ("profili",
                                    "Profili",
                                    $url_dir."profili",
                                    $tab_params);
    $tabs_ui->addTab($tab_ui);
    $tab_ui = new \UIComponents\Tab ("competenze_specifiche",
                                    "Competenze specifiche",
                                    $url_dir."competenze_specifiche",
                                    $tab_params);
    $tabs_ui->addTab($tab_ui);
    $tab_ui = new \UIComponents\Tab ("competenze_profilo",
                                    "Competenze profilo",
                                    $url_dir."competenze_profilo",
                                    $tab_params);
    $tabs_ui->addTab($tab_ui);
    $tab_ui = new \UIComponents\Tab ("valori_attesi_profilo",
                                    "Valori attesi profilo",
                                    $url_dir."valori_attesi_profilo",
                                    $tab_params);
    $tabs_ui->addTab($tab_ui);
    $tab_ui = new \UIComponents\Tab ("associazione_profili",
                                    "Associazione profili",
                                    $url_dir."associazione_profili",
                                    $tab_params,
                                    true);
    $tabs_ui->addTab($tab_ui);
}
$cm->oPage->addContent($tabs_ui->getHtml());