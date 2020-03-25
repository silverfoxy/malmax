<?php
require_once __DIR__."/php-emul/PHP-Parser/lib/bootstrap.php";
use PhpParser\Node;
ini_set("memory_limit","-1");
function getAllPhpFiles($Path, $Limit = -1)
{
    $Directory = new RecursiveDirectoryIterator($Path);
    $Iterator = new RecursiveIteratorIterator($Directory);
    $Iterator = new LimitIterator($Iterator, 0, $Limit);
    $list=[];
    foreach ($Iterator as $it)
        if (pathinfo($it->getPathName(), PATHINFO_EXTENSION)=="php")
                $list[]=$it->getPathName();
    return $list;
}
class ObfuscationCounter extends PHPParser_NodeVisitorAbstract
{
    public $statements=0;
    public $expressions=0;
    public $includes=0;
    public $ifs=0,$switches=0;
    public function leaveNode(Node $node) {
        if ($node instanceof Node\Stmt) # plain strings
        // if ($node instanceof PHPParser_Node_Scalar_String) # plain strings
            $this->statements++;
        if ($node instanceof Node\Expr) # plain strings
            $this->expressions++;
        if ($node instanceof Node\Expr\Include_) # plain strings
            $this->includes++;
        if ($node instanceof Node\Stmt\If_) # plain strings
            $this->ifs++;
        if ($node instanceof Node\Stmt\Switch_) # plain strings
            $this->switches++;
        // elseif ($node instanceof PHPParser_Node_Scalar_Encapsed) # php encapsulated strings
        // {
        // 	foreach ($node->parts as $part)
        // 		if (is_string($part)) self::add($part);
        // }
    }
    function reset()
    {
        $this->statements=$this->expressions=$this->includes=$this->ifs=$this->switches=0;
        $this->stats=[];
    }
    public $stats=[];
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



        if ($node instanceof Node\Stmt)
        {
            $this->inc("statements/parsed");
        }
        if ($node instanceof Node\Expr)
        {
            if ($node instanceof Node\Expr\Exit_)
                $this->inc("node/exit");
            elseif ($node instanceof Node\Expr\Include_)
                $this->inc("node/include");
            elseif ($node instanceof Node\Expr\Eval_)
                $this->inc("node/eval");
        }
        if ($node instanceof Node\Stmt\If_)
        {
            $this->inc("node/if");
            $this->inc("node/branches");
            if (isset($node->elseifs))
                $this->inc("node/branches",count($node->elseifs));
            if (isset($node->else))
                $this->inc("node/branches");
        }
        if ($node instanceof Node\Stmt\Switch_)
            $this->inc("node/switch");
        // if (isset($node->stmts))
        //     $this->node_traverser($node->stmts);
        // if (isset($node->expr))
        //     $this->node_traverser($node->expr);
    }
}
echo "Listing files, this might take up to several minutes...";
flush();
$limit = -1;
if (count(@$argv)>2)
    $limit = $argv[2];

$targetdir=$argv[1];
if (is_file($targetdir))
    $files=[$targetdir];
else
    $files=getAllPhpFiles($targetdir, $limit);

echo " done.\n";

echo "Parsing, this might take up to several minutes...\n";
flush();

$parser = new PHPParser\Parser(new PHPParser\Lexer);	
$traverser     = new PHPParser\NodeTraverser;
// $prettyPrinter = new PHPParser_PrettyPrinter_Default;

$ObfuscationCounter=new ObfuscationCounter;
$traverser->addVisitor($ObfuscationCounter);
$n=0;
$LOC=0;
$found = 0;
foreach ($files as $file)
{
    $ObfuscationCounter->reset();
	$n++;
    // echo $file,PHP_EOL;
	// if (($n)%80==0) 
	// 	echo PHP_EOL;
	// else
	// 	echo ".";
    try {
        $syntax_tree = $parser->parse(file_get_contents($file));
        $ObfuscationCounter->node_traverser($syntax_tree);
    }
    catch (PHPParser_Error $e)
    {
        // echo PHP_EOL."ERROR: Unable to parse {$file}: ".$e->getMessage().PHP_EOL;
        continue;
    }
    $traverser->traverse($syntax_tree);
    $LOC=count(file($file));
    if (isset($ObfuscationCounter->stats['node']['eval']) and $ObfuscationCounter->stats['node']['eval']>=1)
    {
        $sep = "\t";
        $s= $ObfuscationCounter->stats;
        echo ++$found, $sep;
        echo $file, $sep;
        echo @$s['node']['eval'], $sep;
        echo @$s['node']['if'], $sep;
        echo @$s['node']['branches'], $sep;
        echo @$s['node']['switch'], $sep;
        echo @$s['node']['include'], $sep;
        echo @$s['statements']['parsed'], $sep;
        echo $LOC, $sep;
        echo sprintf('%.1fKB', filesize($file)/1024), $sep;

        // print_r($ObfuscationCounter->stats);
        echo PHP_EOL;
            // "Files: ",count($files),PHP_EOL,
            // "Statements: ",$ObfuscationCounter->statements,PHP_EOL,
            // "Expressions: ",$ObfuscationCounter->expressions,PHP_EOL,
            // "Ifs: ",$ObfuscationCounter->ifs,PHP_EOL,
            // "Switches: ",$ObfuscationCounter->switches,PHP_EOL,
            // "Includes: ",$ObfuscationCounter->includes,PHP_EOL,
            // "LOC: ",$LOC,PHP_EOL;
        // break;
    }
}
