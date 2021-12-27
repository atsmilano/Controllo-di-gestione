<?php
class Entity {
    protected static $tablename;
    //array di gestione delle corrispondenze fra tipologie del db e applicative
    private static $db_types = array(
        /* numerici */
        array("id_db_type" => 16, "db_type" => "BIT", "app_type" => "Number"),
        array("id_db_type" => 1, "db_type" => "TINYINT", "app_type" => "Number"),
        array("id_db_type" => 2, "db_type" => "SMALLINT", "app_type" => "Number"),
        array("id_db_type" => 9, "db_type" => "MEDIUMINT", "app_type" => "Number"),
        array("id_db_type" => 3, "db_type" => "INTEGER", "app_type" => "Number"),
        array("id_db_type" => 8, "db_type" => "BIGINT", "app_type" => "Number"),
        array("id_db_type" => 8, "db_type" => "SERIAL", "app_type" => "Number"),
        array("id_db_type" => 4, "db_type" => "FLOAT", "app_type" => "Number"),
        array("id_db_type" => 5, "db_type" => "DOUBLE", "app_type" => "Number"),
        array("id_db_type" => 246, "db_type" => "DECIMAL", "app_type" => "Number"),
        array("id_db_type" => 246, "db_type" => "NUMERIC", "app_type" => "Number"),
        array("id_db_type" => 246, "db_type" => "FIXED", "app_type" => "Number"),
        /* date */
        array("id_db_type" => 10, "db_type" => "DATE", "app_type" => "Date"),
        array("id_db_type" => 12, "db_type" => "DATETIME", "app_type" => "Date"),
        array("id_db_type" => 7, "db_type" => "TIMESTAMP", "app_type" => "Number"),
        array("id_db_type" => 11, "db_type" => "TIME", "app_type" => "Number"),
        array("id_db_type" => 13, "db_type" => "YEAR", "app_type" => "Number"),
        /* stringhe e binari */
        array("id_db_type" => 254, "db_type" => "CHAR", "app_type" => "Text"),
        array("id_db_type" => 253, "db_type" => "VARCHAR", "app_type" => "Text"),
        array("id_db_type" => 254, "db_type" => "ENUM", "app_type" => "Text"),
        array("id_db_type" => 254, "db_type" => "SET", "app_type" => "Text"),
        array("id_db_type" => 254, "db_type" => "BINARY", "app_type" => "Text"),
        array("id_db_type" => 253, "db_type" => "VARBINARY", "app_type" => "Text"),
        array("id_db_type" => 252, "db_type" => "TINYBLOB", "app_type" => "Text"),
        array("id_db_type" => 252, "db_type" => "BLOB", "app_type" => "Text"),
        array("id_db_type" => 252, "db_type" => "MEDIUMBLOB", "app_type" => "Text"),
        array("id_db_type" => 252, "db_type" => "TINYTEXT", "app_type" => "Text"),
        array("id_db_type" => 252, "db_type" => "TEXT", "app_type" => "Text"),
        array("id_db_type" => 252, "db_type" => "MEDIUMTEXT", "app_type" => "Text"),
        array("id_db_type" => 252, "db_type" => "LONGTEXT", "app_type" => "Text"),
        /* booleani */
        array("id_db_type" => 1, "db_type" => "BOOL", "app_type" => "Boolean"),
    );

    //costruttore
    public function __construct($id = null) {
        if ($id !== null) {
            $calling_class = static::class;

            //******************************************************************
            //Il seguente codice sostituisce la soluzione originale
            //$record = $calling_class::getAll(array("ID" => $id));
            //Si introduce la ridondanza per evitare errori nell'estensione della classe in caso overriding delle classi getAll
            //che generalmente richiamano la getAll di Entity valorizzando l'array dei filtri o degli ordinamenti
            //In questi casi l'utilizzo del costruttore non risulta funzionare correttamente
                                    
            $db = ffDB_Sql::factory();            
            $sql = "SELECT * FROM ".$calling_class::$tablename." WHERE ID=".$id;
            $db->query($sql);
            if ($db->nextRecord()) {
                do {
                    $record = new $calling_class();
                    foreach ($db->fields as $field) {
                        //viene recuperato il tipo di campo per recuperare il valore corretto                     
                        $found = array_search($field->type, array_column(Entity::$db_types, "id_db_type"));
                        $app_type = Entity::$db_types[$found]["app_type"];
                        //vengono inizializzati i dati in base al tipo di campo                    
                        if ($app_type == "Date") {
                            $record->{strtolower($field->name)} = CoreHelper::getDateValueFromDB($db->getField($field->name, $app_type, true));
                        }
                        //in caso di valori numerici comunque viene restituito null nel caso in cui il campo non sia definito
                        else if ($app_type == "Number") {
                            if ($db->getField($field->name, $app_type, true) == null) {
                                $record->{strtolower($field->name)} = null;
                            } else {
                                $record->{strtolower($field->name)} = $db->getField($field->name, $app_type, true);
                            }
                        } else if ($app_type == "Boolean") {
                            $record->{strtolower($field->name)} = CoreHelper::getBooleanValueFromDB($db->getField($field->name, "Number", true));
                        } else {
                            $record->{strtolower($field->name)} = $db->getField($field->name, $app_type, true);
                        }
                    }
                    $result[] = $record;
                } while ($db->nextRecord());
            }
            //******************************************************************
            if (!count($result) > 0) {
                throw new Exception("Impossibile creare l'oggetto " . $calling_class . " con ID = " . $id);
            } else {
                foreach (get_object_vars($result[0]) as $key => $val) {
                    $this->$key = $val;
                }
            }
        } 
    } 
    
