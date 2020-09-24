<?php


namespace PHPEmul;


class SymbolicVariable
{
    public string $variable_name;
    public function __construct($variable_name='')
    {
        $this->variable_name = $variable_name;
    }

    public function __toString()
    {
        if ($this->variable_name !== '')
            return sprintf('SymbolicVariable for $%s', $this->variable_name);
        else
            return 'SymbolicVariable';
    }
}