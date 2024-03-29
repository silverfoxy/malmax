<?php

namespace PHPEmul;

require_once __DIR__."/emulator.php";
use PhpParser\Node;
use PhpParser\NodeAbstract;

#TODO: interfaces behave like classes when using each other, e.g. an interface can "extend"
#	another interface. Add support. Good example is CakePHP.
#TODO: closure,closureUse
#TODO: namespace,use
//TODO: magic methods, destructor
//TODO: enforce visibilities
//TODO: print_r,var_export output EmulatorObject not actual object

/**
 * The user defined objects are wrapped in this class.
 */
class EmulatorObject
{
	const Visibility_Public=1;
	const Visibility_Protected=2;
	const Visibility_Private=4;

	public static $emul=null;
	public static $object_count=0;

	/**
	 * @var string
	 */
	public $classname;
	/**
	 * Array of keys as propname, values as EmulatorObjectProperty
	 * @var [type]
	 */
	public $properties;
	/**
	 * Visibility of properties. 
	 * if a visibility does not exist for a property (dynamic creation), it's assumed public
	 * @var [type]
	 */
	public $property_visibilities;
	/**
	 * Which class this property was generated from (inheritance)
	 * @var array
	 */
	public $property_class=[];
	/**
	 * Parent if inherited from a core PHP class
	 * @var  object 
	 */
	public $parent=null;
	/**
	 * A numeric value which is distinct for every object
	 * @var integer
	 */
	public $objectid;
    public $symbolic_status;
	public function __construct($classname,$properties=[],$visibilities=[],$classes=[],$symbolic_status=null)
	{
		$this->objectid=self::$object_count++;
		$this->classname=$classname;
		self::$emul->verbose("EmulatorObject('{$this->classname}') __construct() id={$this->objectid}\n",8);
		$this->properties=$properties;
		$this->property_visibilities=$visibilities;
		$this->property_class=$classes;
	}
	function __clone()
	{
		$this->objectid=self::$object_count++;
		self::$emul->verbose("EmulatorObject('{$this->classname}')) __clone() id={$this->objectid}\n",8);
		//TODO: call clone
	}

	public $destructor=null;
	// Switching from PHP destructor calls to manuall almost shutdown_function like invokation of destructors
    // with the goal of making destructor calls more predictable
	// function __destruct()
	// {
	// 	self::$emul->verbose("EmulatorObject('{$this->classname}') __destruct() id={$this->objectid}\n",8);
	// 	self::$object_count--;
	// 	if (self::$emul->method_exists($this, "__destruct")) {
	// 	    self::$emul->verbose("Running {$this->classname} destructor".PHP_EOL);
	// 	    self::$emul->magic_function = '__destruct';
    //         self::$emul->run_method($this, "__destruct");
    //         self::$emul->magic_function = null;
    //     }
	// 	// TODO: call the internal destructor from OOEmulator instead of here
	// }

	function __toString()
	{
		#FIXME: this might cause errors if the class has no __toString but is used as one, causing errors
		if (self::$emul->method_exists($this, "__toString"))
			return self::$emul->run_method($this,"__toString");
		self::$emul->notice("Object of class '{$this->classname}' could not be converted to string");
        return $this->classname;
	}

	public function isVisible(string $property_name) {
        if (array_key_exists($property_name, $this->properties)) {
            return $this->property_visibilities[$property_name];
        }
        else {
            $current_object = $this;
            while (isset($current_object->parent)) {
                $current_object = $current_object->parent;
                // Parent is not wrapped in emulator object
                // $has_property = $current_object->hasProperty($property_name);
                if (property_exists($current_object, $property_name)) {
                    return true;
                }
            }
        }
        // Neither current object nor any of its parent define the target property
        return false;
    }

	public function hasProperty(string $property_name) {
	    if (array_key_exists($property_name, $this->properties)) {
            return true;
        }
	    else {
	        $current_object = $this;
	        while (isset($current_object->parent)) {
	            $current_object = $current_object->parent;
	            // Parent is not wrapped in emulator object
	            // $has_property = $current_object->hasProperty($property_name);
                if (property_exists($current_object, $property_name)) {
                    return true;
                }
            }
        }
	    // Neither current object nor any of its parent define the target property
        return false;
    }

    public function &getPropertiesArray(string $property_name) {
        if (array_key_exists($property_name, $this->properties)) {
            return $this->properties;
        }
        else {
            $current_object = $this;
            while (isset($current_object->parent)) {
                $current_object = $current_object->parent;
                $has_property = $current_object->hasProperty($property_name);
                if ($has_property === true) {
                    return $current_object->getPropertiesArray($property_name);
                }
            }
        }
        // Neither current object nor any of its parent define the target property
        $this->notice('Undefined property '.$property_name);
    }

    public function setProperty(string $property_name, $value) {
        if (array_key_exists($property_name, $this->properties)) {
            $this->properties[$property_name] = $value;
            return $value;
        }
        else {
            $current_object = $this;
             while (isset($current_object->parent)) {
                $current_object = $current_object->parent;
                $has_property = $current_object->hasProperty($property_name);
                if ($has_property === true) {
                    $current_object->properties[$property_name] = $value;
                    return $value;
                }
            }
        }
        // Create and set the property if doesn't exist
        $this->properties[$property_name] = $value;
        return $value;
    }

}

