<?php

class CmsHomeSezione extends Entity{
    protected static $tablename = "cms_home_sezione";
    
    public static function getSezioneAnno(AnnoBudget $anno_budget) {
        $result = array();
        $calling_class = static::class;
        foreach ($calling_class::getAll() as $sezione) {
            if ($sezione->anno_inizio <= $anno_budget->descrizione && 
                ($sezione->anno_fine == null || $sezione->anno_fine >= $anno_budget->descrizione)) {
                $result[] = $sezione;
            }
        }
        
        return $result;
    }

    public function isAllegato() {
        return $this->tipo == "A";
    }
    
    public function getTipoDescrizione() {
        if ($this->isAllegato()) {
            return "ALLEGATO";
        }
        
        return "HTML";
    }
    
    public function getTipoField($cm, $anno, $user) {
        $oFields = array();

        $oField = ffField::factory($cm->oPage);
        $oField->id = "testo";
        $oField->base_type = "Text";
        $oField->required = true;

        if ($this->isAllegato()) {
            $oField->label = "Descrizione file";
            $oFields[] = $oField;

            $allegati_helper = new AllegatoHelper();
            if (count(CmsHomeSezioneAllegato::getAll(['ID_sezione' => $this->id])) == 0) {
                $oField = $allegati_helper->getUploadForm(
                    'CmsHomeSezioneAllegato',
                    [
                        'id_sezione' => $this->id,
                        'user_id' => $user->matricola_utente_selezionato,
                        'anno_riferimento' => $anno->descrizione,
                        'use_documenti_home' => true
                    ]
                );
                $oFields[] = $oField;
            }
            else {
                $oFields[] = $allegati_helper->addResponseAllegatiAjaxForm();
            }
        }
        else {
            $oField->extended_type = "Text";
            $oField->widget = "ckeditor";
            $oField->label = "Contenuto della sezione";

            $oFields[] = $oField;
        }

        return $oFields;
    }

    public static function isValidRangeAnno($anno_introduzione, $anno_termine) {
        if (!empty($anno_termine) && $anno_termine < $anno_introduzione) {
            return false;
        }

        return true;
    }
    
    public function delete() {
        $allegati = CmsHomeSezioneAllegato::getAll(["ID_sezione" => $this->id]);
        
        foreach ($allegati as $allegato) {
            $allegato->hardDelete($allegato->filename_md5);
        }
    }
}