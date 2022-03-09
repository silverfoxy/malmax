<?php

namespace PHPEmul;

#TODO: isset returns false on null values. Replace with array_key_exists everywhere
#major TODO: do not use recursive function calls in emulator, instead have stacks of operations and have a central
#	function that loops over them and executes them. That way state can be saved and termination and other conditions are easy to control.

// require_once __DIR__."/PHP-Parser/lib/bootstrap.php";
require_once __DIR__."/vendor/autoload.php";

use AnimateDead\Utils;
use malmax\ExecutionMode;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node\Scalar;

require_once "emulator-variables.php";
require_once "emulator-functions.php";
require_once "emulator-errors.php";
require_once "emulator-expression.php";
require_once "emulator-statement.php";
require_once "LineLogger.php";
require_once "ReanimationEntry.php";
require_once "Checkpoint.php";

/**
 * Holds an execution context, used when switching context
 */
class EmulatorExecutionContext
{
    function __construct($arr=[])
    {
        foreach ($arr as $k=>&$v)
            // {
            // if (property_exists($this, $k))
            $this->{$k}=&$v;
        // else
        // throw new \Exception("'{$k}' is not a context variable.");
        // }
    }
    // //available for everything, even includes
    // public $file;
    // public $line;

    // public $namespace;
    // public $active_namespaces;

    // //only available for functions
    // public $function;

    // //only available for [static] methods
    // public $method;
    // public $class; //dynamic class
    // public $self; //static class

    // //only available for bound methods
    // public $this;
}

class Emulator
{
    /**
     * Emulator constructor
     * init the emulator
     */
    function __construct($init_environ=null, $httpverb=null, $predefined_constants=null, $reanimation_callback_object=null, $correlation_id=null)
    {
        $this->state=array_flip(['variables','constants','included_files'
            ,'current_namespace','current_active_namespaces'
            ,'current_file','current_line','current_function'
            ,'output','output_buffer','functions'
            ,'eval_depth','trace','output','break','continue'
            ,'variable_stack'
            ,'try','loop_depth','return','return_value'
            ,'shutdown_functions','terminated'
            ,'execution_context_stack' //all previous contexts, i.e. all current_* vars
            ,'data'
        ]);
        $this->reanimation_callback_object = $reanimation_callback_object;
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->printer = new Standard;
        $this->initenv = $init_environ;
        $this->httpverb = $httpverb;
        $this->init($init_environ, $predefined_constants);
        echo $correlation_id;
        $this->correlation_id = $correlation_id;
        $this->lineLogger = new LineLogger(Utils::$PATH_PREFIX, $this->correlation_id);
        if(!defined('EXECUTED_FROM_PHPUNIT')) {
            define('EXECUTED_FROM_PHPUNIT', false);
        }
    }
    use EmulatorVariables;
    use EmulatorErrors;
    use EmulatorFunctions;
    use EmulatorExpression;
    use EmulatorStatement;

    public array $ast;

    public $execution_id = 0; // Used in the distributed version

    public array $initenv;
    public string $httpverb;
    public string $entry_file;
    // unique execution identifier
    public string $correlation_id;


    public $symbolic_loop_iterations;

    public $child_pids = [];

    public $parent_pid = -1;

    public LineLogger $lineLogger;

    public string $cache_limiter;
    public string $session_name = 'PHPSESSID';

    public \ReanimationState $reanimation_state;

    // Includes the hash and file/line info about forked processes at the time of fork
    public array $fork_info = [];
    // Includes the information about forked processes after they have been terminated
    public array $fork_stats = [];
    // Duplicate line coverage threshold
    public const DUPLICATE_STATE_THRESHOLD = 30;
    // Total number of forks allowed at any line
    public const FORK_THRESHOLD = 30;
    public array $symbolic_parameters = [];
    public array $symbolic_parameters_extended_logs_emulation_mode = [];
    public array $symbolic_functions = [];
    public array $input_sensitive_symbolic_functions = [];
    public array $symbolic_methods = [];
    public array $symbolic_classes = [];
    public array $input_sensitive_symbolic_methods = [];
    public array $function_summaries = [];
    public $mocked_core_function_args = null;
    public bool $is_child = false;
    public bool $diehard = false;
    public int $process_count = 1;

    public array $symbolic_variables = [];

    public int $execution_mode = ExecutionMode::OFFLINE;
    public $reanimation_callback_object;

    public Emulator $last_checkpoint;
    public array $last_checkpoint_ast = [];
    public array $checkpoints = [];
    public bool $checkpoint_restore_mode = false;
    public int $active_checkpoint_node;
    public array $overall_coverage_info = [];

    public bool $immutable_symbolic_variables = true;
    public int $max_output_length = 10000;

    /**
     * If true, the specific child process will be replayed as the parent process
     * This affects the forking procedure of the emulator
     * @var bool
     */
    public bool $reanimate = false;

    /**
     * Holds the "state hash" of places where we forked and went into the child process
     * @var array
     */
    public array $reanimation_transcript = [];

    public array $full_reanimation_transcript = [];

    /**
     * A data storage for use by mock functions and other
     * third parties working on this emulator
     * @var array
     */
    public $data=[];

    /**
     * @var string holds the include path for the application
     */
    public string $include_path;

    /**
     * @var string root directory of the application
     */
    public string $main_directory;

    public $namespaces_enabled=true;
    /**
     * An array that holds all properties that constitute
     * emulation state as keys.
     * Reaedonly
     * @var array
     */
    public $state=[];
    /**
     * Configuration: inifite loop limit
     * @var integer
     */
    public $infinite_loop	=	1000;

    /**
     * Maximum PHP version fully supported by the emulator
     * this is the version that will be returned via phpversion()
     * @var integer
     */
    // public $max_php_version	=	"5.4.45";
    public $max_php_version	=	"7.4.6 ";
    /**
     * Configuration: whether to output directly, or just store it in $output
     * @var boolean
     */
    public $direct_output	=	false;
    /**
     * Configuration: Verbose messaging depth. -1 means no messages, even critical ones
     * @var integer
     */
    public $verbose			=	1;

    /**
     * Whether to stop on all errors or not.
     * @var boolean
     */
    public $strict			= 	false;
    /**
     * Whether to automatically mock functions or not
     * If true, on init emulator will mock all internal php functions with their mocked version.
     * If there's a mocked function (e.g array_walk_mock), emulator will use that instead of the original function
     * Some functions need to be mocked for the emulator to work properly. These are placed in 'mocks/' folder
     * @var boolean
     */
    public $auto_mock		=	true;

    /**
     * Emulator current settings
     * mostly used for error reporting
     * @state
     * @var string
     */
    protected $current_node,$current_statement_index;
    public $current_function,$current_file,$current_line,$magic_function;
    protected $current_namespace="";
    protected $current_class = null;
    protected $current_closure_scope = null;
    protected $current_closure_boundobject = null;
    protected bool $potential_fork_loop = false;
    /**
     * Number of statements executed so far
     * @var integer
     */
    public $statement_count	=	0;

    /**
     * The list of included files. used by *_once include functions as well
     * @state
     * @var array
     */
    public $included_files=[];
    /**
     * The output of the program
     * @state
     * @var string
     */
    public $output;
    /**
     * The output buffer
     * @state
     * @var array
     */
    public $output_buffer=[];

    /**
     * List of super global variables (e.g $GLOBALS, $_GET, etc.)
     * these should be available in all contexts, i.e from all symbol tables
     * keys are the names, values are ints
     * @var array
     */
    public $superglobals=[];

    /**
     * User-defined (emulated) functions
     * @state
     * @var array
     */
    public $functions=[];
    /**
     * User-defined (emulated) constants
     * @state
     * @var array
     */
    public $constants=[];