require_once "oo-methods.php";
require_once "oo-spl-autoload.php";

class OOEmulator extends Emulator
{
	use OOEmulatorMethods;
	use OOEmulator_spl_autoload;
	function __construct($init_environ=null, $httpverb=null, $predefined_constants=null, $reanimation_callback_object=null, $correlation_id=null)
	{
		parent::__construct($init_environ, $httpverb, $predefined_constants, $reanimation_callback_object, $correlation_id);
		$this->state['autoloaders']=
		$this->state['classes']=
		$this->state['mock_classes']=
		$this->state['current_method']=
		$this->state['current_trait']=
		$this->state['current_this']=
		$this->state['current_self']=
		$this->state['current_class']=
		$this->state['magic_method_reentrant']= //re-entrant flag check for magic-methods
		1; //emulation state elements
		if ($this->auto_mock)
		foreach(get_declared_classes() as $class) 
		{
			if (class_exists($class."_mock"))
				$this->mock_classes[strtolower($class)]=$class."_mock";
		}

		EmulatorObject::$emul=$this; //HACK for allowing destructor calls
	}
	/**
	 * Holds the class definitions
	 * @var array
	 */
	public $classes=[];
	/**
	 * List of mocked core classes
	 * @var array
	 */
	public $mock_classes=[];
    public $current_method,$current_trait;
	/**
	 * Holds $this, a reference to current active object
	 * @var EmulatorObject
	 */
	public $current_this=null;
	/**
	 * Holds the current static class, for example A for A::x()
	 * This is what self and __CLASS__ resolve to
	 * @var null
	 */
    public $current_self=null;
	/**
	 * Holds the current dynamic class (late static binding)
	 * @var null
	 */
    public $current_class=null;

