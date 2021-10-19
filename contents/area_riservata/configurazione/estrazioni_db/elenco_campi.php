<?php
if (isset($_GET["table_name"])) {    
    //viene verificato che la tabella sia fra quelle esistenti
    $table_name = null;
    foreach(CoreHelper::getDbTables() as $table){
        if ($table == $_GET["table_name"]) {
            $table_name = $_GET["table_name"];
        }
    }
    if ($table_name == null) {
        die("Errore durante il passaggio dei parametri.");
    }
    $fields = Entity::describe(array(),$table_name);
}
else {
    die("Errore durante il passaggio dei parametri.");
}

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory((__DIR__) . "/tpl");
$tpl->load_file("elenco_campi.html", "main");

$tpl->set_var("table", $table_name);

//generazione elenco fields
foreach ($fields as $field) {
    $tpl->set_var("nome_campo", $field->field);
    $tpl->set_var("tipo_campo", $field->type);
    
    $tpl->parse("SectField", true);
}
$tpl->parse("SectFields", true);

die($tpl->rpparse("main", true));