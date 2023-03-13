<?php
class DelegaAccesso extends Entity{		
    protected static $tablename = "delega_accesso";
    
    //restituisce un array di id di moduli collegati alla delega
    public function getModuliDelega() {
        $moduli_delega = array();
        
        $moduli = \core\Modulo::getActiveModulesFromDisk();
        
        $db = ffDb_Sql::factory();
        $sql = "
                SELECT 
                    delega_accesso_modulo.ID_modulo
                FROM
                    delega_accesso_modulo
                WHERE
                    delega_accesso_modulo.ID_delega_accesso = " . $db->toSql($this->id)
        ;
        $db->query($sql);        
        if ($db->nextRecord()) {
            do {      
                $id = $db->getField("ID_modulo", "Number", true);
                foreach ($moduli as $modulo) {
                    if ($modulo->id == $id) {
                        $moduli_delega[] = $modulo;
                    }
                }                
            } while ($db->nextRecord());
        }
        return $moduli_delega;
    }
    
    //riceve un array di id di moduli collegati alla delega per salvare le relazioni ed eliminare quelle non mantenute
    public function saveModuliDelega($id_moduli) {
        $db = ffDb_Sql::factory();
        $db2 = ffDb_Sql::factory();
        //eliminazione relazioni non mantenute
        //vengono estratti dal db tutti i moduli abilitati per la delega
        $sql = "
                SELECT 
                    delega_accesso_modulo.ID
                FROM
                    delega_accesso_modulo
                WHERE
                    delega_accesso_modulo.ID_delega_accesso = " . $db->toSql($this->id);
            ;
        $db->query($sql);        
        if ($db->nextRecord()) {
            do {
                $found = false;
                //viene verificato se il modulo è stato mantenuto
                foreach ($id_moduli as $id_modulo) {
                    if ($db->getField("ID_modulo", "Number", true) == $id_modulo){
                        $found = true;
                        break;
                    }
                }
                //se la relazione con id modulo non è fra quelli mantenuti viene eliminata
                if ($found == false) {
                    $sql2 = "DELETE FROM delega_accesso_modulo WHERE ID = " . $db->getField("ID", "Number", true);
                    $db2->execute($sql2);
                }            
            }while ($db->nextRecord());
        }
        //vengono salvate tutte le nuove relazioni in caso ce ne siano        
        foreach ($id_moduli as $id_modulo) {
            //se la reazione esiste già viene mantenuta altrimenti inserita
            $sql = "
                SELECT 
                    delega_accesso_modulo.ID
                FROM
                    delega_accesso_modulo
                WHERE
                    delega_accesso_modulo.ID_delega_accesso = " . $db->toSql($this->id) . "
                    AND delega_accesso_modulo.ID_modulo = " . $db->toSql($id_modulo)
            ;
            $db->query($sql);        
            if (!$db->nextRecord()) {
                $sql2 = "INSERT INTO
                            delega_accesso_modulo 
                            (ID_delega_accesso,ID_modulo)
                        VALUES
                            (".$db->toSql($this->id).", ".$db->toSql($id_modulo).")
                        ";
                $db2->execute($sql2);  
            }
        }                
    }
    
    public function delete () {
        $this->deleteRelations();
        $db = ffDb_Sql::factory();            
        $sql = "DELETE FROM ".self::$tablename." WHERE ID = " . $this->id;
        $db->execute($sql);
    }
    
    //eliminazione delle relazioni
    public function deleteRelations() {
        $db = ffDb_Sql::factory();
        foreach ($this->getModuliDelega() as $modulo_delega) {                       
            $sql = "DELETE FROM delega_accesso_modulo WHERE
                    ID_modulo = " . $modulo_delega->id .
                    " AND ID_delega_accesso = " .$this->id;            
            $db->execute($sql);                    
        }
    }
}