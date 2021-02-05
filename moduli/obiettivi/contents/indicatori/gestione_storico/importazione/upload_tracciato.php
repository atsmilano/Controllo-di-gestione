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
    $db_columns = IndicatoriValoreParametroRilevato::describe(
        array("Field" => array("ID", "data_importazione"))
    );

    $first_row = true;     
    $intestazioni = array();

    //Costruzione degli array contenti i valori ammissibili delle colonne del tracciato
    $parametri_allowed = array();
    foreach(IndicatoriParametro::getAll() as $parametro_allowed) {
        $parametri_allowed[] = $parametro_allowed->id;
    }

    $periodi_rendicontazione_allowed = array();
    foreach(ObiettiviPeriodoRendicontazione::getAll() as $periodo_rendicontazione_allowed) {
        $periodi_rendicontazione_allowed[] = $periodo_rendicontazione_allowed->id;
    }

    $periodi_cruscotto_allowed = array();
    foreach(IndicatoriPeriodoCruscotto::getAll() as $periodo_cruscotto_allowed) {
        $periodi_cruscotto_allowed[] = $periodo_cruscotto_allowed->id;
    }

    $row_save = array();
    $row_error = array();
    foreach ($worksheet->getRowIterator() AS $row) {
        $current_row = $row->getRowIndex();
        $valore_parametro_rilevato = new IndicatoriValoreParametroRilevato();
        $valore_parametro_rilevato->id = null;
        $valore_parametro_rilevato->data_importazione = date("Y-m-d H:i:s");

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
                if ($found !== true) {
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

                    $valore_parametro_rilevato->{$attribute_name} = $value;
                }
                $column_index += 1;
            }
        }

        if ($first_row == true) {        
            $first_row = false;
        }
        else {
            $anagrafiche_cdr_data = [];
            try {
                //vengono in primis validati i dati obbligatori
                if (empty($valore_parametro_rilevato->id_parametro)) {
                    throw new Exception("<b>ID_parametro</b> mancante");
                }

                if (empty($valore_parametro_rilevato->data_riferimento)) {
                    throw new Exception("<b>data_riferimento</b> mancante");
                }

                if (empty($valore_parametro_rilevato->valore)) {
                    throw new Exception("<b>valore</b> mancante");
                }

                if ($valore_parametro_rilevato->modificabile < 0 &&
                    $valore_parametro_rilevato->modificabile > 1) {
                    throw new Exception("<b>modificabile</b> mancante/non valido");
                }

                //Viene costruito l'array delle anagrafiche cdr valide in data riferimento
                if(!empty($valore_parametro_rilevato->codice_cdr)) {
                    $data_riferimento = DateTime::createFromFormat("Y-m-d", $valore_parametro_rilevato->data_riferimento);
                    foreach(AnagraficaCdr::getAnagraficaInData($data_riferimento) as $anagrafica_cdr_data) {
                        $anagrafiche_cdr_data[] = $anagrafica_cdr_data->codice;
                    }
                }

                // Verifica su corretteza dati
                //Verifica che il formato della data sia Y-m-d
                $pattern_Y_m_d = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
                if(!preg_match($pattern_Y_m_d, $valore_parametro_rilevato->data_riferimento)) {
                    throw new Exception("
                        Valore $valore_parametro_rilevato->data_riferimento
                        per il campo <b>data_riferimento</b> non valido
                    ");
                }

                checkValiditaValore($valore_parametro_rilevato, "ID_parametro", $parametri_allowed);
                checkValiditaValore($valore_parametro_rilevato, "ID_periodo_rendicontazione", $periodi_rendicontazione_allowed);
                checkValiditaValore($valore_parametro_rilevato, "ID_periodo_cruscotto", $periodi_cruscotto_allowed);
                checkValiditaValore($valore_parametro_rilevato, "codice_cdr", $anagrafiche_cdr_data);
                
                $row_save[] = $valore_parametro_rilevato;
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
        foreach ($row_save as $item_vpr) {
            $item_vpr->save();
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