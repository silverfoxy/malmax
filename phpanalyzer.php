<?php
namespace malmax;
require_once "cli-colors.php";
//function available in apache
if (!function_exists("apache_getenv"))
{
	function apache_getenv()
	{
		return "";
	}
}
function timer($what=-1)
{
	static $t=0;
	if ($what==0)
		$t=microtime(true);
	else
	{
		$diff=microtime(true)-$t;
		if ($what==1)
			printf("%.2fms\n",$diff*1000.0);
		elseif ($what==2)
			return $diff*1000*1000;
		else
			return sprintf("%.2f",$diff);
	}
}
require_once dirname(__FILE__)."/php-emul/oo.php";

use PHPEmul\Checkpoint;
use PHPEmul\LineLogger;
use PHPEmul\SymbolicVariable;
use PhpParser\Node;
if (!function_exists("set_magic_quotes_runtime"))
{
	function set_magic_quotes_runtime() {}
}

// function serialize_mock2($emul,$var)
// {
// 	#FIXME: temporary workaround on infinite recursion
// 	if ($emul->off_branch>0)
// 		return serialize($var);
// 	else
// 		return serialize_mock($emul,$var);
// }
// function unserialize_mock2($emul,$var)
// {
// 	if ($emul->off_branch>0)
// 		return unserialize($var);
// 	else
// 		return unserialize_mock($emul,$var);
// }

abstract class ExecutionMode {
    const ONLINE = 1;
    const OFFLINE = 2;
}

class PHPAnalyzer extends \PHPEmul\OOEmulator
{
    /*
     * Includes the file and line information where this process was forked.
     * [file_name => line_number]
     */

	/**
	 * A list of statements processed
	 * @var array
	 */
	public $statements=[];

	public $declared_statements=[];

	/**
	 * Whether to isolate every off-branch from its parent or just run
	 * the entire off-branches in one isolation.
	 * Damages the accuracy greatly, but only saves roughly 50% performance.
	 * @var boolean
	 */
	public $off_branch_recursive_isolation=true;
	/**
	 * Whether to force down on all paths or not
	 * @var boolean
	 */
	public $concolic=false;
	/**
	 * Depth of the off-branch
	 * @var integer
	 */
	public $off_branch=0;
	/**
	 * Snapshot stack for restoring them
	 * @var array
	 */
	private $snapshot_stack=[];

	/**
	 * Halting problem depth limit
	 * @var integer
	 */
	public $off_branch_depth_limit=8;
	/**
	 * Maximum number of times to run each unique off-branch
	 * @var integer
	 */
	public $off_branch_execution_limit=1;
	/**
	 * Holds the entry point of off-branches to 
	 * prevent rerun.
	 * @var array
	 */
	protected $off_branch_execution=[];


	/**
	 * Postprocessing data
	 * @var array
	 */
	public $post_processing=[];
	/**
	 * Whether to isolate post-processing evaluations
	 * @var boolean
	 */
	public $post_processing_isolation=false;

	/**
	 * Included files that is not restored after isolation.
	 * @var array
	 */
	public $all_files=[];
	
	/**
	 * Statistics of the analyzer, modified using inc()
	 * @var array
	 */
	public $stats=[];

	/**
	 * Holds a list of current active conditions (ifs)
	 * @var array
	 */
	public $active_conditions=[];

	/**
	 * List of all includes found in parsed code
	 * @var array
	 */
	public $includes=[];

	function inc($name,$number=1)
	{
		$parts=explode("/",$name);
		$ref=&$this->stats;
		$last_part=array_pop($parts);
		foreach ($parts as $part)
		{
			if (!isset($ref[$part]))
				$ref[$part]=[];
			$ref=&$ref[$part];
		}
		if (!isset($ref[$last_part]))
			$ref[$last_part]=$number;
		else
			$ref[$last_part]+=$number;
	}

