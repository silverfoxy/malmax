<?php
use PhpParser\Node;
$_GET[1]="input";
$_GET['log']="input"; //for wordpress test
$_GET['pwd']="input"; //for wordpress test
require_once __DIR__."/../analyzer.php";
/**
 * There's a problem with expressions and taint
 * taint propagation needs access to both the evaluated expression and its shadow taint values
 * but evaluation is isolated, can not hook inside it at every step
 *
 *
 * What I need to do is to have a stack of taint values corresponding with expressions
 * that are evaluated, i.e. each evaluated expression must have an evaluated taint as well.
 * These taints need to be stacked, and pushed and popped with beginning and ending of evaluate expression
 * so that other components can use them
 *
 * It would be much easier if evaluate expression didn't have its own stack state (internally, function call stack).
 * 
 * @return [type] [description]
 */
function taint_stat(){}
function taint_statz($emul)
{
	print_R($emul->taint);
	print_r($emul->variable_stack['global']['_GET']);
}
class TaintAnalyzer extends PHPAnalyzer
{
	public $taint=[];
	public $taint_stack=[];
	public $return_taint_value=null;
	public $sinks="mysqli_query,exec,system,shell_exec,mysql_query,passthru,mail,curl_init,proc_open,glob,getenv,
	,call_user_func_array,call_user_func,ini_get,ini_set,ini_alter,set_cookie,header,fopen,mkdir,file_put_contents,
	,readdir,dl";
	public $taint_functions;
	public $threshold=.8;

