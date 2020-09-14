<?php
$allegato = new AllegatoHelper();
$uploaded_file = $allegato->uploadFile();
echo json_encode($uploaded_file);
exit;