<?php
class TableCdr extends Singleton {
    protected static $instance = null;
    private $piani_cdr = null;
    
    protected function __construct() {      
        $tipo_piani = TipoPianoCdr::getAll();
        $cm = cm::getInstance();
        
        //per ogni tipo piano viene costruito un array contenente tipo piano cdr, piano cdr e cdr che viene accodato all'array $piani_cdr.
        foreach($tipo_piani as $tipo_piano) {
            $cdr_piano = array();
            $piano_attivo = PianoCdr::getAttivoInData($tipo_piano, $cm->oPage->globals["data_riferimento"]["value"]->format('Y-m-d'));
            
            if($piano_attivo != null) {
                foreach($piano_attivo->getCdr() as $cdr) {
                    $cdr_piano[$cdr->id] = $cdr;
                }
            }
            
            $this->piani_cdr[] = array(
                'tipo_piano_cdr' => $tipo_piano,
                'piano_cdr' => $piano_attivo,
                'cdr' => $cdr_piano,
            );
        }
    }
    
    public function getPianiCdr() {
        return $this->piani_cdr;
    }
}
