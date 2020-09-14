<?php
class AllegatoHelper {

    protected $error_operazione_vietata = "Permessi insufficienti per compiere l'operazione di ";
    protected $error_md5 = "Il file non è valido";
    
    public function getErrorMD5() {
        return $this->error_md5;
    }
    
    public function downloadFile($filename_md5){
        if($this->isValidMd5($filename_md5)){
            if($this->canDownload($filename_md5)){
                return $this->renderFile($filename_md5);
            }else{
                echo $this->error_operazione_vietata;
                return false;
            }               
        }else{            
            return false;
        }        
    }

    public function deleteFile($filename_md5, $use_hard_delete = false){
        if($this->isValidMd5($filename_md5)){
            if($this->canDelete($filename_md5)){
                $allegato = new Allegato();
                return $allegato->delete($filename_md5, $use_hard_delete);
            }else{
                echo $this->error_operazione_vietata;
                return false;
            }               
        }else{
            echo $this->error_md5;
            return false;
        } 
    }

    private function isValidMd5($md5 = ''){
        $md5 = strtolower($md5);
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    private function canDownload($filename_md5){
        $user = LoggedUser::Instance();
        $return = false;
        $permission_token_object = $this->decodePermissions($_COOKIE['p_2_#']);
        if($permission_token_object['user_id'] == $user->matricola_utente_selezionato || 
            $permission_token_object['user_id'] == $_SESSION['matricola_utente_selezionato']){

            foreach ($permission_token_object['allegati_permissions']['canDownload'] as $key => $md5) {
                if (strcmp($md5, $filename_md5) == 0) {
                    $return = true; 
                    break;
                }
            }
        }
        
        return $return;
    }

    private function canDelete($filename_md5){
        $user = LoggedUser::Instance();
        $return = false;
        $permission_token_object = $this->decodePermissions($_COOKIE['p_2_#']);
        if($permission_token_object['user_id'] == $user->matricola_utente_selezionato || 
            $permission_token_object['user_id'] == $_SESSION['matricola_utente_selezionato'] ){
                
            foreach ($permission_token_object['allegati_permissions']['canDelete'] as $key => $md5) {
                if (strcmp($md5, $filename_md5) == 0) {
                    $return = true; 
                    break;
                }
            }    
        }
        return $return;
    }

    public function getDownloadLink($filename_md5, $filename_plain){
        $cm = Cm::getInstance();
        return '<a href="'.FF_SITE_PATH.DIRECTORY_SEPARATOR.area_riservata.DIRECTORY_SEPARATOR.'download.php?file='.$filename_md5.'&'.$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST).'">'.$filename_plain.'</a>';
    }

