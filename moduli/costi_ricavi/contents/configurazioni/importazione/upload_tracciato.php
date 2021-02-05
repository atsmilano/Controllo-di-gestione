<?php
if ($_FILES["file-upload"]["error"] !== UPLOAD_ERR_OK) {
    $result[] = [
        "status" => false, 
        "msg" => "Errore nel caricamento del file"
    ];
    die(json_encode($result));
}

try {
    $phpspreadsheet_path = FF_DISK_PATH.DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR
        ."PHPSpreadsheet".DIRECTORY_SEPARATOR."PHPExcel".DIRECTORY_SEPARATOR."PHPExcel";
    require_once ($phpspreadsheet_path.DIRECTORY_SEPARATOR."IOFactory.php");
    require_once ($phpspreadsheet_path.DIRECTORY_SEPARATOR."Shared".DIRECTORY_SEPARATOR."Date.php");

    $filename = $_FILES["file-upload"]["tmp_name"];

    $input_file_type = PHPExcel_IOFactory::identify($filename);
    $obj_reader = PHPExcel_IOFactory::createReader($input_file_type);
    $obj_phpexcel = $obj_reader->load($filename);

    //il primo whorksheet viene considerato quello di default del tracciato
    $sheet_index = 0;

    $worksheet = $obj_phpexcel->getSheet($sheet_index);
    $db_columns = CostiRicaviImportoPeriodo::describe(
        array("Field" => array("ID"))
    );

    $first_row = true;     
    $intestazioni = array();

    //Costruzione degli array contenti i valori ammissibili delle colonne del tracciato
    $periodi_allowed = array();
    foreach(CostiRicaviPeriodo::getAll() as $periodo_allowed) {
        $periodi_allowed[] = $periodo_allowed->id;
    }
        
    $conti_allowed = array();
    foreach(CostiRicaviConto::getAll() as $conto_allowed) {
        $conti_allowed[] = $conto_allowed->id;
    }      

    $row_save = array();
    $row_error = array();
    $id_periodi = array();
    foreach ($worksheet->getRowIterator() AS $row) {
        $current_row = $row->getRowIndex();
        
        $importo_periodo = new CostiRicaviImportoPeriodo();
        $importo_periodo->id = null;                

        $cell_iterator = $row->getCellIterator();
        $column_index = 0;
        $result_message_array = [];
        foreach ($cell_iterator as $cell) {
            if ($first_row == true) {
                $header = strtolower($cell->getValue());
                $found = false;
                //verifica esistenza del campo nel db (attributo della classe)
                foreach ($db_columns as $obj) {
                    if (strcasecmp($header, $obj->field) == 0) {
                        $intestazioni[] = $header;
                        $found = true;
                        break;
                    }
                }                                                    
                if ($found == false) {
                    throw new Exception("Colonna $header sconosciuta");
                }
            }
            else {
                $attribute_name = strtolower($intestazioni[$column_index]);

                if (!empty($attribute_name)) {
                    if(PHPExcel_Shared_Date::isDateTime($cell)) {
                        $date_value = PHPExcel_Shared_Date::ExcelToPHP($cell->getValue());
                        $value = date('Y-m-d', $date_value);
                    }
                    else {
                        $value = $cell->getValue();
                    }

                    $importo_periodo->{$attribute_name} = $value;
                }
                $column_index += 1;
            }
        }

        if ($first_row == true) {        
            $first_row = false;
        }
        else {
            try {
                //vengono in primis validati i dati obbligatori
                if (empty($importo_periodo->id_periodo)) {
                    throw new Exception("<b>valore</b> mancante");
                }
                
                if (empty($importo_periodo->id_conto)) {
                    throw new Exception("<b>ID_parametro</b> mancante");
                }

                // Verifica su corretteza dati
                checkValiditaValore($importo_periodo, "ID_periodo", $periodi_allowed);
                checkValiditaValore($importo_periodo, "ID_conto", $conti_allowed);
                     
                //verifica campi numerici
                if (!is_numeric($importo_periodo->campo_1)){
                    throw new Exception("
                        Valore '$importo_periodo->campo_1' per il campo <b>campo_1</b> non numerico.
                    ");
                }
                if (!is_numeric($importo_periodo->campo_2)){
                    throw new Exception("
                        Valore '$importo_periodo->campo_2' per il campo <b>campo_2</b> non numerico.
                    ");
                }
                if (!is_numeric($importo_periodo->campo_3)){
                    throw new Exception("
                        Valore '$importo_periodo->campo_3' per il campo <b>campo_3</b> non numerico.
                    ");
                }
                if (!is_numeric($importo_periodo->campo_4)){
                    throw new Exception("
                        Valore '$importo_periodo->campo_4' per il campo <b>campo_4</b> non numerico.
                    ");
                }
                
                //viene salvato l'id del periodo nell'array dei periodi da importare se non già presente
                $found = false;
                foreach($id_periodi as $periodo) {
                    if ($importo_periodo->id_periodo == $periodo){
                        $found = true;
                        break;
                    }                    
                }
                if ($found == false){
                    $id_periodi[] = $importo_periodo->id_periodo;
                }
                
                $row_save[] = $importo_periodo;
            }
            catch (Exception $e) {
                $result[] = [
                    "status" => false, 
                    "msg" => "Riga $current_row, errore: ".$e->getMessage()
                ];
                
                $row_error[] = $current_row;
            }
        }
    }
    
    if (empty($row_error)) {
        //vengono eliminati i dati per il periodo/i selezionato/i        
        foreach($id_periodi as $id_periodo) {
            CostiRicaviImportoPeriodo::deleteDatiPeriodo($id_periodo);
        }        
        //vengono salvati i dati
        foreach ($row_save as $importo_periodo) {
            $importo_periodo->save();
        }
        
        $result[] = [
            "status" => true,
            "msg" => "File importato con successo"
        ];
    }
}
catch (Exception $e) {
    $result[] = [
        "status" => false, 
        "msg" => "Errore durante il salvataggio dei dati, errore: ".$e->getMessage()
    ];
}
finally {
    die(json_encode($result));
}

//validazione di un valore in un insieme discreto per un campo
function checkValiditaValore($valore_parametro_rilevato, $field, $parametri_allowed) {
    $real_field = strtolower($field);
    $value = $valore_parametro_rilevato->{$real_field};
    if(!empty($value)) {
        if(!in_array($value, $parametri_allowed)) {
            throw new Exception("
                Valore '$value' per il campo <b>$field</b> non valido
            ");
        }
    }
}