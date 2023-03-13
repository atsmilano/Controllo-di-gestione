<?php
namespace scadenze;

class AbilitazioneCdr extends \Entity
{
    protected static $tablename = "scadenze_abilitazione_cdr";      
    protected static $relations = array(
        "relation" => array("target_class" => "\scadenze\Scadenza",
            "keys" => array(
                "ID_abilitazione_cdr" => "ID",
            ),
            "allow_delete" => false,
            "propagate_delete" => false,
        )
    );
    
    public function getContattiMail() {            
        return ContattoMail::getAll(array("ID_abilitazione_cdr" => $this->id));
    }
}
