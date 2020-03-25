<?php
/**
 * PHP Deep Copy
 * @version  3.1: 
 *           if is_ref and zval_id are available
 *           in PHP core, we will use them.
 *           otherwise, we will use PHP based implementations of these
 *           functions.
 *           
 * warnings on attempting uncloneable objects and resources
 * Resources and uncloneable objects can not be deep
 * copied, this will return the original instead.
 * http://php.net/manual/en/resource.php
 */

function _is_ref($container=[],$varname)
{

	foreach ($container as $k=>$v)
		if ($k!==$varname)
			unset($container[$k]);
	$erred=false;
	set_error_handler(function () use ($erred) {$erred=true;});
	ob_start();
	/* DO NOT REMOVE */@var_dump($container);
	 //DO NOT REMOVE, not DEBUG!
	$dump=ob_get_clean();
	restore_error_handler();
	if ($erred)
	{
		echo "Discrepency in _is_ref, var_dump caused an error:\n";
		var_dump($dump);
		die();
	}
	if (($r=strpos($dump,"=>\n  &"))===false)
		return false;
	else 
	{
		if ($r>=15+strlen($varname) /*array(_) {\n  [_]*/
			and $r<20+strlen($varname))
			return true;
	}
	return false;
}
/**
 * This is a hacky is_ref function.
 * Determines whether a variable is a reference or not
 * by using var_dump on its symbol table and
 * checking whether it has '&' before its value or not.
 * @param  array   $defined_vars should send get_defined_vars() to this. enforced.
 * @param  mixed  $var          any variable, should be a variable and not a expression
 * @return boolean               
 */
function _is_ref_plain($defined_vars=[],$var)
{
	$t=&debug_backtrace()[0];
	preg_match("/".__FUNCTION__."\(\s*get_defined_vars\s*\(\s*\)\s*,\s*\\$(.*?)\)/i", file($t['file'])[$t['line']-1],$varname);
	if ($varname)
		$varname=$varname[1];
	else
	{
		trigger_error("You need to provide two arguments to '".__FUNCTION__.
		"', namely get_defined_vars() and a variable");	
		return false; //an expression is not a reference
	}
	return _is_ref($defined_vars,$varname);
}
/**
 * This is a hacky zval_id implementation.
 * It tries to be as fast as possible,
 * and can only detect unique arrays, leaving them
 * modified
 * When in doubt, assumes it's a new zval!
 * @param  mixed &$zval 
 * @return integer        
 */
function _zval_id_light(&$zval)
{
	static $id=0;
	if (is_array($zval))
	{
		if (isset($zval['___VISITED___']))
			return $zval['___VISITED___'];
		else
			return $zval['___VISITED___']=$id++;
	}
	else
		return $id++;
}
/**
 * This is a more accurate zval_id
 * implementation. for each new zval,
 * it compares it to all previously checked
 * zval and if they are the same, same id is returned.
 * @param  mixed &$zval 
 * @param  array &$zvals the zval pool. for every set of variables, use a new pool
 * @return integer
 */
function _zval_id_accurate(&$zval,&$zvals)
{
	if ($zvals===null) $zvals=[];
	$backup=$zval;
	$zval="___VISITED___";
	foreach ($zvals as $k=>$v)
		if ($v==='___VISITED___')
		{
			$id=$k;
			break;
		}
	$zval=$backup;
	if (!isset($id))
	{
		$id=count($zvals);
		$zvals[$id]=&$zval;
	}
	return $id;
}
function _zval_id(&$zval,&$zvals) 
{
	return _zval_id_accurate($zval,$zvals);
}
/**
 * An alias to deep_copy that does not need to be invoked by reference
 * deep copies a zval (PHP variable)
 * @param  mixed &$variable the variable to clone
 * @param  [type] &$clone    the clone
 * @return null
 */
function zval_clone(&$variable,&$clone)
{
	$clone=&deep_copy($variable);
}
/**
 * Deep Copy a PHP Variable
 * Note: you need to access the result of this function byreference,
 * or an implicit copying will be made
 *
 * @param  mixed  &$variable    the variable to copy
 * @param  array   &$object_pool for internal use
 * @param  array   &$zval_pool   for internal use
 * @param  integer $depth        for internal use
 * @param  array   &$id_zvals   for internal use
 * @return mixed                a deep copy of the variable
 */
