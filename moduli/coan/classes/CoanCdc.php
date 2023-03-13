<?php

class CoanCdc extends Entity
{

    protected static $tablename = "coan_cdc";
    protected static $relations = array(
        "relation" => array("target_class" => "CoanConsuntivoPeriodo",
            "keys" => array(
                "ID_cdc_coan" => "ID",
            ),
            "allow_delete" => false,
            "propagate_delete" => false,
        )
    );

    public static function isCdrAssociatoAnno(AnnoBudget $anno, $cdr)
    {
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT DISTINCT " . self::$tablename . ".codice_cdr
            FROM " . self::$tablename . "
                INNER JOIN coan_consuntivo_periodo ON " . self::$tablename . ".ID = coan_consuntivo_periodo.ID_cdc_coan
                INNER JOIN coan_periodo ON coan_consuntivo_periodo.ID_periodo_coan = coan_periodo.ID
            WHERE coan_periodo.ID_anno_budget = " . $db->toSql($anno->id) . "
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                if ($cdr->codice == $db->getField("codice_cdr", "Text", true)) {
                    return true;
                }
            } while ($db->nextRecord());
        }
        return false;
    }

    public static function getAttiviAnno(AnnoBudget $anno)
    {
        $item_anno = array();

        foreach (CoanCdc::getAll() as $item) {
            if ($item->anno_introduzione <= $anno->descrizione &&
                    ($item->anno_termine == 0 || $item->anno_termine >= $anno->descrizione)
            ) {
                $item_anno[] = $item;
            }
        }

        return $item_anno;
    }

    public static function getCdrAssociatiCdc(CoanPeriodo $periodo_coan)
    {
        $result = array();
        $anno = new AnnoBudget($periodo_coan->id_anno_budget);

        $piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $periodo_coan->data_fine);
        $cdr_radice_piano = $piano_cdr->getCdrRadice();
        $cdr_anno = $cdr_radice_piano->getGerarchia();

        foreach (CoanCdc::getAttiviAnno($anno) as $cdc) {
            foreach ($cdr_anno as $cdr_associato) {
                if ($cdc->codice_cdr == $cdr_associato["cdr"]->codice && !in_array($cdr_associato, $result)) {
                    $result[] = $cdr_associato;
                    break;
                }
            }
        }

        return $result;
    }

}
