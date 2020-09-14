<?php
/**
 * objects common abstract classes
 *
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * Questa classe astratta implementa i check per i magic methods, in modo da impedire la creazione di proprietà o metodi non precedentemente definiti.
 * 
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffClassChecks
{
 	public function __set ($name, $value)
 	{
 		ffErrorHandler::raise("property \"$name\" not found on class " . get_class($this), E_USER_ERROR, $this, get_defined_vars());
	}

	public function __get ($name)
	{
 		ffErrorHandler::raise("property \"$name\" not found on class " . get_class($this), E_USER_ERROR, $this, get_defined_vars());
	}

	public function __isset ($name)
	{
 		ffErrorHandler::raise("property \"$name\" not found on class " . get_class($this), E_USER_ERROR, $this, get_defined_vars());
	}

	public function __unset ($name)
	{
 		ffErrorHandler::raise("property \"$name\" not found on class "  . get_class($this), E_USER_ERROR, $this, get_defined_vars());
	}
	
	public function __call ($name, $arguments)
	{
 		ffErrorHandler::raise("function \"$name\" not found on class "  . get_class($this), E_USER_ERROR, $this, get_defined_vars());
	}
	/*
	public static function __callStatic ($name, $arguments)
	{
 		ffErrorHandler::raise("function \"$name\" not found on class "  . get_class($this), E_USER_ERROR, $this, get_defined_vars());
	}*/
}

