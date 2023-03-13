<?php
namespace scadenze;

class Tipologia extends \Entity
{
    protected static $tablename = "scadenze_tipologia";        
    protected static $relations = array(
        "relation" => array("target_class" => "\scadenze\Scadenza",
            "keys" => array(
                "ID_tipologia" => "ID",
            ),
            "allow_delete" => false,
            "propagate_delete" => false,
        )
    );
}
