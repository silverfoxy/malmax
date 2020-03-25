<?php
use PhpParser\Node;
require_once 'sandbox.class.php';
class PHPSandboxDeobfuscator extends PHPSandbox
{

	function __destruct()
	{

	}
	/**
	 * Create a reference point for the file that is deobfuscated
	 * @param  [type] $file [description]
	 * @return [type]       [description]
	 */
	function parse($file)
	{
		$ast = parent::parse($file);
		$ast = $this->deobfuscation_start($ast);
		return $ast;
	}
	/**
	 * Override eval to make a call to deobfuscate in the middle
	 */
	function evaluate_expression($node)
	{
		if ($node instanceof Node\Expr\Eval_)
		{
			$this->inc("eval/sum");
			$this->eval_depth++;
			if (!isset($this->stats['eval']['maxdepth']) or $this->stats['eval']['maxdepth']  < $this->eval_depth)
				$this->stats['eval']['maxdepth'] = $this->eval_depth;
			$this->verbose("Found Eval code...".PHP_EOL);

			$code=$this->evaluate_expression($node->expr);

			$this->deobfuscate($node, $code);

			$bu=$this->current_namespace;
			$this->current_namespace="";

			$ast=$this->parser->parse('<?php '.$code);
			$this->dynamic_node_traverser($ast);
			$res=$this->run_code($ast);
			$this->current_namespace=$bu;
			$this->eval_depth--;
			return $res;
		}
		else
			return parent::evaluate_expression($node);
	}
	/**
	 * Called on eval code to get stats
	 * @param  [type] $node [description]
	 * @return [type]       [description]
	 */
	function dynamic_node_traverser($node)
	{
		if (is_array($node))
        {
            foreach ($node as $n)
                $this->dynamic_node_traverser($n);
            return;
        }
        if ($node instanceof Node)
        {
            foreach ($node->getSubNodeNames() as $name)
                $this->dynamic_node_traverser($node->$name);
        }

        //stats
		if ($node instanceof Node\Stmt)
		{
			$this->inc("eval/statements/parsed");
		}
		if ($node instanceof Node\Expr)
		{
			$this->inc("eval/expressions/parsed");
		}
	}
	/**
	 * Parses and unparses the file once,
	 * so that we have a reference file where
	 * unparsed strings can match strings.
	 *
	 * Original file uses custom syntax and string match won't work.
	 *
	 * @param  Node $ast AST
	 * @return Node      AST
	 */
	function deobfuscation_start($ast)
	{
		$this->verbose(strcolor("Setting reference code for deobfuscation.\n",'blue'));

		$file = substr($this->entry_file, 0, -4);

		$this->deobfuscation_reference_code = $this->print_ast($ast);

		$this->deobfuscation_reference_file = "{$file}.normalized.php";

		file_put_contents($this->deobfuscation_reference_file, '<?php' . PHP_EOL . $this->deobfuscation_reference_code);

		return parent::parse($this->deobfuscation_reference_file);
	}
	public $deobfuscation_reference_file;
	public $deobfuscation_index = 0;
	public $deobfuscation_reference_code;

	/**
	 * Replace a node with its unwrapped (evaluated) version
	 * in the code.
	 *
	 * @param  Node $node           AST
	 * @param  string $unwrapped_code the code that is meant to be executed
	 */
	function deobfuscate($node, $unwrapped_code)
	{

		$this->deobfuscation_index++;
		$file = substr($this->deobfuscation_reference_file, 0, -4);
		$versioned_file = "{$file}.{$this->deobfuscation_index}.php";

		$original_code = $this->print_ast($node);

		$this->verbose(strcolor("Deobfuscating eval AST {$this->deobfuscation_index} on line {$node->getLine()}.\n",'blue'), 0);

		if (strpos($this->deobfuscation_reference_code, '@'.$original_code)!==false)
		{
			// replace @ at the beginning of eval
			$original_code = '@'.$original_code;
		}
		$code = str_replace($original_code, $unwrapped_code, $this->deobfuscation_reference_code);
		if (!$unwrapped_code)
		{
			$this->verbose(strcolor("Could not deobfuscated the following code on line {$node->getLine()}:\n",'purple'), 1);
			$this->inc('deobfuscation/failed');
			if ($this->verbose >= 1)
			{
				var_dump($original_code);
				echo str_repeat('-',80), PHP_EOL;
				var_dump($unwrapped_code);
			}
			$this->deobfuscation_index--;
		}
		else
		{
			$this->deobfuscation_reference_code = $code;
			file_put_contents($versioned_file, '<?php' . PHP_EOL . $code);
		}

	}

