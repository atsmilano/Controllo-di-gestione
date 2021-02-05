<?php
class ObiettiviRendicontazioneAllegato extends Allegato {
    protected static $tablename = "obiettivi_rendicontazione_allegato";
    
    public function save($array_row) {
        $return = array();
        if (!empty($array_row)) {
            if ($array_row['Allegato']['content_lenght'] <= $this->max_content_lenght) {
                if (array_search($array_row['Allegato']['mime_type'], $this->allowed_mime_type)) {
                    $db = ffDb_Sql::factory();
                    // Save allegato
                    $sql_allegato = "INSERT INTO allegato ("
                        . " filename_plain,filename_md5, file_path, mime_type, content_lenght, user_id, createdAt "
                        . ") VALUES (" .
                        $db->toSql($array_row['Allegato']['filename_plain']) . ", " .
                        $db->toSql(md5($array_row['Allegato']['filename_plain'])) . ", " .
                        $db->toSql($array_row['Allegato']['file_path']) . ", " .
                        $db->toSql($array_row['Allegato']['mime_type']) . ", " .
                        $db->toSql($array_row['Allegato']['content_lenght']) . ", " .
                        $db->toSql($array_row['ObiettiviRendicontazioneAllegato']['user_id']) . ", " .
                        $db->toSql(date('Y-m-d H:i:s', time()))
                        . ")";
                    $db->query($sql_allegato);
                    $allegato = parent::getAll(array('filename_plain' => $array_row['Allegato']['filename_plain']));
                    //Save custom fields in bdrige table
                    $sql_bridge_query = "INSERT INTO ".self::$tablename." (rendicontazione_id, allegato_id, createdAt ) VALUES (" .
                        $db->toSql($array_row['ObiettiviRendicontazioneAllegato']['rendicontazione_id']) . ", " .
                        $db->toSql($allegato->id) . ", " .
                        $db->toSql(date('Y-m-d H:i:s', time()))
                        . ")";
                    $return_query = $db->query($sql_bridge_query);
                    if ($return_query == 1) {
                        $return['success'] = $allegato;
                    } else {
                        $return['error'] = 'allegato non salvato';
                    }
                } else {
                    $return['error'] = "il tipo di file (mimetype) non è valido";
                }
            } else {
                $return['error'] = "file troppo grande, supera " . $this->max_content_lenght . "bytes";
            }
        }
        return $return;
    }

    public function delete($filename_md5, $use_hard_delete = false) {
        $db = ffDb_Sql::factory();
        $allegato = parent::getAll(array('filename_md5' => $filename_md5));
        $sql_delete = "UPDATE allegato SET deletedAt = " . $db->toSql(date('Y-m-d H:i:s', time())) . "WHERE id =" . $db->toSql($allegato->id);
        return $db->query($sql_delete);
    }

    public static function getAll($filters = array()) {
        $rendicontazioni_allegati = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value) {
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        }
        $sql = "
            SELECT *
            FROM ".self::$tablename."
                INNER JOIN allegato ON (
                    ".self::$tablename.".allegato_id = allegato.ID AND allegato.deletedAt is null
                )
            " . $where . "            
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $rendicontazione_allegato = new ObiettiviRendicontazioneAllegato();
                $rendicontazione_allegato->id = $db->getField("ID", "Number", true);
                $rendicontazione_allegato->id_rendicontazione = $db->getField("rendicontazione_id", "Number", true);
                $rendicontazione_allegato->id_allegato = $db->getField("allegato_id", "Number", true);
                $rendicontazione_allegato->filename_md5 = $db->getField("filename_md5", "Text", true);
                $rendicontazione_allegato->filename_plain = $db->getField("filename_plain", "Text", true);
                $rendicontazione_allegato->file_path = $db->getField("file_path", "Text", true);
                $rendicontazione_allegato->mime_type = $db->getField("mime_type", "Text", true);
                $rendicontazione_allegato->content_lenght = $db->getField("content_lenght", "Text", true);
                $rendicontazione_allegato->user_id = $db->getField("user_id", "Number", true);
                $rendicontazione_allegato->createdAt = CoreHelper::getDateValueFromDB($db->getField("createdAt", "Date", true));
                $rendicontazione_allegato->updatedAt = CoreHelper::getDateValueFromDB($db->getField("updatedAt", "Date", true));
                $rendicontazione_allegato->deletedAt = CoreHelper::getDateValueFromDB($db->getField("deletedAt", "Date", true));                
                
                $rendicontazioni_allegati[] = $rendicontazione_allegato;
            } while ($db->nextRecord());
        }
        return $rendicontazioni_allegati;
    }

    public function hardDelete() {
        $db = ffDB_Sql::factory();
        $sql = "
            DELETE FROM ".self::$tablename."
            WHERE ID = ".$db->toSql($this->id);

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto ".static::class
                . " con ID = " . $this->id . " nel DB");
        }
        else {
            $sql = "
                DELETE FROM allegato
                WHERE ID = ".$db->toSql($this->id_allegato);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto Allegato"
                    . " con ID = " . $this->id_allegato . " nel DB");
            }
            else {
                if (!AllegatoHelper::deleteFileFromDisk($this->file_path.$this->filename_plain)) {
                    throw new Exception("Impossibile rimuovere dal disco il file " . $this->filename_plain
                        . " salvato " . $this->file_path);
                }
            }
        }

        return true;
    }
}