    /**
     * The parser object used by the emulator.
     * @var [type]
     */
    public $parser;

    /**
     * The depth of eval.
     * Everything eval is used, this is incremented. Allows us to know whether we're inside eval'd code or not.
     * @state
     * @var integer
     */
    public $eval_depth=0;

    /**
     * The variable stack (pushdown)
     * On function calls and new scopes, $variables is pushed on this
     * @state
     * @var array
     */
    public $variable_stack=[];
    /**
     * Whether the application has terminated or not.
     * Used inside the emulator to prevent further execution, e.g when die is used.
     * @state
     * @var boolean
     */
    public $terminated=false;

    public ?string $termination_reason = null;

    /**
     * List of mocked functions.
     * Keys are original functions, values are mocked equivalents
     * @var array
     */
    public $mock_functions=[];

    /**
     * Stack trace.
     * Used to see if we're inside a function or a method or etc.
     * Obeys the structure of debug_backtrace()
     * @state
     * @var array
     */
    public $trace=[];
    /**
     * Holds a list of execution contexts currently active (i.e. stack frames)
     * @var array
     */
    public $execution_context_stack=[];
    /**
     * Number of breaks/continues
     * Whether we still need to break or not
     * For a normal break it becomes 1, and then back to 0 in the loop emulation code
     * @state
     * @var integer
     */
    protected $break=0,$continue=0;
    /**
     * Whether we're inside a try block or not (number of nested tries)
     * @state
     * @var integer
     */
    protected $try=0;
    /**
     * Whether we're in a loop or not (and the number of nested loops)
     * @state
     * @var integer
     */
    protected $loop_depth=0;

    /**
     * Whether return value is available
     * @state
     * @var boolean
     */
    protected $return=false;
    /**
     * The return value
     * @state
     * @var mixed
     */
    protected $return_value=null;

    /**
     * List of functions to run on shuwtdown
     * Each element is an object of callback and args.
     * @state
     * @var array
     */
    public $shutdown_functions=[];

    /**
     * List of class destructors
     * Each element is an EmulatorObject instance that has a __destruct method
     * @var array
     */
    public $destructors = [];
    /**
     * Retains a list of active namespaces via "use" PHP statement
     * does not include the namespace we are in
     * do not use directly, use active_namespaces() instead.
     * @state
     * @var array
     */
    public $current_active_namespaces=[];

    public function get_script_dir() {
        return realpath($this->current_file);
    }

    public function get_real_path($file_name, $is_dir=false) {
        if (substr($file_name, 0, 1) !== '/' && ($is_dir || !file_exists($file_name))) {
            $file_name = $this->main_directory . '/' . $file_name;
        }
        return realpath($file_name);

    }

    public function get_include_path() {
        if (isset($this->include_path)) {
            return $this->include_path;
        }
        else {
            return get_include_path();
        }
    }

    public function get_candidate_files(string $regex)
    {
        /* NOTE:
         * This is redundant, because we keep doing this for every regex script inclusion
         * we should do it once and use it every time reduce performance overhead
         */
        $candidates = array();
        foreach(glob($regex) as $file)
        {
            array_push($candidates, $file);
        }

        return $candidates;
    }
    public function get_include_file_path(string $file_name)
    {
        // If the path is absolute (/ or \ or relative . or ..) include_path is ignored
        if (substr($file_name, 0, 1) === '/' || substr($file_name, 0, 1) === '\\' || substr($file_name, 0, 1) === '.') {
            return realpath($file_name);
        } else {
            // Resolve the file based on include_path
            $include_path = $this->get_include_path();
            $include_path = explode(PATH_SEPARATOR , $include_path);
            $found = false;
            foreach ($include_path as $path) {
                $real_path = $this->get_real_path($path, true);
                $candidate_filename = $real_path . '/' . $file_name;
                if (file_exists($candidate_filename)) {
                    $file_name = $candidate_filename;
                    $found = true;
                    break;
                }
            }
            // If not found in include_path, try current script path and local dir
            if (!$found) {
                $file_candidate =realpath(dirname($this->current_file)."/".$file_name); //first check the directory of the file using include (as per php)
                if (!file_exists($file_candidate) or !is_file($file_candidate)) {
                    //second check current dir
                    $file_candidate = realpath($this->main_directory) . "/" . $file_name;
                }
                // If not found, try current directory
                if (!file_exists($file_candidate) or !is_file($file_candidate)) {
                    $file_candidate = getcwd() . "/" . $file_name;
                }
                $file_name = $file_candidate;
            }
        }
        return $file_name;

    }

    /**
     * Output status messages of the emulator
     * @param  string  $msg
     * @param  integer $verbosity 1 is basic messages, 0 is always shown, higher means less important
     */
    function verbose($msg,$verbosity=1)
    {
        $this->stash_ob(); #don't really need it here, handled at a higher level
        static $lastVerbosity=1;
        static $verbosities=[0];
        $number="";
        if ($verbosity>0)
        {
            if ($verbosity>$lastVerbosity)
                for ($i=0;$i<$verbosity-$lastVerbosity;++$i)
                    $verbosities[]=1;
            else
            {
                if ($verbosity<$lastVerbosity)
                    for ($i=0;$i<$lastVerbosity-$verbosity;++$i)
                        array_pop($verbosities);
                $verbosities[count($verbosities)-1]++;
            }
            $lastVerbosity=$verbosity;
            $number=implode(".",$verbosities);
        }

        if ($this->verbose>=$verbosity)
            echo str_repeat("---",$verbosity)." ".$number." ".sprintf("[%d] ", getmypid()).$msg;
        $this->restore_ob();
    }

