<?php

namespace PHPEmul;

use PhpParser\Node;

class LineLogger
{
    /*
     * Array [file_name => [ lines... ], ... ]
     */
    public array $coverage_info = [];
    public CONST LOGPATH = '/mnt/c/Users/baminazad/Documents/Pragsec/autodebloating/malmax/index_logs.txt';
    public function logNodeCoverage(Node $node, $current_file) {
        $log_entry = '';
        $start_line = 0;
        $end_line = 0;
        switch (true) {
            case ($node instanceof Node\Stmt\Class_):
                // Log only the first line of the class
                $start_line = $node->getAttribute('startLine');
                $end_line = $node->getAttribute('startLine');
                // $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $node->getAttribute('startLine'), $node->getAttribute('startLine'));
                break;
            case ($node instanceof Node\Stmt\If_):
            case ($node instanceof Node\Stmt\While_):
                // Log only the condition of the branch
                $start_line = $node->getAttribute('startLine');
                $end_line = $node->getAttribute('startLine');
                // $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $node->cond->getAttribute('startLine'), $node->cond->getAttribute('endLine'));
                break;
            case ($node instanceof Node\Stmt\ElseIf_):
            case ($node instanceof Node\Stmt\Else_):
                $start_line = 0;
                $end_line = 0;
                break;
            case ($node instanceof Node\Stmt\TryCatch):
            // case ($node instanceof Node\Stmt\Finally):
                // Log only the first line of the class
                $start_line = $node->getAttribute('startLine');
                $end_line = $node->getAttribute('startLine');
                // $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $node->getAttribute('startLine'), $node->getAttribute('startLine'));
                break;
            case ($node instanceof Node\Stmt\Foreach_):
                // Log only the expression of the loop
                $start_line = $node->getAttribute('startLine');
                $end_line = $node->getAttribute('endLine');
                // $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $node->expr->getAttribute('startLine'), $node->expr->getAttribute('endLine'));
                break;
            case ($node instanceof Node\Stmt\For_):
                $start_line = 0;
                $end_line = 0;
                break;
            case ($node instanceof Node\Stmt\Namespace_):
                $start_line = 0;
                $end_line = 0;
                break;
            case ($node instanceof Node\Stmt\Function_):
                // Log only the first line of the function
                $start_line = $node->getAttribute('startLine');
                $end_line = $node->getAttribute('startLine');
                // $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $node->getAttribute('startLine'), $node->getAttribute('startLine'));
                break;
            default:
                $start_line = $node->getAttribute('startLine');
                $end_line = $node->getAttribute('endLine');
                // $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $node->getAttribute('startLine'), $node->getAttribute('endLine'));
                break;
        }
        $this->updateCoverageInfo($current_file, $start_line, $end_line);
        $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $start_line, $end_line);
        file_put_contents('/mnt/c/Users/baminazad/Documents/Pragsec/autodebloating/line_coverage_logs.txt', $log_entry, FILE_APPEND);
    }

    protected function updateCoverageInfo(string $current_file, int $start_line, int $end_line) {
        for($line_number = $start_line; $line_number <= $end_line; $line_number++) {
            $this->coverage_info[$current_file][$line_number] = true;
        }
    }
}