<?php
class NoDbTabellaSupporto
{
    private static $id;
    //data_array è un array di array costituito dai dati della tabella 
    // [["ID"=>1,"descrizione"=>"dato1"],["ID"=>2,"descrizione"=>"dato2"]]
    //NB campo ID, obbligatorio, gli altri campi sono arbitrari e popoleranno
    //gli attributi dell'oggetto con lo stesso nome in lwrcase
    protected static $data_array = array();
    
    public function __construct ($id) {
        $classname = static::class;
        $found = false;
        foreach ($classname::$data_array as $data) {
            if ($data["ID"] == $id) {
                foreach ($data as $key=>$value) {
                    $this->{strtolower($key)} = $value;
                }
                $found = true;
                break;
            }
        }
        if ($found == false) {
            throw new \Exception("Impossibile creare l'oggetto di tipo '".$classname."' con ID = " . $id);
        }        
    }

    public static function getData() {
        $classname = static::class;
        $return_data_array = array();
        foreach ($classname::$data_array as $data) {
            $return_data_array[] = new $classname($data["ID"]);
        }
        return $return_data_array;
    }

    protected static function getElement($value, $key="ID") {
        $classname = static::class;
        return array_search($value, array_column($classname::getData(), $key));
    }
}