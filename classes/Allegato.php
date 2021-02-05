<?php
class Allegato{
    //Fields
    public $id;
    public $filename_md5;
    public $filename_plain;
    public $file_path;
    public $mime_type;
    public $content_lenght;
    public $user_id;
    public $createdAt;
    public $updatedAt;
    public $deletedAt;
     
    protected $allowed_mime_type = ALLOWED_MIMETYPE;
    protected $max_content_lenght = MAX_CONTENT_LENGHT;

    public function __construct($id = null){				
        if ($id !== null) {
            $db = ffDb_Sql::factory();
            
            $sql = "
                SELECT ".$this->table_name.".*
                FROM ".$this->table_name."
                WHERE ".$this->table_name.".ID = " . $db->toSql($id)
            ;
            
            $db->query($sql);
			
            if ($db->nextRecord()){
                $this->id = $db->getField("ID", "Number", true);
                $this->filename_md5 = $db->getField("filename_md5", "Text", true);
                $this->filename_plain = $db->getField("filename_plain", "Text", true);
                $this->file_path = $db->getField("file_path", "Text", true);
                $this->mime_type = $db->getField("mime_type", "Text", true);
                $this->content_lenght = $db->getField("content_lenght", "Text", true);
                $this->user_id = $db->getField("user_id", "Number", true);
                $this->createdAt = CoreHelper::getDateValueFromDB($db->getField("createdAt", "Date", true));
                $this->updatedAt = CoreHelper::getDateValueFromDB($db->getField("updatedAt", "Date", true));
                $this->deletedAt = CoreHelper::getDateValueFromDB($db->getField("deletedAt", "Date", true));
            }
            else {
                throw new Exception("Impossibile creare l'oggetto Allegato con ID = ".$id);
            }
        }
    }

    public static function getAll($filters = array()){
        $file = array();
        if (!empty($filters)) {
            $db = ffDb_Sql::factory();
            $where = "WHERE 1 = 1 ";
            foreach($filters as $field => $value){
                $where .= "AND ".$field."=".$db->toSql($value);
            }
            $sql = "
                SELECT allegato.*
                FROM allegato
                " . $where." AND deletedAt IS NULL
            ";
            $db->query($sql);
            if ($db->nextRecord()) {
                do{
                    $file = new Allegato();
                    $file->id = $db->getField("ID", "Number", true);
                    $file->filename_md5 = $db->getField("filename_md5", "Text", true);
                    $file->filename_plain = $db->getField("filename_plain", "Text", true);
                    $file->file_path = $db->getField("file_path", "Text", true);
                    $file->mime_type = $db->getField("mime_type", "Text", true);
                    $file->content_lenght = $db->getField("content_lenght", "Text", true);
                    $file->user_id = $db->getField("user_id", "Number", true);
                    $file->createdAt = CoreHelper::getDateValueFromDB($db->getField("createdAt", "Date", true));
                    $file->updatedAt = CoreHelper::getDateValueFromDB($db->getField("updatedAt", "Date", true));
                    $file->deletedAt = CoreHelper::getDateValueFromDB($db->getField("deletedAt", "Date", true));
                } while ($db->nextRecord());
            }
        }
        return $file;
    }

    public function create(){
        return array();
    }

    public function save($array_row){
        $return = array();
        if(!empty($array_row)){
            $db = ffDb_Sql::factory();                           
            // Save allegato
            $sql_allegato = "
                INSERT INTO allegato ("
                    . "filename_plain, filename_md5, file_path, "
                    . "mime_type, content_lenght, user_id, createdAt"
                    . ") VALUES (".
                    $db->toSql($array_row['Allegato']['filename_plain']).", ".
                    $db->toSql(md5($array_row['Allegato']['filename_plain'])).", ".
                    $db->toSql($array_row['Allegato']['file_path']).", ".
                    $db->toSql($array_row['Allegato']['mime_type']).", ".
                    $db->toSql($array_row['Allegato']['content_lenght']).", ".
                    $db->toSql($array_row['ObiettiviRendicontazioneAllegato']['user_id']).", ".
                    $db->toSql(date('Y-m-d H:i:s',time()))
                .")";
            $return = $db->query($sql_allegato);
            if($return == 1){
                $return['success'] = $allegato;
            }else{                
                ffErrorHandler::raise('Allegato non salvato nel database');
            }	
        }else{
            ffErrorHandler::raise('Allegato non salvato nel database');
        }
        return $return;
    }

    public function delete($filename_md5, $use_hard_delete){
        $return = false;
        if(is_string($filename_md5)){ 
            $object = new Allegato();
            $allegato = $object->getAll(array('filename_md5'=> $filename_md5));
            $db = ffDb_Sql::factory();
            $sql_delete = ""
                . "UPDATE allegato "
                . "SET deletedAt = ".$db->toSql(date('Y-m-d H:i:s',time())). ""
                . "WHERE id =".$db->toSql($allegato->id)
            ;
            $return = $db->query($sql_delete);

            if (!empty($return) && $use_hard_delete) {
                $isDeletedFromDisk = AllegatoHelper::deleteFileFromDisk($allegato->file_path.$allegato->filename_plain);

                $return = $isDeletedFromDisk ? $return : false;
            }
        }
        return $return;
    }

    public function getAllByUserId($user_id){
        return array('Class'=>"Abstract");
    }

    public function getAllByFilenamePlain(){
        return array();
    }

    public function getAllByFilenameMd5(){
        return array();
    }

    public function isTypeFileAllowed(){
        return false;
    }

    public function isTooLarge(){
        return false;
    }

    private function encodeFilename($filename_plain){
        return md5($filename_plain);
    }
}