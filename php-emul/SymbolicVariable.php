<?php


namespace PHPEmul;


use PhpParser\NodeAbstract;

class SymbolicVariable
{
    public string $variable_name;
    public string $variable_value;
    public $isset;
    public $type;
    public array $concrete_values = [];

    public function __construct($variable_name='',$variable_value = '*', $type=NodeAbstract::class, $isset=null, ?array $concrete_values=[])
    {
        /*
         * For normal SymbolicVariables, isset also returns symbolic.
         * In extended logs emulation mode, certain Symbolic parameters Symbolic but isset returns true
         */
        $this->isset = $isset ?? new SymbolicVariable('isset', '*', $type, true);
        $this->variable_name = $variable_name;
        $this->variable_value = $variable_value;
        $this->type = $type;
        $this->concrete_values = $concrete_values ?? [];
    }

    public function __toString()
    {
        /* Default value is "*" which works with regex for file inclusion
         * May confuse logging in other places
         */
        return $this->variable_value;
    }
}