	function init($init_environ)
	{
		parent::init($init_environ);

		#DEFINING SOURCES: 
		$this->sinks=explode(",",$this->sinks);
		ob_start();
		include "function-taint.txt";
		$function_taint=ob_get_clean();

		$this->taint_functions=array_reduce(explode("\n",$function_taint), function($carry,$item){
			$t=explode(" ",trim($item));
			$t=array_filter($t,function($x){return trim($x)!=="";});
			if (empty($t)) return $carry;
			$t=array_values($t);
			if (count($t)!==2 or !is_numeric($t[1])) return $carry;
			// var_dump($t);
			$carry[strtolower(trim($t[0]))]=trim($t[1]);
			return $carry;
		});
		// print_r($this->taint_functions);
		if ($init_environ===null)
		{
			$init_environ=[];	
			foreach ($this->superglobals as $k=>$sg)
			{

				if (in_array($k, ['_GET','_REQUEST','_POST','_COOKIE']))
					$taint=1;
				else
					$taint=0;
				if (isset($GLOBALS[$k]))	
					foreach ($GLOBALS[$k] as $k2=>$v2)
						if ($k=="_SERVER" and strpos($k2, "HTTP_")===0) 
							$init_environ[$k][$k2]=1;
						else
							$init_environ[$k][$k2]=$taint;
				else
					$init_environ[$k]=[];
			}
			$init_environ['GLOBALS']=&$init_environ;
		}
		$this->taint_stack['global']=array(); //the first key in var_stack is the global scope
		$this->reference_taint_to_stack();
		foreach ($init_environ as $k=>$v)
			$this->taint[$k]=$v;
		$this->taint['GLOBALS']=&$this->taint;

		$this->mock_functions['taint_stat']="taint_statz";
	}
	protected function push()
	{
		parent::push();
		array_push($this->taint_stack,array()); //create one more symbol table
		$this->reference_taint_to_stack();
	}
	private function reference_taint_to_stack()
	{
		unset($this->taint);
		end($this->taint_stack);
		$this->taint=&$this->taint_stack[key($this->taint_stack)];
	}
	protected function pop()
	{
		parent::pop();
		array_pop($this->taint_stack);
		$this->reference_taint_to_stack();
	}
	function sink($name,$args,$number=null)
	{
		if ($number!==null)
			$percent=sprintf("%.1f%%",$number*100);
		else
			$percent="unknown";
		$this->verbose(strcolor("Taint has reached sink '{$name}' at {$this->current_file}:{$this->current_line} with {$percent} potency\n","purple"),2);	
		echo "Active conditions: \n";
		print_r($this->active_conditions);
	}
	function evaluate_args_taint($args)
	{
		$argTaints=[];
		foreach ($args as &$arg)
			if (!$arg instanceof Node\Arg)
				$this->error("Argument sent to evaluate_args is not Node\Arg");
				// $argTaints[]=$arg;
			else
				$argTaints[]=$this->evaluate_taint($arg->value);
		return $argTaints;
	}
	function propagate_taint($taint,$value)
	{
		if (is_object($taint))
		{
			if ($taint instanceof EmulatorObject)
			{
				foreach ($taint->taint as $k=>$v)
					$taint->taint[$k]=$this->propagate_taint($v,$value);
			}
			else
				if (is_array($taint->__taint))
				foreach ($taint->__taint as $k=>$v)
					$taint->__taint[$k]=$this->propagate_taint($v,$value);
			return $taint;
		}
		elseif (is_array($taint))
		{
			foreach ($taint as $k=>$v)
				$taint[$k]=$this->propagate_taint($v,$value);
			return $taint;
		}
		return $value;
	}
	function max_taint($taint)
	{
		if (is_null($taint))
			return 0;
		if (is_numeric($taint))
			return $taint;
		if (is_array($taint))
		{
			foreach ($taint as $k=>$v)
				$taint[$k]=$this->max_taint($v);
			if (!empty($taint))
				return max($taint);
			else
				return 0;
		}
		// if (is_object($taint))
			$this->error("dang");
	}
	function core_function_taint($name,$args,$res)
	{
		$argsTaint=$this->evaluate_args_taint($args);
		$argCount=count($argsTaint);
		if ($argCount)
			$max=$this->max_taint($argsTaint);
		else
			$max=0;
		// var_dump($args);
		// var_dump($argsTaint);
		$tainted=$max>=$this->threshold;
		if (in_array($name, $this->sinks) and $tainted)
			$this->sink($name,$args,$max);
		if (isset($this->taint_functions[strtolower($name)]))
		{

			return $this->propagate_taint($res,$max*$this->taint_functions[strtolower($name)]);
			// return $max*$this->taint_functions[strtolower($name)];
		}
		#TODO: some functions require custom logic,
		#specially those that work with arrays, because taint has to be 
		#retained in arrays and can not be converted to numbers.
		#For example, array_pop should return taint of corresponding value
		// if (strpos($name,"str_")===0)
		// 	return $max;
		return 0; //clear out for the rest
	}
	public function call_function_taint($name,$args,$res)
	{
		if ($name instanceof EmulatorClosure)
		{
			return 0; //for now, closures erase the taint #FIXME
		}
		elseif (function_exists($name)) //global core function
			return $this->core_function_taint($name,$args,$res);
		else
			return $this->return_taint_value;
		// return 0;
	}
	public function method_taint($res)
	{
		return $this->return_taint_value;
		// return 1;
	}
	function evaluate_expression($node)
	{
		$this->current_taint=0;
		if (0);
		elseif ($node instanceof Node\Expr\New_)
		{
			$classname=$this->namespaced_name($node->class);
			$obj=$this->new_object($classname,$node->args); 
			$obj->taint=[];
			return $obj;
		}
		elseif ($node instanceof Node\Expr\FuncCall)
		{
			$name=$this->name($node);
			// print_r($this->taint);
			$res=$this->call_function($name,$node->args);
			// print_r($this->taint);
			$this->current_taint=$this->call_function_taint($name,$node->args,$res);
			return $res;
			
		}
		elseif ($node instanceof Node\Expr\MethodCall)
		{
			$res=parent::evaluate_expression($node);
			$this->current_taint=$this->method_taint($res);
			return $res;
		}
		elseif ($node instanceof Node\Expr\StaticCall)
		{
			$res=parent::evaluate_expression($node);
			$this->current_taint=$this->method_taint($res);
			return $res;
		}
		elseif ($node instanceof Node\Expr\AssignRef)
		{
			if (!$this->variable_isset($node->expr)) //referencing creates
			{
				$this->variable_set($node->expr);
				$this->taint_set($node->expr,0);
			}
			$originalVar=&$this->variable_reference($node->expr,$success);
			$originalTaint=&$this->taint_reference($node->expr);
			if ($success)
			{
				$this->variable_set_byref($node->var,$originalVar);
				$this->taint_set_byref($node->var,$originalTanit);
			}
			else
				$this->warning("Can not assign by reference, the referenced variable does not exist");
			return $originalVar;
		}
		elseif ($node instanceof Node\Expr\Assign)
		{
			if ($node->var instanceof Node\Expr\List_) //list(x,y)=f()
			{
				$resArray=$this->evaluate_expression($node->expr);
				// 	PHP's list uses numeric iteration over the resArray. 
				// 	This means that list($a,$b)=[1,'a'=>'b'] will error "Undefined offset: 1"
				// 	because it wants to assign $resArray[1] to $b. Thus we use index here.
				$index=0;
				$outArray=[];
				foreach ($node->var->vars as $var)
				{
					if (!isset($resArray[$index]))
						$this->notice("Undefined offset: {$index}");
					if ($var===null)
						$outArray[]=$resArray[$index++];
					else
					{

						$outArray[]=$this->variable_set($var,$resArray[$index++]);
						$this->taint_set($var,$this->evaluate_taint($node->expr));
					}
				}
				while ( $index<count($resArray))
				{
					if (!isset($resArray[$index]))
						$this->notice("Undefined offset: {$index}");
					$outArray[]=$resArray[$index++];
				}
				return $outArray;
			}
			else
			{
				$res=$this->variable_set($node->var,$this->evaluate_expression($node->expr));
				$this->taint_set($node->var,$this->evaluate_taint($node->expr));
				return $res;	
				// var_dump($this->evaluate_taint($node->expr))
			}
		}
		elseif ($node instanceof Node\Expr\AssignOp) //important for taint
		{
			$varT=&$this->taint_reference($node->var); 
			$val=$this->evaluate_taint($node->expr);
			if ($node instanceof Node\Expr\AssignOp\Concat)
				$varT=max($val,$varT);
		}
		return parent::evaluate_expression($node);
	}
	public $current_taint=0;
	function evaluate_taint($node,&$result=null)
	{
		if (0);
		elseif ($node instanceof Node\Expr\FuncCall)
			return $this->current_taint;
		elseif ($node instanceof Node\Expr\MethodCall)
			return $this->current_taint;
		elseif ($node instanceof Node\Expr\StaticCall)
			return $this->current_taint;
		elseif ($node instanceof Node\Expr\PropertyFetch)
		{
			$var=&$this->taint_reference($node);
			return $var;
		}
		elseif ($node instanceof Node\Expr\StaticPropertyFetch)
		{
			$var=&$this->taint_reference($node); 
			return $var;
		}
		elseif ($node instanceof Node\Expr\Clone_)
		{
			$var=$this->taint_get($node->expr);
			return $var;
		}
		elseif ($node instanceof Node\Expr\AssignRef)
			return $this->current_taint;
		elseif ($node instanceof Node\Expr\Assign)
			return $this->current_taint;
		elseif ($node instanceof Node\Expr\ArrayDimFetch) //access multidimensional arrays $x[...][..][...]
			return $this->taint_get($node); //should not create
		elseif ($node instanceof Node\Expr\Array_)
		{
			#FIXME: array elements might be expression
			$out=[];
			foreach ($node->items as $item)
			{
				if (isset($item->key))
				{
					$key=$this->evaluate_expression($item->key);
					if ($item->byRef)
						$out[$key]=&$this->taint_reference($item->value);
					else
						$out[$key]=$this->evaluate_taint($item->value);
				}
				else
					if ($item->byRef)
						$out[]=&$this->taint_reference($item->value);
					else
						$out[]=$this->evaluate_taint($item->value);
			}
			return $out; #FIXME: return an array
		}
		elseif ($node instanceof Node\Expr\BinaryOp)
		{
			
			$l=$this->evaluate_taint($node->left); #called on two string or variables
			$r=$this->evaluate_taint($node->right);
			if ($node instanceof Node\Expr\BinaryOp\Concat)
				return max($l,$r);
		}
		elseif ($node instanceof Node\Expr\Variable)
			return $this->taint_get($node); //should not be created on access
		elseif ($node instanceof Node\Expr\ShellExec)
		{
				$res=0;
				foreach ($node->parts as $part)	
					if (!is_string($part))
						$res=max($res,$this->evaluate_taint($part));
				return $res;
		}
		return 0;
	}
	function run_statement($node)
	{
		if ($node instanceof Node\Stmt\Return_)
		{
			if ($node->expr)
				$this->return_taint_value=$this->evaluate_taint($node->expr);
			else
				$this->return_taint_value=null;
		}
		return parent::run_statement($node);
	}
	/**
	 * Returns the correspnding taint entry
	 * @param  [type] $node   [description]
	 * @param  [type] &$key   [description]
	 * @param  [type] $create [description]
	 * @return [type]         [description]
	 */
	function &taint_table($node,&$key,$create)
	{
		if ($node===null)
		{
			// $this->notice("Undefined variable (null node).");	
			return $this->null_reference($key);
		}
		elseif (is_string($node))
		{
			if (array_key_exists($node, $this->taint))
			{
				$key=$node;	
				return $this->taint;
			}
			elseif ($node == "GLOBALS")
			{
				$key='global';	
				return $this->taint_stack;
			}
			elseif (array_key_exists($node, $this->superglobals) //super globals
				and isset($this->taint_stack['global'][$node])) //can be deleted too!
			{
				$key=$node;
				if ($key=="_GET" or $key=="_POST")
					$this->verbose("Tainted source accessed!\n",3);
				return $this->taint_stack['global'];
			}
			else
			{
				if ($create)
				{
					$key=$node;
					$this->taint[$key]=0;
					return $this->taint;
				}
				else
				{
					// $this->notice("Undefined variable: {$node}");	
					return $this->null_reference($key);
				}
			}
		}
		elseif ($node instanceof Node\Scalar\Encapsed)
		{
			$key=$this->name($node);
			if ($create)
				$this->taint[$key]=0;
			return $this->taint;
		}
		elseif ($node instanceof Node\Expr\ArrayDimFetch)
		{
			$t=$node;

			//each ArrayDimFetch has a var and a dim. var can either be a variable, or another ArrayDimFetch
			$dim=0;
			$indexes=[];
			
			//extracting indices
			while ($t instanceof Node\Expr\ArrayDimFetch)
			{
				$dim++;
				if ($t->dim) //explicit dimension
				{
					$ev=$this->evaluate_expression($t->dim);
					//DISCREPENCY: a literal null index evaluates to empty string rather than
					//	a null dim which means a[]=2;
					if ($ev===null) 
						$ev="";
					$indexes[]=$ev; 
				}
				else //implicit dimension
					$indexes[]=null;
				$t=$t->var;
			}
			$indexes=array_reverse($indexes);
			//check if the array exists at all or not
			$base=&$this->taint_table($t,$key2,$create);
			if ($key2===null) //the base arrayDimFetch variable not exists, e.g $a in $a[1][2]
				return $this->null_reference($key);
			$base=&$base[$key2];
			$key=array_pop($indexes);
			if (is_string($base) and empty($indexes) and is_int($key)) //string arraydimfetch access
				return $base; //already done
			
			if (is_scalar($base)) //arraydimfetch on scalar returns null 
				return $this->null_reference($key);

			if (is_object($base) and !$base instanceof ArrayAccess)			
			{
				return $this->null_reference($key);
			}
			foreach ($indexes as $index)
			{
				if ($index===NULL)
				{
					$base[]=0; //append to base array
					end($base);	//move the pointer to end of base array
					$index=key($base); //retrieve the key as index
				}
				elseif (!isset($base[$index]))
					if ($create)
					{
						// $this->verbose("Creating array index '{$index}'...".PHP_EOL,5);	
						$base[$index]=0;
					}
					else
						return $this->null_reference($key);

				$base=&$base[$index];
			}
			if ($create) 
				if ($key===null) #$a[...][...][]=x //add mode
				{
					$base[]=0;
					end($base);
					$key=key($base);
				}
				elseif (!isset($base[$key])) //non-existent index
					$base[$key]=0;
			return $base;
		}
		elseif ($node instanceof Node\Expr\Variable and is_string($node->name) and $node->name=="this") //$this
		{
			$key='temp';
			$t=array($key=>&$this->current_this->taint);
			return $t;
		}
		elseif ($node instanceof Node\Expr\PropertyFetch)
		{
			$base=&$this->taint_table($node->var,$key2,$create);
			if ($key2===null)
			{
				// $name=is_string($node->var)?$node->var:"Unknown";
				// $this->notice("Undefined variable: {$name}");	
				return $this->null_reference($key);
			}
			$var=&$base[$key2];
			if ($var instanceof EmulatorObject)
			{
				if (!isset($var->taint))
					$var->taint=[];
				$property_name=$this->name($node->name);
				if (!array_key_exists($property_name, $var->properties) or !isset($var->taint[$property_name]))
				{
					$var->taint[$property_name]=0;
				}
				if (!$this->is_visible($node))
				{
					// $this->notice("Cannot access property: {$var->classname}::\${$property_name}");
					return $this->null_reference($key);
				}					
				$key=$property_name;
				return $var->taint; //reference its value only!
			}
			elseif(is_object($var)) //native object
			{
				$property_name=$this->name($node->name);
				if (!property_exists($var, "__taint"))
					$var->__taint=[];
				if (!isset($var->__taint[$property_name]))
					$var->__taint[$property_name]=0;
				// $this->verbose(sprintf("Fetching object property: %s::$%s\n",get_class($var),$property_name),4);
				$key=$property_name;
				return $var->__taint;
			}
			else 
			{
				// $this->notice("Trying to get property of non-object",$var);
				return $this->null_reference($key);
			}
		}
		elseif ($node instanceof Node\Expr\StaticPropertyFetch)
		{
			$classname=$this->name($node->class);
			if ($classname instanceof EmulatorObject) //support for $object::static_method
				$classname=$classname->classname;
			$classname=$this->real_class($classname);
			$property_name=$this->name($node->name);
			if ($this->ancestry($classname))
			{
				foreach($this->ancestry($classname)  as $class)
				{
					if (!isset($this->classes[strtolower($class)]->static_taint))
						$this->classes[strtolower($class)]->static_taint=[];
					if (array_key_exists($property_name,$this->classes[strtolower($class)]->static_taint))
					{
						if (!$this->is_visible($node))
						{
							// $this->notice("Cannot access property: {$classname}::\${$property_name}");
							return $this->null_reference($key);
						}	
						$key=$property_name;	
						return $this->classes[strtolower($class)]->static_taint; //only access its value #TODO: check for visibility
					}
				}
				// $this->error("Access to undeclared static property: {$classname}::\${$property_name}");
			}
			// else
			// 	$this->error("Class '{$classname}' not found");
			return $this->null_reference($key);
		}
		elseif ($node instanceof Node\Expr\Variable)
		{
			return $this->taint_table($node->name,$key,$create);
		}
		elseif ($node instanceof Node\Expr)
		{
			$hack=array('temp'=>$this->evaluate_taint($node))	;
			$key='temp';
			return $hack;
		}
		else
		{
			// $this->error("Can not find variable reference of this node type.",$node);
			return $this->null_reference($key);
		}
		$this->error("oh oh");
	}

