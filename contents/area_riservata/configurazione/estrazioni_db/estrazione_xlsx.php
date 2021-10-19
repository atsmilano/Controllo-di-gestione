<?php
if (isset($_GET["table_name"])) {    
    //viene verificato che la tabella sia fra quelle esistenti
    $table_name = null;
    //verifica della correttezza del nome della tabella
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

//recupero parametri da payloaod json
$request_body = file_get_contents('php://input');
$field_names = json_decode($request_body);

//inizializzazione matrice e intestazioni	
$matrice_dati = array();
//intestazioni
$record = array();
foreach ($field_names as $fieldname) {
    $record[] = $fieldname;
}
$matrice_dati[] = $record;

$db = ffDB_Sql::factory();
//non viene effettuato l'escaping dei caratteri in quanto è stata verificata la correttezza del nome della tabella
$sql = "SELECT * FROM ".$table_name;
$db->query($sql);
if ($db->nextRecord()){
    do {
        $record = array();
        foreach ($field_names as $fieldname) {
            $record[] = $db->getField($fieldname, "Text", true);
        }        
        $matrice_dati[] = $record;
    } while($db->nextRecord());
}

$xls_file = "estrazione-".$table_name;
$nome_foglio_lavoro =  CoreHelper::cutText($table_name, 30, false);
die(CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati), true));