	/**
	 * Default emulator exception handler
	 * @param  Exception $e 
	 * @return        [description]
	 */
	public function exception_handler($e)
	{
		if (count($this->exception_handlers))
		{
			$this->call_function(end($this->exception_handlers),[$e]);
			$this->terminated=true;	
			return true;
		}
		// return $this->error_handler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
		//program output
		$this->output("PHP Fatal error: Uncaught Error: ".$e->getMessage()," in ",$this->current_file,":",$this->current_line,PHP_EOL);
		$this->output("Stack trace:\n");
		$backtrace=$this->print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$this->output($backtrace);
		$count=count($this->trace);
		$this->output("#",$count," {main}",PHP_EOL);
		$this->output("  thrown in ",$this->current_file," on line ",$this->current_line,PHP_EOL);
		
		//emulator output
		if ($this->off_branch>0)
		{
			$this->verbose(strcolor("Off-Branch PHP-Emul Fatal error: Uncaught Error: ".$e->getMessage()." in ".$e->getFile().":".$e->getLine()."\n","yellow"),0); 
		}	
		else
		{
			$this->verbose("PHP-Emul Fatal error: Uncaught Error: ".$e->getMessage()." in ".$e->getFile().":".$e->getLine()."\n",0); 
			if ($this->verbose>=2 and $this->off_branch==0)
			{
				$this->verbose("Emulator Backtrace:\n");
				echo $e->getTraceAsString(),PHP_EOL;
				$this->verbose("Emulation Backtrace:\n");
				echo $this->print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			}
		}	
		

		$this->terminated=true;
		return true;
	}	
	/**
	 * Overriden error handler to terminate the off-branches
	 * @param  [type] $errno   [description]
	 * @param  [type] $errstr  [description]
	 * @param  [type] $errfile [description]
	 * @param  [type] $errline [description]
	 * @return [type]          [description]
	 */
	function error_handler($errno, $errstr, $errfile, $errline)
	{
		if (count($this->error_handlers) and $errno&end($this->error_handlers)['error_reporting'])
			if (false!==$this->call_function(end($this->error_handlers)['handler'],func_get_args())) return true;
		$this->stash_ob();
		$file=$errfile;
		$line=$errline;
		$file2=$line2=null;
		if (isset($this->current_file)) $file2=$this->current_file;
		if (isset($this->current_node)) $line2=$this->current_node->getLine();
		$fatal=false;
		switch($errno) //http://php.net/manual/en/errorfunc.constants.php
		{
			case E_USER_NOTICE:
			case E_NOTICE:
				$str="Notice";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$fatal=true;
				$str="Error";
				break;
			case E_USER_WARNING:
			case E_WARNING:
				$str="Warning";
				break;
			default:
				$str="Error(".$errno.")"; //unknown error type
		}
		if ($fatal)
			$fatal_str="Fatal ";
		else
			$fatal_str="";
		$this->inc("error/handled/all");
		$this->inc("error/handled/{$str}");
		if ($this->off_branch==0)
		{
			$this->inc("error/handled/mainbranch/all");
			$this->inc("error/handled/mainbranch/{$str}");
			$this->verbose("PHP-Emul {$str}:  {$errstr} in {$file} on line {$line} ($file2:$line2)".PHP_EOL,0);
			$this->output("PHP {$fatal_str}{$str}:  {$errstr} in {$file2} on line {$line2}".PHP_EOL);
		}
		else
		{
			$this->inc("error/handled/offbranch/all");
			$this->inc("error/handled/offbranch/{$str}");
			$this->verbose(
				strcolor("Off-Branch PHP-Emul {$str}:  {$errstr} in {$file} on line {$line} ($file2:$line2)".PHP_EOL,"yellow")
				,3);
		}
		if ($fatal or $this->strict) 
		{
			$this->terminated=true;
			$this->termination_value=-1;
			if ($this->verbose>=2 and $this->off_branch==0)
			{
				$this->verbose("Emulator Backtrace:\n");
				debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				$this->verbose("Emulation Backtrace:\n");
				echo $this->print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			}
		}
		$this->restore_ob();
		return true;
	}
	protected function _error($msg,$node=null,$details=true)
	{
		$this->inc("error/all");
		if ($this->off_branch!=0)
		{
			$this->inc("error/offbranch");
			$this->verbose(
				strcolor("(Off-Branch) ".$msg." in ".$this->current_file." on line ".$this->current_line.PHP_EOL
					,"yellow")
					,0);
			return ;
		}
		$this->inc("error/mainbranch");
		$this->verbose($msg." in ".$this->current_file." on line ".$this->current_line.PHP_EOL,0);
		if ($details)
		{
			// print_r($node);
			if ($this->verbose>=2)
			{
				$this->verbose("Emulator Backtrace:\n");
				debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				$this->verbose("Emulation Backtrace:\n");
				echo $this->print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			}
		}
		if ($this->strict) 
		{
			$this->terminated=true;
			$this->termination_value=-2;
		}

	}
	public $verbose_state=['lastVerbosity'=>1,'verbosities'=>[0]];
	/**
	 * When called from another emulation context, can be used to increase all verbose levels
	 * @var integer
	 */
	public $verbose_base	=	0;
	/**
	 * Overriden verbose to distinguish between off-branch and main branch messages
	 * @param  string  $msg       
	 * @param  integer $verbosity 1 is basic messages, 0 is always shown, higher means less important
	 */
	function verbose($msg,$verbosity=1)
	{
		$verbosity+=$this->verbose_base;
		$this->stash_ob(); #don't really need it here, handled at a higher level
		// static $lastVerbosity=1;
		// static $verbosities=[0];
		$lastVerbosity=&$this->verbose_state['lastVerbosity'];
		$verbosities=&$this->verbose_state['verbosities'];
		$number="";

		///Analyzer Part
		$statementid=$this->statement_id();
		$unique=(!isset($this->statements[$statementid]));

		if ($unique) 
			$msg="✔ {$msg}";

		if ($this->off_branch>0)
			$msg=strcolor(str_repeat(">",$this->off_branch)." ".$msg,"light green"); //colored

		///End Analyzer Part


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

		if ($number and $this->off_branch>0)
			$number="($number)";
		if ($this->verbose>=$verbosity) {
            // echo str_repeat("---",$verbosity)." ".$number." ".$msg;
            echo str_repeat("---",$verbosity)." ".$number." ".sprintf("[%d] ", getmypid()).$msg;
        }
		$this->restore_ob();
	}	

