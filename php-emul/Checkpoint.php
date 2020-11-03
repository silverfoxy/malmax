<?php

namespace PHPEmul;

use malmax\PHPAnalyzer;
use PhpParser\Node;

class Checkpoint
{
    public Emulator $checkpoint_context;
    public int $next_instruction;

    public function __construct(Emulator $checkpoint, string $filename, Node $node) {
        $this->checkpoint_context = $checkpoint;
        $this->next_instruction = Checkpoint::instruction_id($filename, $node);
    }

    /**
     * Returns a 32bit integer supposedly unique for each node
     * based on File, Start/End Lines and instruction type
     */
    public static function instruction_id(string $filename, Node $node) {
        if (is_object($node) and method_exists($node, "getStartLine"))
        {
            $line_range = $node->getStartLine().'-'.$node->getEndLine();
            $type = get_class($node);
            return crc32("$filename:$line_range:$type");
        }
        return 0;
    }
}