<?php
class CoreHelper {
    //metodo per restituire la query utilizzabile dalla grid da un array di field associati ai campi specificati nell'array fields
    //fields array(fieldname1,fieldname2 ...)
    //recordset array(
    //              array(value1,value2 ...) 
    //              array(value1,value2 ...) 
    //              array(value1,value2 ...) 
    //           )
    //ritorna sempre una stringa
    public static function GetGridSqlFromArray($grid_fields, $grid_recordset, $table_name) {
        $db = ffDb_Sql::factory();

        $sql = "";
        foreach ($grid_recordset as $record) {            
            if (strlen($sql)) {
                $sql .= " UNION ";
            }
            $sql .= "SELECT ";
            //fields
            foreach ($record as $key => $field_value) {
                $sql .= $db->toSql($field_value) . " AS " . $grid_fields[$key] . ",";
            }
            //viene eliminata la virgola dall'ultimo field accodato
            $sql = rtrim($sql, ",");
        }
        //se non è stato trovato nessun dato viene restituita una query vuota con l'alias dei fields
        if (strlen($sql) > 0) {
            $grid_source_sql = "	
                SELECT *
                FROM (" . $sql . ") AS " . $table_name . "
                [WHERE]
                [HAVING]
                [ORDER]
            ";
        } else {
            $grid_source_sql = "SELECT ";
            foreach ($grid_fields as $fieldname) {
                $grid_source_sql .= "'' AS " . $fieldname . ",";
            }                       
            //viene eliminata la virgola dall'ultimo field accodato
            $grid_source_sql = rtrim($grid_source_sql, ",");
            $grid_source_sql .= "
                FROM " . $table_name . "
                WHERE 1=0
                [AND]
                [WHERE]
                [HAVING]
                [ORDER]
            ";
        }
        return $grid_source_sql;
    }

    //metodo per inclusione di jquery-ui nelle pagine che lo utilizzano
    public static function includeJqueryUi() {
        $cm = cm::getInstance();        
        $cm->oPage->addContent('
				<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">				
				<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
            ');
    }

    //restituisce il calcolo percentuale
    public static function percentuale($valore, $totale) {
        if ($totale == 0) {
            $perc = 0;
        } else {
            $perc = $valore / $totale * 100;
        }
        return $perc;
    }

    //creazione di un file semplice e non formattato in xlsx per il download generato da una matrice di dati
    //come parametro viene passato il nome del file generato e array associativo nome foglio => matrice di dati da visualizzare in excel    
    //la matrice di dati è un array di array 
    //fogli_lavoro{foglio1{record1{0=>valorecampo1, 1=>valorecampo2, ...},$record2{...},...},$foglio2{...},...}
    //json indica se il metodo deve restituire una risposta json oppure se generare il download del file
    public static function simpleExcelWriter($filename, $fogli_lavoro, $json = false) {                
        //error_reporting(0);
        require_once (FF_DISK_PATH.DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."PHPSpreadsheet".DIRECTORY_SEPARATOR."PHPExcel".DIRECTORY_SEPARATOR."PHPExcel.php");

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $date_format = 'dd/mm/yyyy';
        
        $first_sheet = true;
        foreach($fogli_lavoro as $nome_foglio_lavoro => &$matrici) {
            if ($first_sheet == false){
                $sheet = $objPHPExcel->createSheet();
            }            
            else {
                $first_sheet = false;
            }
            $sheet->setTitle($nome_foglio_lavoro);
                        
            foreach ($matrici as $riga => &$record) {
                $column_index = 0;
                foreach ($record as &$valore_cella) {
                    //in caso di data viene formattata la cella
                    if (CoreHelper::isDate($valore_cella)) {
                        $sheet->getStyleByColumnAndRow($column_index, ($riga+1))->getNumberFormat()->setFormatCode($date_format);
                        $valore_cella_xls = PHPExcel_Shared_Date::PHPToExcel(new DateTime($valore_cella));
                    }  
                    else {
                        $valore_cella_xls = $valore_cella;
                    }
                    $nome_colonna = PHPExcel_Cell::stringFromColumnIndex($column_index);                    
                    $sheet->SetCellValue($nome_colonna.($riga+1), $valore_cella_xls);
                    
                    $column_index += 1;
                    
                    unset($nome_colonna);
                    unset($valore_cella_xls);
                    unset($valore_cella);                   
                }
                unset($record);
            } 
            unset($matrici);
        }        
        $objPHPExcel->setActiveSheetIndex(0);

        if ($json == false){
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //mime type
            header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
            header('Cache-Control: max-age=0'); //no cache    
        }
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');                                                 
        //download del file        
        if ($json == true ){
            ob_start();
        }
        $objWriter->save('php://output');        
        
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);   
        
        if ($json == true ){            
            $xlsData = ob_get_contents();
            ob_end_clean();
            $response =  array(
                'filename' => $filename.".xlsx",
                'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData)
            );
            return json_encode($response);
        }
        else {
            exit;
        }
    }   