if (!defined("DEBUG")) define ("DEBUG",false);
define("ZVAL_ID_DISABLED",true); //doesn't work for now
function get_all_properties($obj)
{
	static $lister=null;
	if ($lister===null)
	{
		$lister=function ($object) {
			$all=get_object_vars($object);
			$res=[];
			foreach ($all as $k=>$t)
				$res[$k]=&$object->{$k};
			return $res;
		};
	}
	$t=Closure::bind($lister,null,$obj);
	return $t($obj);

}
function force_property_set($obj,$property,&$val)
{
	static $forcer=null;
	if ($forcer===null)
	{
		$forcer=function($object,$prop,&$val) {
			$object->{$prop}=$val;
		};
	}
	$t=Closure::bind($forcer,null,$obj);
	$t($obj,$property,$val);
}

function &deep_copy(&$variable,&$object_pool=[],&$zval_pool=[],$depth=0,&$id_zvals=[])
{
	if ($depth>24)
	 	die("\n==Deep Copy infinite recursion==\n");
	if (DEBUG) echo "start deep copy\n";
	if (!defined("ZVAL_ID_DISABLED") and function_exists("zval_id"))
		$id=zval_id($variable);
	else
		$id=_zval_id($variable,$id_zvals);
	if (DEBUG) echo str_repeat("-",$depth+1),"id:#",$id," (type=",gettype($variable),")",PHP_EOL;
	if (isset($zval_pool[$id]))
	{
		if (DEBUG and gettype($zval_pool[$id])!==gettype($variable)) 
		{

			echo "Discrepency in Zval ID found: {$id} is ",gettype($zval_pool[$id])
			," should be ",gettype($variable),PHP_EOL;
			var_dump($variable);
			var_dump($zval_pool[$id]);
			die();
		}
		return $zval_pool[$id];
	}
	if (is_array($variable))
	{
		$res=[]; 
		$zval_pool[$id]=&$res; //the copy array
		foreach ($variable as $k=>$v)
		{
			// file_put_contents("serialize.txt",serialize($id_zvals));
			if (DEBUG) echo "Key:", str_repeat("-",3*($depth+1)).":{$k}\n";
			// if (DEBUG and $k==="GLOBALS")
				// var_dump($variable[$k]);
			// if (DEBUG and $k==="wp_die")
			// {
			// 	//this crashes only in PHP 7... probably PHP7 specific
			// 	//crashes even without PHPx compiled in. works fine on PHP 5.4
			// 	//
			// 	echo "var_dumping\n";
			// 	// var_dump($v);
			// 	var_dump(count($depth));	
			// 	var_dump(count($id_zvals));
			// 	echo "DIE:";
			// 	$a=[];
			// 	echo "isolated call:\n";
			// $t=&deep_copy($a,$object_pool,$zval_pool,$depth+1,$a);
			// 	echo "NOOO!\n";
			// 	// die("SUPER!");
			// }
			if (DEBUG) echo "going to call myself\n";
			$t=&deep_copy($variable[$k],$object_pool,$zval_pool,$depth+1,$id_zvals);
			if (DEBUG) echo "returned from deep copy call\n";
			if (function_exists("is_ref"))
			{

				if (DEBUG and is_ref($variable[$k])!==_is_ref($variable,$k))
				{
					echo "Discrepency in is_ref (key={$k}): PHPx says ",is_ref($variable[$k]),
					" whereas _is_ref says ",_is_ref($variable,$k)."",PHP_EOL;
					var_dump($variable);
					die();
				}
				if (is_ref($variable[$k]))
					$res[$k]=&$t;
				else
					$res[$k]=$t;
			}
			else //php-based is_ref
				if (_is_ref($variable,$k))
					$res[$k]=&$t;
				else
					$res[$k]=$t;
			if (DEBUG) echo "end of loop\n";
		}
		return $res;
	}	
	elseif (is_object($variable))
	{
		$hash=spl_object_hash($variable);
		if (isset($object_pool[$hash]))
			$res=$object_pool[$hash];
		else
		{
			$reflection=new ReflectionObject($variable);
			if ($reflection->isCloneable()===false or get_class($variable)=="SplFileObject")
			{
				trigger_error("Attempting to deep copy an unclonable object (depth={$depth})",E_USER_WARNING);	
				$res=$object_pool[$hash]=$variable;
			}
			else
			{
				///clone all objects inside this object too
				$c=clone $variable;
				$res=$object_pool[$hash]=&$c; //have it for now, working on it
				$temp=get_all_properties($c);
				// echo "___\n";
				// var_dump($temp);
				foreach ($temp as $k=>&$v)
				{
					if (//is_array($v) or 
						is_object($v))
					{
						//TODO: put both inside one function/closure
						// echo "---\n";
						// var_dump($v);	
						$v=&deep_copy($v,$object_pool,$zval_pool,$depth+1,$id_zvals);
						force_property_set($c,$k,$v);
						// var_dump($v);	
						// echo "/---\n";
					}
				}	
				// echo "/___\n";
				// var_dump($c);
			}
		}
		$zval_pool[$id]=&$res;
		return $res;
	}
	else
	{
		if (is_resource($variable))
			trigger_error("Attempting to deep copy a resource of type '".get_resource_type($variable)."' (depth={$depth})");	
		$zval_pool[$id]=$variable; //copy
		return $zval_pool[$id];
	}
}
return;
//------------ TESTS ---------------
die(test_zval_id());
function test_zval_id()
{
	$a=2;
	$b=$a;
	$dulldozer=&$b;
	var_dump(_zval_id($a,$pool));
	var_dump(_zval_id($b,$pool));
	var_dump(_zval_id($dulldozer,$pool));

}
function is_ref_test()
{
	$a=2;
	$b=$a;
	$dulldozer=&$b;
	// $vars=get_defined_vars();
	// foreach ($vars as $k=>$v)
	// {
	// 	if ($k!=='b') unset($vars[$k]);
	// }
	// var_dump($vars);
	// echo str_repeat("-",40),PHP_EOL;

	// debug_zval_dump($vars);
	// $c=&$b;
	// debug_zval_dump($c);
	var_dump(_is_ref(get_defined_vars(),$a));
	var_dump(_is_ref(get_defined_vars(),$dulldozer));
}
die(test_thorough());