    protected function define_class($node)
	{
        //has type, implements (array), stmts (Array), name, extends
        //type=0 is normal, type=16 is abstract

        $classtype=null;
        if (isset($node->type))
            $classtype=$node->type;
        $classname=$this->current_namespace($this->name($node->name));
        $flags=strtolower(substr(explode("\\",get_class($node))[3],0,-1)); #intertface, class, trait
        // $class_index=strtolower($this->namespace($classname));
        $class_index=strtolower($classname);
        $this->classes[$class_index] = new \stdClass;
        $class = &$this->get_class_object($class_index);

        $extends=null;
        $this->verbose("Extracting declaration of class '{$classname}'...\n",3);

        $class->name=$classname;
        $class->interfaces=[];
        $class->traits=[];
        $class->consts=[];
        $class->type=$flags;
        $class->methods=[];
        $class->properties=[];
        $class->property_visibilities=[];
        $class->static=[];

        if (isset($node->extends) and $node->extends) {
            $extends = $this->namespaced_name($node->extends);
            if (!$this->class_exists($extends)) {
                $this->spl_autoload_call($extends);
            }
            if (!$this->class_exists($extends)) {
                $this->error("Class '{$extends}' not found");
            }
            if ($this->user_class_exists($extends)) {
                $extends = $this->get_class_object($extends)->name;
            }
        }
        $class->parent=$extends;
            // Autoload class hierarchy (Parents of parent)
            // Copy properties of parents to the current class
        //     $parent = $extends;
        //     do {
        //         if (!$this->class_exists($parent)) {
        //             $this->spl_autoload_call($parent);
        //         }
        //         if (!$this->class_exists($parent)) {
        //             $this->error("Class '{$parent}' not found");
        //         }
        //         $parent = new \ReflectionClass($parent);
        //         // Get properties of the parent and add them to the current class
        //         $public_props = $parent->getProperties(\ReflectionProperty::IS_PUBLIC);
        //         foreach ($public_props as $property) {
        //             $class->properties[$property->getName()] = $property->getDefaultValue();
        //             $class->property_visibilities[$property->getName()] = EmulatorObject::Visibility_Public;
        //         }
        //         $protected_props = $parent->getProperties(\ReflectionProperty::IS_PROTECTED);
        //         foreach ($protected_props as $property) {
        //             $class->properties[$property->getName()] = $property->getDefaultValue();
        //             $class->property_visibilities[$property->getName()] = EmulatorObject::Visibility_Protected;
        //         }
        //         $private_props = $parent->getProperties(\ReflectionProperty::IS_PRIVATE);
        //         foreach ($private_props as $property) {
        //             $class->properties[$property->getName()] = $property->getDefaultValue();
        //             $class->property_visibilities[$property->getName()] = EmulatorObject::Visibility_Private;
        //         }
        //
        //     } while ($parent = $parent->getParentClass());
        // }
        $predefined = include "hardcoded-vals.php";
        if ($extends && !in_array($extends,$predefined)) {
            foreach ($this->ancestry($class_index,true) as $parent_class) {
                if (!$this->user_class_exists($parent_class)) {
                    $this->spl_autoload_call($parent_class);
                }
                if ($this->user_class_exists($parent_class)) {
                    $class_obj = $this->get_class_object($parent_class);
                    foreach ($class_obj->properties as $property_name => $property) {
                        $class->properties[$property_name] = $property;
                        $class->property_visibilities[$property_name] = $class_obj->property_visibilities[$property_name];
                    }
                }
            }
        }

        $class->context=new EmulatorExecutionContext([ //'class'=>$classname, //this is static, shouldn't be set here
            'self'=>$classname,'file'=>$this->current_file
            ,'namespace'=>$this->current_namespace,'active_namespaces'=>$this->current_active_namespaces]);
        foreach ($node->stmts as $part)
        {
            $current_self_backup = $this->current_self;
            $this->current_self = $classname;
            if ($part instanceof Node\Stmt\Property)
            {
                $flags=$part->flags; //1= public, 2=protected, 4=private, 8= static
                foreach ($part->props as $property)
                {
                    $propname=$this->name($property->name);
                    if ($property->default)
                        $val=$this->evaluate_expression($property->default);
                    else
                        $val=NULL;
                    if ($flags & EmulatorObject::Visibility_Private)
                        $visibility=EmulatorObject::Visibility_Private;
                    elseif ($flags & EmulatorObject::Visibility_Protected)
                        $visibility=EmulatorObject::Visibility_Protected;
                    else
                        $visibility=EmulatorObject::Visibility_Public;

                    if ($flags & 8 ) //static
                    {
                        $class->static[$propname]=$val;
                        $class->static_visibility[$propname]=$visibility;
                    }
                    else
                        $class->properties[$propname]=$val;
                        $class->property_visibilities[$propname]=$visibility;
                }
            }
            elseif ($part instanceof Node\Stmt\ClassMethod)
            {
                $flags=$part->flags;
                if ($flags & EmulatorObject::Visibility_Private)
                    $visibility=EmulatorObject::Visibility_Private;
                elseif ($flags & EmulatorObject::Visibility_Protected)
                    $visibility=EmulatorObject::Visibility_Protected;
                else
                    $visibility=EmulatorObject::Visibility_Public;
                $static=$flags&8;
                $methodname=$this->name($part->name);
                $class->methods[strtolower($methodname)]=(object)array('name'=>$methodname,"params"=>$part->params,"code"=>$part->stmts,
                        'type'=>$flags,'statics'=>[],'visibility'=>$visibility,'static'=>$static);
            }
            elseif ($part instanceof Node\Stmt\ClassConst)
            {
                foreach ($part->consts as $const)
                {
                    $constname=$this->name($const->name);
                    $val=$this->evaluate_expression($const->value);
                    $class->consts[$constname]=$val;
                }
            }
            elseif ($part instanceof Node\Stmt\TraitUse)
            {
                foreach ($part->traits as $trait)
                    $class->traits[]=$this->name($trait);
            }
            elseif (!$part instanceof Node\Stmt\Nop) {
                $this->error("Unknown class part for class '{$classname}'", $part);
            }

        }
        $interfaces=[];
        if (isset($node->implements))
        foreach ($node->implements as $interface)
            $interfaces[]=$this->name($interface);
        $class->classtype=$classtype;
        // $class->file=$this->current_file;
        $class->interfaces=$interfaces;
        $this->current_self = $current_self_backup;
	}
	/**
	 * Create an object from a user defined class
	 * @param  string $classname 
	 * @param  array  $args  		constructor args   
	 * @return EmulatorObject            
	 */
	public function new_user_object($classname,array $args, $symbolic_status=null)
	{
		$this->verbose("Creating object of type {$classname}...".PHP_EOL,2);
        $classname = strtolower($classname);
        if (in_array($classname, $this->symbolic_classes)) {
            return new SymbolicVariable($classname,'*',Node\Stmt\ClassLike::class,true,[],$classname);
        }
		$class_obj = $this->get_class_object($classname);
		$obj=new EmulatorObject($class_obj->name, $class_obj->properties, $class_obj->property_visibilities);
		$constructor=null;
		$destructor = null;
        $symbolic_status = $symbolic_status;
		
		$t=explode("\\",$classname);
		$old_style_constructor=end($t); //strip namespace
        $predefined = include "hardcoded-vals.php";

        foreach ($this->ancestry($classname) as $class) //find the first available constructor
        {
            if ($this->user_method_exists($class,"__construct"))
                $constructor=array($class,"__construct");
            elseif ($this->user_method_exists($class,$old_style_constructor))
                $constructor=array($class,$old_style_constructor);
            if (isset($constructor)) break;
        }

        foreach ($this->ancestry($classname) as $class) //find the first available destructor
        {
            if ($this->user_method_exists($class,"__destruct"))
                $destructor=array($class,"__destruct");
            if (isset($destructor)) break;
        }
        foreach ($this->ancestry($classname,true) as $class)
		{
			if ($this->user_class_exists($class) and !in_array($class,$predefined)) {
			    $class_obj = $this->get_class_object($class);
                foreach ($class_obj->properties as $property_name => $property) {
                    $obj->properties[$property_name] = $property;
                    $obj->property_visibilities[$property_name] = $class_obj->property_visibilities[$property_name];
                    #FIXME: in reality, both classes are preserved by PHP, classname is part of variable name, but here we override:
                    $obj->property_class[$property_name] = $class_obj->name;
                }
            }
			else
			{
				if ($constructor) //has a constructor, do not auto-construct
				{
					try {
						$r=new \ReflectionClass($class);
						$obj->parent=$r->newInstanceWithoutConstructor(); //fails in php 5.6-
					}
					catch(\ReflectionException $e)
					{
						$obj->parent=$r->newInstance();
					}
				}
				else
					$obj->parent=new $class;
			}
		}
		if ($constructor)
			$this->run_user_method($obj,$constructor[1],$args,$constructor[0]);
		if ($destructor) {
		    $this->destructors[] = $obj;
        }
        if ($symbolic_status)
            $obj->symbolic_status = true;
		// $this->verbose("Creation done!".PHP_EOL,2); ///DEBUG
		return $obj;
	}
	/**
	 * Instantiate a core class into a native php object
	 * @param  string $classname 
	 * @param  array  $args      
	 * @return object            
	 */
	protected function new_core_object($classname,array $args)
	{
		$this->verbose("New instance of core class '{$classname}'\n",5);
		$class=$classname;
		if (in_array($classname, $this->symbolic_classes)) {
		    return new SymbolicVariable($classname,'*',Node\Stmt\ClassLike::class,true,[],$classname);
        }
		$mocked=isset($this->mock_classes[strtolower($classname)]);
		if ($mocked)
		{
			$class=$this->mock_classes[strtolower($classname)];
			$class::$emul=$this; //set emulator
		}
		$argValues=[];
		foreach ($args as $arg)
			$argValues[]=$this->evaluate_expression($arg->value);
		ob_start();	
		$r = new \ReflectionClass($class);
		$ret = $r->newInstanceArgs($argValues); #TODO: byref?
		// $ret=new $classname($argValues); //core class
		$output=ob_get_clean();
		$this->output($output);
		return $ret;
	}
	/**
	 * Instantiate a class into an object (native or user defined)
	 * @param  string $classname 
	 * @param  array  $args      
	 * @return object            
	 */
	public function new_object($classname, array $args)
	{
		$classname=$this->real_class($classname); //apparently 'new self' is ok!
		// if (!$this->class_exists($classname))
        $predefined = include "hardcoded-vals.php";
        if (in_array($classname, $this->symbolic_classes)) {
            return new SymbolicVariable($classname, '*', Node\Stmt\ClassLike::class, true, null, $classname);
        }
        if (!$this->user_class_exists($classname) && !in_array($classname,$predefined))
			$this->spl_autoload_call($classname);
		if ($this->user_class_exists($classname)) //user classes
			return $this->new_user_object($classname,$args);
		elseif (class_exists($classname)) //core classes
			return $this->new_core_object($classname,$args);

		$this->error("Class '{$classname}' not found ");
	}
	/**
	 * Evaluate expressions specific to object orientation
	 * @param  Node $node 
	 * @return mixed       
	 */
	protected function evaluate_expression($node, &$is_symbolic=false)
	{
		$this->expression_preprocess($node);		
		if ($node instanceof Node\Expr\New_)
		{
			$classname=$this->namespaced_name($node->class);
			return $this->new_object($classname,$node->args); //user function

		}
		elseif ($node instanceof Node\Expr\MethodCall)
		{
			$object=&$this->variable_reference($node->var);
			$method_name=$this->name($node->name);
			if ($object instanceof EmulatorObject)
				$classname=$object->classname;
            elseif ($object instanceof SymbolicVariable) {
                $classname = $object->classname;
                if ($classname === null) {
                    $this->error("Call to a member function '{$method_name}()' on a Symbolic object without a type");
                }
            }
			elseif (is_object($object))
				$classname=get_class($object);
			else {
				$this->error("Call to a member function '{$method_name}()' on a non-object");
				return null;
			}
			$this->verbose("Method call {$classname}::{$method_name}()".PHP_EOL,3);
			$args=$node->args;
            if ($object instanceof SymbolicVariable) {
                $class_method = $classname.'/'.$method_name;
                $mocked_name=strtolower(str_replace('/','_',$class_method)).'_mock';
                if (function_exists($mocked_name)){
                    array_unshift($args, $this);
                    $ret=call_user_func_array($mocked_name,$args);
                    return $ret;
                }
                if(array_key_exists($class_method,$this->symbolic_methods)){
                    return new SymbolicVariable($class_method,'*',NodeAbstract::class,true,[],$this->symbolic_methods[$class_method]);
                }
			    return new SymbolicVariable($method_name);
            }
			return $this->run_method($object,$method_name,$args);
		}
		elseif ($node instanceof Node\Expr\StaticCall)
		{
			#TODO: namespace support
			$method_name=$this->name($node->name);
			if ($node->class instanceof Node\Expr\Variable)
			{

				// $class=$this->evaluate_expression($node->class)->classname;
				$class=$this->evaluate_expression($node->class);
				if ($class instanceof EmulatorObject)
					$class=$class->classname;
			}
			elseif ($node->class instanceof Node\Name)
				$class=$this->namespaced_name($node->class);
			else
				$this->error("Unknown class when calling static function {$method_name}",$node);

			$args=$node->args;
			return $this->run_static_method($class,$method_name,$args);
		}
		elseif ($node instanceof Node\Expr\PropertyFetch)
		{
			$var=&$this->variable_reference($node);
			return $var;
		}
		elseif ($node instanceof Node\Expr\StaticPropertyFetch)
		{
			$var=&$this->variable_reference($node); //do not create the property in static
			return $var;
		}
		elseif ($node instanceof Node\Expr\ClassConstFetch)
		{
			$class=$this->name($node->class);
			$constant=$this->name($node->name);
            // Special class constant, returns the fully qualified class name
			if ($constant === 'class') {
                return $this->fully_qualify_name($class);
            }
            $class=$this->real_class($class);
            $fq_classname = stripos($class, $this->current_namespace) !== false ? $class : $this->namespaced_name($class);
            if (class_exists($class)) {
                return constant("{$class}::{$constant}");
            }
            if (!$this->user_class_exists($fq_classname)) {
                $this->spl_autoload_call($fq_classname);
            }
			foreach ($this->ancestry($fq_classname) as $cls)
			{
			    $class_obj = $this->get_class_object($cls);
				if (array_key_exists($constant, $class_obj->consts))
					return $class_obj->consts[$constant];
			}
			$this->error("Undefined class constant '{$constant}'",$node);
		}
		elseif ($node instanceof Node\Expr\Clone_)
		{
			$var=$this->variable_get($node->expr);
			if (!is_object($var)) return $this->error("_clone method called on non-object",$node);
			$var2=clone $var;
			if ($this->method_exists($var2, "__clone"))
				$this->run_method($var2,"__clone");
			// $var2->properties=[];
			// foreach ($var->properties as $k=>$property)
			// 	$var2->properties[$k]=clone $property;
			return $var2;
		}
		elseif ($node instanceof Node\Expr\Instanceof_)
		{
			$var=$this->evaluate_expression($node->expr);
			if (!is_object($var)) return false;
			//here needs FQ because classname is string, not going through ancestry
			$classname=$this->namespaced_name($node->class);
			return $this->is_a($var,$classname);
			// if ($var instanceof EmulatorObject)
			// {
			// 	foreach ($this->ancestry($var->classname) as $class)
			// 		if (strtolower($class)===strtolower($classname)) return true;
			// 	return false;
			// }
			// else
			// 	return $var instanceof $classname;
		}		
		elseif ($node instanceof Node\Expr\Cast\Object_)
				return $this->to_object($this->evaluate_expression($node->expr));
		elseif ($node instanceof Node\Expr\Cast)
		{
			$expr=$this->evaluate_expression($node->expr);
			if ($expr instanceof EmulatorObject) 
			{
				if ($node instanceof Node\Expr\Cast\Array_)
					return (array)$expr->properties; #FIXME: php does this like serialize, e.g private is nullNAMEnull=>val
				#TODO: what if scalar object is cast back to scalar?
				#TODO: string cast (call magic __toString)
			}
			elseif ($node instanceof Node\Expr\Cast\Object_)
				return $this->to_object($expr);
		}

		return parent::evaluate_expression($node, $is_symbolic);

	}	
	/**
	 * Converts any value to an emulator object
	 * @param  mixed $val 
	 * @return EmulatorObject      
	 */
	protected function to_object($val)
	{
		return (object)$val; //convert to direct stdClass instead of Emulator object
	}
	/**
	 * Finds the real class name of a class reference (e.g self, parent, static, etc.)
	 * @param  string $classname 
	 * @return string            
	 */
	protected function real_class($classname)
	{
		$t=explode("\\",$classname);
		$end=array_pop($t);
		if ($end === "this")
		    return $this->current_this;
		elseif ($end === "self")
			return $this->current_self;
		elseif ($end === "static")
			return $this->current_class;
		elseif ($end === "parent")
			return $this->get_class_object($this->current_self)->parent;
		return $classname;
	}

