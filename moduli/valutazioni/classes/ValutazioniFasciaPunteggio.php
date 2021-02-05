<?php
class ValutazioniFasciaPunteggio extends Entity{
    protected static $tablename = "valutazioni_fascia_punteggio";

    public static function getAll($where=array(), $order=array(array("fieldname"=>"min", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }

    public static function getFasceInData(DateTime $data) {
        $fasce_punteggio = array();
        $db = ffDB_Sql::factory();
        $data_sql = $db->toSql($data->format("Y-m-d"));

        $sql = "SELECT ".self::$tablename.".*
                FROM ".self::$tablename."
				WHERE 
				  ".self::$tablename.".data_inizio <= ".$data_sql." AND
				  (".self::$tablename.".data_fine IS NULL
				    OR ".self::$tablename.".data_fine = '0000-00-00' 
				    OR ".self::$tablename.".data_fine >= ".$data_sql.")
				ORDER BY
				    ".self::$tablename.".min
				";

        $db->query($sql);
        if ($db->nextRecord()){
            do{
                $fascia_punteggio = new ValutazioniFasciaPunteggio();

                $fascia_punteggio->id = $db->getField("ID", "Number", true);
                $fascia_punteggio->min =$db->getField("min", "Number", true);
                $fascia_punteggio->max = $db->getField("max", "Number", true);
                $fascia_punteggio->colore = $db->getField("colore", "Text", true);
                $fascia_punteggio->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                $fascia_punteggio->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));

                $fasce_punteggio[] = $fascia_punteggio;
            }while ($db->nextRecord());
        }
        return $fasce_punteggio;
    }

    public static function isFasciaEliminabile() {
        foreach(ValutazioniFasciaPunteggio::getAll() as $fascia) {
            if(!isset($fascia->data_fine)) {
                return true;
            } else {
                $data_fine = DateTime::createFromFormat("Y-m-d", $fascia->data_fine);
                $data_odierna = new DateTime();
                if($data_fine > $data_odierna) {
                    return true;
                }
            }
        }
        return false;
    }
}