    private $isob=false; //TODO: this is part of state
    /**
     * Temporarily disables output buffering.
     * Used by verbose and other functions that need to generate emualtor output
     * @return [type] [description]
     */
    function stash_ob()
    {
        // When the code is executed from phpunit, the ob level is expected to be 1 compared to 0 when executed directly from the cli
        if (EXECUTED_FROM_PHPUNIT) {
            if ($this->execution_mode === ExecutionMode::OFFLINE) {
                // $this->isob=ob_get_level()!=1;
                $this->isob=ob_get_level()!=0;
            }
            elseif ($this->execution_mode === ExecutionMode::ONLINE) {
                if (!$this->is_child) {
                    $this->isob=ob_get_level()!=1;
                }
                else {
                    // $this->isob=false;
                    $this->isob=ob_get_level()!=0;
                }
            }
        }
        else {
            $this->isob=ob_get_level()!=0;
        }
        if ($this->isob)
            $this->output(ob_get_clean());
    }
    function restore_ob()
    {
        if ($this->isob) {
            ob_start();
        }
    }
    /**
     * Initialize the emulator by setting environment variables (super globals)
     * and mocking mock functions
     */
    function init($init_environ, $predefined_constants=null)
    {
        $this->superglobals=array_flip(explode(",$",'_GET,$_POST,$_FILES,$_COOKIE,$_SESSION,$_SERVER,$_REQUEST,$_ENV,$GLOBALS'));
        // echo str_repeat("-",80),PHP_EOL;
        if ($init_environ===null)
        {
            $init_environ=[];

            foreach ($this->superglobals as $k=>$sg)
                if (isset($GLOBALS[$k]))
                    $init_environ[$k]=&$GLOBALS[$k];
                else
                    $init_environ[$k]=[];
            $init_environ['GLOBALS']=&$init_environ;
        }
        $this->variable_stack['global']=array(); //the first key in var_stack is the global scope
        $this->reference_variables_to_stack();
        foreach ($init_environ as $k=>$v) {

            if ($k === '_SESSION') {
                foreach (array_keys($v) as $key) {
                    $v[$key] = new SymbolicVariable('', '*', NodeAbstract::class, true);
                }
            }
            elseif ($k === '_COOKIE') {
                foreach (array_keys($v) as $key) {
                    $v[$key] = new SymbolicVariable('', '*', Node\Scalar\String_::class, true);
                }
            }
            elseif ($k === '_POST') {
                foreach (array_keys($v) as $key) {
                    $v[$key] = new SymbolicVariable('', '*', Node\Scalar\String_::class, true);
                }
            }
            elseif ($k === '_REQUEST') {
                foreach (array_keys($v) as $key) {
                    if (!array_key_exists($key, $init_environ['_GET']) && array_key_exists($key, $init_environ['_POST']) || array_key_exists($key, $init_environ['_COOKIE'])) {
                        $v[$key] = new SymbolicVariable('', '*', Node\Scalar\String_::class, true);
                    }
                    elseif (!array_key_exists($key, $init_environ['_GET']) && array_key_exists($key, $init_environ['_SESSION'])) {
                        $v[$key] = new SymbolicVariable('', '*', NodeAbstract::class, true);
                    }
                }
            }
            elseif ($k === '_FILES') {
                foreach ($v as $key => $value) {
                    if (($file_upload = realpath(Utils::get_current_dir() . 'uploaded_files/' . $value)) !== false) {
                        $temp_file = '/tmp/php' .  substr(md5(rand()), 0, 6);
                        $file_type = pathinfo($file_upload, PATHINFO_EXTENSION);
                        copy($file_upload, $temp_file);
                        $file_size = filesize($temp_file);
                        $v[$key] = ['name' => $value,
                            'type' => $file_type,
                            'tmp_name' => $temp_file,
                            'error' => 0,
                            'size' => $file_size];
                    }
                    else {
                        $v[$key] = ['name' => new SymbolicVariable('name', '*', Node\Scalar\String_::class, true),
                            'type' => new SymbolicVariable('name', '*', Node\Scalar\String_::class, true),
                            'tmp_name' => '/tmp/php' .  substr(md5(rand()), 0, 6),
                            'error' => 0,
                            'size' => new SymbolicVariable('name', '*', Node\Scalar\LNumber::class, true)];
                    }
                }
            }

            $this->variables[$k] = $v;
        }
        $this->variables['GLOBALS'] = &$this->variables; //as done by PHP itself
        if ($this->auto_mock) {
            foreach (get_defined_functions()['internal'] as $function) //get_defined_functions gives 'internal' and 'user' subarrays.
            {
                if (function_exists($function . "_mock"))
                    $this->mock_functions[strtolower($function)] = $function . "_mock";
            }
        }
        // Set the predefined constants
        if (isset($predefined_constants)) {
            $this->constants = $predefined_constants;
        }
    }
    protected function destruct()
    {
        $this->verbose('Running class destructors...'.PHP_EOL);
        foreach ($this->destructors as $obj_with_destructor) {
            $class_index = strtolower($obj_with_destructor->classname);
            foreach ($this->ancestry($class_index) as $class) //find the first available destructor
            {
                if ($this->user_method_exists($class,"__destruct"))
                    $destructor=array($class,"__destruct");
                if (isset($destructor)) break;
            }
            $this->run_user_method($obj_with_destructor, $destructor[1], [], $destructor[0]);
        }
    }
    /**
     * Called after execution finished
     * Runs shutdown functions
     */
    public function shutdown()
    {
        $this->verbose("Shutting down...".PHP_EOL);
        // $this->decrement_pid_count();
        $bu=$this->terminated;
        $this->terminated=false;
        foreach ($this->shutdown_functions as $shutdown_function)
        {
            if (is_array($shutdown_function->callback))
                if ($shutdown_function->callback[0] instanceof EmulatorObject)
                    $name=$shutdown_function->callback[0]->classname."->".$shutdown_function->callback[1];
                else
                    $name=implode("::",$shutdown_function->callback);
            else {
                $name = $shutdown_function->callback;
                if ($name instanceof EmulatorClosure) {
                    $name = 'Closure';
                }
            }
            $this->verbose( "Calling shutdown function: {$name}()\n");
            $this->call_function($shutdown_function->callback,$shutdown_function->args);
        }
        $this->terminated=$bu;
        $r="";
        if (count($this->output_buffer))
        {
            $this->verbose("Dumping buffered output.".PHP_EOL);
            while (count($this->output_buffer))
                $r.=array_shift($this->output_buffer);
            $this->output($r);
        }
        $this->verbose("Dumping reanimation logs.".PHP_EOL);
        if (!$this->reanimate) {
            Utils::append_reanimation_log($this->correlation_id, $this->full_reanimation_transcript);
        }
        // Sync the latest coverage and fork information
        // $data = ['fork_info' => $this->fork_info, 'fork_stats' => $this->fork_stats, 'line_coverage' => $this->lineLogger->coverage_info, 'output' => $this->output];
        // $data = ['fork_info' => [], 'line_coverage' => [], 'output' => $this->output];
        // $this->verbose("Updating fork info.".PHP_EOL);
        // $this->update_shared_coverage_info();
        $time_elapsed_secs = microtime(true) - $this->start_time;
        $this->verbose(sprintf("Execution time: %d seconds.".PHP_EOL, $time_elapsed_secs));
    }

    /**
     * Outputs the args
     * This is equal to calling echo from PHP
     * @return [type] [description]
     */
    function output()
    {
        $args=func_get_args();
        $data=implode("",$args);
        Utils::log_output(getmypid(), $data);
        if (count($this->output_buffer)) {
            $this->output_buffer[0] .= $data; #FIXME: shouldn't this be -1 instead of 0? i.e the one to the last nesting?
            if ($this->max_output_length !== false && strlen($this->output_buffer[0]) > $this->max_output_length) {
                $this->output_buffer[0] = substr($this->output_buffer[0], -$this->max_output_length);
            }
        }
        else
        {
            $this->output.=$data;
            if ($this->max_output_length !== false && strlen($this->output) > $this->max_output_length) {
                $this->output = substr($this->output, -$this->max_output_length);
            }
            if ($this->direct_output) {
                echo $data;
            }
        }
    }
    /**
     * Push current variables on var stack
     */
    protected function push()
    {
        array_push($this->variable_stack,array()); //create one more symbol table
        $this->reference_variables_to_stack();
    }
    /**
     * References $this->variables to the top of the variable_stack, instead of copying it
     */
    private function reference_variables_to_stack()
    {
        unset($this->variables);
        end($this->variable_stack);
        $this->variables=&$this->variable_stack[key($this->variable_stack)];
    }
    /**
     * Pop off the variable stack
     */
    protected function pop()
    {
        array_pop($this->variable_stack);
        // end($this->variable_stack);
        // unset($this->variable_stack[key($this->variable_stack)]);
        $this->reference_variables_to_stack();
    }

