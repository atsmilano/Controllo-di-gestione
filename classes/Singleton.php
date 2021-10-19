<?php
class Singleton {    
    public static function getInstance() {   
        $class = static::class;
        if ($class::$instance == null){
            $class::$instance = new $class(...func_get_args());
        }
        return $class::$instance; 
    }
}