die(test_binary_tree_reference_value());
die(test_2array_ref());
die(test_resource());
die(test_array_ref());
die(test_value_ref());
die(test_object_ref());
function test_thorough()
{
	$a=[];
	$o=new stdClass;
	$o->data="o data";
	$oref=&$o;
	$ocopy=$o;
	$v="value";
	$vcopy=$v." copy";
	$vref=&$v;
	$a=[$o,$oref,$ocopy,$v,$vcopy,$vref
	,&$o,&$oref,&$ocopy,&$v,&$vcopy,&$vref];
	$a2=&deep_copy($a);
	$a2[0]->data.=" (deep_copy)";
	$a2[3].=" (deep_copy)";
	$a2[4].=" (deep_copy)";

	$a[0]->data.=" (original)";
	$a[3].=" (original)";
	$a[4].=" (original)";
	var_dump($a);
	echo str_repeat("-",80),PHP_EOL;
	var_dump($a2);
}
function test_resource()
{
	$file=tempnam(0,0);
	file_put_contents($file, "hello\nthere");
	$f=fopen($file,"rt");
	$a=[2,5,$f];
	$a2=&deep_copy($a);
	var_dump($a);
	var_dump($a2);
	echo fgets($a[2]),PHP_EOL;
	echo fgets($a2[2]),PHP_EOL;

}
function test_2array_ref()
{
	$b=['data'=>'b'];
	$c=['data'=>'c'];
	$a=['data'=>'a'
	, 'b'=>&$b
	,'c'=>&$c
	];
	$c['a']=&$a;
	$b['a']=&$a;


	$a2=&deep_copy($a);
	$a['data']='original a';
	$a2['data']='a2';
	print_r($a);
	print_r($a2);
}
function test_array_ref()
{
	#doesnt work with 2.2, three copies are made!?
	#passes on 2.4
	$b=[4,5,6];
	$a=[1,2,3,&$b,&$b,$b];
	$a2=&deep_copy($a);
	$a2[3][0]=44;
	$a[3][0]=55;
	var_dump($a2);
	var_dump($a);
}