    /**
     * Returns the base array (symbol table) that the variable exists in, as well as the key in that array for the variable
     *
     *
     * @param  Node  $node
     * @param  byref  &$key  the key of the element, which will be null if not found
     * @param  boolean $create
     * @return reference          reference to the symbol table array (check key first before accessing this)
     */
    protected function &symbol_table($node,&$key,$create)
    {
        if ($node===null)
        {
            $this->notice("Undefined variable (null node).");
            return $this->null_reference($key);
        }
        elseif (is_string($node))
        {
            if (array_key_exists($node, $this->variables))
            {
                $key=$node;
                return $this->variables;
            }
            elseif ($node == "GLOBALS")
            {
                $key='global';
                return $this->variable_stack;
            }
            elseif (array_key_exists($node, $this->superglobals) //super globals
                and isset($this->variable_stack['global'][$node])) //can be deleted too!
            {
                $key=$node;
                return $this->variable_stack['global'];
            }
            else
            {
                if ($create)
                {
                    $key=$node;
                    $this->variables[$key]=null;
                    return $this->variables;
                }
                else
                {
                    if (!in_array($node, $this->symbolic_parameters)) {
                        $this->notice("Undefined variable: {$node}");
                    }
                    return $this->null_reference($key);
                }
            }
        }
        elseif ($node instanceof Node\Scalar\Encapsed)
        {
            $key=$this->name($node);
            if ($create)
                $this->variables[$key]=null;
            return $this->variables;
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
            $base=&$this->symbol_table($t,$key2,$create);
            if ($key2===null) //the base arrayDimFetch variable not exists, e.g $a in $a[1][2]
                return $this->null_reference($key);
            $base=&$base[$key2];
            $key=array_pop($indexes);
            if (is_string($base) and empty($indexes) and is_int($key)) //string arraydimfetch access
                return $base; //already done
            if ($base === "" && in_array($key2, $this->symbolic_parameters))
                return new SymbolicVariable();
            if (is_scalar($base)) //arraydimfetch on scalar returns null
                return $this->null_reference($key);
            if ($base instanceof SymbolicVariable) {
                return $base;
            }
            if (is_object($base) and !in_array('ArrayAccess', $this->class_implements($base)))
            {
                if ($base instanceof EmulatorObject)
                    $type=$base->classname;
                else
                    $type=get_class($base);
                $this->error("Cannot use object of type {$type} as array");
                return $this->null_reference($key);
            }
            // if (is_null($base))
            // 	return $this->null_reference($key);
            foreach ($indexes as $index)
            {
                if ($index===NULL)
                {
                    $base[]=NULL; //append to base array
                    end($base);	//move the pointer to end of base array
                    $index=key($base); //retrieve the key as index
                }
                elseif ($index instanceof SymbolicVariable) {
                    $this->verbose('Creating a Symbolic Index (Not supported)'.PHP_EOL);
                    return new SymbolicVariable('Symbolic Index', '*', NodeAbstract::class, new SymbolicVariable(), $base);
                }
                elseif ($base instanceof SymbolicVariable) {
                    if ($create)
                    {
                        // Temporarily enable creating indexes within symbolic arrays
                        // This would overwrite the array to concrete so may produce side effects
                        // eg: $_SESSION['cache'] = Symbolic, $_SESSION['cache']['server_1'] = '123';
                        // $this->warning('Creating an index within a Symbolic Array (Not supported)'.PHP_EOL);
                        // $symbolic_arr_dim = new SymbolicVariable('SymbolicVar for '. $key);
                        // return $symbolic_arr_dim;
                        $base = [$index => null];
                    }
                    else {
                        $symbolic_arr_dim = new SymbolicVariable('SymbolicVar for '. $key);
                        return $symbolic_arr_dim;
                    }
                }
                elseif (!isset($base[$index]))
                    if ($create)
                    {
                        $this->verbose("Creating array index '{$index}'...".PHP_EOL,5);
                        if(!is_int($index) && !is_string($index)) {
                            $this->verbose(print_r($index, true).PHP_EOL);
                        }
                        $base[$index]=null;
                    }
                    else
                        return $this->null_reference($key);

                $base=&$base[$index];
            }
            if ($create) {
                if ($key instanceof SymbolicVariable) {
                    $key = $key->variable_name;
                }
                if ($key===null) #$a[...][...][]=x //add mode
                {
                    $base[]=null;
                    end($base);
                    $key=key($base);
                }
                elseif (!$base instanceof SymbolicVariable && !isset($base[$key])) //non-existent index
                    $base[$key] = null;
            }

            // Prevents setting concrete values to symbolic variables
            // Always return a symbol
            // $this->verbose(print_r($base, true).PHP_EOL);
            // $this->verbose(print_r($key, true).PHP_EOL);
            if ($key instanceof SymbolicVariable // Fetching a symbolic key returns a SymbolicVariable
                || !$create &&
                            ((in_array(strval($key), $this->symbolic_parameters)) // Not in create mode, and fetching a symbolic parameter returns a SymbolicVariable
                            || (in_array(strval($key2), $this->symbolic_parameters)
                                && (!$this->immutable_symbolic_variables && !array_key_exists($key, $base) && !$this->extended_logs_emulation_mode))
                            || ($this->extended_logs_emulation_mode && in_array($key2, $this->symbolic_parameters_extended_logs_emulation_mode) && array_key_exists($key, $base) && $base[$key] instanceof SymbolicVariable))) {

                /*
                 * If the base is a symbolic parameter (e.g. $_POST)
                 * and we are not creating an entry,
                 * and the key doesn't exist in base,
                 * and we are not in extended_logs_emulation_mode
                 * return SymbolicVariable
                 */
                /*
                 * For Concrete arrays and Symbolic keys, perform regex matching
                 */
                if ($key instanceof SymbolicVariable && !$base instanceof SymbolicVariable) {
                    $matched_elements = $this->regex_array_fetch($base, $key->variable_value);
                    if (is_array($base) && sizeof($matched_elements) === sizeof($base)) {
                        // Regex matched all elements
                        $dbg = 1;
                    }
                    elseif (sizeof($matched_elements) > 0) {
                        // Regex matched some elements
                        $dbg = 1;
                    }
                    else {
                        // Regex matched no elements
                        $key = null;
                        return $base;
                    }
                    $dbg = 1;

                }
                return new SymbolicVariable(sprintf('%s[%s]', $this->get_variableـname($node->var), $key), '*', Scalar::class, true, $matched_elements);
            }
            // else {
            //     $this->notice(sprintf('Undefined index: %s[%s] at [%s:%s]', $node->var->name, $key, $this->current_file, $this->current_line));
            // }
            return $base;
        }
        elseif ($node instanceof Node\Expr\Variable)
        {
            $node_name = $node->name;
            $current_node = $node;
            while ($node_name instanceof Node\Expr\Variable) {
                // Handle variable variable
                $node_name = $this->evaluate_expression($current_node->name);
                $current_node = $current_node->name;
            }
            return $this->symbol_table($node_name,$key,$create);
        }
        elseif ($node instanceof Node\Expr\ArrayItem) {
            if (! ($node->value instanceof Node\Expr\PropertyFetch
                    || $node->value instanceof Node\Expr\StaticPropertyFetch
                    || $node->value instanceof Node\Expr\ArrayDimFetch)) {
                return $this->symbol_table($node->value->name, $key, $create);
            } else {
                return $this->symbol_table($node->value, $key, $create);
            }

        }
        elseif ($node instanceof Node\Expr)
        {
            #TODO: temporary variable for symbol table to return... think of a workaround?
            #Fatal error: Can't use function return value in write context
            $hack=array('temp'=>$this->evaluate_expression($node));
            $key='temp';
            return $hack;
        }
        else
        {
            $this->error("Can not find variable reference of this node type.",$node);
            return $this->null_reference($key);
        }
    }
    /**
     * Removes ** with * in regex
     * @param $regex
     * @return void
     */
    function summarize_regex($regex, $prepare_preg_replace=false) {
        $chars = str_split($regex);
        $summarized_regex = '';
        $asterisk_last = false;
        foreach ($chars as $char) {
            if ($char !== '*') {
                $asterisk_last = false;
                $summarized_regex .= $char;
            }
            else if ($asterisk_last === false) {
                $asterisk_last = true;
                // Convert * to .* for preg_replace
                if ($prepare_preg_replace === true) {
                    $summarized_regex .= '.';
                }
                $summarized_regex .= $char;
            }
            // Skipping duplicate asterisks
        }
        if ($prepare_preg_replace) {
            $summarized_regex = '/^' . str_replace('\.\*', '.*', preg_quote($summarized_regex)) . '$/';
        }
        return $summarized_regex;
    }