    /**
     * Checks whether class object exists, and if not calls the autoloader
     */
    public function try_load_class($classname)
    {
        $fq_classname = $this->namespaced_name($classname);
        $fq_class_obj = $this->get_class_object($fq_classname);
        $class_obj = $this->get_class_object($classname);
        if (isset($fq_class_obj)) {
            $class_obj = $fq_class_obj;
        }
        elseif (!isset($class_obj)) {
            $this->spl_autoload_call($classname);
            $fq_classname = $this->namespaced_name($classname);
            $fq_class_obj = $this->get_class_object($fq_classname);
            $class_obj = $this->get_class_object($classname);
            if (isset($fq_class_obj)) {
                $class_obj = $fq_class_obj;
            }
            elseif (!isset($class_obj)) {
                return null;
            }

        }
        return $class_obj;
    }

	/**
	 * Returns all parents, including self, of a class, ordered from youngest
	 * Looks up self and static keywords
	 * @param  string $classname 
	 * @return array            
	 */
	public function ancestry($classname, $top_to_bottom=false)
	{
        // Remove starting "/"
		$classname = $this->real_class($classname);
        $class_obj = $this->try_load_class($classname);
        if ($class_obj === null) {
            return [];
        }
		$res = [$classname];
		while (isset($class_obj) and $class_obj->parent) {
			$classname = $class_obj->parent;
			$res[] = $classname;
			$class_obj = $this->get_class_object($classname);
		}
		if ($top_to_bottom) {
            $res = array_reverse($res);
        }
		return $res;
	}
	/**
	 * Run object oriented statements
	 * @param  Node $node 
	 * @return null       
	 */
	protected function run_statement($node)
	{
		if ($node instanceof Node\Stmt\ClassLike)
			$this->define_class($node);
		elseif ($node instanceof Node\Stmt\Static_)
		{
			//TODO: bind this static variable to the method being runned in the class it belongs to
			$isMethod= (end($this->trace)->type=="::" or end($this->trace)->type=="->");
			if ($isMethod and  $this->user_method_exists(end($this->trace)->class,end($this->trace)->function)) //statc inside a method)
			{
				$class=end($this->trace)->class;
				$method=end($this->trace)->function;

				//TODO
				$statics = &$this->get_class_object($class)->methods[strtolower($method)]->statics;// &$this->functions[$this->current_function]->statics;
				foreach ($node->vars as $var)
				{
				    // $name=$this->name($var->name)
					$name = $this->get_variableـname($var->var);
					if (!array_key_exists($name,$statics))
						$statics[$name]=$this->evaluate_expression($var->default);
					$this->variables[$name]=&$statics[$name];
				}
			}
			else
				parent::run_statement($node); //static variable in a function
		}		
		// elseif ($node instanceof Node\Stmt\Unset_) //magic method
		// {
		// 	#TODO:
		// }
		else
			parent::run_statement($node);
	}

