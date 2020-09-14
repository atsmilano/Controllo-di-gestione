<?php
if(!isset($_REQUEST["titolo_grafico"]) || !isset($_REQUEST["left"]) || !isset($_REQUEST["right"])) {
    die(
        json_encode(
            array(
                "err" => true,
                "msg" => "Parametri non validi"
            )
        )
    );
}

$cm = Cm::getInstance();
$modulo = Modulo::getCurrentModule();
$anno = $cm->oPage->globals["anno"]["value"];
error_reporting(0);

//libreria per la generazione dei pdf
require_once(FF_DISK_PATH
    . DIRECTORY_SEPARATOR."library"
    . DIRECTORY_SEPARATOR."mpdf"
    . DIRECTORY_SEPARATOR.CURRENT_USE_MPDF_VERSION
    . DIRECTORY_SEPARATOR."mpdf.php"
);

$mpdf = new mPDF();
$mpdf->SetFont("dejavusans");

//viene aggiunto il css al pdf
$stylesheet = file_get_contents($modulo->module_theme_dir
    . DIRECTORY_SEPARATOR."css"
    . DIRECTORY_SEPARATOR."grado_differenziazione_export.css"
);
$mpdf->WriteHTML($stylesheet,1);

//reperimento logo
$img_path = FF_DISK_PATH
    . DIRECTORY_SEPARATOR."themes"
    . DIRECTORY_SEPARATOR."ats"
    . DIRECTORY_SEPARATOR.'images'
    . DIRECTORY_SEPARATOR.'logo'
    . DIRECTORY_SEPARATOR.LOGO_RESTRICTED_FILENAME;

$img = '<img style="width:100px;float:left" src="'.$img_path.'" />';

$html =<<<EOT
<div id="grado_differenziazione_pdf">
    <table id="header_pdf">
        <tr>
            <td style="width: 15%">{$img}</td>
            <td style="text-align: center"><h3>{$_REQUEST["titolo_grafico"]}</h3></td>
            <td style="width: 15%">&nbsp;</td>
        </tr>
    </table>
    <br /><br />
    <div id="report_grado_differenziazione">
        <div id="left_gd"> 
            {$_REQUEST["left"]}
        </div>
        <div id="right_gd">
            {$_REQUEST["right"]}
        </div>
    </div>
</div>
EOT;

$mpdf->WriteHTML($html,2);

$filename = $anno->descrizione . " -  Grado di differenziazione.pdf";
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