	function taint_set($node,$value)
	{
		if (!(is_object($value) or ($value>=0 and $value<10) or is_array($value)))
		{
			var_dump($value);
			$this->error("Bad taint set!");
		}

		$t=&$this->taint_table($node,$key,true);
		if ($key!==null)
			return $t[$key]=$value;
		else 
			return null;
	}
	function taint_set_byref($node,&$ref)
	{

		$r=&$this->taint_table($node,$key,true);
		if ($key!==null)
			return $r[$key]=&$ref;
		else 
			return null;
	}

	function taint_get($node)
	{
		$r=&$this->taint_table($node,$key,false);
		if ($key!==null)
			if (is_string($r))
				return 0.9; //a char of a string
			elseif (is_null($r)) //any access on null is null [https://bugs.php.net/bug.php?id=72786]
				return 0;
			elseif (is_array($r)) //support for iterable objects
			{
				if (!array_key_exists($key, $r)) //only works for arrays, not strings
				{
					// $this->notice("Undefined index: {$key}");
					return 0;
				}
				return $r[$key];
			}
			else
			{
				if ($r[$key])
						$this->verbose("Tainted variable accessed!\n",4);
				// $this->warning("Using unknown type as array");
				return $r[$key];
			}
		return 0;
	}
	function taint_isset($node)
	{
		$this->error_silence();
		$r=$this->taint_table($node,$key,false);
		$this->error_restore();
		return $key!==null and isset($r[$key]);
	}
	function taint_unset($node)
	{
		$base=&$this->taint_table($node,$key,false);
		if ($key!==null)
			unset($base[$key]);
	}		
	function &taint_reference($node,&$success=null)
	{
		$r=&$this->taint_table($node,$key,false); //this should NOT always create, e.g. static property fetch
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
		else
		{
			$success=false;	
			$this->error("Could not retrieve taint reference",$node);
		}
	}		
}

$usage="Usage: php main.php -f file.php [-v verbosity --output --strict --concolic --diehard --postprocessing]\n";
if (isset($argc))// and realpath($argv[0])==__FILE__)
{
	$options=getopt("f:v:o",['strict','output','concolic','postprocessing','diehard','ppisolation']);
	if (!isset($options['f']))
		die($usage);
	
	ini_set("memory_limit",-1);
	$x=new TaintAnalyzer;
	$x->strict=isset($options['strict']);
	$x->direct_output=isset($options['output']);
	if (isset($options['v'])) $x->verbose=$options['v'];
	$entry_file=$options['f'];
	$x->concolic=isset($options['concolic']);
	$x->diehard=isset($options['diehard']);
	$x->post_processing_isolation=isset($options['ppisolation']);
	$x->start($entry_file);


	if (isset($x->termination_value))
		exit($x->termination_value);
	else
		exit(0);
}
else
	die($usage);