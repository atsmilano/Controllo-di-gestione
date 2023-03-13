<?php
//TODO verifica parametro chart_img
$html_img = ("<div id='chart_container'><img src='".$_POST['chart_img']."'></div>");
$modulo = core\Modulo::getCurrentModule();

$premessa_url = FF_DISK_PATH . "/moduli".$modulo->site_path."/contents/report/premessa/index.php";
$report_url = FF_DISK_PATH . "/moduli".$modulo->site_path."/contents/report/individuali/report_individuale.php";

$report_pdf = true;
require($premessa_url);
$html_report .= "<h2>Report individuale</h2>".$html_img;
require($report_url);
$html_intestazione = "<h1>Report individuale - ".$personale->cognome." ".$personale->nome." (matr. ".$personale->matricola.")</h1>";

$html = '<div id="report_individuale">'.$html_intestazione.$html_report.'</div>';

//error_reporting(0);
//libreria per la generazione dei pdf
require_once(FF_DISK_PATH.DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR.CURRENT_USE_MPDF_VERSION.DIRECTORY_SEPARATOR."mpdf.php");
//generazione pdf
$mpdf = new mPDF();
$stylesheet = "";

$stylesheet .= file_get_contents("https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css");
$stylesheet .= file_get_contents($modulo->module_theme_dir.DIRECTORY_SEPARATOR ."css".DIRECTORY_SEPARATOR."report_individuali.css");

$mpdf->WriteHTML($stylesheet,1);
$mpdf->WriteHTML($html,2);
$filename = "report_".$personale->matricola."_".date("Ymd").".pdf";

$pdf = $mpdf->Output($filename, "S");
die(
    json_encode(
        array(
            "err" => false,
            "pdf" => base64_encode($pdf),
            "filename" => $filename,
        )
    )
);