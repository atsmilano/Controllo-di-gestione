<?php
//recupero e validazione dei parametri
if (isset($_REQUEST["id_piano_cdr"])) {
    $id_piano_cdr = $_REQUEST["id_piano_cdr"];
    try {
        $piano_cdr = new PianoCdr($id_piano_cdr);
    } catch (Exception $ex) {
        die(json_encode(array('messaggio' => $ex->getMessage(), 'esito' => "error")));
    }
} else
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: piano cdr", 'esito' => "error")));

if (isset($_REQUEST["id_nuovo_padre"])) {
    $id_cdr_padre = $_REQUEST["id_nuovo_padre"];
    try {
        $cdr_padre = new Cdr($id_cdr_padre);
    } catch (Exception $ex) {
        die(json_encode(array('messaggio' => $ex->getMessage(), 'esito' => "error")));
    }
} else
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: nuovo cdr padre", 'esito' => "error")));

if (isset($_REQUEST["id"])) {
    $id_cdr = $_REQUEST["id"];
    try {
        $cdr = new Cdr($id_cdr);
    } catch (Exception $ex) {
        die(json_encode(array('messaggio' => $ex->getMessage(), 'esito' => "error")));
    }
} else
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: cdr", 'esito' => "error")));

//CONTROLLI DI COERENZA
//viene verificato che l'id del piano dei cdr corrisponda al piano del cdr padre e nel caso del cdr figlio
if (
        !(
        (
        $id_cdr_padre == 0 &&
        $id_cdr == 0
        ) ||
        (
        $id_cdr_padre !== 0 &&
        $id_cdr !== 0 &&
        $piano_cdr->id == $cdr_padre->id_piano_cdr &&
        $piano_cdr->id == $cdr->id_piano_cdr
        ) ||
        (
        $id_cdr_padre !== 0 &&
        $piano_cdr->id == $cdr_padre->id_piano_cdr
        ) ||
        (
        $id_cdr !== 0 &&
        $piano_cdr->id == $cdr->id_piano_cdr
        )
        )
)
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: piani dei cdr non corrispondenti", 'esito' => "error")));

if(isset($piano_cdr) && isset($piano_cdr->data_introduzione)) {
    die(json_encode(array(
        "messaggio" => "Non è possibile spostare un cdr appartenente ad un piano già introdotto.",
        "esito" => "error",
    )));
}

$tipo_cdr = new tipoCdr($cdr->id_tipo_cdr);
$tipo_cdr_padre = new tipoCdr($cdr_padre->id_tipo_cdr);

//viene verificato che il cdr padre non sià già il padre attuale
if ($cdr->id_padre == $cdr_padre->id)
    die(json_encode(array('messaggio' => "Il cdr '" . $tipo_cdr_padre->abbreviazione . " " . $cdr_padre->codice . " - " . $cdr_padre->descrizione .
        "' risulta già padre del cdr '" . $tipo_cdr->abbreviazione . " " . $cdr->codice . " - " . $cdr->descrizione . "'",
        'esito' => "error")));

//viene verificato che il tipo cdr di destinazione sia definibile come padre del cdr che si sta spostando
$found = false;
foreach ($tipo_cdr->getPadri() as $tipo_padre) {
    if ($tipo_padre->id == $tipo_cdr_padre->id) {
        $found = true;
        break;
    }
}
if ($found == false)
    die(json_encode(array('messaggio' => "Non è possibile spostare un cdr di tipo '" . $tipo_cdr->descrizione . "' come figlio di un cdr di tipo '" . $tipo_cdr_padre->descrizione . "'", 'esito' => "error")));

//viene verificato che il cdr di destinazione non sia un figlio sul ramo gerarchico del cdr che si sta spostando
$found = false;
foreach ($cdr->getGerarchia() as $figlio) {
    if ($figlio["cdr"]->id_padre == $cdr_padre->id) {
        die(json_encode(array('messaggio' => "Non è possibile spostare il cdr '"
            . $tipo_cdr->abbreviazione . " " . $cdr->codice . " - " . $cdr->descrizione . "' come figlio del figlio sul ramo gerarchico '"
            . $tipo_cdr_padre->abbreviazione . " " . $cdr_padre->codice . " - " . $cdr_padre->descrizione . "'",
            'esito' => "error")));
    }
}

//in caso di verifiche con esito positivo viene salvato il nuovo padre del cdr
$cdr->id_padre = $cdr_padre->id;
$cdr->save();

die(json_encode(array('messaggio' => "'" . $tipo_cdr->abbreviazione . " " . $cdr->codice . " - " . $cdr->descrizione .
    "' spostato correttamente come figlio di '" . $tipo_cdr_padre->abbreviazione . " " . $cdr_padre->codice . " - " . $cdr_padre->descrizione . "'",
    'esito' => "success")));