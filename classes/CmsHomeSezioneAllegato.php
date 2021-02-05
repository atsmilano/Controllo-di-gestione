<?php
class CmsHomeSezioneAllegato extends Allegato {
    public static $bridge_table_name = "cms_home_sezione_allegato";
    
    public static function getAll($filters = array()) {
        $allegati = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value) {
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        }
        $sql = "
            SELECT bta.*, a.filename_md5, a.filename_plain, a.file_path,
                a.mime_type, a.content_lenght, a.user_id, 
                a.createdAt, a.updatedAt, a.deletedAt
            FROM ".self::$bridge_table_name." bta
                INNER JOIN allegato a ON (bta.ID_allegato = a.ID)
            " . $where . "
        ";
        $db->query($sql);
        
        if ($db->nextRecord()) {
            do {
                $deletedAt = CoreHelper::getDateValueFromDB($db->getField("deletedAt", "Date", true));
                
                if ($deletedAt === NULL) {
                    $allegato = new CmsHomeSezioneAllegato();
                    $allegato->id = $db->getField("ID", "Number", true);
                    $allegato->id_allegato = $db->getField("ID_allegato", "Number", true);
                    $allegato->id_sezione = $db->getField("ID_sezione", "Number", true);
                    $allegato->filename_md5 = $db->getField("filename_md5", "Text", true);
                    $allegato->filename_plain = $db->getField("filename_plain", "Text", true);
                    $allegato->file_path = $db->getField("file_path", "Text", true);
                    $allegato->mime_type = $db->getField("mime_type", "Text", true);
                    $allegato->content_lenght = $db->getField("content_lenght", "Text", true);
                    $allegato->user_id = $db->getField("user_id", "Number", true);
                    $allegato->createdAt = CoreHelper::getDateValueFromDB($db->getField("createdAt", "Date", true));
                    $allegato->updatedAt = CoreHelper::getDateValueFromDB($db->getField("updatedAt", "Date", true));
                    $allegato->deletedAt = $deletedAt;

                    $allegati[] = $allegato;
                }
            } while ($db->nextRecord());
        }
        
        return $allegati;
    }
    
    //TODO eliminare, ridondante rispetto a metodo della classe allegato
    public function save($array_row) {
        $return = array();
        if (!empty($array_row)) {
            if ($array_row['Allegato']['content_lenght'] <= $this->max_content_lenght) {
                if (array_search($array_row['Allegato']['mime_type'], $this->allowed_mime_type)) {
                    $db = ffDb_Sql::factory();
                    // Save allegato
                    $sql_allegato = "INSERT INTO allegato ("
                        . "filename_plain, filename_md5, file_path, "
                        . "mime_type, content_lenght, user_id, createdAt "
                        . ") VALUES (" .
                            $db->toSql($array_row['Allegato']['filename_plain']) . ", " .
                            $db->toSql(md5($array_row['Allegato']['filename_plain'])) . ", " .
                            $db->toSql($array_row['Allegato']['file_path']) . ", " .
                            $db->toSql($array_row['Allegato']['mime_type']) . ", " .
                            $db->toSql($array_row['Allegato']['content_lenght']) . ", " .
                            $db->toSql($array_row['CmsHomeSezioneAllegato']['user_id']) . ", " .
                            $db->toSql(date('Y-m-d H:i:s', time()))
                        . ")";
                    $db->query($sql_allegato);
                    
                    $allegato = parent::getAll(array('filename_plain' => $array_row['Allegato']['filename_plain']));
                    //Save custom fields in bdrige table
                    $sql_bridge_query = "INSERT INTO ".self::$bridge_table_name." ("
                        . "ID_sezione, ID_allegato, createdAt "
                        . ") VALUES (" .
                            $db->toSql($array_row['CmsHomeSezioneAllegato']['id_sezione']) . ", " .
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
    
    public function hardDelete() {
        $db = ffDB_Sql::factory();
        $sql = "
            DELETE FROM ".self::$bridge_table_name."
            WHERE ID = ".$db->toSql($this->id);
        
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto CmsHomeSezioneAllegato con ID = " . $this->id . " nel DB");
        }
        else {
            $sql = "
                DELETE FROM allegato
                WHERE ID = ".$db->toSql($this->id_allegato);
            
            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto Allegato con ID = " . $this->id_allegato . " nel DB");
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