	/**
	 * Overriden get_declarations counts what it sees
	 * @param  [type] $node [description]
	 * @return [type]       [description]
	 */
	function get_declarations($node)
	{
		$this->inc("statements/visited/sum");
		$statementid=$this->statement_id($node);
		if (isset($this->declared_statements[$statementid]))
			$this->declared_statements[$statementid]++;
		else
		{
			$this->inc("statements/visited/unique");
			$this->declared_statements[$statementid]=1;
		}
		return parent::get_declarations($node);
	}
	/**
	 * Determines whether or not an expression is statically resolvable or not
	 * @param  Node  $node [description]
	 * @return boolean       [description]
	 */
	function is_statically_resolvable($node)
	{
		//TODO: manually verified, wordpress only has these.
		if ($node instanceof Node\Scalar)
			return true;
		if ($node instanceof Node\Expr\ConstFetch)
			return true;
		// if ($node instanceof Node\Expr\Assign)
		// 	return $this->is_statically_resolvable($node->var)
		// 	and $this->is_statically_resolvable($node->expr);
		if ($node instanceof Node\Expr\Variable)
			if (is_string($node->name) and array_key_exists($node->name, $this->variables))
				return true;
		if ($node instanceof Node\Expr\BinaryOp\Concat)
			return $this->is_statically_resolvable($node->left) and $this->is_statically_resolvable($node->right);
		if ($node instanceof Node\Expr\FuncCall)
			if (isset($node->name->parts))
			{
				$name=$node->name->parts[0];
				if ($name=="dirname"
					or $name=="basename")
					return true;
			}
		if ($node instanceof Node\Expr\UnaryMinus
			or $node instanceof Node\Expr\BitwiseNot)
			return $this->is_statically_resolvable($node->expr);
		var_dump($node);
		return false;

	}
	/**
	 * Used after parse to extract stats or static artifacts
	 * @param  Node $node AST
	 * @return [type]       [description]
	 */
	function node_traverser($node)
	{
		if (is_array($node))
        {
            foreach ($node as $n)
                $this->node_traverser($n);
            return;
        }
        if ($node instanceof Node)
        {
            foreach ($node->getSubNodeNames() as $name)
                $this->node_traverser($node->$name);
        }
        //static resolving
        if ($this->static)
        {
			if ($node instanceof Node\Stmt\Const_)
			{
				foreach ($node->consts as $const)
				{
					$this->verbose("Constant found in static mode: ".
						$const->name."=".$this->evaluate_expression($const->value)."\n");
					$this->constant_set($const->name,$this->evaluate_expression($const->value));
				}
			}
			if ($node instanceof Node\Expr\Assign)
			{
				$this->verbose("Assignment found in static mode: '".$this->print_ast($node)."' \n");
				if ($this->is_statically_resolvable($node->expr))
				{
					$val=$this->evaluate_expression($node->expr);
					if ($val!==null)
						$this->variable_set($node->var,$val);
					// var_dump($this->variables);
				}

			}
			if ($node instanceof Node\Expr\FuncCall)
			{
				$name=$node->name;
				if (isset($name->parts))
					$name=$name->parts[0];
				if (is_string($name) and $name=="define")
				{
					$this->verbose("Define found in static mode: '".$this->print_ast($node)."' \n");
					$value=$node->args[1]->value;
					if ($this->is_statically_resolvable($value))
						$this->evaluate_expression($node);
				}
			}
        }

        //stats
		if ($node instanceof Node\Stmt)
		{
			$this->inc("statements/parsed");
		}
		if ($node instanceof Node\Expr)
		{
			if ($node instanceof Node\Expr\Exit_)
				$this->inc("node/exit");
			elseif ($node instanceof Node\Expr\Include_)
			{
				#TODO: record these includes
				#TODO: try to evaluate those includes that are not run after the script ends
				$name=$this->current_file.":".$node->getLine();
				if (isset($this->includes[$name]))
					$new="old";
				else
					$new="new";
				// $this->verbose("Include found ({$new}): (".$this->print_ast($node->expr).")\n",3);
                // $this->verbose("Include found ({$new}): (".$this->evaluate_expression($node->expr).")\n",3);
				$this->includes[$name]=$node;
				$this->inc("node/include");
			}

		}
	}

	/**
	 * overriden parse to count parsed files and count their statements
	 * @param  [type] $file [description]
	 * @return [type]       [description]
	 */
	public function parse($file)
	{
		$this->inc("parsed/files/all");
		$ast=parent::parse($file);

		if (isset($this->all_files[$file]))
		{
			$this->all_files[$file]++;
		}
		else
		{
			$this->inc("parsed/files/unique");
			$this->all_files[$file]=1;
			$bu=$this->current_file;
			$this->current_file=$file;
			$this->node_traverser($ast);
			$this->current_file=$bu;
		}

		// $this->inc("statements/parsed/all",$this->count_statements($ast));
		// $this->inc("statements/parsed/no-expressions",$this->count_statements($ast,true));
		
		// $bu=$this->current_file;
		// $this->current_file=$file;
		// $this->node_traverser($ast);
		// $this->current_file=$bu;
		return $ast;
	}
	/**
	 * Returns a 32bit integer supposedly unique for each statement
	 * based on File, Line and statement type
	 * meaning that two similar statements on one line will have similar ids
	 * @param  Node $node optional
	 * @return integer or null
	 */
	function statement_id($node=null)
	{
		if ($node===null)
			$node=$this->current_node;
		if (is_object($node) and method_exists($node, "getLine"))
			//TODO:works for expressions too, make it statement only
		{
			$line=$node->getLine();
			$file=$this->current_file;
			$type=get_class($node);
			return crc32("$file:$line:$type");
		}	
		return 0;
	}
	/**
	 * Override for try/catch handling so it doesn't pop
	 * out of isolation
	 * @param  [type] $e [description]
	 * @return [type]    [description]
	 */
	public function throw_exception($e)
	{
		#TODO: make the isolation for try/catch in off_branches sound
		//hacky workaround, exceptions will simply terminate the off-branch
		if ($this->off_branch>0)
			return $this->exception_handler($e);
		parent::throw_exception($e);

	}
	/**
	 * Creates a snapshot of current emulator state
	 * @param  boolean $swap whether to put the created snapshot as active
	 * @return either the copy or original state, based on swap
	 */
	public function snapshot($swap=true)
	{
		$items=array_keys($this->state);
		$items[]='verbose_state';
		$res=[];
		foreach ($items as $item )
			$res[$item]=&$this->{$item}; 

		// echo "Statement ID: ",$this->statement_id(),PHP_EOL;
		if (function_exists("deep_copy"))
			$r=deep_copy($res);
		else
		{
			require_once "deepcopy.php";
			@$r=&deep_copy($res);
		}
		gc_collect_cycles();

		if ($swap) //swap the snapshot with active state
		{
			foreach ($r as $key=>$value)
				$this->{$key}=&$r[$key];//$value;
			return $res;
		}
		else
			return $r;
	}
	/**
	 * restore state from snapshot
	 * @param  [type] $snapshot [description]
	 * @return [type]           [description]
	 */
	public function restore_snapshot($snapshot)
	{
		foreach ($snapshot as $key=>$value)
			$this->{$key}=&$snapshot[$key];//$value;
		gc_collect_cycles();
	}

