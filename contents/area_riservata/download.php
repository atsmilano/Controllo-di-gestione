<?php
$allegato = new AllegatoHelper();
$file = $allegato->downloadFile($_GET['file']);

if ($file) {
    ob_start();
    header('Content-Description: File Transfer');
    header('Content-Type: '.$file->mime_type);
    header('Content-Disposition: attachment; filename="'.basename($file->file_path.$file->filename_plain).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: '.filesize($file->file_path.$file->filename_plain));
    ob_clean();
    flush();
    readfile($file->file_path.$file->filename_plain);
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
}
else{
    $filename = rawurldecode($_GET['file']);         
    $cm = cm::getInstance();
    $data = $cm->oPage->globals["data_riferimento"]["value"]->format('Y');
    $fullPath = FF_DISK_PATH . "/uploads/".FF_ENV."/documenti_home/" . $filename;

    if ($fd = fopen ($fullPath, "r")) {
        $fsize = filesize($fullPath); 
        $path_parts = pathinfo($fullPath);
        $ext = strtolower($path_parts["extension"]);        
        switch ($ext) {
            case "pdf":
                header("Content-type: application/pdf");
                header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
            break;     
                    case "doc":
                    case "docx":
                header("Content-type: application/msword");
                header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
            break;   
                    case "xls":
                    case "xlsx":
                header("Content-type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
            break; 
            default;
                header("Content-type: application/octet-stream");
                header("Content-Disposition: filename=\"" . $path_parts["basename"] . "\"");
        }
        header("Content-length: $fsize");
        header("Cache-control: private");
        while(!feof($fd)) {
            $buffer = fread($fd, 2048);
            echo $buffer;
        }
    }
    else
    {
        ffErrorHandler::raise("File non trovato");
        die();
    }
    fclose ($fd);
    exit;
}