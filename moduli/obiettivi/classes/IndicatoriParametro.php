<?php
class IndicatoriParametro extends Entity {
    protected static $tablename = "indicatori_parametro";

    public static function getAttiviAnno(AnnoBudget $anno) {
        $parametri_anno = array();
        foreach (IndicatoriParametro::getAll() as $parametro) {
            if ($parametro->anno_introduzione <= $anno->descrizione && ($parametro->anno_termine == 0 || $parametro->anno_termine >= $anno->descrizione)) {
                $parametri_anno[] = $parametro;
            }
        }
        return $parametri_anno;
    }

    //la funzione restituisce l'oggetto IndicatoriValoreParametroRilevato per il parametro alla data di rilevazione
    //il valore di riferimento è il più recente fra quelli del periodo passato come parametro o senza periodo specificato
    //nel caso venga passato un cdr il valore considerato è quello più recente fra quelli del cdr specificato o senza cdr
    public function getValoreParametroIndicatoreRilevatoPeriodoRendicontazione(ObiettiviPeriodoRendicontazione $periodo, $codice_cdr = null) {
        //vengono recuperati tutti i valori del parametro ordinati in ordine decrescente per data di riferimento
        $filters = array(
            "ID_parametro" => $this->id,
        );

        $order = array(
            array(
                "fieldname" => "data_riferimento",
                "direction" => "DESC",
            )
        );

        $valore = null;
        $valore_aziendale = null;
        $valore_anno = null;
        $valore_aziendale_anno = null;
        //viene restituito il primo valore valido alla data di riferimento o per l'anno impostato per il cdr.
        //Se non sono stati definiti valori per il cdr viene verificato che ne esista uno per l'anno e in seguito uno aziendale (il più recente fra quelli aziendali)            
        foreach (IndicatoriValoreParametroRilevato::getAll($filters, $order) as $parametro_rilevato) {
            //viene formatatta la data di riferimento per correttezza sul confronto (in caso di data uguale se la data di riferimento supera le 00:00 viene restituito false)
            $data_riferimento = date("Y-m-d", strtotime($parametro_rilevato->data_riferimento));
            if (strtotime($data_riferimento) <= strtotime($periodo->data_riferimento_fine)) {
                //se è definito un dcr sul parametro più recente alla data viene restituito solamente se è definito per il cdr passato come parametro                               
                //"ID_periodo" => $periodo->id,       
                //viene recuperato il valore definito per il periodo altrimenti viene utilizzato quello per l'anno
                if ($parametro_rilevato->id_periodo == null) {
                    if ($parametro_rilevato->codice_cdr == null && $valore_aziendale_anno == null) {
                        $valore_aziendale_anno = $parametro_rilevato;
                    } else if ($parametro_rilevato->codice_cdr == $codice_cdr && $valore_anno == null) {
                        $valore_anno = $parametro_rilevato;
                    }
                } else if ($parametro_rilevato->id_periodo == $periodo->id) {
                    if ($parametro_rilevato->codice_cdr == null && $valore_aziendale == null) {
                        $valore_aziendale = $parametro_rilevato;
                    } else if ($parametro_rilevato->codice_cdr == $codice_cdr) {
                        $valore = $parametro_rilevato;
                        break;
                    }
                }
            }
        }
        //ordine restituzione parametro
        //parametro definito per il cdr per il periodo
        //parametro definito per il cdr per l'anno
        //parametro definito per l'azienda per il periodo
        //parametro definito per l'azienda per l'anno   
        if ($valore == null) {
            if ($valore_anno == null) {
                if ($valore_aziendale == null) {
                    if ($valore_aziendale_anno !== null) {
                        $valore = $valore_aziendale_anno;
                    }
                } else {
                    $valore = $valore_aziendale;
                }
            } else {
                $valore = $valore_anno;
            }
        }
        return $valore;
    }