	// public function run_file($file)
    // {
    //     $result = parent::run_file($file);
    //     while (sizeof($this->rerun_points) > 0 && end($this->rerun_points)->current_file ===  $file) {
    //         $this->verbose(strcolor(
    //             sprintf("Restoring concolic path %s [%s:%s] (depth=%d)...\n",$this->statement_id(),$this->current_file, $this->current_line, $this->off_branch)
    //             ,"light green"),0);
    //         $rerun_point = array_pop($this->rerun_points);
    //         $this->restore_snapshot($rerun_point->snapshot);
    //         $ast = $this->parse($file);
    //         $result = parent::run_code($ast, $this->current_node);
    //     }
    //     return $result;
    // }

    /**
	 * Starts an off-branch by taking a snapshot and applying it.
	 * @return [type] [description]
	 */
	function off_branch_start()
	{
		// if ($this->off_branch==0) //entering
		// {
		// 	$this->mock_functions['serialize']="serialize_mock2";
		// 	$this->mock_functions['unserialize']="unserialize_mock2";
		// }
		$this->off_branch++;
		$this->verbose(strcolor(
			sprintf("Branching off at %s [%s:%s] (depth=%d)...\n",$this->statement_id(),$this->current_file, $this->current_line, $this->off_branch)
			,"light green"),0);
		if ($this->off_branch_recursive_isolation or $this->off_branch==1)
			$this->snapshot_stack[]=$this->snapshot();

		if ($this->off_branch>=$this->off_branch_depth_limit)
		{
			$this->verbose(strcolor("Off branches exhausted, ignoring this path...\n","red"),0);	
			$this->terminated=true;
		}
	}
	/**
	 * Ends an off branch, restoring the parent branch
	 * @return [type] [description]
	 */
	function off_branch_end()
	{

		//NOTE: without this, some branches will run to the end of the program
		//this causes any hiccup in a branch to result in dismissal of the entire branch
		//from the main path. not very accurate, but yields acceptable results.
		// if ($this->off_branch==1) 
		{
			if ($this->terminated)
				$this->terminated=false;
		}
		if ($this->off_branch_recursive_isolation or $this->off_branch==1)
			$this->restore_snapshot(array_pop($this->snapshot_stack));

		$this->off_branch--; //FIXME: move this to the bottom of this function, should return to main branch after restoring

		$this->verbose(strcolor("‌Branch-off (depth=".($this->off_branch+1).") restored\n","light green"),0);

		if ($this->off_branch==0)
		{
			$this->mock_functions['serialize']="serialize_mock";
			$this->mock_functions['unserialize']="unserialize_mock";
			$this->verbose(strcolor("Resuming main branch...\n","green"),0);
		}
	}
	/**
	 * Run code isolated
	 * @param  [type] $stmts [description]
	 * @param  [type] $node  [description]
	 * @param  [type] $branch  used to determine branches of one statement
	 * @return [type]        [description]
	 */
	function run_off_branch_code($stmts,$node,$branch)
	{
		$statementid=$this->statement_id($node);
		if (!isset($this->off_branch_execution[$statementid]))
			$this->off_branch_execution[$statementid]=[];
		if (!isset($this->off_branch_execution[$statementid][$branch]))
			$this->off_branch_execution[$statementid][$branch]=0;
		$this->inc("off-branch/all");
		
		if (++$this->off_branch_execution[$statementid][$branch] > $this->off_branch_execution_limit)
		{
			$this->verbose(strcolor("This off-branch has already been executed, ignoring.\n","light green"),3);	
			return;	 //run everything twice at most
		}
		$this->inc("off-branch/executed");
		$this->off_branch_start();
		$this->run_code($stmts);
		$this->off_branch_end();
	}
	function evaluate_expression($node, &$is_symbolic = false)
	{
		if ($node instanceof Node\Expr\Exit_ and $this->diehard)
		{
			$this->inc("exit/all");
		 	if ($this->off_branch==0)
		 	{
				$this->verbose("Exit encountered in main branch, instead of terminating, I will resume as off-branch...\n",1);	
				$this->inc("exit/avoided");
				$this->off_branch_start(); //no corresponding end, results in shutdown in off_branch mode
		 	}
		 	else
		 	{
				$this->verbose("Exit encountered in off branch, instead of terminating, I will resume the off-branch!\n",2);	
				$this->inc("exit/ignored");
		 	}
		 	return null;
		}
		if ($node instanceof Node\Expr\Include_)
			$this->inc("statements/executed/include");


		return parent::evaluate_expression($node, $is_symbolic);
	}
	function file_line($prepend="",$append="")
	{
		return "{$prepend}{$this->current_file}:{$this->current_line}{$append}";
	}

