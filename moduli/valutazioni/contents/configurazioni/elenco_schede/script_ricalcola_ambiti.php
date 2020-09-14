<?php
if (isset($_REQUEST["ID_periodo"])) {
    try {
        $periodo = new ValutazioniPeriodo($_REQUEST["ID_periodo"]);
        $anno_valutazione = new ValutazioniAnnoBudget($periodo->id_anno_budget);

        //recupero categorie e ambiti associati in array
        $categorie_attive = array();        
        foreach($anno_valutazione->getCategorieAnno() as $categoria_attiva) {
            $categoria_attiva->ambiti_associati = $periodo->getAmbitiCategoriaPeriodo($categoria_attiva);
            $categorie_attive[] = $categoria_attiva;
        }

        $valutazioni_attive = $periodo->getValutazioniAttivePeriodo();

        if (empty($valutazioni_attive)) {
            die("Non ci sono valutazioni attive");
        }
        else {
            echo "Ci sono valutazioni attive <br />";

            foreach ($valutazioni_attive as $valutazione) {
                for ($j = 0; $j < count($categorie_attive); $j++) {
                    $categoria_attiva = $categorie_attive[$j];
                    if ($valutazione->id_categoria == $categoria_attiva->id) {
                        $categoria_index = $j;
                        break;
                    }
                }

                $ambiti_categoria = $categorie_attive[$categoria_index];
                $ambiti = $ambiti_categoria->ambiti_associati;

                $edit_ambiti = false;
                foreach ($ambiti as $ambito) {
                    if ($ambito->isAmbitoDaAggiornare($valutazione) &&
                        $ambito->isValutatoCategoriaAnno($ambiti_categoria, $anno_valutazione)) {
                        $edit_ambiti = true;
                        break;
                    }
                }

                if ($edit_ambiti) {
                    echo "Valutazione con ID=".$valutazione->id." (valutato='".$valutazione->matricola_valutato."', valutatore='".$valutazione->matricola_valutatore."') aggiornata <br />";
                    foreach ($ambiti as $ambito) {
                        $valutazione->saveAmbitoPrecalcolato($ambito, true);
                    }
                } else {
                    echo "Valutazione con ID=".$valutazione->id." (valutato='".$valutazione->matricola_valutato."', valutatore='".$valutazione->matricola_valutatore."') NON necessita di aggiornamento<br />";
                }
            }
            die("Procedura di aggiornamento degli ambiti precalcolati conclusa");
        }
    }
    catch (Exception $e) {
        die("Impossibile creare il periodo con ID '".$_REQUEST["ID_periodo"]."'. Errore: ".$e->getMessage());
    }
}
else {
    die("Non &egrave; stato specificato alcun periodo. L'operazione sar&agrave; interrotta");
}