	protected function run_core_function($name,$args)
	{
		$this->inc('functions/core/sum');
		if ($this->eval_depth)
			$this->inc('eval/functions/core/sum');
		if (!array_key_exists($name, $this->unique_functions))
		{
			$this->unique_functions[$name] = true;
			$this->inc('functions/core/unique');
			if ($this->eval_depth)
				$this->inc('eval/functions/core/unique');
		}
		return parent::run_core_function($name, $args);
	}
	private $unique_functions = [];
	protected function run_function($function,$args,EmulatorExecutionContext $context,$trace_args=array(),$vars=[])
	{

		$function_id = $this->statement_id($function);
		$this->inc('functions/user/sum');
		if ($this->eval_depth)
			$this->inc('eval/functions/user/sum');
		if (!array_key_exists($function_id, $this->unique_functions))
		{
			$this->unique_functions[$function_id] = true;
			$this->inc('functions/user/unique');
			if ($this->eval_depth)
				$this->inc('eval/functions/user/unique');
		}

		return parent::run_function($function, $args, $context, $trace_args, $vars);
	}
	function run_statement($node)
	{
		if (!isset($this->stats['if']['maxdepth']) or $this->stats['if']['maxdepth'] < $this->if_nesting)
			$this->stats['if']['maxdepth'] = $this->if_nesting;

		if ($this->eval_depth)
			$this->inc("eval/statements/executed/sum");
		return parent::run_statement($node);
	}
	function get_declarations($node)
	{
		if ($node instanceof Node\Stmt\Function_)
			$this->inc("functions/user/declared");
		return parent::get_declarations($node);

	}



}


$usage="Usage: php main.php -f file.php [-v verbosity --output --strict --concolic --diehard --postprocessing]\n";
if (!isset($argc))
	die($usage);

$options=getopt("f:v:o",['strict','output','concolic','postprocessing','diehard','ppisolation']);
if (!isset($options['f']))
	die($usage);
$current_dir=getcwd();
$entry_file=$options['f'];
ini_set("memory_limit",-1);


$x=new PHPSandboxDeobfuscator();

$x->strict=isset($options['strict']);
$x->direct_output=isset($options['output']);
if (isset($options['v'])) $x->verbose=$options['v'];
$x->concolic=isset($options['concolic']);
$x->diehard=isset($options['diehard']);
$x->post_processing_isolation=isset($options['ppisolation']);
$x->quiet=isset($options['q']);

timer(0);
$x->start($entry_file);
$exec_time=timer();
$s = $x->stats;
$sep = PHP_EOL;
$sep = "\t";
// echo PHP_EOL, str_repeat("-",80), PHP_EOL;
echo basename($entry_file), $sep;
echo $s['statements']['parsed']??0, $sep;
// echo @$s['statements']['visited']['sum'], $sep;
// echo @$s['statements']['visited']['unique'], $sep;
// echo @$s['statements']['executed']['sum'], $sep;
// echo @$s['statements']['executed']['unique'], $sep;
echo $s['eval']['sum']??0, $sep;
echo $s['eval']['maxdepth']??0, $sep;
echo $s['eval']['statements']['parsed']??0, $sep;
// echo @$s['eval']['expressions']['parsed'], $sep;
// echo @$s['eval']['statements']['executed']['sum'], $sep;
// echo @$s['functions']['core']['sum'], $sep;
// echo @$s['functions']['core']['unique'], $sep;
// echo @$s['functions']['user']['declared'], $sep;
// echo @$s['functions']['user']['sum'], $sep;
// echo @$s['functions']['user']['unique'], $sep;
// echo $s['eval']['functions']['core']['sum'], $sep;
// echo @$s['eval']['functions']['core']['unique'], $sep;
// echo @$s['eval']['functions']['user']['sum'], $sep;
// echo @$s['eval']['functions']['user']['unique'], $sep;
echo $s['switch']['count']??0 + $s['if']['count']??0, $sep;
echo $s['switch']['branches']??0 + $s['if']['branches']??0, $sep;
echo $s['counterfactual']['switch']['count']??0 + $s['counterfactual']['if']['count']??0, $sep;
echo $s['counterfactual']['switch']['branches']??0 + $s['counterfactual']['if']['branches']??0, $sep;
echo $s['if']['maxdepth']??0, $sep;
echo $s['deobfuscation']['failed']??0, $sep;
echo (int)($exec_time*1000),"ms", $sep;
echo sprintf("%.1f", filesize($entry_file)/1024),"KB", $sep;
echo PHP_EOL;
// print_r($x->stats);

if (isset($x->termination_value))
	exit($x->termination_value);
else
	exit(0);