	function add_rerun_point(Node $current_condition) {
        $this->rerun_points[] = new RerunPoint($this->snapshot(false), $this->statement_id($current_condition), $this->current_file);
    }

	public $if_nesting =0;
	/**
	 * Overriden run_statement that counts and runs ifs concolicly
	 * @param  [type] $node [description]
	 * @return [type]       [description]
	 */
	function run_statement($node)
	{
		//crashes:
		// if ($node instanceof Node\Stmt\Return_ and !$node->expr and $this->off_branch>0 and $this->diehard)
	 // 	{
		// 	$this->verbose("Empty return encountered in off branch, instead of returning, I will resume the off-branch...\n",1);	
		// 	$this->return_value=null;
		// 	$this->inc("return/avoided");
		// 	return;
	 // 	}		
		$this->inc("statements/executed/sum");
		$statementid=$this->statement_id($node);
		if (!isset($this->statements[$statementid]))
		{
			$this->statements[$statementid]=1;
			$this->inc("statements/executed/unique");
		}
		else
			$this->statements[$statementid]++;

		if ($node instanceof Node\Stmt\If_)
		{
			$this->inc("if/count");
			$this->inc("if/branches");
			if ($this->off_branch)
			{
				$this->inc("counterfactual/if/count");
				$this->inc("counterfactual/if/branches");
			}
			if (is_array($node->elseifs))
				foreach ($node->elseifs as $elseif)
				{
					if ($this->off_branch)
					{
						$this->inc("counterfactual/if/branches");
						$this->inc("counterfactual/if/elseifs");
					}
					$this->inc("if/branches");
					$this->inc("if/elseifs");
				}
			if (isset($node->else))
			{
				if ($this->off_branch)
				{
					$this->inc("counterfactual/if/branches");
					$this->inc("counterfactual/if/elses");
				}
				$this->inc("if/branches");
				$this->inc("if/elses");
			}
			if (!count($node->elseifs) and !isset($node->else))
			{
				if ($this->off_branch)
				{
					$this->inc("counterfactual/if/single");
				}
				$this->inc("if/single");
			}

			if ($this->concolic) //execute them all!
			{
			    // TODO: For proper implementation here
			    // $branch_statements = null;
			    // $branch_execution_done = false;
			    // // Check if one of the conditions are factual and the condition is satisfied.
                // $branch_evaled_conditions = [];
                // $branch_evaled_conditions[$this->statement_id($node->cond)] = $this->evaluate_expression($node->cond);
                // // If main branch is factual, only execute that one
                // if ($branch_evaled_conditions[$this->statement_id($node->cond)]) {
                //     $branch_statements = $node->stmts;
                //     $branch_execution_done = true;
                //     $condition = $this->print_ast($node->cond);
                // }
                // else {
                //     // Check if any of the elseif and else branches are factual
                //     $else_statements = [];
                //     if (is_array($node->elseifs)) {
                //         $else_statements = $node->elseifs;
                //     }
                //     foreach ()
                // }
                // if ($this->evaluate_expression())
				$selected_branch_statements = null;
				$covered_one_branch = false;
				// Check if any of the branches are concrete
                $have_symbolic_conditions = false;
                $is_symbolic = false;
                $this->lineLogger->logNodeCoverage($node->cond, $this->current_file);
				$main_branch_condition = $this->evaluate_expression($node->cond, $is_symbolic);
                if ($main_branch_condition && !$main_branch_condition instanceof SymbolicVariable && !$is_symbolic) {
                    // Condition of this branch is concretely satisfied
                    $covered_one_branch = true;
                    $selected_branch_statements = $node->stmts;
                    $condition = $this->print_ast($node->cond);
                }
                elseif ($main_branch_condition instanceof SymbolicVariable || $is_symbolic) {
                    $forked_process_info = $this->fork_execution();
                    if ($forked_process_info !== false) {
                        list($pid, $child_pid) = $forked_process_info;
                        if ($child_pid === 0) {
                            $selected_branch_statements = $node->stmts;
                            $covered_one_branch = true;
                        }
                    } else {
                        // Terminated
                        return;
                    }
                }
                $branch_conditions = [];

                if (!$covered_one_branch && is_array($node->elseifs) && sizeof($node->elseifs) > 0) {
                    // Main branch is not satisfied, now checking the elseif branches
                    foreach ($node->elseifs as $elseif) {
                        $this->lineLogger->logNodeCoverage($elseif->cond, $this->current_file);
                        $branch_condition = $this->evaluate_expression($elseif->cond);
                        $branch_conditions[$this->statement_id($elseif)] = $branch_condition;
                        if ($branch_condition && !$branch_condition instanceof SymbolicVariable) {
                            // Condition of this branch is concretely satisfied
                            $covered_one_branch=true;
                            $selected_branch_statements = $elseif->stmts;
                            $condition = $this->print_ast($elseif->cond);
                            break;
                        }
                        elseif ($branch_condition instanceof SymbolicVariable) {
                            // Elseif condition is symbolic
                            $forked_process_info = $this->fork_execution();
                            if ($forked_process_info !== false) {
                                list($pid, $child_pid) = $forked_process_info;
                                if ($child_pid === 0) {
                                    if (isset($elseif->cond)) {
                                        $condition = $this->print_ast($elseif->cond);
                                    } else {
                                        $condition = 'else';
                                    }
                                    $selected_branch_statements = $elseif->stmts;
                                    $covered_one_branch = true;
                                    break;
                                }
                            } else {
                                // Terminated
                                return;
                            }
                        }
                    }
                }
                if (!$covered_one_branch) {
                    // None of the If/Elseif conditions were satisfied
                    // Therefore we run Else
                    if (isset($node->else)) {
                        // run else
                        $covered_one_branch = true;
                        $selected_branch_statements = $node->else->stmts;
                        $condition="not ".$this->print_ast($node->cond);
                    }
                    else {
                        // Nothing to do
                        $covered_one_branch = true;
                        $condition = '';
                    }
                }
				if ($selected_branch_statements !==null)
				{
				    if (!isset($condition)) {
				        $condition = 'not set';
                    }
					array_push($this->active_conditions,$condition);
					$this->if_nesting++;
                    // $this->verbose("Running the code inside if body ".$node->getStartLine().PHP_EOL);
					$this->run_code($selected_branch_statements);
					$this->if_nesting--;
					array_pop($this->active_conditions);
				}
				return ;
			}
			else //non-concolic
			{
				$condition=$this->print_ast($node->cond).$this->file_line(" (",")");
				array_push($this->active_conditions,$condition);
				$res=parent::run_statement($node);
				array_pop($this->active_conditions);
				return $res;
			}
		}
		elseif ($node instanceof Node\Stmt\Switch_)
		{
			//its hard to determine which cases will be executed
			//prior to doing it, because they break the execution themselves
			//we need to find cases that are not executed and off_branch them
			//but it might be impossible to do so prior to running the actual cases
			//
			$this->inc("switch/count");
			$this->inc("switch/branches",count($node->cases));
			if ($this->off_branch)
			{
				$this->inc("counterfactual/switch/count");
				$this->inc("counterfactual/switch/branches", count($node->cases));
			}
			if ($this->concolic)
			{
				$covered_one_branch=false;
				$condition=false;
				$index=0;
				$master_condition=$this->evaluate_expression($node->cond);
				$this->verbose("Concolic switch with ".count($node->cases)." cases found...\n",3);
				$case_conditions = [];
				$index = 0;
				$have_symbolic_conditions = true;
				// If the main condition is Symbolic then run everything
                $run_next_case = false;
                if ($master_condition instanceof SymbolicVariable) {
                    $this->verbose(strcolor("Switch condition relies on SymbolicVariable, running all branches\n", "light green"), 0);
                    foreach ($node->cases as $case) {
                        if ($case->cond === null) { // If default branch, do not fork
                            $this->verbose(strcolor(
                                sprintf("Running default branch %s [%s:%s] ...\n", $this->statement_id(), $this->current_file, $this->current_line)
                                , "light green"), 0);
                            $this-> run_code($case->stmts);
                            if ($this->loop_condition()) {
                                $covered_one_branch = true;
                                break;
                            }
                        }
                        // If not default branch, fork for each case
                        if (!$run_next_case) {
                            if ($this->execution_mode === ExecutionMode::OFFLINE) {
                                if ($this->checkpoint_restore_mode) {
                                    $this->checkpoint_restore_mode = false;
                                    $this->run_code($case->stmts);
                                    // loop_condition reduces the number of breaks, it needs to be here
                                    if ($this->loop_condition()) {
                                        $covered_one_branch = true;
                                        break;
                                    } else {
                                        $run_next_case = true;
                                    }
                                }
                                else {
                                    if (LineLogger::has_covered_new_lines($this->lineLogger->coverage_info, $this->overall_coverage_info)) {
                                        $this->checkpoints[] = new Checkpoint($this->last_checkpoint, $this->current_file, $case);
                                    }
                                    else {
                                        $this->terminated = true;
                                        return;
                                    }
                                }
                            }
                            elseif ($this->execution_mode === ExecutionMode::ONLINE) {
                                $forked_process_info = $this->fork_execution(true);
                                if ($forked_process_info !== false) {
                                    list($pid, $child_pid) = $forked_process_info;
                                    if ($child_pid === 0) {
                                        $this->verbose(strcolor(sprintf("Covering %s line.".PHP_EOL, $case->getStartLine()), "light green"));
                                        $this->run_code($case->stmts);
                                        // loop_condition reduces the number of breaks, it needs to be here
                                        if ($this->loop_condition()) {
                                            $covered_one_branch = true;
                                            break;
                                        } else {
                                            $run_next_case = true;
                                        }
                                    }
                                }
                            }
                        }
                        else {
                            $this->run_code($case->stmts);
                            // loop_condition reduces the number of breaks, it needs to be here
                            if ($this->loop_condition()) {
                                $covered_one_branch = true;
                                break;
                            } else {
                                $run_next_case = true;
                            }
                        }
                    }
                }
                else {
                    // If individual cases are Symbolic then run that one
                    $this->verbose("Switch condition is concrete, checking branch conditions branches\n",0);
                    $covered_one_branch = false;
                    $run_next_case = false;
                    foreach ($node->cases as $case) {
                        if ($covered_one_branch) {
                            break;
                        }
                        // If some of the conditions are concretely satisfied, run those
                        $cond = $this->evaluate_expression($case->cond);
                        if ($cond == $master_condition && !$cond instanceof SymbolicVariable) {
                            $this->run_code($case->stmts);
                            // loop_condition reduces the number of breaks, it needs to be here
                            if ($this->loop_condition()) {
                                $covered_one_branch=true;
                            }
                        }
                        elseif ($cond instanceof SymbolicVariable || $run_next_case) {
                            // If some of the conditions are Symbolic, fork and run them
                            // Or if the case is piggy backing a Symbolic condition, also run that one ($run_next_case)
                            $this->process_count++;
                            $pid = true;
                            if (!$run_next_case) {
                                if ($this->execution_mode === ExecutionMode::OFFLINE) {
                                    if ($this->checkpoint_restore_mode) {
                                        $this->checkpoint_restore_mode = false;
                                        $this->run_code($case->stmts);
                                        // loop_condition reduces the number of breaks, it needs to be here
                                        if ($this->loop_condition()) {
                                            $covered_one_branch = true;
                                            break;
                                        } else {
                                            $run_next_case = true;
                                        }
                                    }
                                    else {
                                        if (LineLogger::has_covered_new_lines($this->lineLogger->coverage_info, $this->overall_coverage_info)) {
                                            $this->checkpoints[] = new Checkpoint($this->last_checkpoint, $this->current_file, $case);
                                        }
                                        else {
                                            $this->terminated = true;
                                            return;
                                        }
                                    }
                                }
                                elseif ($this->execution_mode === ExecutionMode::ONLINE) {
                                    $forked_process_info = $this->fork_execution();
                                    if ($forked_process_info !== false) {
                                        list($pid, $child_pid) = $forked_process_info;
                                        if ($child_pid === 0) {
                                            $this->run_code($case->stmts);
                                            // loop_condition reduces the number of breaks, it needs to be here
                                            if ($this->loop_condition()) {
                                                $covered_one_branch = true;
                                                break;
                                            } else {
                                                $run_next_case = true;
                                            }
                                        }
                                    } else {
                                        // Terminated
                                        return;
                                    }
                                }
                            }
                            else {
                                $this->run_code($case->stmts);
                                // loop_condition reduces the number of breaks, it needs to be here
                                if ($this->loop_condition()) {
                                    $covered_one_branch = true;
                                    break;
                                } else {
                                    $run_next_case = true;
                                }
                            }
                        }
                        elseif ($case->cond === null) {
                            // If none are symbolic nor concretely satisfied, run default
                            $this->verbose(strcolor(
                                sprintf("Running default branch %s [%s:%s] ...\n", $this->statement_id(),$this->current_file, $this->current_line)
                                ,"light green"),0);
                            $this->run_code($case->stmts);
                            $covered_one_branch=true;
                            // To take care of the last break
                            $this->loop_condition();
                            break;
                        }
                    }
                }
                return;
                // If none of the cases are Symbolic nor satisfied then run the default case

                // Else, run everything
                foreach ($node->cases as $case)
                {
                    $index++;
                    $case_condition[$this->statement_id($case)] = $this->evaluate_expression($case->cond);
                    if ($case_condition && !$case_condition instanceof SymbolicVariable) {
                        // We have found a concretely satisfied case
                        $default=$case->cond===NULL;
                        $d=$default?" (default)":"";
                        $this->verbose("Case {$index}{$d} is a matching case, running...\n",0);
                        $this->run_code($case->stmts);
                        if ($this->loop_condition())
                            $covered_one_branch=true;
                    }
                    elseif ($case_condition instanceof SymbolicVariable) {
                        $have_symbolic_conditions = false;
                    }
                }
                // if ($none_symbolic) {
                //     // None of the cases are symbolic and none are concretely satisfied
                //     // Run the default case
                // }
                $index = 0;
				foreach ($node->cases as $case)
				{
                    if ($covered_one_branch) {
                        break;
                    }
					$index++;
					$default=$case->cond===NULL;
					$this->verbose("Checking switch case {$index}...\n",4);
					$this->inc("switch/branches");
					if ($default and !$covered_one_branch) // default case
						$condition=true; //run everything henceforth
					elseif ($this->evaluate_expression($case->cond)==$cond) //real path
						$condition=true;

					$d=$default?" (default)":"";
					if ($condition)
					{
						$this->verbose("Case {$index}{$d} is a matching case, running...\n",0);
						$this->run_code($case->stmts);
						if ($this->loop_condition())
							$covered_one_branch=true;

					}
					else
					{
						$this->verbose("Case {$index}{$d} is a non-matching case, running off-branch...\n",0);
                        if ($this->execution_mode === ExecutionMode::OFFLINE) {
                            if ($this->checkpoint_restore_mode) {
                                $this->checkpoint_restore_mode = false;
                                $this->run_code($case->stmts);
                                // loop_condition reduces the number of breaks, it needs to be here
                                if ($this->loop_condition()) {
                                    $covered_one_branch = true;
                                }
                            }
                            else {
                                if (LineLogger::has_covered_new_lines($this->lineLogger->coverage_info, $this->overall_coverage_info)) {
                                    $this->checkpoints[] = new Checkpoint($this->last_checkpoint, $this->current_file, $case);
                                }
                                else {
                                    $this->terminated = true;
                                    return;
                                }
                            }
                        }
                        elseif ($this->execution_mode === ExecutionMode::ONLINE) {
                            $forked_process_info = $this->fork_execution();
                            if ($forked_process_info !== false) {
                                list($pid, $child_pid) = $forked_process_info;
                                if ($child_pid === 0) {
                                    $this->run_code($case->stmts);
                                    // loop_condition reduces the number of breaks, it needs to be here
                                    if ($this->loop_condition()) {
                                        $covered_one_branch = true;
                                    }
                                }
                            } else {
                                // Terminated
                                return;
                            }
                        }
					}
				}
				// if (!empty($stmts))
				// 	foreach ($stmts as $st)
				// 		$this->run_code($st);
				return;
			}
		}
		// if (!$this->concolic)
		return parent::run_statement($node);
	}
	function __destruct()
	{
		$this->verbose("Analyzer destructor.\n");
		parent::__destruct();
	}
	public $static=false;
	function postprocessing()
	{
		$this->verbose("Starting post processing\n",1);
		$this->verbose("Execution finished. Now looping over ".count($this->includes)." found includes...\n",1);
		$iteration=0;
		$count=0;
		$this->off_branch_start();
		$this->terminated=false; //otherwise more advanced structures in includes won't be evaluated
		do{
			$iteration++;
			$firstcount=count($this->includes);
			$this->verbose("Iteration {$iteration} ({$firstcount} items)...\n",1);
			$more_includes=[];	
			$index=0;
			foreach ($this->includes as $k=>$include)
			{
				if ($index<$count) //already processed in previous iteration
				{
					$index++;
					continue; 
				}
				$code=$this->print_ast($include);
				$this->verbose("Trying to evaluate: {$code} ({$k})\n",2);
				$file=explode(":",$k)[0]; //filename
				if ($this->static and !$this->is_statically_resolvable($include->expr))
					$val=null;
				else
				{

					if ($this->post_processing_isolation)
						$this->off_branch_start();
					$this->current_file=$file; //current file is used in evaluating many includes
					$val=$this->evaluate_expression($include->expr);
					if ($this->post_processing_isolation)
						$this->off_branch_end();
				}
				if ($this->terminated)
				{
					$this->terminated=false;	//no need, no side-effects yet
					$val=null;
				}
				if ($val===null)
				{
					$this->inc("postprocessing/failed");	
					$this->verbose("Failed.\n",3);
					$this->postprocessing['failed_include'][]=$code. " ({$k})";
				}
				else
				{
					$this->inc("postprocessing/success/all");	

					$this->verbose("Success! Evaluates to '{$val}'\n",3);
					if (isset($this->included_files[$val]))
					{
						$this->inc("postprocessing/success/already_included");	
						$this->verbose("But already included in the run.\n",4);
					}
					else
					{
						if (file_exists($val) and is_file($val))
						{
							$this->inc("postprocessing/success/exists");	
							$this->verbose("Adding to list...\n",4);
							$more_includes[]=$val;
						}
						else 
						{
							$this->inc("postprocessing/success/not-exists");	
							$this->verbose("Invalid file.\n",4);
							$this->postprocessing['non-existent_include'][]=$code. " ({$k})";
						}
					}
				}
			}
			$count=count($this->includes); //this far processed
			$this->verbose("Found ".count($more_includes)." more files in iteration {$iteration}:\n",2);
			// print_r($more_includes);
			$this->verbose("Lets parse them all.\n",2);

			// $this->includes=[]; //THIS SHOULD NOT BE HERE! 
			foreach ($more_includes as $inc)
			{
				$this->verbose("Parsing {$inc}...\n",3);
				$this->included_files[$inc]=1;
				$s=$this->static;
				$this->static=true;
				$this->parse($inc);
				$this->static=$s;
			}

		}
		while (count($this->includes)!=$firstcount);
		$this->postprocessing['included_files']=$this->included_files;
		$this->postprocessing['iterations']=$iteration;
		$this->off_branch_end();
	}

