<?php

namespace PHPEmul;

use PhpParser\Node;

class LineLogger
{
    /*
     * Array [file_name => [ lines... ], ... ]
     */
    public array $coverage_info = [];
    public array $reanimation_coverage_info = [];
    public array $raw_logs = [];
    public string $PATH_PREFIX;
    public string $correlation_id;

    public function __construct(string $PATH_PREFIX, string $correlation_id) {
        $this->PATH_PREFIX = $PATH_PREFIX;
        $this->correlation_id = $correlation_id;
    }

    public function logTerminationReason(string $termination_reason) {
        file_put_contents($this->PATH_PREFIX.$this->correlation_id.'_line_coverage_logs.txt', $termination_reason, FILE_APPEND);
    }

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
            case ($node instanceof Node\Stmt\For_):
            case ($node instanceof Node\Stmt\Namespace_):
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
                $end_line = $node->getAttribute('startLine');
                // $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $node->expr->getAttribute('startLine'), $node->expr->getAttribute('endLine'));
                break;
            case ($node instanceof Node\Stmt\Function_):
                // Log only the first line of the function
                $start_line = $node->getAttribute('startLine');
                $end_line = $node->getAttribute('startLine');
                // $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $node->getAttribute('startLine'), $node->getAttribute('startLine'));
                break;
            case ($node instanceof Node\Stmt\Switch_):
                $start_line = $node->getAttribute('startLine');
                $end_line = $node->getAttribute('startLine');
                break;
            default:
                $start_line = $node->getAttribute('startLine');
                $end_line = $node->getAttribute('endLine');
                // $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $current_file, $node->getAttribute('startLine'), $node->getAttribute('endLine'));
                break;
        }
        $this->updateCoverageInfo($current_file, $start_line, $end_line);
        $file_name = str_replace('/mnt/c/Users/baminazad/Documents/Pragsec/autodebloating/debloating_DVWA/web/', '', $current_file);
        $log_entry =  sprintf('%s: %s-%s'.PHP_EOL, $file_name, $start_line, $end_line);
        $this->raw_logs[] = $log_entry;
        file_put_contents($this->PATH_PREFIX.$this->correlation_id.'_line_coverage_logs.txt', $log_entry, FILE_APPEND);
    }

    public function logFunctionCall(?string $current_file, string $function_name, array $parameters) {
        $print_parameters = '';
        $current_file = $current_file ?? '';
        foreach($parameters as $param_name=>$param_value) {
            $print_parameters .= sprintf('%s=%s, ', $param_name, print_r($param_value, true));
        }
        if (strlen($print_parameters) > 2) {
            // Drop the last comma-space
            $print_parameters = substr($print_parameters, 0, strlen($print_parameters) - 2);
        }
        $file_name = str_replace('/mnt/c/Users/baminazad/Documents/Pragsec/autodebloating/debloating_DVWA/web/', '', $current_file);
        $log_entry =  sprintf('%s: FuncCall-%s(%s)'.PHP_EOL, $file_name, $function_name, $print_parameters);
        $this->raw_logs[] = $log_entry;
        file_put_contents($this->PATH_PREFIX.$this->correlation_id.'_function_call_logs.txt', $log_entry, FILE_APPEND);
    }

    public function logFunctionCallReturnValue(?string $current_file, string $function_name, array $parameters, $return_value) {
        $print_parameters = '';
        $current_file = $current_file ?? '';
        foreach($parameters as $param_name=>$param_value) {
            $print_parameters .= sprintf('%s=%s, ', $param_name, print_r($param_value, true));
        }
        if (strlen($print_parameters) > 2) {
            // Drop the last comma-space
            $print_parameters = substr($print_parameters, 0, strlen($print_parameters) - 2);
        }
        $file_name = str_replace('/mnt/c/Users/baminazad/Documents/Pragsec/autodebloating/debloating_DVWA/web/', '', $current_file);
        $log_entry =  sprintf('%s: FuncCall-%s(%s): %s'.PHP_EOL, $file_name, $function_name, $print_parameters, print_r($return_value, true));
        $this->raw_logs[] = $log_entry;
        file_put_contents($this->PATH_PREFIX.$this->correlation_id.'_function_call_logs.txt', $log_entry, FILE_APPEND);
    }

    protected function updateCoverageInfo(string $current_file, int $start_line, int $end_line) {
        for($line_number = $start_line; $line_number <= $end_line; $line_number++) {
            $this->coverage_info[$current_file][$line_number] = true;
            $this->reanimation_coverage_info[$current_file][$line_number] = true;
        }
    }

    public static function has_covered_new_lines(array $new_coverage_info, array $old_coverage_info) {
        foreach ($new_coverage_info as $filename=>$lines) {
            if (array_key_exists($filename, $old_coverage_info)) {
                foreach ($lines as $line=>$covered) {
                      if (!array_key_exists($line, $old_coverage_info[$filename])) {
                          return true;
                      }
                }
            }
            else {
                return true;
            }
        }
        return false;
    }
}