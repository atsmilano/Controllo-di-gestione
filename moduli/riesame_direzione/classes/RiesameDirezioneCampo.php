<?php

class RiesameDirezioneCampo {		
	public $id;
    public $nome;
	public $descrizione;
    public $id_tipo_campo;
    public $ordinamento;
    public $id_sezione;
    public $anno_introduzione;
    public $anno_termine;
	
    public static $tipi_campo = array	(
                                                array(  "ID" => 1,
														"descrizione" => "Testo",
													),
                                                array(  "ID" => 2,
														"descrizione" => "Flag (Si / No)",
                                                    ),
												);
    
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						riesame_direzione_campo                       
					WHERE
						riesame_direzione_campo.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
                $this->nome = $db->getField("nome", "Text", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
                $this->id_tipo_campo = $db->getField("ID_tipo_campo", "Number", true);
                $this->ordinamento = $db->getField("ordinamento", "Number", true);
                $this->id_sezione = $db->getField("ID_sezione", "Number", true);
                $this->anno_introduzione = $db->getField("anno_introduzione", "Number", true);
                if ((int)$db->getField("anno_termine", "Text", true) !== 0) {
					$this->anno_termine = $db->getField("anno_termine", "Text", true);
				}
				else {
					$this->anno_termine = null;
				}
			}	
			else
				throw new Exception("Impossibile creare l'oggetto RiesameDirezioneCampo con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$campi = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					riesame_direzione_campo.*
				FROM
					riesame_direzione_campo
                    " . $where . "
                ORDER BY
                    riesame_direzione_campo.ordinamento
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $campi[] = new RiesameDirezioneCampo($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $campi;
	}
    
    public static function getCampiAnno (AnnoBudget $anno) {
        //i campi verranno restituiti secondo ordinamento nel db per via dei valori restituiti da getAll
		$campi_anno = array();
		foreach(RiesameDirezioneCampo::getAll() as $campo) {
			if ($campo->anno_introduzione <= $anno->descrizione && ($campo->anno_termine == null || $campo->anno_termine >= $anno->descrizione)) {
				$campi_anno[] = $campo;
			}
		}
		return $campi_anno;
	}
    
    public function getValoreCampoRiesame(RiesameDirezioneRiesame $riesame) {
        $valore = null;
        
        $db = ffDb_Sql::factory();
        $sql = "
				SELECT 
					riesame_direzione_valore_campo.valore
				FROM
					riesame_direzione_valore_campo
                WHERE
                    riesame_direzione_valore_campo.ID_campo = " . $db->toSql($this->id) . "
                    AND riesame_direzione_valore_campo.ID_riesame = " . $db->toSql($riesame->id)
                ;
		$db->query($sql);
		if ($db->nextRecord()){
            $valore = $db->getField("valore", "Text", true);
		}	
        
		return $valore;
    }
    
    public function salvaValoreCampoRiesame(RiesameDirezioneRiesame $riesame, $valore) {        
        $db = ffDb_Sql::factory();
        $sql = '
				SELECT 
                    riesame_direzione_valore_campo.ID,
					riesame_direzione_valore_campo.valore
				FROM
					riesame_direzione_valore_campo
                WHERE
                    riesame_direzione_valore_campo.ID_campo = ' . $db->toSql($this->id) . '
                    AND riesame_direzione_valore_campo.ID_riesame = ' . $db->toSql($riesame->id)
                ;
		$db->query($sql);
		if ($db->nextRecord()){
            //verifica per evitare query nel caso in cui il valore non sia variato
            if ($db->getField("valore", "Text", true) != $valore) {
                $sql = 'UPDATE
                            riesame_direzione_valore_campo
                        SET
                            ID_riesame = '.$db->toSql($riesame->id).', 
                            ID_campo = '.$db->toSql($this->id).', 
                            valore = '.$db->toSql($valore).'
                        WHERE
                            ID = '.$db->toSql($db->getField("ID", "Number", true)).'
                        ';
                if ($db->execute($sql)) {
                    return true;
                }
                else {
                    return false;
                }
            }
            return true;
		}	
        else {
            $sql = 'INSERT INTO
                        riesame_direzione_valore_campo 
                        (
                            ID_riesame, 
                            ID_campo, 
                            valore
                        )
                    VALUES
                        (
                            '.$db->toSql($riesame->id).'
                            , '.$db->toSql($this->id).'
                            , '.$db->toSql($valore).'
                        )
                    ';
            if ($db->execute($sql)) {
                return true;
            }
            else {
                return false;
            }
        }
    }
}