<?php
class TableResponsabileCdr extends Singleton {
    protected static $instance = null;
    private $respCdrInData = null;
    
    protected function __construct() {
        $cm = cm::getInstance();
        $responsabili_cdr = ResponsabileCdr::getResponsabiliCdrInData($cm->oPage->globals["data_riferimento"]["value"]);
        foreach($responsabili_cdr as $responsabile_cdr) {
            $this->respCdrInData[$responsabile_cdr->id] = $responsabile_cdr;
        }       
    }    
    
    public function getRespCdrInData() {
        return $this->respCdrInData;
    }
}

