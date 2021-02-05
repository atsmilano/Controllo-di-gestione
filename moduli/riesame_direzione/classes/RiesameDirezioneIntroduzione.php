<?php
class RiesameDirezioneIntroduzione extends Entity{	
	protected static $tablename = "riesame_direzione_introduzione";	    
	
	public static function getIntroduzioneAnno (AnnoBudget $anno) {
		$introduzione_anno = false;
		foreach(RiesameDirezioneIntroduzione::getAll() as $introduzione) {
			if ($introduzione->anno_introduzione <= $anno->descrizione && ($introduzione->anno_termine == null || $introduzione->anno_termine >= $anno->descrizione)) {
				return $introduzione;                
			}
		}
		return $introduzione_anno;
	}
}