    //la funzione restituisce l'oggetto IndicatoriValoreParametroRilevato per il parametro alla data di rilevazione
    //il valore di riferimento è il più recente fra quelli del periodo passato come parametro o senza periodo specificato
    //nel caso veng apassato un cdr il valore considerato è quello più recente fra quelli del cdr specificato o senza cdr
    public function getUltimoValoreParametroIndicatoreRilevatoCruscotto(IndicatoriPeriodoCruscotto $periodo_cruscotto = null, $codice_cdr = null) {

        //viene formatatta la data di riferimento per correttezza sul confronto
        //(in caso di data uguale se la data di riferimento supera le 00:00 viene restituito false)
        if ($periodo_cruscotto !== null) {
            $data_riferimento_periodo_cruscotto = date("Y-m-d", strtotime($periodo_cruscotto->data_riferimento_fine));
            $filters = array("ID_parametro" => $this->id, "ID_periodo_cruscotto" => $periodo_cruscotto->id);
        } else {
            //viene definita una data di riferimento in base al fatto che venga passato un periodo o meno
            $cm = cm::getInstance();
            $data_riferimento_periodo_cruscotto = $cm->oPage->globals["data_riferimento"]["value"]->format("Y-m-d");
            $filters = array("ID_parametro" => $this->id);
        }

        //vengono recuperati tutti i valori del parametro ordinati in ordine decrescente per data di riferimento
        $order = array(
            array(
                "fieldname" => "data_riferimento",
                "direction" => "DESC",
            ),
            array(
                "fieldname" => "data_importazione",
                "direction" => "DESC",
            )
        );

        $parametri_rilevati = IndicatoriValoreParametroRilevato::getAll($filters, $order);

        $valore = null;
        $valore_aziendale = null;
        $valore_anno = null;
        $valore_aziendale_anno = null;
        //viene restituito il primo valore valido alla data di riferimento o per l'anno impostato per il cdr.
        //Se non sono stati definiti valori per il cdr viene verificato che ne esista uno per l'anno 
        //e in seguito uno aziendale (il più recente fra quelli aziendali)            
        foreach (IndicatoriValoreParametroRilevato::getAll($filters, $order) as $parametro_rilevato) {
            if (strtotime($parametro_rilevato->data_riferimento) <= strtotime($data_riferimento_periodo_cruscotto)) {
                //viene verificato che il valore sia specificato per il cdr specifico
                if ($parametro_rilevato->codice_cdr == null) {
                    //viene verificato se il volore è ralativo ad un periodo specifico
                    if ($periodo_cruscotto !== null && $valore_aziendale == null) {
                        $valore_aziendale = $parametro_rilevato;
                    } else if ($valore_aziendale_anno == null) {
                        $valore_aziendale_anno = $parametro_rilevato;
                    }
                } else if ($parametro_rilevato->codice_cdr == $codice_cdr) {
                    //viene verificato se il volore è ralativo ad un periodo specifico
                    if ($periodo_cruscotto !== null) {
                        $valore = $parametro_rilevato;
                        break;
                    } else if ($valore_anno == null) {
                        $valore_anno = $parametro_rilevato;
                    }
                }
            }
        }

        //ordine restituzione parametro
        //parametro definito per il cdr per il periodo
        //parametro definito per il cdr per l'anno
        //parametro definito per l'azienda per il periodo
        //parametro definito per l'azienda per l'anno
        if ($valore == null) {
            if ($valore_anno == null) {
                if ($valore_aziendale == null) {
                    if ($valore_aziendale_anno !== null) {
                        $valore = $valore_aziendale_anno;
                    }
                } else {
                    $valore = $valore_aziendale;
                }
            } else {
                $valore = $valore_anno;
            }
        }
        return $valore;
    }
    
    public function jsGotoStoricoParametri($fix_url, $dialog_name) {
        return "
            <script type='text/javascript'>
                function gotoStoricoParametri(id_parametro, id_periodo_rendicontazione, id_periodo_cruscotto, cdr) {

                    const init_url = '$fix_url';
                    let url = init_url + (
                        'storico_parametro_search_src='+id_parametro+
                        '&storico_periodo_cruscotto_search_src='+id_periodo_cruscotto+
                        '&storico_periodo_rendicontazione_search_src='+id_periodo_rendicontazione+
                        '&storico_cdr_search_src='+cdr
                    );
                    // Apro il dialog
                    ff.ffPage.dialog.doOpen('$dialog_name', url);
                    ff.ffPage.dialog.get('$dialog_name').params.callback = 'location.reload()';
                }
            </script>
        ";
    }
}