	/**
	 * Symbol table lookup specific to object orientation
	 * See Emulator::symbol_table for more
	 * @param  Node  $node   
	 * @param  string  &$key   if null lookup failed
	 * @param  boolean $create create variable if not exists (default behavior in property access, impossible on static property access)
	 * @return array base array          
	 */
	protected function &symbol_table($node,&$key,$create=true)
	{
		if ($node instanceof Node\Expr\PropertyFetch)
		{
			$base=&$this->symbol_table($node->var,$key2,$create);
			if ($key2===null)
			{
				$name=is_string($node->var)?$node->var:"Unknown";
				$this->notice("Undefined variable: {$name}");	
				return $this->null_reference($key);
			}
            if ($base instanceof SymbolicVariable)
            {
                $key = 'unknown';
                return new SymbolicVariable("PropertyFetch from " . $base->variable_name);
            }
			$var=&$base[$key2];
			if ($var instanceof EmulatorObject)
			{
				$property_name=$this->name($node->name);
				if (!$var->hasProperty($property_name))
				{
					if (!$create)
					{
						$this->notice("Undefined property: {$var->classname}::\${$property_name}");
						return $this->null_reference($key);
					}
					else //dynamic properties, on all classes 
					{
					    $var->setProperty($property_name, null);
					}
				}
				if (!$this->is_visible($node))
				{
					$this->notice("Cannot access property: {$var->classname}::\${$property_name}");
					return $this->null_reference($key);
				}					
				$key=$property_name;
                return $var->getPropertiesArray($property_name); //reference its value only!
			}
			elseif(is_object($var)) //native object
			{
				$property_name=$this->name($node->name);
				$this->verbose(sprintf("Fetching object property: %s::$%s\n",get_class($var),$property_name),4);
				// if (!isset($var->{$property_name}))
				// 	$this->notice("Undefined property: ".get_class($var)."::\${$property_name}");
				#TODO: review this
                // $reflectionClass = new \ReflectionClass($var);
                // $prop = $reflectionClass->getProperty($property_name);
                // $prop->isPrivate();
                if ($var instanceof SymbolicVariable) {
                    $temp = ['temp' => new SymbolicVariable()];
                }
                else {
                    $temp=['temp'=>&$var->{$property_name}]; //creates
                }
				$key='temp';
				return $temp;
				// return $var->{$property_name}; //self notice? #TEST
			}
			else 
			{
				$this->notice("Trying to get property of non-object",$var);
				return $this->null_reference($key);
			}
		}
		elseif ($node instanceof Node\Expr\StaticPropertyFetch)
		{
			$classname = $this->name($node->class);
			if ($classname instanceof EmulatorObject) //support for $object::static_method
				$classname=$classname->classname;
			$classname = $this->real_class($classname);
			$property_name = $this->name($node->name);
			$fq_classname = stripos($classname, $this->current_namespace) !== false ? $classname : $this->namespaced_name($classname);
            // Try to autoload the class if it doesn't exist
            if (!$this->class_exists($fq_classname)) {
                $this->spl_autoload_call($fq_classname);
            }
			if ($this->ancestry($fq_classname))
			{
				foreach($this->ancestry($fq_classname) as $class)
				{
					if (array_key_exists($property_name, $this->get_class_object($class)->static))
					{
						if (!$this->is_visible($node) && $this->current_class !== $class) // 2nd condition handles reference to self object inside closures
						{
							$this->notice("Cannot access property: {$classname}::\${$property_name}");
							return $this->null_reference($key);
						}	
						$key=$property_name;	
						return $this->get_class_object($class)->static; //only access its value #TODO: check for visibility
					}
				}
				$this->error("Access to undeclared static property: {$classname}::\${$property_name}");
			}
			else
				$this->error("Class '{$classname}' not found");
			return $this->null_reference($key);
		}
		elseif ($node instanceof Node\Expr\Variable and is_string($node->name) and $node->name=="this") //$this
		{
			$key='temp';
			$t=array($key=>&$this->current_this);
			return $t;
		}
		else
			return parent::symbol_table($node,$key,$create);
	}
	/**
	 * Call a function. This does the same thing as call_user_func_array
	 * but for emulator. Can handle all 6 types of dynamic function call
	 * @param  mixed $name array of object/method or class/method, or string of static method or function name
	 * @param  array $args 
	 * @return mixed       function return value
	 */
	public function call_function($name,$args)
	{
		$this->stash_ob();

		#http://php.net/manual/en/language.types.callable.php
		if (is_array($name) and count($name)==2) //method call
		{
			$class_or_obj=$name[0];
			$method_name=$name[1];
			if (is_string($class_or_obj)) //class, type 2
			{
				#TODO: handle type 5 here	
				$ret=$this->run_static_method($class_or_obj,$method_name,$args);
			}
			elseif (is_object($class_or_obj)) //object, type 3
				$ret=$this->run_method($class_or_obj,$method_name,$args);
			else
				$this->error("Unknown function call '{$name}'.");
		}
		elseif (is_string($name) and strpos($name,"::")!==false) //static call
		{
			list($class,$method)=explode("::",$name); //type 4, static method call
			$ret=$this->run_static_method($class,$method,$args);
		}
		else
			#TODO: handle type 6 (invoke)
			$ret=parent::call_function($name,$args); //non-OO
		$this->restore_ob();
		return $ret;
	}