    //getAll
    //where = array("fieldname"=>"value");
    //order = array(array("fieldname"=>nome_campo, "direction"=>ASC/DESC));
    public static function getAll($where = array(), $order = array()) {
        $result = array();

        $db = ffDB_Sql::factory();
        //condizioni where
        $where_sql = "";
        if (count($where) > 0) {
            $where_sql = "WHERE ";
            $first = true;
            foreach ($where as $field => $value) {
                if ($first !== true) {
                    $where_sql .= " AND ";
                } else {
                    $first = false;
                }
                if ($value !== null) {
                    $where_sql .= $field . "=" . $db->toSql($value) . " ";
                } else {
                    $where_sql .= "(" . $field . " is null OR " . $field . " = '')";
                }
            }
        }
        //parametri ordinamento
        $order_sql = "";
        if (count($order) > 0) {
            $order_sql = "ORDER BY ";
            $first = true;
            foreach ($order as $order_rule) {
                if ($first !== true) {
                    $order_sql .= " , ";
                } else {
                    $first = false;
                }
                if ($order_rule == null) {
                    $order_direction = "ASC";
                } else {
                    $order_direction = $order_rule["direction"];
                }
                $order_sql .= $order_rule["fieldname"] . " " . $order_direction;
            }
        }
        $calling_class = static::class;
        $sql = "
					SELECT 
						" . $calling_class::$tablename . ".*
					FROM
						" . $calling_class::$tablename . "					
                    " . $where_sql . "
                    " . $order_sql
        ;
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $record = new $calling_class();
                foreach ($db->fields as $field) {
                    //viene recuperato il tipo di campo per recuperare il valore corretto                     
                    $found = array_search($field->type, array_column(Entity::$db_types, "id_db_type"));
                    $app_type = Entity::$db_types[$found]["app_type"];
                    //vengono inizializzati i dati in base al tipo di campo                    
                    if ($app_type == "Date") {
                        $record->{strtolower($field->name)} = CoreHelper::getDateValueFromDB($db->getField($field->name, $app_type, true));
                    }
                    //in caso di valori numerici comunque viene restituito null nel caso in cui il campo non sia definito
                    else if ($app_type == "Number") {
                        if ($db->getField($field->name, $app_type)->getValue() === null) {
                            $record->{strtolower($field->name)} = null;
                        } else {                            
                            $record->{strtolower($field->name)} = $db->getField($field->name, $app_type)->getValue();
                        }
                    } else if ($app_type == "Boolean") {
                        $record->{strtolower($field->name)} = CoreHelper::getBooleanValueFromDB($db->getField($field->name, "Number", true));
                    } else {
                        $record->{strtolower($field->name)} = $db->getField($field->name, $app_type, true);
                    }
                }
                $result[] = $record;
            } while ($db->nextRecord());
        }
        return $result;
    }
        
    //metodo per la generazione di una matrice dati rappresentante una collezione di oggetti della classe, con intestazione
    //i parametri determinano i filtri con lo stesso funzionamento  di getAll 
    public static function getMatriceDati ($where = array(), $order = array()) {
        $calling_class = static::class;        
        
        $matrice = array();
        $intestazione = array();
        $first = true;
        foreach($calling_class::getAll($where, $order) as $obj) {                            
            $record= array();            
            foreach ($obj as $attributo => $valore){
                if($first == true) {
                    $intestazione[] = $attributo;
                }
                $record[] = $valore;              
            }   
            if ($first == true) {
                $matrice[] = $intestazione;
                $first = false;
            }            
            $matrice[] = $record;    
        }
        return $matrice;
    }
                      
    public static function describe($hide_columns = array(), $table_name=null) {  
        if ($table_name == null) {
            $calling_class = static::class;
            $table_name = $calling_class::$tablename;
        }
        $db = ffDB_Sql::factory();
        //condizioni where
        $where_sql = "";
        if (count($hide_columns) > 0) {
            $where_sql = "WHERE ";
            $first = true;
            foreach ($hide_columns as $field => $values) {
                if ($first !== true) {
                    $where_sql .= " AND ";
                } else {
                    $first = false;
                }
                
                $exclude_field = "";
                foreach ($values as $value) {
                    if (strlen($exclude_field) > 0) {
                        $exclude_field .= ", ";
                    }
                    $exclude_field .= $db->toSql($value);
                }
                $where_sql .= $field . " NOT IN ($exclude_field)";
            }
        }

        $calling_class = static::class;
        $sql = "SHOW FULL COLUMNS FROM ".$table_name." $where_sql";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $record = new stdClass();
                foreach ($db->fields as $field) {
                    //viene recuperato il tipo di campo per recuperare il valore corretto                     
                    $found = array_search($field->type, array_column(Entity::$db_types, "id_db_type"));
                    $app_type = Entity::$db_types[$found]["app_type"];
                    //vengono inizializzati i dati in base al tipo di campo                    
                    if ($app_type == "Date") {
                        $record->{strtolower($field->name)} = CoreHelper::getDateValueFromDB($db->getField($field->name, $app_type, true));
                    }
                    //in caso di valori numerici comunque viene restituito null nel caso in cui il campo non sia definito
                    else if ($app_type == "Number") {
                        if ($db->getField($field->name, $app_type, true) == null) {
                            $record->{strtolower($field->name)} = null;
                        } else {
                            $record->{strtolower($field->name)} = $db->getField($field->name, $app_type, true);
                        }
                    } else if ($app_type == "Boolean") {
                        $record->{strtolower($field->name)} = CoreHelper::getBooleanValueFromDB($db->getField($field->name, "Number", true));
                    } else {
                        $record->{strtolower($field->name)} = $db->getField($field->name, $app_type, true);
                    }
                }
                $result[] = $record;
            } while ($db->nextRecord());
        }
        return $result;
    }
        
    //restituisce un oggetto di una classe differente con gli stessi attributi di quello corrente
    public function cloneAttributesToNewObject ($class){
        try {
            $obj = new $class;
        } catch (Exception $ex) {
            throw new Exception("Impossibile creare l'istanza della classe: " . $class);
        }            
        foreach (get_object_vars($this) as $attribute => $value) {
            $obj->{$attribute} = $value;
        }
        return $obj;
    } 
    
    //inserimento o modifica dei campi del record (array("nomecampo1", "nomecampo2") corrispondente agli attributi dell'oggetto
    public function save($fields_names=array()) {
        $db = ffDb_Sql::factory();
        
        $calling_class = static::class;
        if ($this->id == null) {            
            $insert_values_sql = "";
            foreach ($fields_names as $field_name) {
                if (strlen($insert_sql)>0) {
                    $insert_sql .= ",";
                }
                if (strlen($insert_values_sql)>0) {
                    $insert_values_sql .= ",";
                }
                $insert_sql .= $field_name;
                $insert_values_sql .= $db->toSql($this->{strtolower($field_name)});
            }
            
            // INSERT
            $sql = "INSERT INTO ".$calling_class::$tablename." (
                    ".$insert_sql."
                ) VALUES (
                    ".$insert_values_sql."
                )
            ";            
        }
        else {
            $update_sql = "";
            foreach ($fields_names as $field_name) {                
                if (strlen($update_sql)>0) {
                    $update_sql .= ",";
                }                
                $update_sql .= $field_name."=".$db->toSql($this->{strtolower($field_name)});
            }
            $sql = "
                UPDATE ".static::$tablename."
                SET
                    ".$update_sql."
                WHERE
                    ID = ".$db->toSql($this->id)
                ;           
        }
        if (!$db->execute($sql)) {		
            throw new Exception("Impossibile aggiornare l'oggetto ".static::class." con ID='".$this->id."' nel DB");
	}
        else {
            //restituisce l'id dell'elemento salvato
            if ($this->id == null) {
                return $db->getInsertID(true);
            }
            else {
                return $this->id;
            }
        }
    }
    
    //Eliminazione del record con fieldname = $this->fieldname (default ID) dal db della tabella rappresentante l'oggetto
    public function delete($field_name="ID") {
        $db = ffDb_Sql::factory(); 
              
        $calling_class = static::class;        
        $sql = "DELETE FROM ".$calling_class::$tablename." 
                WHERE ".$field_name." = ".$db->toSql($this->{strtolower($field_name)}); 
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare il record con ID = ".$this->id." dalla tabella ".$calling_class::$tablename);
        }        
    }
    
    //selezione di un record in base al valore dei campi
    //array di selezione "nome_campo"=>valore
    //restituisce il primo record trovato tramite la selezione
    public static function getByFields($filters = array()) {
        $calling_class = static::class;  
        $recordset = $calling_class::getAll($filters);
        if (!empty($recordset)) {
                return $recordset[0];
        }
        return null;
    }
}