function test_binary_tree_reference_value()
{
	#doesnt work with 2.2, infinite recursion
	#does not pass on any version
	$a=[];
	$a['ref']=&$a;
	$a['val']=$a;
	var_dump(serialize($a));
	var_dump(unserialize(serialize($a)));
	// var_dump(zval_id($a['val']));
	// var_dump(zval_id($a['ref']['val']));
	// var_dump(zval_id($a['val']['val']));
	// var_dump(zval_id($a['val']['val']['val']));
	// var_dump(zval_id($a['val']['val']['val']['val']));
	echo "Ready: ";
	$a2=&deep_copy($a);
	echo "done.\n";
	var_dump($a2);
}


function test_array_double_ref()
{
	#works fine with version 2.2
	$a['data']='initial data';
	$a['a_val']=$a;
	$a['a1']=&$a;
	$a['a2']=&$a;
	$a2=&deep_copy($a);
	$a2['data']='copy data';
	$a2['a_val']['data']='copy val data';
	var_dump($a);
	var_dump($a2);
}
function test_value_ref()
{
	$v='value';
	$v2=$v;
	$vref=&$v;
	$a=['val'=>$v,'val copy'=>$v2,'val ref copy'=>$vref,'valref'=>&$v,'val copy ref'=>&$v2,'val ref ref'=>&$vref];

	$a2=&deep_copy($a);

	$a2['valref']="copy";
	$a['val']="original";
	$a['val copy']="original copy";
	$a['val ref ref']="original reference";

	var_dump($a);
	echo str_repeat("-",80),PHP_EOL;


	echo "3,5 values should be 'copy' here, different from the original, but the same:",PHP_EOL;
	var_dump($a2);
	echo str_repeat("-",80),PHP_EOL;


	echo "3 and 5 should be 'copy reference', 2 should be 'copy value':",PHP_EOL;
	$a2['val copy']="copy value";
	$a2['val ref ref']='copy reference';
	var_dump($a2);
	echo str_repeat("-",80),PHP_EOL;
}
function test_object_ref()
{

	class test{
		static public $count=0;
		public $id=0;
		public $data="initial data";
		function __construct()
		{	
			$this->id=self::$count++;
			echo "Construct {$this->id}",PHP_EOL;
		}
		function __clone()
		{
			$this->id=self::$count++;
			echo "Clone {$this->id}",PHP_EOL;
		}
		function __destruct()
		{
			echo "Destruct {$this->id}",PHP_EOL;
			self::$count--;
		}
	}

	$o=new test();
	$o2=$o;
	$oref=&$o;
	$a=[$o,$o2,$oref,&$o,&$o2,&$oref];

	$a2=&deep_copy($a);

	$a2[0]->data="copy";
	$a[0]->data="original";
	$a[1]="original value";
	$a[5]="original reference";

	var_dump($a);
	echo str_repeat("-",80),PHP_EOL;


	echo "All values should be 'copy' here, different from the original, but the same:",PHP_EOL;
	var_dump($a2);
	echo str_repeat("-",80),PHP_EOL;


	echo "3 and 5 should be copy reference, just like the original:",PHP_EOL;
	echo "2 should be 'copy reference' and 4 should be 'copy' as well",PHP_EOL;
	$a2[1]="copy value";
	$a2[5]='copy reference';
	var_dump($a2);
	echo str_repeat("-",80),PHP_EOL;


}
function test_deep_copy_deep_object()
{
	class myClass 
	{
		private $y;
		function assign($obj)
		{
			$this->y=$obj;
		}
		public $val=5;
	}

	$x=new myClass;
	$x->val=6;
	$x2=new myClass;
	$x2->val=7;
	$x->assign($x2);

	$x3=deep_copy($x);
	$x2->val++;
	$x->val++;
	var_dump($x3); //should be 7 and 6

}