    /**
     * Returns the elements in $array that match the $regex
     * @param $array
     * @param $regex
     * @return array
     */
    function regex_array_fetch($array, $regex): ?array
    {
        if (!is_array($array)) {
            return [];
        }
        $regex = $this->summarize_regex($regex, true);
        $matched_elements = [];
        foreach ($array as $key => $value) {
            if (preg_match($regex, strval($key)) === 1) {
                $matched_elements[$key] = $value;
            }
        }
        return $matched_elements;
    }
    /**
     * Resolves symbol name
     * e.g function calls, variables, etc.
     * @param  Node $ast
     * @return string      name
     */
    protected function name($ast)
    {
        /**
         * namespaced names have a name that has an array of parts.
         * however, if it is FullyQualified (Node\Name\FullyQualified)
         * it will only have parts
         */

        if (is_string($ast))
            return $ast;
        elseif ($ast instanceof Node\Expr\FuncCall)
        {
            if ($ast->name instanceof Node\Name\FullyQualified) {
                return $this->name($ast->name);
            }
            elseif (is_string($ast->name) or $ast->name instanceof Node\Name) {
                return $this->name($ast->name);
            }
            else {
                return $this->evaluate_expression($ast->name);
            }
        }
        elseif ($ast instanceof Node\Scalar\Encapsed)
        {
            $res="";
            foreach ($ast->parts as $part)
                if (is_string($part))
                    $res.=$part;
                else
                    $res.=$this->evaluate_expression($part);
            return $res;
        }
        elseif ($ast instanceof Node\Scalar)
            return $ast->value;
        elseif ($ast instanceof Node\Param)
            return $ast->name ?? $ast->var->name;
        elseif ($ast instanceof Node\Stmt\Namespace_
        )
        {
            if ($ast->name===null)
                return "";
            else
                return $this->name($ast->name);
        }
        // elseif ($ast instanceof Node\Name\FullyQualified)
        // {
        // 	return "\\".implode("\\",$ast->parts);
        // }
        // elseif ($ast instanceof Node\Name\Relative)
        // {
        // 	return $this->fully_qualify_name(implode("\\",$ast->parts));
        // }
        elseif ($ast instanceof Node\Name)
        {
            //compound name (of any kind), e.g variable, function, class
            $res=[];
            foreach ($ast->parts as $part)
            {
                if (is_string($part))
                    $res[]=$part;
                else
                    $res[]=$this->evaluate_expression($part);
            }
            return implode("\\",$res);
        }
        elseif ($ast instanceof Node\Expr\Variable)
            return $this->evaluate_expression($ast);
        elseif ($ast instanceof Node\Expr) //name can be any expr..., or can it?
            return $this->evaluate_expression($ast);
        elseif ($ast instanceof Node\Identifier)
            return $ast->name;
        else
            $this->error("Can not determine name: ",$ast);
    }
    /**
     * Used on names that can be a namespace
     * @param  Node|String $node
     * @return string
     */
    function namespaced_name($node)
    {
        // Exclude self / parent / etc.
        if (is_string($node)) {
            if (in_array($node, ['self', 'static', 'parent'])) {
                return $node;
            }
            return $this->fully_qualify_name($node);
        }
        elseif ($node instanceof Node\Name\FullyQualified)
            return implode("\\",$node->parts);
        elseif ($node instanceof Node\Name) // or $node instanceof Node\Name\Relative)
            return $this->fully_qualify_name(implode("\\",$node->parts));
        else
            return $this->name($node);
    }
    /**
     * Returns the fully qualified namespace name associated with a relative/full/base name
     * A fully qualified namespace starts with \
     * @param  string $name name, can be either simple or relative or fully qualified namespaced name
     * @return string
     */
    protected function fully_qualify_name($name)
    {
        // If namespaces are not enabled
        // or name is already fully qualified (starts with \)
        if (!$this->namespaces_enabled || substr($name, 0, 1) === '\\') {
            return $name;
        }
        // if ($name[0]=="\\")
        // {
        // 	// $this->notice("FQ called on an already FQ name!")	;
        // 	return $name; //fully qualified
        // }
        $this->verbose("Resolving relative name '{$name}'...\n",5);
        // $this->verbose("Resolving relative name '{$name}' to fully qualified name...\n",5);
        $parts=explode("\\",$name);
        if (!isset($this->current_active_namespaces[strtolower($parts[0])])) {//no alias
            return $this->current_namespace($name);
        }
        $parts[0]=$this->current_active_namespaces[strtolower($parts[0])];
        return "".implode("\\",$parts);
    }
    /**
     * Namespaces quirk:
     * There are 3 types of names, normal, Fully Qualified (starting with \) and Relative (possibly having \).
     * The latter 2 are automatically resolved by name() function.
     * Only the first can be used in definitions, but all three can be used in referencing a symbol (class, function, const)
     * However, the important thing is that a normal name can be a relative name.
     * For example:
     * namespace X1\X2 {
     * 	class X{};
     * }
     * namespace {
     * 	use X1\X2\X;
     * 	$o=new X;
     * }
     *
     * X in the last line is a normal name, but is a relative namespace and needs to be resolved.
     * For classes, call to fully_qualify_name is forced.
     * For constants and functions, first current_namespace+name is checked, then name itself (name is always resolved).
     * For classes, only name is checked.
     */
    /**
     * Returns a name in the current namespace
     * @param  string $name symbol
     * @return string symbol in namespace
     */
    function current_namespace($name)
    {
        if (!$this->namespaces_enabled) return $name;
        if ($this->current_namespace)
            return "".$this->current_namespace."\\".$name;
        else
            return "".$name;
    }
    /**
     * Runs a PHP file
     * Basically it sets up current file and other state variables, reads the file,
     * parses it and passes the AST to run_code
     * @param  string $file
     * @return mixed
     */
    public function run_file($file, $main_file = false)
    {
        // $last_file=$this->current_file;
        $realfile=realpath($file);
        foreach (explode(":",get_include_path()) as $path)
        {
            if (file_exists($path."/{$file}"))
            {
                $realfile=realpath($path."/{$file}");
                break;
            }
        }
        // $this->current_file=$realfile;
        $tfolder=dirname($realfile)."/";
        if (strlen($this->folder)>strlen($tfolder))
            if (substr($this->folder,0,strlen($tfolder))===$tfolder)
                $this->folder=$tfolder;
            else
                $this->folder=dirname($tfolder);
        if(!isset($this->main_directory)) {
            $this->main_directory = $this->folder.'/';
        }
        //resetting namespace
        $context=new EmulatorExecutionContext;
        $context->namespace="";
        $context->active_namespaces=[];
        $context->file=$realfile;
        $context->line=1;
        $this->context_switch($context);

        $this->verbose(strcolor(sprintf("Now running %s...\n", $this->current_file), "light green"));
        $this->included_files[$this->current_file]=true;
        $this->ast = $this->parse($file);
        $res = $this->run_code($this->ast);
        $this->verbose(strcolor(sprintf("%s finished.".PHP_EOL, $this->current_file), "light green"));

        $this->context_restore();

        if ($this->return)
            $this->return=false;

        return $res;
    }
    /**
     * Starts the emulation
     * A php file should be given here
     * Current directory and other variables are set up here
     * @param  string  $file
     * @param  boolean $chdir whether to change dir to the file's location or not
     * @return mixed
     */
    function start($file,$chdir=true)
    {
        $this->start_time = microtime(true);
        $this->entry_file=realpath($file);
        if (!$this->entry_file)
        {
            $this->verbose("File not found '{$file}'.".PHP_EOL,0);
            return false;
        }
        $this->original_dir=getcwd();
        $this->folder=dirname($this->entry_file);
        chdir(dirname($this->entry_file));
        // $file=basename($this->entry_file);
        ini_set("memory_limit",-1);
        // set_error_handler(array($this,"error_handler"));
        // set_exception_handler(array($this,"exception_handler")); //exception handlers can not return. they terminate the program.
        $res=$this->run_file($this->entry_file, true);
        if(!isset($this->termination_reason)) {
            $this->termination_reason = 'Finished running the script';
        }
        $this->destruct();
        $this->shutdown();
        // restore_exception_handler();
        if (EXECUTED_FROM_PHPUNIT) {
            if (ob_get_level() === 0) {
                ob_start();
            }
            else {
                while (ob_get_level() > 1) {
                    ob_get_clean();
                }
            }
        }
        restore_error_handler();
        chdir($this->original_dir);
        return $res;
    }

