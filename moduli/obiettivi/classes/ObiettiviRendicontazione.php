<?php
class ObiettiviRendicontazione extends Entity{
    protected static $tablename = "obiettivi_rendicontazione";

    public static function getAll($where=array(), $order=array(array("fieldname"=>"ID_periodo_rendicontazione", "direction"=>"ASC"),array("fieldname"=>"ID_obiettivo_cdr", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }    

    //factory da periodo e obiettivo_cdr
    public static function factoryFromObiettivoCdrPeriodo(ObiettiviObiettivoCdr $obiettivo_cdr, ObiettiviPeriodoRendicontazione $periodo) {
        $filters = array("ID_obiettivo_cdr" => $obiettivo_cdr->id, "ID_periodo_rendicontazione" => $periodo->id);
        foreach (ObiettiviRendicontazione::getAll($filters) as $rendicontazione) {
            return $rendicontazione;
        }
        return null;
    }

    //recupera la valutazione del nucleo per la rendicontazione
    //se l'obiettivo non è stato assegnato dalla direzione viene recuperata la valutazione della rendicontazione del primo cdr superiore
    public function getValutazioneNucleo() {
        $obiettivo_cdr = new ObiettiviObiettivoCdr($this->id_obiettivo_cdr);
        $periodo = new ObiettiviPeriodoRendicontazione($this->id_periodo_rendicontazione);
        $anno = new AnnoBudget($periodo->id_anno_budget);
        $obiettivo_cdr_aziendale = $obiettivo_cdr->getObiettivoCdrAziendale();
        $rendicontazione = ObiettiviRendicontazione::getAll(array(
                "ID_periodo_rendicontazione" => $this->id_periodo_rendicontazione,
                "ID_obiettivo_cdr" => $obiettivo_cdr_aziendale->id,
        ));
        if (count($rendicontazione) > 0) {
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");
            $tipo_piano = Cdr::getTipoPianoPriorita($obiettivo_cdr->codice_cdr, $date);
            $piano_cdr = Pianocdr::getAttivoInData($tipo_piano, $date);
            $cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
            $cdr = new Cdr($cdr->id);
            return array(
                "cdr_valutato" => $cdr,
                "rendicontazione" => $rendicontazione[0],
            );
        } else {
            return null;
        }
    }

    //metodo per la visualizzazione delle informazioni dell'obiettivo_cdr in html
    public function showHtmlInfo() {
        $obiettivo_cdr = new ObiettiviObiettivoCdr($this->id_obiettivo_cdr);
        $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
        if ($this->raggiungibile == 1) {
            $raggiungibile = "Si";
        } else {
            $raggiungibile = "No";
        }

        if (strlen($this->note_nucleo) > 0) {
            $ragg_nucleo = $this->perc_nucleo . "%";
            $note_nucleo = $this->note_nucleo;
        } else {
            $ragg_nucleo = "NV";
            $note_nucleo = "NV";
        }

        //viene attribuita una classe specifica per le note del nucleo nel caso non siano quelle di default
        if ($note_nucleo !== OBIETTIVI_NOTE_NUCLEO_DEFAULT) {
            $note_nucleo_class = "nucleo_non_favorevole";
        } else {
            $note_nucleo_class = "";
        }

        $periodo_rendicontazione = new ObiettiviPeriodoRendicontazione($this->id_periodo_rendicontazione);
        
        //generazione html indicatori
        $indicatori_associati = $obiettivo->getIndicatoriAssociati();
        $html_indicatori = "";
        if (count($indicatori_associati)) {
            $html_indicatori .= "<div class='form-group clearfix padding'>
                                        <label>Indicatori</label>
                                        <span class='form-control readonly'>";
            foreach ($indicatori_associati as $indicatore) {
                $parametri_indicatore = $indicatore->getParametri();
                $html_parametri = "";
                if (count($parametri_indicatore)) {
                    $parametri_calcolo = array();                    
                    $html_parametri .= "<ul>";
                    foreach ($parametri_indicatore as $parametro) {
                        $valore_parametro = $parametro->parametro_indicatore->getValoreParametroIndicatoreRendicontazione($periodo_rendicontazione, $obiettivo_cdr);
                        $html_parametri .= "<li>" . $parametro->nome . " -> " . $valore_parametro["utilizzato"] . "</li>";
                        $parametri_calcolo[] = array("id_parametro_indicatore" => $parametro->parametro_indicatore->id, "valore" => $valore_parametro["utilizzato"]);
                    }
                    $html_parametri .= "</ul>";
                }
                $risultato_calcolo_indicatore = $indicatore->calcoloRisultatoIndicatore($parametri_calcolo)["risultato"];
                $cm = cm::getInstance();
                $date = $cm->oPage->globals["data_riferimento"]["value"];
                if ($obiettivo_cdr->id_tipo_piano_cdr != null) {
                    $tipo_piano = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano_cdr);
                } else {
                    $tipo_piano = TipoPianoCdr::getPrioritaMassima();
                }
                $piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date->format("Y-m-d"));
                $cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
                $valore_target_indicatore = $indicatore->obiettivo_indicatore->getValoreTarget($cdr);
                $raggiungimento_indicatore = $indicatore->calcoloRaggiungimentoIndicatore($risultato_calcolo_indicatore, $valore_target_indicatore)["risultato"];
                $html_indicatori .= "
                                        <ul>
                                            <li class='elenco_parametri'>"
                    . $indicatore->nome
                    . "  <ul>
                                                    <li>Parametri: " . $html_parametri . "</li>
                                                    <li>Risultato: " . $risultato_calcolo_indicatore . "</li>
                                                    <li>Raggiungimento: " . $raggiungimento_indicatore . "%</li>
                                                </ul>
                                            </li>
                                    ";
                $html_indicatori .= "</ul>";
            }
            $html_indicatori .= "</span></div>";
        }
        if ($periodo_rendicontazione->id_campo_revisione != null){
            $scelta_campo_revisione = new ObiettiviSceltaCampoRevisione($this->id_scelta_campo_revisione);        
            $campo_revisione = new ObiettiviCampoRevisione($scelta_campo_revisione->id_campo_revisione);
            $html_campo_revisione = 
                "<div class='form-group clearfix padding'>
                    <label>" . $campo_revisione->nome . "</label>
                    <span class='form-control readonly'>
                    " . $scelta_campo_revisione->descrizione . 
                    "</span>
                </div>";
        }
        $html = "
                <div class='form-group clearfix padding'>
                    <label>Azioni</label>
                    <span class='form-control readonly'>" . $this->azioni . "</span>
                </div>    
                <div class='form-group clearfix padding'>
                    <label>Provvedimenti</label>
                    <span class='form-control readonly'>" . $this->provvedimenti . "</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Criticit&agrave;</label>
                    <span class='form-control readonly'>" . $this->criticita . "</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Misurazione del grado di raggiungimento coerentemente con quanto specificato negli indicatori</label>
                    <span class='form-control readonly'>" . $this->misurazione_indicatori . "</span>
                </div>
                " . $html_indicatori . "
                <div class='form-group clearfix padding'>
                    <label>Percentuale raggiungimento Aziendale</label>
                    <span class='form-control readonly'>" . $this->perc_raggiungimento . "%</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Si ritiene l&acute;obiettivo raggiungibile al 31/12</label>
                    <span class='form-control readonly'>" . $raggiungibile . "</span>
                </div>
                " . $html_campo_revisione . "
                <div class='form-group clearfix padding'>
                    <label>Raggiungimento Nucleo (NVP) di Dipartimento</label>
                    <span class='form-control readonly'>" . $ragg_nucleo . "</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Note Nucleo</label>
                    <span class='form-control readonly " . $note_nucleo_class . "'>" . $note_nucleo . "</span>
                </div>
                ";

        return $html;
    }

    public function delete($propagate = true) {
        
        if ($propagate) {
            $allegati_rendicontazione = ObiettiviRendicontazioneAllegato::getAll(array("rendicontazione_id" => $this->id));
            $valori_indicatore_rendicontazione = IndicatoriValoreParametroIndicatoreRendicontazione::getAll(array("ID_rendicontazione" => $this->id));
        }
        
        $db = ffDB_Sql::factory();
        $sql = "
            DELETE FROM ".self::$tablename."
            WHERE ID = " . $db->toSql($this->id) . "
        ";
        
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare fisicamente l'oggetto ".static::class
                . " con ID = " . $this->id . " nel DB");
        }
        else if ($propagate) {
            foreach($allegati_rendicontazione as $allegato_rendicontazione) {
                $allegato_rendicontazione->hardDelete();
            }
            
            foreach ($valori_indicatore_rendicontazione as $valore) {
                $valore->delete();
            }
        }        
        return true;
    }    
}