    //Funzione per la generazione di messaggi di errore all'interno della pagina di dettaglio dei record
    //$str_error messaggio di errore     
    public static function setError($oRecord, $str_error) {
        $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != ""
            ? $oRecord->strError : $str_error;
        return true;
    }

    //Viene tagliato il testo passato come parametro 1 alla lunghezza passata come parametro 2 e aggiunto [...]
    //add_dots viene impostato a true se si intende accodare al testo tagliato la stringa '[...]'
    public static function cutText ($text, $length, $add_dots = true){
        if (strlen($text) >= $length) {  
            $dots = $add_dots?"[...]":"";
            $return_string = substr($text, 0, $length) . $dots;
            return $return_string;
        }
        else {
            return $text;
        }
    }
        
    //recupero della data di riferimento budget dell'anno passato come parametro
    public static function getDataRiferimentoBudget (AnnoBudget $anno){
        $cm = Cm::getInstance();
        $anno_selezionato = $cm->oPage->globals["anno"]["value"];
        //se l'anno passato come parametro è l'anno selezionato nei globals viene restituita la data_riferimento nei globals
        if ($anno->id == $anno_selezionato->id) {
            return $cm->oPage->globals["data_riferimento"]["value"];
        }

        if ($anno->descrizione == date("Y")) {
            $data_riferimento = new DateTime("NOW");
        }
        else {
            $data_riferimento = new DateTime($anno->descrizione."-12-31");
        }        
        return $data_riferimento;
    }
    
    //verifica che una stringa sia una data
    public static function isDate($string) {        
        if (DateTime::createFromFormat('Y-m-d H:i:s', $string) !== false) {
            return true;
        }
        if (DateTime::createFromFormat('Y-m-d', $string) !== false) {
            return true;
        }
        if (DateTime::createFromFormat('d/m/Y H:i:s', $string) !== false) {
            return true;
        }
        if (DateTime::createFromFormat('d/m/Y', $string) !== false) {
            return true;
        }
        return false;        
    }
    
    //recupero date da db
    public static function getDateValueFromDB($date) {
        if ($date != '0000-00-00 00:00:00'
            && $date !== '0000-00-00' 
            && $date !== null 
            )
            return $date;

        return null;
    }
    
    //recupero booleani da db
    public static function getBooleanValueFromDB($boolean) {
        if ($boolean == 1 || $boolean == "1")
            return 1;

        return 0;
    }

    public static function refreshTabOnDialogClose($id_dialog) {
        $cm = Cm::getInstance();
        $cm->oPage->addContent("
            <script>
                const callback = '$(\'#tabs\').tabs(\'load\', $(\'#tabs\').tabs(\'option\', \'active\'))';            
                ff.ffPage.dialog.get('".$id_dialog."').params.callback = callback;
            </script>
        ");
    }    

    public static function disableNonEditableOfield($oField, $edit_condition) {
        if(!$edit_condition) {
            $oField->required = false;            
            if($oField->control_type != "radio" && $oField->control_type != "checkbox") {
                $oField->control_type = "label";
            } else {
                $oField->properties["disabled"] = "disabled";
            }

            $oField->store_in_db = false;
        }
    }

    public static function isNonEditableFieldUpdated($oRecord, $fields) {
        $error = false;
        foreach ($fields as $key => $value) {
            $oRecordField = $oRecord->form_fields[$key];
            //Valore attuale != valore originale
            if ($oRecordField->value->getValue() != $oRecordField->value_ori->getValue()) {
                $error = true;
                //Ripristino i valori presi da db
                $oRecord->form_fields[$key]->value->setValue($value);
            }
        }
        return $error;
    }

    public static function formatUiDate($date_str, $input_format = "Y-m-d", $output_format = "d/m/Y") {
        $date_str_formatted = "";
        if(!empty($date_str) && $date_str !== '0000-00-00 00:00:00' && $date_str !== '0000-00-00') {
            $datetime = DateTime::createFromFormat($input_format, $date_str);
            if($datetime) {
                $date_str_formatted = $datetime->format($output_format);
            }
        }
        return $date_str_formatted;
    }
}
