<?php
class ValutazioniFasciaPunteggio {
    public $id;
    public $min;
    public $max;
    public $data_inizio;
    public $data_fine;
    public $colore;

    public function __construct($id=null){
        if($id !== null){
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT 
                    *
                FROM
                    valutazioni_fascia_punteggio
                WHERE
                    valutazioni_fascia_punteggio.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord())
            {
                $this->id = $db->getField("ID", "Number", true);
                $this->min = $db->getField("min", "Number", true);
                $this->max = $db->getField("max", "Number", true);
                $this->colore = $db->getField("colore", "Text", true);
                $this->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                $this->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));

            }
            else {
                throw new Exception("Impossibile creare l'oggetto ValutazioniFascePunteggio con ID = ".$id);
            }
        }
    }

    public static function getAll ($filters=array()) {
        $fasce_punteggio = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value){
            $where .= "AND ".$field."=".$db->toSql($value)." ";
        }

        $sql = "
            SELECT valutazioni_fascia_punteggio.*
            FROM valutazioni_fascia_punteggio
            " . $where . "
            ORDER BY valutazioni_fascia_punteggio.min
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

    public static function getFasceInData(DateTime $data) {
        $fasce_punteggio = array();
        $db = ffDB_Sql::factory();
        $data_sql = $db->toSql($data->format("Y-m-d"));

        $sql = "SELECT valutazioni_fascia_punteggio.*
                FROM valutazioni_fascia_punteggio
				WHERE 
				  valutazioni_fascia_punteggio.data_inizio <= ".$data_sql." AND
				  (valutazioni_fascia_punteggio.data_fine IS NULL
				    OR valutazioni_fascia_punteggio.data_fine = '0000-00-00' 
				    OR valutazioni_fascia_punteggio.data_fine >= ".$data_sql.")
				ORDER BY
				    valutazioni_fascia_punteggio.min
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