	/**
	 * Compatible with PHP's is_a
	 * @param  [type]  $object_or_string [description]
	 * @param  [type]  $class_name       [description]
	 * @param  boolean $allow_string     [description]
	 * @return boolean                   [description]
	 */
	public function is_a($object_or_string,$class_name,$allow_string=false)
	{
		if (is_object($object_or_string) and !($object_or_string instanceof EmulatorObject))
			return is_a($object_or_string,$class_name) or is_a($object_or_string, $class_name.'_mock');
		if (is_string($object_or_string) and $allow_string!=true) return null;
		if (is_string($object_or_string) and !$this->user_class_exists($object_or_string))
			return is_a($object_or_string,$class_name,true);
		if (is_object($object_or_string))
			$class=$this->get_class($object_or_string);
		else
			$class=$object_or_string;
		foreach ($this->ancestry($class) as $ancestor)
			if (strtolower($ancestor)===strtolower($class_name))
				return true;	
		return false;
	}
	/**
	 * Compatible with PHP's is_subclass_of
	 * @param  [type]  $object_or_string [description]
	 * @param  [type]  $class_name       [description]
	 * @param  boolean $allow_string     [description]
	 * @return boolean                   [description]
	 */
	public function is_subclass_of($object_or_string, $class_name,$allow_string=true)
	{
		if (is_object($object_or_string) and !($object_or_string instanceof EmulatorObject))
			return is_subclass_of($object_or_string,$class_name);
		if (is_string($object_or_string) and $allow_string!=true) return null;
		if (is_string($object_or_string) and !$this->user_class_exists($object_or_string))
			return is_subclass_of($object_or_string,$class_name,true);
		if (is_object($object_or_string))
			$class=$this->get_class($object_or_string);
		else
			$class=$object_or_string;
		
		foreach ($this->ancestry($class) as $ancestor)
			if (strtolower($class)!=strtolower($ancestor) and strtolower($ancestor)===strtolower($class_name))
				return true;	
		return false;
	}

