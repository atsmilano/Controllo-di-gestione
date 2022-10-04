<?php

ini_set('max_execution_time', 123456);
if (isset($_GET["anno"])) {
    $anno = new AnnoBudget($_GET["anno"]);
    $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);

    $xls_file = "obiettivi_cdr_personale-" . date("Ymd");
    $nome_foglio_lavoro = "Obiettivi CdR Personale";

    //inizializzazione matrice e intestazioni	
    $matrice_dati = array(
        array(
            "codice obiettivo",
            "titolo",
            "descrizione",
            "indicatori di performance",
            "origine",
            "tipo obiettivo",
            "area_risultato",
            "area",
            "ID tipo piano",
            "descrizione tipo piano",
            "codice cdr",
            "descrizione cdr",
            "codice cdr coreferenza",
            "peso",
            "azioni",
            "parere azioni",
            "note azioni",
            "data_chiusura_modifiche",
            "matricola",
            "peso_dipendente",
            "data_firma_presa_visione",
        )
    );

    // per ogni cdr della gerarchia del cdr selezionato vengono estratti tutti gli obiettivi-cdr
    foreach (ObiettiviObiettivo::getAll(array("ID_anno_budget" => $anno->id)) as $obiettivo) {
        if ($obiettivo->data_eliminazione == null) {
            $record = array();

            //vengono recuperate le descrizioni delle chiavi esterne
            $origine = new ObiettiviOrigine($obiettivo->id_origine);
            $tipo = new ObiettiviTipo($obiettivo->id_tipo);
            $area_risultato = new ObiettiviAreaRisultato($obiettivo->id_area_risultato);
            $area = new ObiettiviArea($obiettivo->id_area);

            $record[] = $obiettivo->codice;
            $record[] = $obiettivo->titolo;
            $record[] = $obiettivo->descrizione;
            $record[] = $obiettivo->indicatori;
            $record[] = $origine->descrizione;
            $record[] = $tipo->descrizione;
            $record[] = $area_risultato->descrizione;
            $record[] = $area->descrizione;

            $info_obiettivo = $record;

            $obiettivi_cdr_associati = $obiettivo->getObiettivoCdrAssociati();
            if (count($obiettivi_cdr_associati) > 0) {
                $obiettivi_cdr_associati_found = false;
                foreach ($obiettivi_cdr_associati as $obiettivo_cdr_associato) {
                    $record = $info_obiettivo;
                    if ($obiettivo_cdr_associato->data_eliminazione == null) {
                        $obiettivi_cdr_associati_found = true;
                        if ($obiettivo_cdr_associato->isObiettivoCdrAziendale()) {
                            $desc_tipo_piano = "Aziendale";
                        }
                        else {
                            $desc_tipo_piano = "Cdr";
                        }
                        if ($obiettivo_cdr_associato->isCoreferenza()) {
                            $obiettivo_cdr_aziendale = $obiettivo_cdr_associato->getObiettivoCdrAziendale();
                            $azioni = $obiettivo_cdr_aziendale->azioni;
                            try {
                                $parere_azioni = new ObiettiviParereAzioni($obiettivo_cdr_aziendale->id_parere_azioni);
                                $parere_azioni_desc = $parere_azioni->descrizione;
                            } catch (Exception $ex) {
                                $parere_azioni_desc = "Non definite";
                            }
                            $codice_cdr_coreferenza = $obiettivo_cdr_aziendale->codice_cdr;
                        }
                        else {
                            $azioni = $obiettivo_cdr_associato->azioni;
                            try {
                                $parere_azioni = new ObiettiviParereAzioni($obiettivo_cdr_associato->id_parere_azioni);
                                $parere_azioni_desc = $parere_azioni->descrizione;
                            } catch (Exception $ex) {
                                $parere_azioni_desc = "Non definite";
                            }
                            $codice_cdr_coreferenza = "";
                        }

                        if ($obiettivo_cdr_associato->id_tipo_piano_cdr == 0) {
                            $desc_tipo_piano = "Aziendale";
                            $tipo_piano = TipoPianoCdr::getPrioritaMassima();
                        }
                        else {
                            $tipo_piano = new TipoPianoCdr($obiettivo_cdr_associato->id_tipo_piano_cdr);
                            $desc_tipo_piano = $tipo_piano->descrizione;
                        }
                        $piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $data_riferimento->format("Y-m-d"));
                        $cdr = Cdr::factoryFromCodice($obiettivo_cdr_associato->codice_cdr, $piano_cdr);
                        $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
                        $record[] = $obiettivo_cdr_associato->id_tipo_piano_cdr;
                        $record[] = $desc_tipo_piano;
                        $record[] = $obiettivo_cdr_associato->codice_cdr;
                        $record[] = $tipo_cdr->abbreviazione . " " . $cdr->descrizione;
                        $record[] = $codice_cdr_coreferenza;
                        $record[] = $obiettivo_cdr_associato->peso;
                        $record[] = $azioni;
                        $record[] = $parere_azioni_desc;
                        $record[] = $obiettivo_cdr_associato->note_azioni;
                        $record[] = $obiettivo_cdr_associato->data_chiusura_modifiche;

                        $info_obiettivo_cdr = $record;

                        //estrazione di una riga per il responsabile del cdr
                        $responsabile_cdr = $cdr->getResponsabile($data_riferimento);

                        $record[] = $responsabile_cdr->matricola_responsabile;
                        $record[] = "resp";

                        $matrice_dati[] = $record;

                        $obiettivi_cdr_personale = $obiettivo_cdr_associato->getObiettivoCdrPersonaleAssociati();
                        if (count($obiettivi_cdr_personale) > 0) {
                            $obiettivi_cdr_personale_found = false;
                            foreach ($obiettivi_cdr_personale as $obiettivo_cdr_personale) {
                                $record = $info_obiettivo_cdr;
                                $obiettivi_cdr_personale_found = true;
                                if ($obiettivo_cdr_personale->data_eliminazione == null) {
                                    $record[] = $obiettivo_cdr_personale->matricola_personale;
                                    $record[] = $obiettivo_cdr_personale->peso;
                                    $record[] = $obiettivo_cdr_personale->data_accettazione;

                                    $matrice_dati[] = $record;
                                }
                            }
                            if ($obiettivi_cdr_personale_found == false) {
                                $matrice_dati[] = $record;
                            }
                        }
                    }
                }
                if ($obiettivi_cdr_associati_found == false) {
                    $matrice_dati[] = $record;
                }
            }
            else {
                $matrice_dati[] = $record;
            }
        }
    }
    CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati));
}
else {
    mod_notifier_add_message_to_queue("Impossibile effettuare l'estrazione: errore nel passaggio dei parametri.", MOD_NOTIFIER_ERROR);
}