<?php


namespace PHPEmul;


class SymbolicVariable
{
    public string $variable_name;
    public string $variable_value;
    public function __construct($variable_name='',$variable_value = '*')
    {
        $this->variable_name = $variable_name;
        $this->variable_value = $variable_value;
    }

    public function __toString()
    {
        return $this->variable_value;
        // for now see if this fixes the problem

        if ($this->variable_name !== '')
            return sprintf('SymbolicVariable for $%s', $this->variable_name);
        else
            return 'SymbolicVariable';
    }
}