    public function class_implements($class_or_object,$autoload=true)
    {
        $class = $class_or_object;
        if ($autoload) {
            $this->autoload($class);
        }
        if (!is_string($class)) {
            $class = $this->get_class($class_or_object);
        }
        if (class_exists($class) and $class != "EmulatorObject" ) {
            return class_implements($class_or_object, $autoload);
        }
        $interfaces = [];
        foreach ($this->ancestry($class) as $cls) {
            $interfaces = array_merge($interfaces, $this->get_class_object($cls)->interfaces);
        }
        // Merge with parents interfaces
        return array_combine($interfaces, $interfaces);
    }
	/**
	 * Checks whether a symbol table node is visible or not, in current context
	 * @param  $node Node
	 * @return boolean 
	 */
	public function is_visible(Node $node)
	{
		if ($node instanceof Node\Expr\PropertyFetch)
		{
			$base=&$this->symbol_table($node->var,$key2,false);
			if ($key2===null)
				return false;

			if ($base instanceof SymbolicVariable) {
                return true;
            }
			$var=&$base[$key2];
			if ($var instanceof EmulatorObject)
			{
				$property_name=$this->name($node->name);
                if ($property_name instanceof SymbolicVariable) {
                    return true;
                }
				else if (array_key_exists($property_name, $var->properties))
				{
					if (isset($var->property_visibilities[$property_name]))
						$visibility=$var->property_visibilities[$property_name];
					else //dynamic properties are public
						$visibility=EmulatorObject::Visibility_Public;

                    $class = $var->property_class[$property_name] ?? $var->classname;
                    $current_class = $this->current_this->classname ?? $this->current_class;

					return ($visibility==EmulatorObject::Visibility_Public
						   || ($visibility==EmulatorObject::Visibility_Protected && $current_class !== null && ($this->is_a($current_class, $class,true)))
						   || ($visibility==EmulatorObject::Visibility_Private && $current_class !== null && strtolower($current_class) == strtolower($class) )
							);
				}
				elseif ($var->hasProperty($property_name)) {
				    if ($var->isVisible($property_name)) {
				        return true;
                    }
                }
			}
			elseif(is_object($var)) //native object
			{
				$property_name=$this->name($node->name);
				return isset($var->{$property_name});
			}
			return false;
		}
		elseif ($node instanceof Node\Expr\StaticPropertyFetch)
		{
            $classname = $this->name($node->class);
            if ($classname instanceof EmulatorObject)
                $classname = $classname->classname;
            $class_scopes[] = $this->namespaced_name($classname);
		    if (isset($this->current_closure_scope)) {
                $class_scopes[] = $this->current_closure_scope;
            }
            foreach ($class_scopes as $classname) {
                $property_name=$this->name($node->name);
                if ($this->ancestry($classname))
                {
                    foreach($this->ancestry($classname)  as $class)
                    {
                        $class_obj = $this->get_class_object($class);
                        if (array_key_exists($property_name,$class_obj->static))
                        {
                            $visibility = $class_obj->static_visibility[$property_name];
                            return ($visibility==EmulatorObject::Visibility_Public
                                or ($visibility==EmulatorObject::Visibility_Protected and $this->is_a($classname, $class,true)  )
                                or ($visibility==EmulatorObject::Visibility_Private and strtolower($classname)==strtolower($class) )
                            );
                        }
                    }
                }
            }
			return false;
		}
		#TODO: handle method visibility
	}