/**
 * Questa classe astratta implementa le funzionalità di base di ogni classe del framework.
 * Nel dettaglio:
 *  - la possibilità di ottenere i settaggi di default tramite il metodo get_defaults()
 *  - la variabile "user_vars" per isolare le variabili definite dall'utente (replica la funzionalità delle magic vars)
 *  - la gestione degli eventi
 *
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffCommon extends ffClassChecks
{
	/**
	 * Questa variabile permette di simulare l'utilizzo delle magic vars, a discrezione del programmatore.
	 *
	 * @var user_vars
	 */
	var $user_vars 		= array();

	/**
	 * Questa variabile memorizza la coda standard degli eventi, utilizzata dalle funzioni di gestione medesime.
	 *
	 * @var ffEvents
	 */
	var $events 		= array();
	
	/**
	 * Questa funzione permette di recuperare i settaggi di default per una classe dall'array globale $ff_global_setting
	 * viene automaticamente richiamato nel costruttore di ogni classe del framework.
	 * 
	 * @global mixed $ff_global_setting
	 * @param string $name il nome della classe. se omesso è la classe corrente
	 */
	function get_defaults($name = null)
	{
		global $ff_global_setting;

		if ($name === null)
			$name = get_class($this);
		
		if (isset($ff_global_setting[$name]) && is_array($ff_global_setting[$name]) && count($ff_global_setting[$name]))
		{
            $this->get_defaults_walkarray($this, $ff_global_setting[$name]);
		}
	}

    public function getAbsPath($path = null)
    {
        return ($this::TOP_DIR != $this::PRJ_DIR
            && $path
            && (strpos($path, "/themes/library") === 0
                || strpos($path, "/themes/restricted") === 0
                || strpos($path, "/themes/responsive") === 0
                || strpos($path, "/modules") === 0
            )
                ? $this::TOP_DIR
                : $this::DISK_PATH
            ) . $path;
    }

	private function get_defaults_walkarray(&$node, &$array)
	{
		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				if (is_array($node))
				{
					if (!isset($node[$key]))
						$node[$key] = array();
					$this->get_defaults_walkarray($node[$key], $value);
				}
				elseif (is_object($node))
				{
					if (!isset($node->$key))
						$node->$key = array();
                    $this->get_defaults_walkarray($node->$key, $value);
				}
				else
					ffErrorHandler::raise("Mismatch variable type (try to init as null)", E_USER_ERROR, null, get_defined_vars());
			}
			else
			{
				if (is_array($node))
				{
					$node[$key] = $value;
				}
				elseif (is_object($node))
				{
					$node->$key = $value;
				}
				else
					ffErrorHandler::raise("Mismatch variable type (try to init as null)", E_USER_ERROR, null, get_defined_vars());
			}
		}
		reset($array);
	}
	
	/**
	 * Questa funzione permette di settare ricorsivamente i default di una classe basandosi su un array
	 * che ne replica i membri sotto forma di chiave => valore
	 * 
	 * @param Array $defaults l'array di parametri di defaults
	 */
	/*function set_defaults($defaults)
	{
		if (!is_array($defaults) || !count($defaults))
			return;
		
		$name = get_class($this);
		foreach ($defaults as $key => $value)
		{
			if (property_exists($name, $key))
			{
				$this->$key = $value;
			}
		}
		reset ($defaults);
	}*/
	
	/*function __toString()
	{
		return spl_object_hash($this);
	}*/
	
	/**
	 * Questa funzione permette di aggiungere un evento alla coda dei medesimi.
	 * 
	 * @param string $event_name il nome dell'evento
	 * @param string $func_name il nome della funzione da richiamare
	 * @param int $priority la priorità dell'evento. Può essere un qualsiasi valore di ffEvent::PRIORITY_*
	 * @param int $index la posizione dell'evento rispetto ad eventi della stessa priorità
	 * @param int $break_when se il processing della coda dev'essere interrotto sulla base di $break_value. Può essere un qualsiasi valore di ffEvent::BREAK_*
	 * @param mixed $break_value il valore da utilizzare in coppia con $break_when
	 * @param mixed $additional_data eventuali dati addizionali da passare insieme all'evento
	 */
	public function addEvent($event_name, $func_name, $priority = null, $index = 0, $break_when = null, $break_value = null, $additional_data = null)
	{
		if (is_array($func_name))
		{
			$data = $func_name;
			$func_name			= $data["func_name"];
			$priority			= $data["priority"];
			$index				= $data["index"] === null ? 0 : $data["index"];
			$break_when			= $data["break_when"];
			$break_value		= $data["break_value"];
			$additional_data	= $data["additional_data"];
		}
		
		if ($priority === null)
		{
			if (isset($this->events[$event_name]["defaults"]))
			{
				$priority = $this->events[$event_name]["defaults"]["priority"];
			}
			else
				$priority = ffEvent::PRIORITY_DEFAULT;
		}

		if ($break_when === null)
		{
			if (isset($this->events[$event_name]["defaults"]))
			{
				$break_when = $this->events[$event_name]["defaults"]["break_when"];
			}
		}

		if ($break_when !== null && $break_value === null)
		{
			if (isset($this->events[$event_name]["defaults"]))
			{
				$break_value = $this->events[$event_name]["defaults"]["break_value"];
			}
		}
		
		if ($index === null)
			$index = 0;

		$event = new ffEvent($func_name, $break_when, $break_value, $additional_data);
		
		switch ($priority)
		{
			case ffEvent::PRIORITY_TOPLEVEL:
				if (isset($this->events[$event_name]["toplevel"]))
					ffErrorHandler::raise("A toplevel event already exists", E_USER_ERROR, $this, get_defined_vars());
				
				$this->events[$event_name]["toplevel"] = $event;
				break;
				
			case ffEvent::PRIORITY_FINAL:
				if (isset($this->events[$event_name]["final"]))
					ffErrorHandler::raise("A final event already exists", E_USER_ERROR, $this, get_defined_vars());
				
				$this->events[$event_name]["final"] = $event;
				break;
				
			default:
			    $counter = (is_array($this->events[$event_name]["queues"][$priority])
                            ? count($this->events[$event_name]["queues"][$priority])
                            : 0
                        );
				$this->events[$event_name]["queues"][$priority][] = array("index" => $index, "counter" => $counter, "event" => $event);
				break;
		}
		
		return $this;
	}

	/**
	 * Questa funzione esegue tutte le code per l'evento selezionato.
	 *
	 * @param string $event_name il nome dell'evento da eseguire
	 * @param mixed $event_params i parametri dell'evento passati all'interno di un array. Il numero e il tipo di parametri dipendono dall'evento.
	 * @return $mixed un array contenente i risultati di ogni funzione eseguita
	 */
	public function doEvent($event_name, $event_params = array())
	{
		$results = array(null);
		if (defined("FF_EVENTS_STOP"))
			return $results;

		if (isset($this->events[$event_name]))
		{
			if (isset($this->events[$event_name]["toplevel"]))
			{
				$event = $this->events[$event_name]["toplevel"];
				if (is_array($event->additional_data))
					$calling_params = array_merge($event_params, $event->additional_data);
				else
					$calling_params = $event_params;
				if (is_string($event->func_name))
					$event_key = $event->func_name;
				else
					$event_key = "__toplevel__";
				$results[$event_key] = call_user_func_array($event->func_name, $calling_params);
				
				if ($event->checkBreak($results[$event_key]))
					return $results;
			}

			if (isset($this->events[$event_name]["queues"]) && is_array($this->events[$event_name]["queues"]) && count($this->events[$event_name]["queues"]))
			{
				ksort($this->events[$event_name]["queues"], SORT_NUMERIC);
				foreach ($this->events[$event_name]["queues"] as $key => $value)
				{
					if (is_array($value) && count($value))
					{
						usort($value, "ffCommon_IndexReverseOrder");
		
						foreach ($value as $subkey => $subvalue)
						{
							if (is_array($subvalue["event"]->additional_data))
								$calling_params = array_merge($event_params, $subvalue["event"]->additional_data);
							else
								$calling_params = $event_params;
							$calling_params["__last_result__"] = end($results);
							if (is_string($subvalue["event"]->func_name))
								$event_key = $subvalue["event"]->func_name;
							else
								$event_key = $key;
							
							if (!is_callable($subvalue["event"]->func_name))
								ffErrorHandler::raise ("Wrong Event Params", E_USER_ERROR, $this, get_defined_vars ());
							
							$results[$event_key] = call_user_func_array($subvalue["event"]->func_name, $calling_params);
							
							if($subvalue["event"]->checkBreak($results[$event_key]))
								return $results;
						}
						reset($value);
					}
				}
				reset($this->events[$event_name]["queues"]);
			}

			if (isset($this->events[$event_name]["final"]))
			{
				$event = $this->events[$event_name]["final"];
				if (is_array($event->additional_data))
					$calling_params = array_merge($event_params, $event->additional_data);
				else
					$calling_params = $event_params;
				if (is_string($event->func_name))
					$event_key = $event->func_name;
				else
					$event_key = "__final__";
				$calling_params["__last_result__"] = end($results);
				$results[$event_key] = call_user_func_array($event->func_name, $calling_params);
			}
		}
		
		return $results;
	}
}