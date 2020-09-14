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
            $calling_class_name = static::class;
            $record = $calling_class_name::getAll(array("ID" => $id));
            if (!count($record) > 0) {
                throw new Exception("Impossibile creare l'oggetto " . $calling_class_name . " con ID = " . $id);
            } else {
                foreach (get_object_vars($record[0]) as $key => $val) {
                    $this->$key = $val;
                }
            }
        }        
    }

    //getAll
    //where = array("fieldname"=>"value=);
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
    
    public static function describe($hide_columns = array()) {        
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
        $sql = "SHOW FULL COLUMNS FROM ".$calling_class::$tablename." $where_sql";
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
                    //in caso di valori numerici comunque viene restituito null ne lcaso in cui il campo non sia definito
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
}
