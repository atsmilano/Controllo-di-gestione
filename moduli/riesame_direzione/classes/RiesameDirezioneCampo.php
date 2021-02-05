<?php
class RiesameDirezioneCampo extends Entity{		
	protected static $tablename = "riesame_direzione_campo";
	
    public static $tipi_campo = array	(
                                                array(  "ID" => 1,
														"descrizione" => "Testo",
													),
                                                array(  "ID" => 2,
														"descrizione" => "Flag (Si / No)",
                                                    ),
												);
    
    //restituisce array con tutti campi del riesame ordinati per ordinamento
    public static function getAll($where=array(), $order=array(array("fieldname"=>"ordinamento", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
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