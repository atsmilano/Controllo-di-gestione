<?php
$use_hard_delete = empty($_GET["hd"]) ? false : $_GET["hd"];
$allegato = new AllegatoHelper();
if($allegato->deleteFile($_GET['file'], ((bool)$use_hard_delete))){
    echo "Deleted";
}else{
    ffErrorHandler::raise("Si è verificato un errore in fase di cancellazione del file");    
}
exit;