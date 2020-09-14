<?php
class StrategiaDescrizioneIntroduttiva {	
	public $id;
    public $descrizione;
    public $anno_introduzione;
	
	public function __construct(AnnoBudget $anno)
    {				
        $db = ffDb_Sql::factory();

        $sql = "
                SELECT 
                    strategia_descrizione_introduttiva.*
                FROM
                    strategia_descrizione_introduttiva
                WHERE
                    strategia_descrizione_introduttiva.anno_introduzione <= " . $db->toSql($anno->descrizione) . "
				ORDER BY anno_introduzione DESC
				LIMIT 1
				";
        $db->query($sql);
        if ($db->nextRecord()){			
            $this->id = $db->getField("ID", "Number", true);
			$this->descrizione = $db->getField("descrizione", "Text", true);			
			$this->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
        }	
        else
            throw new Exception("Nessuna descrizione introduttiva di strategia definita per l'anno ".$anno->descrizione);
    }
}