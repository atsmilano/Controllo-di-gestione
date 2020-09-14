<?php
class RiesameDirezioneRiesame {		
	public $id;
    public $codice_cdr;
    public $id_anno_budget;
    public $data_chiusura;
	
    public static $stati_riesame = array	(
                                                array(  "ID" => 0,
														"descrizione" => "Non compilato",
													),
                                                array(  "ID" => 1,
														"descrizione" => "In fase di compilazione",
													),
                                                array(  "ID" => 2,
														"descrizione" => "Compilato",
                                                    ),
												);
    
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						riesame_direzione_riesame                       
					WHERE
						riesame_direzione_riesame.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
                $this->codice_cdr = $db->getField("codice_cdr", "Text", true);				
                $this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $this->data_chiusura = CoreHelper::getDateValueFromDB($db->getField("data_chiusura", "Date", true));
			}	
			else
				throw new Exception("Impossibile creare l'oggetto RiesameDirezioneRiesame con ID = ".$id);
		}
	}
    
    public static function factoryFromCdrAnno(Cdr $cdr, AnnoBudget $anno){
		$db = ffDb_Sql::factory();

        $sql = "
                SELECT 
                    *
                FROM
                    riesame_direzione_riesame 
                WHERE
                    riesame_direzione_riesame.codice_cdr = " . $db->toSql($cdr->codice) . "
                    AND riesame_direzione_riesame.ID_anno_budget = " . $db->toSql($anno->id)
                ;
		$db->query($sql);		
		if ($db->nextRecord()){				
            $riesame = new RiesameDirezioneRiesame();
            $riesame->id = $db->getField("ID", "Number", true);
            $riesame->codice_cdr = $db->getField("codice_cdr", "Text", true);				
            $riesame->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
            $riesame->data_chiusura = CoreHelper::getDateValueFromDB($db->getField("data_chiusura", "Date", true));
            
            return $riesame;
		}
		else{
			throw new Exception("Impossibile creare l'oggetto RiesameDirezioneRiesame con codice_cdr = ".$cdr->codice." per l'anno ".$anno->descrizione);
		}
	}
    
    //inserimento o update su db
	public function save(){
		$db = ffDB_Sql::factory();
		//insert
		if ($this->id !== null){            
			$sql = "
				UPDATE riesame_direzione_riesame
				SET
                    codice_cdr=".$db->toSql($this->codice_cdr).",
                    ID_anno_budget=".$db->toSql($this->id_anno_budget).",
                    data_chiusura=".$db->toSql($this->data_chiusura)."
				WHERE
					ID = ".$db->toSql($this->id)
				;			
		}	
        else {            
            $sql = "
				INSERT INTO riesame_direzione_riesame
                    (
                    codice_cdr,
                    ID_anno_budget,
                    data_chiusura
                    )
				VALUES
                    (
                    ".$this->codice_cdr=null?"NULL":$db->toSql($this->codice_cdr).",
                    ".$this->id_anno_budget=null?"NULL":$db->toSql($this->id_anno_budget).",
                    ".$this->data_chiusura=null?"NULL":$db->toSql($this->data_chiusura)."
                    )";	            
        } 
        if (!$db->execute($sql)){
            throw new Exception("Impossibile aggiornare l'oggetto RiesameDirezioneRiesame con ID='".$this->id."' nel DB");
        }
        else {
            if($this->id == null) {
                return $db->getInsertID()->getValue();
            }
            else {
                return $this->id;
            }
        }
	}
    
    //funzione che restituisce lo stato d'avanzamento del riesame
	public function getIdStato () {		
		//la variabile stato viene inizializzta con stato in fase di compilazione (se l'oggetto esiste esiste anche il record)
		$stato = 1;        
        if ($this->data_chiusura !== null) {            
            $stato = 2;
        }  
		return $stato;
	}
}