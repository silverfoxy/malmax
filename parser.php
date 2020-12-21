<?php
require_once __DIR__."/php-emul/PHP-Parser/lib/bootstrap.php";
use PhpParser\Node;
ini_set("memory_limit","-1");
function getAllPhpFiles($Path)
{
    $Directory = new RecursiveDirectoryIterator($Path);
    $Iterator = new RecursiveIteratorIterator($Directory);
    $list=[];
    foreach ($Iterator as $it)
        if (pathinfo($it->getPathName(), PATHINFO_EXTENSION)=="php")
                $list[]=$it->getPathName();
    return $list;
}
class StatementCounter extends PHPParser_NodeVisitorAbstract
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
echo "Parsing, this might take up to several minutes...\n";
flush();


$targetdir=$argv[1];
if (is_file($targetdir))
    $files=[$targetdir];
else
    $files=(getAllPhpFiles($targetdir));

$parser = new PHPParser\Parser(new PHPParser\Lexer);	
$traverser     = new PHPParser\NodeTraverser;
// $prettyPrinter = new PHPParser_PrettyPrinter_Default;

$StatementCounter=new StatementCounter;
$traverser->addVisitor($StatementCounter);
$n=0;
$LOC=0;
foreach ($files as $file)
{
	$n++;
    // echo $file,PHP_EOL;
	if (($n)%80==0) 
		echo PHP_EOL;
	else
		echo ".";
    try {
        $syntax_tree = $parser->parse(file_get_contents($file));
        $StatementCounter->node_traverser($syntax_tree);
    }
    catch (PHPParser_Error $e)
    {
        echo PHP_EOL."ERROR: Unable to parse {$file}: ".$e->getMessage().PHP_EOL;
        continue;
    }
    $traverser->traverse($syntax_tree);
    $LOC+=count(file($file));
}
print_r($StatementCounter->stats);
echo PHP_EOL,
    "Files: ",count($files),PHP_EOL,
    "Statements: ",$StatementCounter->statements,PHP_EOL,
    "Expressions: ",$StatementCounter->expressions,PHP_EOL,
    "Ifs: ",$StatementCounter->ifs,PHP_EOL,
    "Switches: ",$StatementCounter->switches,PHP_EOL,
    "Includes: ",$StatementCounter->includes,PHP_EOL,
    "LOC: ",$LOC,PHP_EOL;