	protected $magic_method_reentrant=[];
	/**
	 * Handles magic method
	 * @param  [type] $node  [description]
	 * @param  [type] $magic [description]
	 * @param  array  $args  [description]
	 * @return [type]        [description]
	 */
	private function handle_magic($node,$magic,&$result,$args=[])
	{
		if ($node instanceof Node\Expr\PropertyFetch)// and !parent::variable_isset($node))
		{
		    if ($magic === 'get' || $magic === 'set') { // __get and __set are only used when the property is protected/private
                $visibile = $this->is_visible($node);
                if ($visibile === true) { // Do not invoke __get or __set on public class properties
                    return false;
                }
            }
			$base=&$this->symbol_table($node->var,$key2,false);
			if ($key2===null)
				return false;
			if ($base instanceof SymbolicVariable) {
			    $this->verbose(strcolor(sprintf('[Warning] Trying to run magic functions on Symbolic Array Object in %s:%d'.PHP_EOL, $this->current_file, $this->current_line), 'red'));
            }
			$obj=&$base[$key2];
			if ($obj instanceof EmulatorObject)	
				$prop=$this->name($node->name);
			else
				return false;
			if (array_key_exists($prop, $obj->properties) and $this->is_visible($node))
				return false;

			$reentrant_index="{$magic}_{$obj->objectid}";
			if (isset($this->magic_method_reentrant[$reentrant_index]))
				return false; //already inside
			foreach ($this->ancestry($obj->classname) as $class)
				if ($this->user_method_exists($class,"__{$magic}")) //magic_method
				{

					$this->magic_method_reentrant[$reentrant_index]=true;
					$this->verbose("Calling magic method {$class}::__{$magic}() for '{$prop}'...\n",3);
					array_unshift($args,$prop); //add name to args
					$result=$this->run_user_method($obj,"__{$magic}",$args,$class);
					unset($this->magic_method_reentrant[$reentrant_index]);
					return true;
				}
		}	
		return false;
	}

	function variable_set($node,$value=null)
	{
		if ($this->handle_magic($node,"set",$r,[$value])) return $r;
		else return parent::variable_set($node,$value);
	}
	function &variable_reference($node,&$success=null)
	{
		if ($this->handle_magic($node,"get",$r))
		    return $r;
		else
		    return parent::variable_reference($node,$success);
	}
	function variable_get($node)
	{
		if ($this->handle_magic($node,"get",$r)) return $r;
		else return parent::variable_get($node);
	}
	function variable_isset($node)
	{
		if ($this->handle_magic($node,"isset",$r)) return $r;
		else return parent::variable_isset($node);	
	}
	function variable_unset($node)
	{
		if ($this->handle_magic($node,"unset",$r)) return $r;
		else return parent::variable_unset($node);	
	}



}

foreach (glob(__DIR__."/mocks/oo/*.php") as $mock)
	require_once $mock;
unset($mock);
