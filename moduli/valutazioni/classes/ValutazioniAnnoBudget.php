<?php
class ValutazioniAnnoBudget extends AnnoBudget {
    //restituisce array con tutti gli ambiti di valutazione attivi per un anno
    public function getAmbitiAnno() {
        $ambiti = array();
        $db = ffDB_Sql::factory();

        $sql = "SELECT valutazioni_ambito.ID
				FROM valutazioni_ambito
				WHERE 
					(anno_inizio <= " . $db->toSql($this->descrizione) . "
						AND (anno_fine >= " . $db->toSql($this->descrizione) . " OR anno_fine is null OR anno_fine = 0))
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $ambiti[] = new ValutazioniAmbito($db->getField("ID", "Number", true));
            } while ($db->nextRecord());
        }
        return $ambiti;
    }

    //restituisce array con tutte le categorie attive in un anno
    public function getCategorieAnno() {
        $categorie = array();
        $db = ffDB_Sql::factory();

        $sql = "SELECT valutazioni_categoria.*
				FROM valutazioni_categoria
				WHERE 
					(anno_inizio <= " . $db->toSql($this->descrizione) . "
						AND (anno_fine >= " . $db->toSql($this->descrizione) . " OR anno_fine is null OR anno_fine = 0))
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {                
                $categoria = new ValutazioniCategoria();

                $categoria->id = $db->getField("ID", "Number", true);
                $categoria->abbreviazione = $db->getField("abbreviazione", "Text", true);
                $categoria->descrizione = $db->getField("descrizione", "Text", true);
                $categoria->dirigenza = CoreHelper::getBooleanValueFromDB($db->getField("dirigenza", "Number", true));
                $categoria->formula_appartenenza_personale = $db->getField("formula_appartenenza_personale", "Text", true);
                $categoria->anno_inizio = $db->getField("anno_inizio", "Number", true);
                if ($db->getField("anno_fine", "Number", true) == 0 || $db->getField("anno_fine", "Number", true) == null) {
                    $categoria->anno_fine = null;
                } else {
                    $categoria->anno_fine = $db->getField("anno_fine", "Number", true);
                }
                $categorie[] = $categoria;
            } while ($db->nextRecord());
        }
        return $categorie;
    }

    //restituisce array con tutti i periodi in un anno
    public function getPeriodiAnno() {
        $periodi = array();
        $db = ffDb_Sql::factory();

        $sql = "SELECT valutazioni_periodo.ID
				FROM valutazioni_periodo
				WHERE 
					valutazioni_periodo.ID_anno_budget = " . $db->toSql($this->id) . "
                ORDER BY
                    valutazioni_periodo.data_fine DESC                    
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $periodi[] = new ValutazioniPeriodo($db->getField("ID", "Number", true));
            } while ($db->nextRecord());
        }
        return $periodi;
    }

    //restituisce array con tutti gli item in un anno
    //se passato l'ambito viene filtrato il risultato
    public function getItemsAnno(ValutazioniAmbito $ambito = null, ValutazioniCategoria $categoria = null, ValutazioniAreaItem $area = null) {
        $items_anno = array();
        $db = ffDb_Sql::factory();
        $where = "";
        $join = "";
        if ($ambito !== null) {
            $where .= " AND valutazioni_area_item.ID_ambito=" . $db->toSql($ambito->id);
        }
        if ($categoria !== null) {
            $join .= " INNER JOIN valutazioni_item_categoria ON valutazioni_item.ID = valutazioni_item_categoria.ID_item";
            $where .= " AND valutazioni_item_categoria.ID_categoria=" . $db->toSql($categoria->id);
        }
        if ($area !== null) {
            $where .= " AND valutazioni_area_item.ID =" . $db->toSql($area->id);
        }

        $sql = "SELECT
					valutazioni_item.*
				FROM
					valutazioni_item
					INNER JOIN valutazioni_area_item ON valutazioni_item.ID_area_item = valutazioni_area_item.ID
					" . $join . "
				WHERE
					anno_introduzione <= " . $db->toSql($this->descrizione) . "
				AND (
					anno_esclusione > " . $db->toSql($this->descrizione) . "
					OR anno_esclusione IS NULL
					OR anno_esclusione = 0)
				" . $where . "
				ORDER BY
					valutazioni_area_item.ordine_visualizzazione,
					valutazioni_area_item.descrizione,
					valutazioni_item.ordine_visualizzazione,
					valutazioni_item.descrizione
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $item = new ValutazioniItem();
                $item->id = $db->getField("ID", "Number", true);
                $item->nome = $db->getField("nome", "Text", true);
                $item->peso = $db->getField("peso", "Number", true);
                $item->descrizione = $db->getField("descrizione", "Text", true);
                $item->anno_introduzione = $db->getField("anno_introduzione", "Number", true);
                if ($db->getField("anno_esclusione", "Number", true) == 0 || $db->getField("anno_esclusione", "Number", true) == null) {
                    $item->anno_esclusione = null;
                } else {
                    $item->anno_esclusione = $db->getField("anno_esclusione", "Number", true);
                }
                $item->id_area_item = $db->getField("ID_area_item", "Number", true);
                $item->ordine_visualizzazione = $db->getField("ordine_visualizzazione", "Number", true);
                $item->tipo_visualizzazione = $db->getField("tipo_visualizzazione", "Number", true);

                $items_anno[] = $item;
            } while ($db->nextRecord());
        }
        return $items_anno;
    }

    //restituisce array con tutti i totali previsti per l'anno
    //in caso venga passata una categoria vengono estratti solamente i totali per la categoria
    public function getTotaliAnno(ValutazioniCategoria $categoria = null) {
        $totali_anno = array();
        $db = ffDb_Sql::factory();

        if ($categoria !== null) {
            $join = " INNER JOIN valutazioni_totale_categoria ON valutazioni_totale.ID = valutazioni_totale_categoria.ID_totale ";
            $where = " AND valutazioni_totale_categoria.ID_categoria = " . $db->toSql($categoria->id);
        } else {
            $join = $where = "";
        }
        $sql = "SELECT
					valutazioni_totale.ID
				FROM
					valutazioni_totale
					" . $join . "
				WHERE
					anno_inizio <= " . $db->toSql($this->descrizione) . "
					" . $where . "
				AND (
					anno_fine > " . $db->toSql($this->descrizione) . "
					OR anno_fine IS NULL
					OR anno_fine = 0)
				ORDER BY
					valutazioni_totale.ordine_visualizzazione
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $totali_anno[] = new ValutazioniTotale($db->getField("ID", "Number", true));
            } while ($db->nextRecord());
        }
        return $totali_anno;
    }
}