<?php
class ValutazioniItem {		
	public $id;
	public $nome;
	public $descrizione;
	public $peso;
	public $anno_introduzione;
	public $anno_esclusione;
	public $id_area_item;
	public $ordine_visualizzazione;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						valutazioni_item
					WHERE
						valutazioni_item.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord())
			{
				$this->id = $db->getField("ID", "Number", true);
				$this->nome = $db->getField("nome", "Text", true);
				$this->peso = $db->getField("peso", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
				$this->anno_introduzione = $db->getField("anno_introduzione", "Number", true);
				if($db->getField("anno_esclusione", "Number", true) == 0 || $db->getField("anno_esclusione", "Number", true) == null){
					$this->anno_esclusione = null;
				}
				else{
					$this->anno_esclusione = $db->getField("anno_esclusione", "Number", true);
				}	
				$this->id_area_item = $db->getField("ID_area_item", "Number", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto ValutazioniItem con ID = ".$id);
		}
	}
	
	//viene restituito un array con le categorie associate all'item
	public function getCategorieAssociate(){
		$categorie = array();
		$db = ffDb_Sql::factory();				
		$sql = "
				SELECT 
					ID_categoria
				FROM
					valutazioni_item_categoria
				WHERE
					valutazioni_item_categoria.ID_item = " . $db->toSql($this->id)
				;
		$db->query($sql);
		if ($db->nextRecord()){
			do{
				$categoria = new ValutazioniCategoria($db->getField("ID_categoria", "Number", true));												
				$categorie[$categoria->id] = $categoria;
			} while($db->nextRecord());
		}	
		return $categorie;
	}	

	public function setCategoriaAssociata($id_categoria, $isInsert) {
        $db = ffDb_Sql::factory();

        if($isInsert) {
            $sql = "
                INSERT INTO valutazioni_item_categoria(
                    ID_item, 
                    ID_categoria
                )
                VALUES (
                    ".$db->toSql($this->id).", 
                    ".$db->toSql($id_categoria)."
                )
            ";
        } else {
            $sql = "
                DELETE FROM valutazioni_item_categoria
                WHERE 
                    ID_item = ".$db->toSql($this->id)." AND
                    ID_categoria = ".$db->toSql($id_categoria);
        }       
        if ($db->execute($sql)){
            return $db->getInsertID(true);

        }
        ffErrorHandler::raise("Associazione/Disassociazione item " . $this->id . " con la categoria " . $id_categoria . " non riuscita");
    }

	//viene restituito un array con i punteggi dell'item
	public function getPunteggi(){
		$punteggi = array();
		$db = ffDb_Sql::factory();						
		$sql = "
				SELECT 
					ID
				FROM
					valutazioni_punteggio_item
				WHERE
					valutazioni_punteggio_item.ID_item = " . $db->toSql($this->id) . "
				ORDER BY
					valutazioni_punteggio_item.punteggio ASC
				";
		$db->query($sql);
		if ($db->nextRecord()){
			do{
				$punteggi[] = new ValutazioniPunteggioItem($db->getField("ID", "Number", true));
			} while($db->nextRecord());
		}	
		return $punteggi;
	}
    
    //viene restituito il punteggio massimo per l'item, null se nessun punteggio
	public function getPunteggioMassimo(){		
		$db = ffDb_Sql::factory();						
		$sql = "
				SELECT 
					valutazioni_punteggio_item.punteggio
				FROM
					valutazioni_punteggio_item
				WHERE
					valutazioni_punteggio_item.ID_item = " . $db->toSql($this->id) . "
				ORDER BY
					valutazioni_punteggio_item.punteggio DESC                
				";
		$db->query($sql);
		if ($db->nextRecord()){
			return $db->getField("punteggio", "Number", true);
		}	
		return null;
	}

    public function getAll($filters = array()) {
        $items = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value){
            $where .= "AND ".$field."=".$db->toSql($value)." ";
        }

        $sql = "SELECT valutazioni_item.*
                FROM valutazioni_item
				" . $where ."
				ORDER BY valutazioni_item.ordine_visualizzazione DESC";
        $db->query($sql);
        if ($db->nextRecord())
        {
            do
            {
                $item = new ValutazioniItem();
                $item->id = $db->getField("ID", "Number", true);
                $nome = $db->getField("nome", "Text", true);
                $item->nome = isset($nome) ? $nome : null;
                $item->descrizione = $db->getField("descrizione", "Text", true);
                $item->peso = $db->getField("peso", "Number", true);
                $item->id_ambito = $db->getField("id_ambito", "Number", true);
                $item->anno_introduzione = $db->getField("anno_introduzione", "Number", true);
                if($db->getField("anno_esclusione", "Number", true) == 0 || $db->getField("anno_esclusione", "Number", true) == null){
                    $item->anno_esclusione = null;
                }
                else{
                    $item->anno_esclusione = $db->getField("anno_esclusione", "Number", true);
                }
                $item->id_area_item = $db->getField("ID_area_item", "Number", true);
                $item->ordine_visualizzazione = $db->getField("ordine_visualizzazione", "Number", true);
                $items[] = $item;
            }while ($db->nextRecord());
        }
        return $items;
    }

    function delete($propaga = true) {
        if($this->canDelete()) {
            if($propaga && !$this->checkOrDeleteRelations("delete")) {
                return false;
            }

            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM valutazioni_item
                WHERE valutazioni_item.ID = ".$db->toSql($this->id)."
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ValutazioniItem con ID='" . $this->id . "' dal DB");
            }

            return true;
        }

        return false;
    }

    function canDelete() {
        return
            count(ValutazioniValutazioneItem::getAll(array("ID_item" => $this->id))) == 0
                &&
            $this->checkOrDeleteRelations("canDelete");
    }

    private function checkOrDeleteRelations($function) {
        $punteggi_item = ValutazioniPunteggioItem::getAll(array("ID_item" => $this->id));
        foreach($punteggi_item as $punteggio_item) {
            if(!$punteggio_item->$function()) {
                return false;
            }
        }

        $item_categorie = ValutazioniItemCategoria::getAll(array("ID_item" => $this->id));
        foreach($item_categorie as $item_categoria) {
            if(!$item_categoria->$function()) {
                return false;
            }
        }
        return true;
    }
}