<?php

namespace PHPEmul;

require_once "SymbolicVariable.php";

use AnimateDead\Utils;
use PhpParser\Node;
trait EmulatorVariables
{
	/**
	 * Symbol table of the current scope (all variables)
	 * @var array
	 */
	public $variables=[]; 
	/**
	 * Function used to return something when reference returning 
	 * functions fail and have to return something.
	 * Can set an input variable to null for ease too.
	 * @var null
	 */
	protected function &null_reference(&$var=null)
	{
		$var=null;
		// unset($this->null_reference);
		$this->null_reference=null;
		return $this->null_reference;

	}
	/**
	 * Set a value to a variable
	 * creates if not exists
	 * @param  Node $node  
	 * @param  mixed $value 
	 * @return mixed or null        
	 */
	function variable_set($node,$value=null)
	{
		$r=&$this->symbol_table($node,$key,true);
		// $this->verbose(strcolor($this->get_variableـname($node).PHP_EOL, "light green"));
		if ($key!==null) {
		    if ($r instanceof SymbolicVariable || $key instanceof SymbolicVariable) {
		        return new SymbolicVariable();
            }
		    else {
		        // if (Utils::breakpoint($this, 'common.inc.php')) {
		        //     $a = 1;
                // }
		        // if ($value instanceof SymbolicVariable) {
		        //     if (!in_array($key, $this->symbolic_variables)) {
                //         $this->verbose(sprintf('Setting symbolic var for %s (%s:%d)'.PHP_EOL,$key, $this->current_file, $this->current_line));
                //         $this->symbolic_variables[] = $key;
                //     }
                // }
		        // else {
                //     if (($array_index = array_search($key, $this->symbolic_variables)) !== false) {
                //         $this->verbose(sprintf('Removing symbolic var for %s (%s:%d)'.PHP_EOL,$key, $this->current_file, $this->current_line));
                //         unset($this->symbolic_variables[$array_index]);
                //     }
                // }
                return $r[$key]=$value;
            }
        }
		else 
			return null;
	}
	function variable_set_byref($node,&$ref)
	{
		$r=&$this->symbol_table($node,$key,true);
		if ($key!==null)
			return $r[$key]=&$ref;
		else 
			return null;
	}
	/**
	 * Get the value of a variable
	 * @param  Node $node 
	 * @return mixed       
	 */
	function variable_get($node)
    {
		$r=&$this->symbol_table($node,$key,false);
		if ($key!==null) {
            if (is_string($r))
                return $r[$key];
            elseif (is_null($r)) //any access on null is null [https://bugs.php.net/bug.php?id=72786]
                return null;
            elseif (is_array($r)) //support for iterable objects
            {
                // If the key is a symbol, return a symbol
                if ($key instanceof SymbolicVariable) {
                    return new SymbolicVariable(sprintf('%s[%s]', $this->get_variableـname($node->var), $key));
                }
                elseif (!array_key_exists($key, $r)) //only works for arrays, not strings
                {
                    // If the Array itself is symbolic return a symbol
                    if (in_array($this->get_variableـname($node), $this->symbolic_parameters)) {
                        return new SymbolicVariable(sprintf('%s[%s]', $this->get_variableـname($node->var), $key));
                    }
                    else {
                        $this->notice("Undefined index: {$key}");
                        return null;
                    }
                }
                elseif (in_array(strval($key), $this->symbolic_parameters)) {
                    return new SymbolicVariable($key);
                }
                return $r[$key];
            }
            elseif ($r instanceof SymbolicVariable) {
                return $r;
            }
            // elseif (is_object($r))
            // {
            // 	if ($r instanceof ArrayAccess)
            // 		return $r[$key];
            // 	else
            // 	{
            // 		if ($r instanceof EmulatorObject)
            // 			$type=$r->classname;
            // 		else
            // 			$type=get_class($r);
            // 		$this->error("Cannot use object of type {$type} as array");
            // 		return null;
            // 	}
            // }
            else
            {
                $this->warning("Using unknown type as array");
                return $r[$key];
            }
        }
		else {
		    $node_name = $this->get_variableـname($node);
		    if (in_array($node_name, $this->symbolic_parameters)) {
                return new SymbolicVariable($node_name);
            }
            return null;
        }
	}
	function get_variableـname($node) {
	    if ($node instanceof Node\Expr\ArrayDimFetch) {
	        return $this->get_variableـname($node->var);
        }
	    else {
	        return $node->name;
        }
    }
	/**
	 * Check whether or not a variable exists
	 * @param  Node $node 
	 * @return bool       
	 */
	function variable_isset($node)
	{
		$this->error_silence();
		$r=$this->symbol_table($node,$key,false);
		$this->error_restore();
		if ($r instanceof SymbolicVariable) {
            // $this->verbose('Variable isset -> '.print_r($r, true).PHP_EOL);
		    return $r;
        }
		elseif($key instanceof SymbolicVariable) {
		    // $this->verbose('Variable isset -> Symbolic'.PHP_EOL);
		    return new SymbolicVariable($this->name($node).'[SymbolicVariable]');
        }
		elseif (isset($r[$key]) && $r[$key] instanceof SymbolicVariable) {
            // $this->verbose('Variable isset -> '.print_r($r[$key], true).PHP_EOL);
		    return $r[$key];
        }
		else {
            // $this->verbose('Variable isset -> '.$key!==null and isset($r[$key]) ? 'true' : 'false'.PHP_EOL);
            return $key!==null and isset($r[$key]);
        }
	}
	/**
	 * Deletes a variable
	 * @param  Node $node 
	 */
	function variable_unset($node)
	{
		$base=&$this->symbol_table($node,$key,false);
		if ($key!==null && !$base instanceof SymbolicVariable && !$key instanceof SymbolicVariable) {
		    // We ignore unset on Symbolic Variables
            unset($base[$key]);
        }
	}
	/**
	 * Returns reference to a variable
	 * minimize uses of this, as it's very hard to behave properly when a variable does not exist
	 * @param  Node $node 
	 * @return reference
	 */
	function &variable_reference($node,&$success=null)
	{
		$r=&$this->symbol_table($node,$key,false); //this should NOT always create, e.g. static property fetch
		//in fact it should never create, anywhere its needed, it is explicitly created by variable_set
		if ($key===null) //not found or GLOBALS
		{
			$success=false;
			return $this->null_reference();
		}
		elseif (is_array($r))
		{
			$success=true;	
			return $r[$key]; //if $r[$key] does not exist, will be created in byref use.
		}
		elseif ($r instanceof SymbolicVariable) {
            $success=true;
            return $r;
        }
		else
		{
			$success=false;	
			$this->error(sprintf("Could not retrieve reference (%s)", $r),$node);
		}
	}

}