    /**
     * Extracts declarations in AST node
     * Constants and function definitions are extracted before code is executed
     * @param  Node $node
     */
    protected function get_declarations($node)
    {
        if (0);
        elseif ($node instanceof Node\Stmt\Namespace_)
        {
            if (!$this->namespaces_enabled)
                $this->error("Namespace support is disabled. Please enabled it in the emulator and rerun");
            $this->current_namespace=$this->name($node);
            $this->verbose("Extracting declarations of namespace '{$this->current_namespace}'...\n",2);
            foreach ($node->stmts as $stmt)
                $this->get_declarations($stmt);
            $this->current_namespace="";

        }
        elseif ($node instanceof Node\Stmt\Use_)
        {
            if ($node->type!==1)
                #TODO:
                $this->error("'use function/const' is not yet supported. Only 'use namespace' supported so far.");
            foreach ($node->uses as $use)
            {
                $alias=$use->alias ?? end($use->name->parts);
                $name=$this->name($use->name);
                $this->verbose("Aliasing namespace '{$name}' to '{$alias}'.\n",3);
                if (array_key_exists(strtolower($alias),$this->current_active_namespaces))
                    $this->error("Cannot use {$name} as {$alias} because the name is already in use");
                $this->current_active_namespaces[strtolower($alias)]=$name;
            }
        }
        elseif ($node instanceof Node\Stmt\Function_)
        {
            $name=$this->current_namespace($this->name($node->name));
            $index=strtolower($name);
            $context=new EmulatorExecutionContext(['function'=>$name,'file'=>$this->current_file,'namespace'=>$this->current_namespace,'active_namespaces'=>$this->current_active_namespaces]);
            $this->functions[$index]=(object)array("params"=>$node->params,"code"=>$node->stmts,'context'=>$context,'statics'=>[]);

        }

    }
    /**
     * Returns only file name of a full path, for messaging
     * @param  string $file
     * @return string
     */
    private function filename_only($file=null)
    {
        if ($file===null)
            $file=$this->current_file;
        return substr($file,strlen($this->folder));
    }
    public function run_ast($save_checkpoint=true, $continue_from=null) {
        //first pass, get all definitions
        foreach ($this->ast as $node)
            $this->get_declarations($node);
        while ($node = array_pop($this->ast)) {
            if (isset($continue_from)) {
                // Skip nodes until we reach our checkpoint
                if ($node !== $this->active_checkpoint_node) {
                    continue;
                }
                else {
                    $continue_from = null;
                }
            }
            if ($node->getLine()!=$this->current_line)
            {
                $this->current_line=$node->getLine();
                if ($this->verbose)
                    $this->verbose(sprintf("%s:%d\n",$this->filename_only(),$this->current_line),3);
            }
            $this->statement_count++;
            // Log line coverage information
            try
            {
                $node_type = get_class($node);
                $this->lineLogger->logNodeCoverage($node, $this->current_file);
                if ($save_checkpoint) {
                    $this->store_checkpoint_context($this->ast, $node);
                }
                $this->run_statement($node);
            }
            catch (Exception $e)
            {
                $this->throw_exception($e);
            }
            catch (Error $e) //php 7. fortunately, even though Error is not a class, this will not err in PHP 5
            {
                // throw $e;
                // $this->throw_exception($e); //should be throw_error, throw_exception relies on type
                $this->exception_handler($e);
            }
            if ($this->terminated) return null;
            if ($this->return) return $this->return_value;
            if ($this->break) break;
            if ($this->continue) break;

            $this->current_statement_index=null;
        }
    }
    /**
     * Runs an AST as code.
     * It basically loops over statements and runs them.
     * @param  Node $ast
     */
    public function run_code($ast)
    {
        //first pass, get all definitions
        foreach ($ast as $node)
            $this->get_declarations($node);
        foreach ($ast as $index=>$node)
        {
            if ($this->execution_mode === ExecutionMode::OFFLINE) {
                $this->current_statement_index=$index;
                if (isset($continue_from)) {
                    if ($index !== $this->active_checkpoint_node) {
                        continue;
                    }
                    else {
                        $continue_from = null;
                    }
                }
            }
            //if ($this->current_file == "/storage/animateDead/distributed_animate_dead/php/debloating_templates/4.6/wp-includes/template-loader.php" && $node->getLine() == 74) {
            //   echo "HERE\n";
            //}

            if ($node->getLine()!=$this->current_line)
            {
                $this->current_line=$node->getLine();

                if ($this->verbose)
                    $this->verbose(sprintf("%s:%d\n",$this->filename_only(),$this->current_line),3);
            }
            $this->statement_count++;
            // Log line coverage information
            try
            {
                $node_type = get_class($node);
                $this->lineLogger->logNodeCoverage($node, $this->current_file);
                if ($this->execution_mode === ExecutionMode::OFFLINE) {
                    $this->store_checkpoint_context($ast, $index);
                }
                $this->run_statement($node);
            }
            catch (\Exception $e)
            {
                $this->throw_exception($e);
            }
            catch (\Error $e) //php 7. fortunately, even though Error is not a class, this will not err in PHP 5
            {
                // throw $e;
                // $this->throw_exception($e); //should be throw_error, throw_exception relies on type
                $this->exception_handler($e);
            }
            if ($this->terminated) {
                return null;
            }
            if ($this->return) {
                return $this->return_value;
            }
            if ($this->break) {
                break;
            }
            if ($this->continue) {
                break;
            }

            $this->current_statement_index=null;
        }
    }

    /**
     * Returns the AST of a PHP file to emulator
     * attempts to cache the AST and re-use
     * @param  string $file
     * @return array
     */
    protected function parse($file)
    {
        #TODO: decide much better memory on single-file unserialize, runtime overhead is about 3-4 times
        #350 mb vs 650 mb (baseline 250 mb), 12s (8 unserialize) vs 7s (3.5 unserialize+gc) (baseline 27s)
        #maybe cache a few files? because the single file cache grows over time
        $mtime=filemtime($file);
        $md5=md5($file);
        $cache_file=__DIR__."/cache/parsetree-{$md5}-{$mtime}";
        if (file_exists($cache_file))
            $ast=unserialize(gzuncompress(file_get_contents($cache_file)));
        else
        {
            $code=file_get_contents($file);
            $ast=$this->parser->parse($code);
            if (!file_exists(__DIR__."/cache")) @mkdir(__DIR__."/cache");
            if (is_writable(__DIR__."/cache"))
                file_put_contents($cache_file,gzcompress(serialize($ast)));
            else
                $this->verbose("WARNING: Can not write to cache folder, caching disabled (performance degradation).\n",0);
        }
        return $ast;
    }

