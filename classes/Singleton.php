<?php
class Singleton {    
    public static function Instance() {    
        $class_name = static::class;
        static $inst = null;
        
        if ($inst === null) {
            $inst = new $class_name;                       
        }        
        return $inst;                
    }
}