	protected function try_add_function_summary($function, $processed_args, $return_value) {
	    return;
        if ($return_value instanceof SymbolicVariable) {
            $symbolic_return = true;
            $symbolic_input = $this->is_function_arg_symbolic($processed_args);
            // Get function name and file
            $function_name = $function->context->function;
            $file_name = $function->context->file;
            // If code coverage is 100%
            if ($symbolic_input || $this->has_full_coverage($function->code, $this->lineLogger->coverage_info[$file_name])) {
                $this->function_summaries[$file_name][$function_name] = ['symbolic_input'=>$symbolic_input, 'symbolic_return'=>$symbolic_return];
            }
        }
    }

    protected function function_summary_exists($trace_args, $processed_args) {
	    return false;
        $function_name = $trace_args['function'];
        $file_name = $this->current_file;
	    if (array_key_exists($file_name, $this->function_summaries)
            && array_key_exists($function_name, $this->function_summaries[$file_name])) {
            $symbolic_input = $this->is_function_arg_symbolic($processed_args);
            $function_summary = $this->function_summaries[$file_name][$function_name];
            if ($function_summary['symbolic_input'] === $symbolic_input &&
                $function_summary['symbolic_return']) {
                return new SymbolicVariable($function_name);
            }
        }
	    return false;
    }

    protected function is_function_arg_symbolic($processed_args) {
        $symbolic_input = false;
        if (sizeof($processed_args) === 0) {
            return true;
        }
        foreach ($processed_args as $arg) {
            if ($arg instanceof SymbolicVariable) {
                $symbolic_input = true;
                break;
            }
        }
        return $symbolic_input;
    }

    protected function has_full_coverage($function_statements, $covered_lines) {
	    foreach($function_statements as $statement) {
	        for($line=$statement->getStartLine(); $line <= $statement->getEndLine(); $line++) {
	            if (!array_key_exists($line, $covered_lines)) {
	                return false;
                }
            }
        }
	    return true;
    }
}