    /**
     * Converts an AST to printable PHP code.
     * @param  Node|Array $ast
     * @return string
     */
    function print_ast($ast)
    {
        if (!is_array($ast))
            $ast=[$ast];
        return $this->printer->prettyPrint($ast);
    }
    /**
     * Emulator destructor
     */
    function __destruct()
    {
        $this->verbose(sprintf("Memory usage: %.2fMB (%.2fMB)\n",memory_get_usage()/1024.0/1024.0,memory_get_peak_usage()/1024.0/1024.0));
        $line_cov_size = 0;
        foreach ($this->lineLogger->coverage_info as $file => $covered_lines) {
            $line_cov_size += count($covered_lines);
        }
        $fork_size = 0;
        foreach ($this->fork_info as $file => $forked_lines) {
            $fork_size += count($forked_lines);
        }
        $this->verbose(sprintf('Coverage info: %s, Fork info: %s, Output: %s, Reanimation logs: %s'.PHP_EOL, $line_cov_size, $fork_size, strlen($this->output), count($this->reanimation_transcript)));
    }

    protected function cleanup_shared_memory() {
        $this->read_shmop(-1, true);
        $this->read_shmop(-2, true);
    }

    protected function update_shared_coverage_info(bool $only_read=false) {
        $semaphore = sem_get(-1);
        if (!$semaphore) {
            $this->error('Failed to create the semaphore'.PHP_EOL);
        }
        sem_acquire($semaphore);
        $coverage_info = $this->read_shmop(-1, !$only_read);
        if ($coverage_info) {
            $info = unserialize($coverage_info, [false]);
            // $this->verbose(print_r($info, true).PHP_EOL);
            // Merge fork info
            $this->merge_fork_info($info['fork_info']);
            // Merge coverage info
            $this->merge_line_coverage($info['line_coverage']);
            // Merge output
            $this->merge_output($info['output']);
        }
        if (!$only_read) {
            // $this->verbose('Updating shared fork info: '.PHP_EOL.print_r($this->fork_info, true).PHP_EOL);
            $data = ['fork_info' => $this->fork_info, 'line_coverage' => $this->lineLogger->coverage_info, 'output' => $this->output];
            $this->write_shmop(-1, serialize($data));
        }
        sem_release($semaphore);
    }

    function read_shmop($id, $delete_shmop=true) {
        // @ to suppress the warning when shmem does not exist
        $original_error_handler = set_error_handler(function() { return true; });
        @$shm_id = shmop_open($id, "a", 0, 0);
        set_error_handler($original_error_handler);
        if(!$shm_id) {
            // echo 'Failed to read shmem'.PHP_EOL;
            $this->verbose(strcolor('Failed to read shmem '.$id.PHP_EOL, 'red'));
            return false;
        }
        // else {
        //     $this->verbose(strcolor('Successfully read shmem '.$id.PHP_EOL, 'red'));
        // }
        $sh_data = shmop_read($shm_id, 0, shmop_size($shm_id));
        if ($delete_shmop) {
            $this->verbose(strcolor('Deleting shmem '.$id.PHP_EOL, 'red'));
            // Set child pid closed = true
            $this->child_pids[$id] = true;
            shmop_delete($shm_id);
            shmop_close($shm_id);
        }
        return $sh_data;
    }

    function get_pid_count() {
        $pid_count = intval($this->read_shmop(-2, false));
        return $pid_count;
    }

    function increment_pid_count() {
        $semaphore = sem_get(-2);
        if (!$semaphore) {
            $this->error('Failed to create the semaphore'.PHP_EOL);
        }
        sem_acquire($semaphore);
        $pid_count = $this->read_shmop(-2, false);
        if ($pid_count === false) {
            // Create the shared memory if it does not exist
            $this->write_shmop(-2, '2', true);
            $this->verbose('New pid count: 2'.PHP_EOL);
            sem_release($semaphore);
            return 2;
        }
        else {
            $pid_count = intval($pid_count);
        }
        $pid_count++;
        $this->verbose('New pid count: '.$pid_count.PHP_EOL);
        $this->write_shmop(-2, strval($pid_count), false);
        sem_release($semaphore);
        return $pid_count;
    }

    function decrement_pid_count() {
        // Make sure we have forked when we get here
        // sometimes its the only pid shutting down.
        if (count($this->fork_info) === 0) {
            return 0;
        }
        $semaphore = sem_get(-2);
        if (!$semaphore) {
            $this->error('Failed to create the semaphore'.PHP_EOL);
        }
        sem_acquire($semaphore);
        $pid_count = $this->read_shmop(-2, false);
        if ($pid_count === false) {
            sem_release($semaphore);
            $this->error('PID count returned false');
        }
        else {
            $pid_count = intval($pid_count);
        }
        $pid_count--;
        $this->write_shmop(-2, strval($pid_count), false);
        $this->verbose('Decrementing PID to '.$pid_count.PHP_EOL);
        sem_release($semaphore);
        return $pid_count;
    }

    function write_shmop($id, string $data, $create_shmop=true) {
        if ($create_shmop) {
            $shm_id = shmop_open($id, "c", 0644, strlen($data));
        }
        else {
            $shm_id = shmop_open($id, "w", 0644, strlen($data));
        }
        if (!$shm_id) {
            $this->verbose(strcolor('Couldn\'t create shared memory segment '.$id.PHP_EOL, 'red'));
        } else {
            // if(shmop_write($shm_id, $data, 0) != strlen($data)) {
            if(shmop_write($shm_id, $data, 0) === false) {
                $this->verbose(strcolor('Couldn\'t create shared memory segment '.$id.PHP_EOL, 'red'));
            }
            else {
                $this->verbose(strcolor('Writing to shared memory segment '.$id.PHP_EOL, 'red'));
            }
        }
    }

    function merge_line_coverage($coverage_info, $existing_coverage=false) {
        if (!isset($coverage_info)) {
            return $existing_coverage;
        }
        foreach ($coverage_info as $filename => $lines) {
            // $this->verbose(print_r($this->lineLogger->coverage_info[$filename], true));
            // $this->verbose(print_r($coverage_info[$filename], true));
            foreach ($lines as $line => $covered) {
                if ($existing_coverage !== false) {
                    $existing_coverage[$filename][$line] = true;
                }
                else {
                    $this->lineLogger->coverage_info[$filename][$line] = true;
                }
            }
        }
        if ($existing_coverage !== false) {
            $this->verbose(array_keys($existing_coverage).PHP_EOL);
            return $existing_coverage;
        }
        else {
            return $this->lineLogger->coverage_info;
        }
    }

    function merge_fork_stats($fork_stats) {
        if (isset($fork_stats)) {
            $this->fork_stats = $fork_stats;
        }
        return $this->fork_stats;
    }

    function merge_fork_info($fork_info) {
        foreach ($fork_info as $filename => $lines) {
            foreach ($lines as $line => $covered) {
                $this->fork_info[$filename][$line] = $covered;
            }
        }
    }

    function merge_output($output) {
        $this->output .= $output;
        if ($this->max_output_length !== false && strlen($this->output) > $this->max_output_length) {
            $this->output = substr($this->output, -$this->max_output_length);
        }
    }

    protected function get_symbol_table_state() {
        $vars = deep_copy($this->variable_stack);
        unset($vars['global']['_SERVER']);
        unset($vars['global']['_ENV']);
        unset($vars['global']['GLOBALS']['_SERVER']);
        unset($vars['global']['GLOBALS']['_ENV']);
        return $vars;
    }