    public function getDeleteLink($filename_md5, $filename_plain, $use_hard_delete = false){
        $cm = Cm::getInstance();
        return '<a href="'.FF_SITE_PATH.DIRECTORY_SEPARATOR.'area_riservata'.DIRECTORY_SEPARATOR.'delete.php?hd='.$use_hard_delete.'&file='.$filename_md5.'&'.$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST).'">'.$filename_plain.'</a>';
    }

    public function getAllegatoTable($allegati){
        return false;
    }
    
    public function getUploadForm($class, $allegato_data = array()){
        $upload_path = FF_SITE_PATH.'/area_riservata/upload.php';
        
        $extra_params = "";
        foreach($allegato_data as $key => $value){
            $extra_params .= 'jQuery(
                "<input>",
                {
                    "name":"field_'.$key.'",
                    "value": "'.$value.'",
                    "type":"hidden"
                }
            ),';
        }
        
        return '            
            <script>
                $(function(){
                    var newForm = jQuery("<form>", {
                        "enctype": "multipart/form-data",
                        "action": "'.$upload_path.'",
                        "target": "_Self",
                        "method": "post"
                    }).append(
                        '.$extra_params.'
                        jQuery(
                            "<input>",
                            {
                                "name":"class",
                                "value": "'.$class.'",
                                "type":"hidden"
                            }
                        ),
                        jQuery(
                            "<input>",
                            {
                                "id": "file_input_obj",
                                "name":"file",
                                "type":"file",
                                "class": "btn active-buttons",
                                "value": ""
                            }
                        ),
                        jQuery(
                            "<input>",
                            {
                                "id":"submit-id",
                                "name":"send",
                                "type":"submit",
                                "class": "btn btn-success active-buttons",
                                "value": "Allega file"
                            }
                        )
                    );
        
                    $("#allegati-ajax-form").append(newForm);
        
                    $("#allegati-ajax-form form").submit(function(e) {
                        e.preventDefault();
                        var formData = new FormData(this);       
                        $("#allegati-ajax-form").append("<p id=\"temp-message\">caricamento in corso...</p>");
                        $.ajax({
                            url: "'.$upload_path.'",
                            type: "POST",
                            data: formData,
                            success: function (data) {                            
                                $("#response_allegati-ajax-form").empty();
                                $("#temp-message").remove();
                                $("#response_allegati-ajax-form").empty();
                                var parsed = JSON.parse(data);
                                if (parsed.error) {
                                    // alert(parsed.error);
                                    $("#response_allegati-ajax-form").append(parsed.error);
                                    $("#response_allegati-ajax-form").attr("class", "alert alert-danger");
                                    $("#response_allegati-ajax-form").show();
                                } else {
                                    if ($("#row_no_allegati").length >= 1) {
                                        $("#row_no_allegati").remove();
                                    }

                                    var allegato = parsed.success;
                                    var nuova_riga = "<tr><td><a href=\"'.FF_SITE_PATH.DIRECTORY_SEPARATOR.'area_riservata'.DIRECTORY_SEPARATOR.'download.php?file="+allegato.filename_md5+"\" >"+allegato.filename_plain+"</a></td><td class=\"delete\"><a onclick=\"delete_allegato()\" href=\"'.FF_SITE_PATH.'/area_riservata/delete.php?file="+allegato.filename_md5+"\">Elimina</a></td></tr>";
                                    $("#allegati-ajax-table tbody").append(nuova_riga);
                                    // alert("Caricamento del file " + allegato.filename_plain + " completato con successo");

                                    $("#response_allegati-ajax-form").append("Caricamento del file " + allegato.filename_plain + " completato con successo");
                                    $("#response_allegati-ajax-form").attr("class", "alert alert-success");
                                    $("#response_allegati-ajax-form").show();
                                }
                                
                                setTimeout(
                                    function() {
                                        $("#response_allegati-ajax-form").hide();
                                        if ($("#sezioni-home-modify").length > 0) {
                                            ff.ajax.ctxDoAction(\'sezioni-home-modify\', \'update\', \'sezioni-home-modify_\');
                                        }
                                    },
                                    8000
                                );

                                $("#file_input_obj").attr("type", "");
                                $("#file_input_obj").attr("type", "file");
                            },
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    });

                    $(document).on(
                        "click", 
                        "#allegati-ajax-table td.delete a",
                        function(e) {
                            e.preventDefault();
                            var link = $(this).attr("href");
                            var tr = $(this).parents("tr:first");

                            $("#inactive_body").show();
                            $("#conferma_delete_allegato").show();
                            
                            $("#conferma_no_eliminare").click(function(){
                                $("#inactive_body").hide();
                                $("#conferma_delete_allegato").hide();
                                e.stopPropagation();
                                link = null;
                                tr = null;
                                return false;
                            });

                            $("#conferma_si_eliminare").click(function(){
                                $.ajax({
                                    url: link,
                                    type: "GET",
                                    success: function (data) {
                                        $("#inactive_body").hide();
                                        $("#conferma_delete_allegato").hide();
                                        $("#response_allegati-ajax-form").empty();

                                        if(data == "Deleted"){
                                            tr.remove();

                                            $("#response_allegati-ajax-form").append("Allegato rimosso con successo");
                                            $("#response_allegati-ajax-form").attr("class", "alert alert-success");
                                            $("#response_allegati-ajax-form").show();
                                            e.stopPropagation();
                                            link = null;
                                            tr = null;
//                                            return false;
                                        } else {
                                            //alert("Error in removing file");
                                            $("#response_allegati-ajax-form").append("Si è verificato un problema nel rimuovere l\'allegato");
                                            $("#response_allegati-ajax-form").attr("class", "alert alert-danger");
                                            $("#response_allegati-ajax-form").show();
                                            e.stopPropagation();
                                            link = null;
                                            tr = null;
//                                            return false;
                                        }

                                        setTimeout(
                                            function() {
                                                $("#response_allegati-ajax-form").hide();
                                                if ($("#sezioni-home-modify").length > 0) {
                                                    ff.ajax.ctxDoAction(\'sezioni-home-modify\', \'close\', \'sezioni-home-modify_\');
                                                }
                                            },
                                            8000
                                        );
                                    },
                                });
                            });
                        }
                    );
                });
            </script>
            '.$this->addAllegatiAjaxForm().'
            '.$this->addResponseAllegatiAjaxForm();
    }
    
    public function addAllegatiAjaxForm() {
        return '<div id="allegati-ajax-form"></div>';
    }
    
    public function addResponseAllegatiAjaxForm() {
        return '<div id="response_allegati-ajax-form"></div>';
    }

    public function uploadFile(){
        $return = array();        
        $anno_global = $_POST['field_anno_riferimento'];
        $class = $_POST['class'];
        $use_documenti_home = $_POST['field_use_documenti_home'];

        $path = FF_DISK_PATH.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.FF_ENV;
        if (!$this->createPathIfNotExists($path)) {
            $return["error"] = "Impossibile creare la cartella per FF_ENV ".FF_ENV;
        }
        else {            
            if ($use_documenti_home) {
                $uploaddir = $path.DIRECTORY_SEPARATOR."documenti_home".DIRECTORY_SEPARATOR;
            }
            else {
                $uploaddir = $path.DIRECTORY_SEPARATOR.$anno_global.DIRECTORY_SEPARATOR;
            }
            
            if (!$this->createPathIfNotExists($uploaddir)) {
                $return["error"] = "Impossibile creare il path $uploaddir";
            }
            else {
                $unique_filename = time().'-'. $_FILES['file']['name']; 
                $uploadfile = $uploaddir . basename($unique_filename);
                $allegato = array(
                    'Allegato' => array(
                        'filename_plain' => $unique_filename,
                        'file_path' => $uploaddir,
                        'mime_type' => $_FILES['file']['type'],
                        'content_lenght' => $_FILES['file']['size']
                    )
                );
                foreach($_POST as $key_field => $value_field){
                    if(stripos($key_field, 'field_') === 0){
                        $key_field = str_replace('field_',"",$key_field);
                        $allegato[$class][$key_field] = $value_field;
                    }
                }  
                $movedFile = move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);            
                if ($movedFile) {
                    $allegatoObject = new $class();        
                    $return = $allegatoObject->save($allegato);
                    $this->updateFileGrant($return['success']->filename_md5);           
                } else {
                    $return['error'] = "Upload non riuscito. La dimensione del file deve essere inferiore a " . ini_get('upload_max_filesize');    
                }
            }
        }
        return $return;
    }

    private function renderFile($filename_md5){
        return Allegato::getAll(array("filename_md5" => $filename_md5));
    }

    public function encodePermissions($permissions_array){
        return str_rot13(base64_encode(json_encode($permissions_array)));
    }

    public function decodePermissions($cookie_text){
        return json_decode(base64_decode(str_rot13($cookie_text)), true);
    }

    public function updateFileGrant($filename_md5){
        $permission_token_object = $this->decodePermissions($_COOKIE['p_2_#']);
        $permission_token_object['allegati_permissions']['canDownload'][] = $filename_md5;
        $permission_token_object['allegati_permissions']['canDelete'][] = $filename_md5;
        $permission_cookie = $this->encodePermissions($permission_token_object);
        setcookie('p_2_#', $permission_cookie, time()+600, '/' );
    }
        
    private function createPathIfNotExists($path) {
        if (!file_exists($path)) {
            return mkdir($path, 0775);
        }
        
        return true;
    }
    
    public function definePermission($allegati, $allegati_permissions, 
        $canDownload = true, $canDelete = true) {
     
        foreach ($allegati as $allegato) {
            if ($canDownload) {
                $allegati_permissions['allegati_permissions']['canDownload'][] = $allegato->filename_md5;
            }
            if ($canDelete) {
                $allegati_permissions['allegati_permissions']['canDelete'][] = $allegato->filename_md5;
            }
        }
        
        return $allegati_permissions;
    }
    
    public function defineAllegatiPermission($user) {
        return array(
            'user_id' => $user->matricola_utente_selezionato,
            'allegati_permissions' => array(
                'canDownload' => array(),
                'canDelete' => array()
            )
        );
    }
    
    public static function deleteFileFromDisk($path) {
        if (file_exists($path)) {
            if (!unlink($path)) {
                return false;
            }
            else {
                return true;
            }
        }
        
        return false;
    }
}
