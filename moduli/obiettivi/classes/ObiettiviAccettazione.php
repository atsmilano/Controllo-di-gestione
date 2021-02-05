<?php
class ObiettiviAccettazione extends Entity{	
	protected static $tablename = "obiettivi_accettazione";	
	
	public static function factoryFromDipendenteAnno(Personale $personale, AnnoBudget $anno){
		$db = ffDb_Sql::factory();

		$sql = "
				SELECT 
					".self::$tablename.".ID
				FROM
					".self::$tablename."
				WHERE
					".self::$tablename.".matricola_personale = " . $db->toSql($personale->matricola) . "
					AND ".self::$tablename.".ID_anno_budget = " . $db->toSql($anno->id) . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
			return new ObiettiviAccettazione($db->getField("ID", "Number", true));
		}
		throw new Exception("Impossibile creare l'oggetto ".static::class." con matricola = ".$personale->matricola." e anno=".$anno->descrizione);
	}
}