    public function fork_execution($new_branch_coverage, bool $always_fork = false) {
        // $this->verbose('forking: '.($this->reanimate ? 'true' : 'false').PHP_EOL);
        // $this->verbose('reanimation transcript: '.sizeof($this->reanimation_transcript).PHP_EOL);
        // file_put_contents('/home/ubuntu/fork_lines.txt', sprintf('%s:%d:%d'.PHP_EOL, $this->current_file, $this->current_line, getmypid()), FILE_APPEND);
        // Prevent duplicate forks
        // Look at line coverage + variables
        $md5_current_state = md5(json_encode($this->lineLogger->coverage_info).serialize($this->variable_stack));
        $md5_current_line_coverage_state = md5(json_encode($this->lineLogger->coverage_info));
        // Look at line coverage only
        // $md5_current_state = md5(json_encode($this->lineLogger->coverage_info) . json_encode($this->symbolic_variables));
        // $current_coverage_state = $this->lineLogger->coverage_info;
        // $md5_current_state = md5(serialize($this->variable_stack));
        // $symbol_table_state = $this->get_symbol_table_state();
        // $md5_reanimation_state = md5(json_encode($this->lineLogger->reanimation_coverage_info).serialize(end($symbol_table_state)));
        $md5_reanimation_state = md5(json_encode($this->lineLogger->reanimation_coverage_info));

        // If in "reanimate" mode, do not fork and only execute the specific branch to be restored.
        if ($this->reanimate) {
            if (sizeof($this->reanimation_transcript) > 0) {
                if (sizeof($this->full_reanimation_transcript) < 1) {
                    // Flip the condition that we take the different branch on for future executions
                    $last_reanimation_entry = array_pop($this->reanimation_transcript);
                    $last_reanimation_entry['forked'] = !$last_reanimation_entry['forked'];
                    array_push($this->reanimation_transcript, $last_reanimation_entry);
                    $this->full_reanimation_transcript = $this->reanimation_transcript;
                }
                $reanimation_entry = null;
                $reanimation_entry = array_shift($this->reanimation_transcript);
                if (sizeof($this->reanimation_transcript) === 0) {
                    $this->reanimate = false;
                }
                // Fetch from ordered transcripts
                // $child_pid == 0 => We are in the child process
                // $child_pid != 0 => We are in the parent process
                $forked = null;
                // If not the last condition, follow the parent
                // If last condition, flip to the other branch
                // if (count($this->reanimation_transcript) > 0) {
                //     // Follow the parent
                //     $reanimation_entry['forked'] = !$reanimation_entry['forked'];
                // }
                // else {
                //     $this->notice(print_r($reanimation_entry, true));
                // }
                if ($reanimation_entry['current_file'] === $this->current_file
                    && $reanimation_entry['current_line'] === $this->current_line) {
                    if ($reanimation_entry['forked'] === true) {
                        $child_pid = 0;
                        $this->verbose(strcolor(sprintf('Reanimating, continued to child process (%s:%d)' . PHP_EOL, $this->current_file, $this->current_line), 'light green'));
                    } else {
                        // $this->notice(PHP_EOL.print_r($reanimation_entry, true));
                        $child_pid = random_int(1, 9999);
                        $this->verbose(strcolor(sprintf('Reanimating in progress, continued to parent process [Random child id: %d] (%s:%d)' . PHP_EOL, $child_pid, $this->current_file, $this->current_line), 'light green'));
                    }
                    // $this->notice('Child pid: '.$child_pid.PHP_EOL);
                    // $this->verbose('Line coverage hash: '.md5(json_encode($this->lineLogger->coverage_info)).PHP_EOL);
                    return array(getmypid(), $child_pid);
                } else {
                    $this->notice(print_r($reanimation_entry, true));
                    $this->notice($this->current_file . ':' . $this->current_line);
                    $this->notice($md5_reanimation_state);
                    $this->error('Inconsistent state when reanimating.');
                    // $this->verbose('Line coverage hash: '.md5(json_encode($this->lineLogger->coverage_info)).PHP_EOL);
                }
            }
            else {
                $this->reanimate = false;
            }
        }
        else {
            // Not in "reanimate" mode, execute normally and fork as requested
            // Populate the reanimate logs
            $this->fork_info[$this->current_file][$this->current_line][] = $md5_current_state;
            $this->process_count++;

            $pid = getmypid();
            $hard_limit_reached = false;
            $child_pid = 123;
            // if (isset($this->full_reanimation_transcript))
            //     $this->verbose('Final reanimation log hash: '.md5(serialize($this->full_reanimation_transcript)).PHP_EOL);
            $this->full_reanimation_transcript[] = new ReanimationEntry($md5_reanimation_state, $this->current_file, $this->current_line, false);
            $this->verbose(sprintf('Adding reanimation task at %s:%d', $this->current_file, $this->current_line).PHP_EOL);
            // $this->verbose(print_r($this->full_reanimation_transcript, true).PHP_EOL);
            // $this->verbose('Line coverage hash: '.md5(json_encode($this->lineLogger->coverage_info)).PHP_EOL);
            $this->reanimation_callback_object->add_reanimation_task($this->initenv, $this->httpverb, $this->entry_file, $this->full_reanimation_transcript, $this->current_file, $this->current_line, $md5_current_line_coverage_state, $md5_current_state, $this->lineLogger->coverage_info, $this->execution_id, $this->extended_logs_emulation_mode, $new_branch_coverage);
            return array($pid, $child_pid);
        }
    }

    protected function coverage_overlaps($coverage_1, $coverage_2) {
        if (!isset($coverage_1) || !isset($coverage_2)) {
            $this->verbose(strcolor('Coverage 1 or 2 is null'.PHP_EOL, 'yellow'));
            return false;
        }
        $this->verbose(strcolor(sprintf('Coverage 1 [%s]'.PHP_EOL, rtrim(implode(',', array_keys($coverage_1)), ',')), 'yellow'));
        $this->verbose(strcolor(sprintf('Coverage 2 [%s]'.PHP_EOL, rtrim(implode(',', array_keys($coverage_2)), ',')), 'yellow'));
        if (count($coverage_1) > count($coverage_2)) {
            $this->verbose(strcolor(sprintf('Current coverage is longer than existing coverage %d, %d'.PHP_EOL, count($coverage_1), count($coverage_2)), 'yellow'));
            return false;
        }
        $return_val = true;
        foreach ($coverage_2 as $filename => $lines) {
            if (!array_key_exists($filename, $coverage_1)) {
                $this->verbose(strcolor(sprintf('File not covered %s'.PHP_EOL, $filename), 'yellow'));
                $return_val = false;
            }
            elseif (count(array_diff($lines, $coverage_1[$filename])) > 0) {
                $diff = array_diff($lines, $coverage_1[$filename]);
                $this->verbose(strcolor(sprintf('Lines not covered in file %s [%s]'.PHP_EOL, $filename, rtrim(implode(',', $diff), ',')), 'yellow'));
                $return_val = false;
            }
        }
        if ($return_val) {
            $this->verbose(strcolor(sprintf('Files and lines fully match'.PHP_EOL), 'yellow'));
            $this->verbose(print_r($coverage_1).PHP_EOL);
        }
        return $return_val;
    }

    protected function store_checkpoint_context($ast, $node) {
        $this->last_checkpoint_ast = $ast;
        $this->active_checkpoint_node = $node;
        $this->last_checkpoint = deep_copy($this);
    }

    protected function &get_checkpoint_context() {
        return $this->last_checkpoint;
    }
}
//this loads all mock functions, so that auto-mock will replace them
foreach (glob(__DIR__."/mocks/*.php") as $mock)
    require_once $mock;
unset($mock);

if (isset($argv) and $argv[0]==__FILE__)
    die("Should not run this directly.".PHP_EOL);