<?php

namespace PHPEmul;

use malmax\ExecutionMode;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;

class EmulatorClosure {
    public string $name;
    public array $code;
    public array $params;
    public bool $static;
    public bool $byRef;
    public array $uses;
    public $returnType;
    public EmulatorExecutionContext $context;

    public function __construct(string $name, array $code, array $params, bool $static, bool $byRef, array $uses, $returnType, EmulatorExecutionContext $context) {
        $this->name = $name;
        $this->code = $code;
        $this->params = $params;
        $this->static = $static;
        $this->byRef = $byRef;
        $this->uses = $uses;
        $this->returnType = $returnType;
        $this->context = $context;
    }

    public static function bind(EmulatorClosure $closure, ?object $newthis, $newscope='static') {
        $closure = self::bind_object_and_scope($closure, $newthis, $newscope);
        return $closure;
    }

    public function bindTo(?object $newthis, $newscope='static') {
        $closure = self::bind_object_and_scope($this->closure, $newthis, $newscope);
        return $closure;
    }

    public function call(?object $newthis, $params) {
        trigger_error('fromCallable not implemented.', E_USER_ERROR);
    }

    public static function fromCallable(callable $callable) {
        trigger_error('fromCallable not implemented.', E_USER_ERROR);
        $closure = new EmulatorClosure();
        $closure->name="{closure}";
        $closure->code=$callable->stmts;
        $closure->params=$callable->params;

        $closure->static=false;
        $closure->byref=false;
        $closure->uses=[];
        $closure->returnType=$callable->returnType;

        $context=new EmulatorExecutionContext(['function'=>"{closure}"
            ,'namespace'=>$callable->current_namespace,'active_namespaces'=>$callable->current_active_namespaces
            ,'file'=>$callable->current_file,'line'=>$callable->current_line]);

        $closure->context=$context;
        return $closure;
    }

    protected static function bind_object_and_scope(EmulatorClosure $closure, ?object $newthis, $newscope='static') {
        $closure->scope = $newscope;
        $closure->bound_object = $newthis;
        return $closure;
    }

};
/**
 * Evaluates an expression for the emulator
 */
trait EmulatorExpression {

	
	protected function expression_preprocess($node)
	{
		$this->current_node=$node;
		if (is_object($node) and method_exists($node, "getLine") and $node->getLine()!=$this->current_line)
		{
			$this->current_line=$node->getLine();
			$this->verbose("Line {$this->current_line} (expression)".PHP_EOL,4);
		}	
	}

	/**
	 * Evaluate all nodes of type Node\Expr and return appropriate value
	 * This is the core of the emulator/interpreter.
	 * @param  Node $ast Abstract Syntax Tree node
	 * @return mixed      value
	 */
	protected function evaluate_expression($node, &$is_symbolic = false)
	{
		if ($this->terminated) return null;
		$this->expression_preprocess($node);		
		if ($node===null)
			return null;
		elseif ($node instanceof SymbolicVariable) {
            $is_symbolic = true;
            return $node;
        }
		elseif (is_array($node)) {
            return $node;
            $this->error("Did not expect array node!",$node);
        }
		elseif ($node instanceof Node\Expr\FuncCall)
		{
			$name=$this->name($node);
			$ret = $this->call_function($name,$node->args);
			if ($ret instanceof SymbolicVariable) {
			    $is_symbolic = true;
            }
			return $ret;
			
		}

		elseif ($node instanceof Node\Expr\AssignRef)
		{
		    $expr_value = $this->evaluate_expression($node->expr);
		    if (!($expr_value instanceof SymbolicVariable)) {
                if (!$this->variable_isset($node->expr)) //referencing creates
                    $this->variable_set($node->expr);
                $originalVar=&$this->variable_reference($node->expr,$success);
                if ($success)
                    $this->variable_set_byref($node->var,$originalVar);
                else
                    $this->warning("Can not assign by reference, the referenced variable does not exist");
            }
		    else {
		        $this->variable_set($node->var, $expr_value);
            }
		}

		elseif ($node instanceof Node\Expr\Assign)
		{
			if ($node->var instanceof Node\Expr\List_) //list(x,y)=f()
			{
				$resArray=$this->evaluate_expression($node->expr, $is_symbolic);
				// 	PHP's list uses numeric iteration over the resArray. 
				// 	This means that list($a,$b)=[1,'a'=>'b'] will error "Undefined offset: 1"
				// 	because it wants to assign $resArray[1] to $b. Thus we use index here.
				$index=0;
				$outArray=[];
				foreach ($node->var->items as $var)
				{
				    if ($resArray instanceof SymbolicVariable) {
				        $outArray[] = new SymbolicVariable();
                    }
					elseif (!array_key_exists($index, $resArray))
						$this->notice("Undefined offset: {$index}");
					if ($var===null)
						$outArray[]=$resArray[$index++];
					elseif (!$resArray instanceof SymbolicVariable)
						$outArray[]=$this->variable_set($var,$resArray[$index++]);
				}
				//return the rest of offsets, they are not assigned to anything by list, but still returned.
				while ( $index<count($resArray))
				{
					if (!isset($resArray[$index]))
						$this->notice("Undefined offset: {$index}");
					$outArray[]=$resArray[$index++];
				}
				return $outArray;
			}
			else
			{
				return $this->variable_set($node->var,$this->evaluate_expression($node->expr));
			}
		}
		elseif ($node instanceof Node\Expr\ArrayDimFetch) {
            //access multidimensional arrays $x[...][..][...]
            $array_dim_fetch = $this->variable_get($node); //should not create
            if ($array_dim_fetch instanceof SymbolicVariable) {
                $is_symbolic = true;
                return $array_dim_fetch;
            }
            else {
                return $array_dim_fetch;
            }
        }
		elseif ($node instanceof Node\Expr\Array_)
		{
			$out=[];
			foreach ($node->items as $item)
			{
				if (isset($item->key))
				{
					$key=$this->evaluate_expression($item->key, $is_symbolic);
					if ($item->byRef)
						$out[$key]=&$this->variable_reference($item->value, $is_symbolic);
					else
						$out[$key]=$this->evaluate_expression($item->value, $is_symbolic);
				}
				else
					if ($item->byRef)
						$out[]=&$this->variable_reference($item->value, $is_symbolic);
					else
						$out[]=$this->evaluate_expression($item->value, $is_symbolic);
			}
			return $out;
		}
		elseif ($node instanceof Node\Expr\Cast)
		{
		    $expr = $this->evaluate_expression($node->expr);
		    if ($expr instanceof SymbolicVariable) {
		        return $expr;
            }
			if ($node instanceof Node\Expr\Cast\Int_)
				return (int)$expr;
			elseif ($node instanceof Node\Expr\Cast\Array_)
				return (array)$expr;
			elseif ($node instanceof Node\Expr\Cast\Double)
				return (double)$expr;
			elseif ($node instanceof Node\Expr\Cast\Bool_)
				return (bool)$expr;
			elseif ($node instanceof Node\Expr\Cast\String_)
				return (string)$expr;
			// elseif ($node instanceof Node\Expr\Cast\Object_)
			// 	return (object)$this->evaluate_expression($node->expr);
			else
				$this->error("Unknown cast: ",$node);
		}
		elseif ($node instanceof Node\Expr\BooleanNot) {
            $expr = $this->evaluate_expression($node->expr);
            if ($expr instanceof SymbolicVariable) {
                $result = clone $expr;
                $result->variable_name = sprintf('not(%s)', $result->variable_name);
                return $result;
            }
            if ($expr instanceof \stdClass) {
                return false;
            } else {
                return !$this->evaluate_expression($expr, $is_symbolic);            }

        }
		elseif ($node instanceof Node\Expr\BitwiseNot) {
            $expr = $this->evaluate_expression($node->expr);
            if ($expr instanceof SymbolicVariable) {
                $result = clone $expr;
                $result->variable_name = sprintf('not(%s)', $result->variable_name);
                return $result;
            }
            return ~$this->evaluate_expression($expr, $is_symbolic);
        }
		
		elseif ($node instanceof Node\Expr\UnaryMinus) {
            $expr = $this->evaluate_expression($node->expr);
            if ($expr instanceof SymbolicVariable) {
                $result = clone $expr;
                $result->variable_name = sprintf('-(%s)', $result->variable_name);
                return $result;
            }
            return -$this->evaluate_expression($expr, $is_symbolic);
        }
		elseif ($node instanceof Node\Expr\UnaryPlus) {
            $expr = $this->evaluate_expression($node->expr);
            if ($expr instanceof SymbolicVariable) {
                $result = clone $expr;
                $result->variable_name = sprintf('+(%s)', $result->variable_name);
                return $result;
            }
            return +$this->evaluate_expression($expr, $is_symbolic);
        }
		elseif ($node instanceof Node\Expr\PreInc)
		{
			return $this->variable_set($node->var,$this->variable_get($node->var)+1);	
		}
		elseif ($node instanceof Node\Expr\PostInc)
		{
			$t=$this->variable_get($node->var);
			$this->variable_set($node->var,$t+1);
			return $t;
		}
		elseif ($node instanceof Node\Expr\PreDec)
		{
			return $this->variable_set($node->var,$this->variable_get($node->var)-1);	
		}
		elseif ($node instanceof Node\Expr\PostDec)
		{
			$t=$this->variable_get($node->var);
			$this->variable_set($node->var,$t-1);
			return $t;
		}
		elseif ($node instanceof Node\Expr\AssignOp)
		{
			$var=&$this->variable_reference($node->var); //TODO: use variable_set and get here instead
			$val=$this->evaluate_expression($node->expr, $is_symbolic);
            if ($val instanceof SymbolicVariable) {
                // Performing AssignOp when RHS is symbolic would cast the RHS to string (eg '*')
                return $var = $val;
            }
			if ($node instanceof Node\Expr\AssignOp\Plus)
				return $var+=$val;
			elseif ($node instanceof Node\Expr\AssignOp\Minus)
				return $var-=$val;
			elseif ($node instanceof Node\Expr\AssignOp\Mod)
				return $var%=$val;
			elseif ($node instanceof Node\Expr\AssignOp\Mul)
				return $var*=$val;
			elseif ($node instanceof Node\Expr\AssignOp\Div)
				return $var/=$val;
			// elseif ($node instanceof Node\Expr\AssignOp\Pow)
			// 	return $this->variables[$this->name($node->var)]**=$this->evaluate_expression($node->expr);
			elseif ($node instanceof Node\Expr\AssignOp\ShiftLeft)
				return $var<<=$val;
			elseif ($node instanceof Node\Expr\AssignOp\ShiftRight)
				return $var>>=$val;
			elseif ($node instanceof Node\Expr\AssignOp\Concat)
				return $var.=$val;
			elseif ($node instanceof Node\Expr\AssignOp\BitwiseAnd)
				return $var&=$val;
			elseif ($node instanceof Node\Expr\AssignOp\BitwiseOr)
				return $var|=$val;
			elseif ($node instanceof Node\Expr\AssignOp\BitwiseXor)
				return $var^=$val;
		}
		elseif ($node instanceof Node\Expr\BinaryOp)
		{
			$l=$this->evaluate_expression($node->left, $is_symbolic); #can't eval right here, prevents short circuit reliant code
            if (!($node instanceof Node\Expr\BinaryOp\BooleanAnd ||
                  $node instanceof Node\Expr\BinaryOp\BooleanOr  ||
                  $node instanceof Node\Expr\BinaryOp\LogicalAnd ||
                  $node instanceof Node\Expr\BinaryOp\LogicalOr)) {
                // No short circuiting is involved and we can evaluate the right hand sight now
                $r = $this->evaluate_expression($node->right, $is_symbolic);
            }
			if ($node instanceof Node\Expr\BinaryOp\Plus) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l+$r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\Div) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l/$r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\Minus) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l-$r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\Mul) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l*$r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\Mod) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l % $r;
            }
			// elseif ($node instanceof Node\Expr\BinaryOp\Pow)
			// 	return $this->evaluate_expression($node->left)**$this->evaluate_expression($node->right);
			elseif ($node instanceof Node\Expr\BinaryOp\Identical) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l === $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\NotIdentical) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l !== $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\Equal) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l == $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\NotEqual) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l != $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\Smaller) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l < $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\SmallerOrEqual) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l <= $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\Greater) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l > $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\GreaterOrEqual) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l >= $r;
            }
            elseif ($node instanceof Node\Expr\BinaryOp\Spaceship) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return new SymbolicVariable();
                }
                if ($l<$r) {
                    return -1;
                }
                elseif ($l>$r) {
                    return 1;
                }
                return 0;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\LogicalAnd) {
			    if ($l) {
			        $r = $this->evaluate_expression($node->right, $is_symbolic);
			        if ($is_symbolic || $r instanceof SymbolicVariable) {
                        return clone $r;
                    }
                    return $l and $r;
                }
			    else {
			        return false;
                }
            }
			elseif ($node instanceof Node\Expr\BinaryOp\Coalesce) {
                if ($l instanceof SymbolicVariable) {
                    $forked_process_info = $this->fork_execution([]);
                    list($pid, $child_pid) = $forked_process_info;
                    if ($child_pid === 0) {
                        return $l;
                    } else {
                        return $r;
                    }
                } elseif (isset($l)) {
                    return $l;
                } else {
                    return $r;
                }
            }
			elseif ($node instanceof Node\Expr\BinaryOp\LogicalOr) {
                if ($l) {
                    return true;
                }
                else {
                    $r = $this->evaluate_expression($node->right, $is_symbolic);
                    if ($is_symbolic || $r instanceof SymbolicVariable) {
                        return clone $r;
                    }
                    return $l or $r;
                }
            }
			elseif ($node instanceof Node\Expr\BinaryOp\LogicalXor) {
                return $l xor $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\BooleanOr) {
			    if ($l && !$l instanceof SymbolicVariable) {
			        return true;
                }
			    else {
                    $r = $this->evaluate_expression($node->right, $is_symbolic);
                    if ($r instanceof SymbolicVariable) {
                        return clone $r;
                    }
                    elseif($l instanceof SymbolicVariable) {
                        return clone $l;
                    }
                    else {
                        return $l || $r;
                    }
                }
            }
			elseif ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
			    if ($l) {
                    $r = $this->evaluate_expression($node->right, $is_symbolic);
                    if ($r instanceof SymbolicVariable) {
                        return clone $r;
                    }
                    elseif ($r == false) {
                        return false;
                    }
                    elseif ($l instanceof SymbolicVariable) {
                        return clone $l;
                    }
                    return true;
                }
			    else {
			        return false;
                }
            }
			elseif ($node instanceof Node\Expr\BinaryOp\BitwiseAnd) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l & $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\BitwiseOr) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l | $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\BitwiseXor) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l ^ $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\ShiftLeft) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l << $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\ShiftRight) {
                if ($l instanceof SymbolicVariable || $r instanceof SymbolicVariable) {
                    return clone ($l instanceof SymbolicVariable ? $l : $r);
                }
                return $l >> $r;
            }
			elseif ($node instanceof Node\Expr\BinaryOp\Concat) {
			    if ($l instanceof  SymbolicVariable && $r instanceof SymbolicVariable) {
			        return new SymbolicVariable("symbolic concat", $l->variable_value . $r->variable_value, String_::class);
                }
			    if ($l instanceof SymbolicVariable) {
			        return new SymbolicVariable("symbolic concat", $l->variable_value . $r, String_::class);
                } else if ($r instanceof  SymbolicVariable) {
                    return new SymbolicVariable("symbolic concat", $l . $r->variable_value, String_::class);
                }

                return $l . $r;
            }
			// elseif ($node instanceof Node\Expr\BinaryOp\Spaceship)
			// 	return $this->evaluate_expression($node->left)<=>$this->evaluate_expression($node->right);
			else
				$this->error("Unknown binary op: ",$node);
		}
		elseif ($node instanceof Node\Scalar)
		{
			if ($node instanceof String_)
				return $node->value;
			elseif ($node instanceof Node\Scalar\EncapsedStringPart)
                return $node->value;
			elseif ($node instanceof Node\Scalar\DNumber)
				return $node->value;
			elseif ($node instanceof Node\Scalar\LNumber)
				return $node->value;
			elseif ($node instanceof Node\Scalar\Encapsed)
			{
				$res="";
				foreach ($node->parts as $part) {
                    if (is_string($part)) {
                        $res .= $part;
                    } else {
                        $res .= $this->evaluate_expression($part, $is_symbolic);
                    }
                }
                if ($is_symbolic) {
                    return new SymbolicVariable('Encapsed', $res);
                }
				return $res;
			}
			elseif ($node instanceof Node\Scalar\MagicConst)
			{
				if ($node instanceof Node\Scalar\MagicConst\File)
					return $this->current_file;
				elseif ($node instanceof Node\Scalar\MagicConst\Dir)
					return dirname($this->current_file);
				elseif ($node instanceof Node\Scalar\MagicConst\Line)
					return $node->getLine();
				elseif ($node instanceof Node\Scalar\MagicConst\Function_)
					return $this->current_function;
				elseif ($node instanceof Node\Scalar\MagicConst\Class_)
					return $this->current_self;
				elseif ($node instanceof Node\Scalar\MagicConst\Method)
					return $this->current_method;
				elseif ($node instanceof Node\Scalar\MagicConst\Namespace_)
					return $this->current_namespace;
				elseif ($node instanceof Node\Scalar\MagicConst\Trait_)
					return $this->current_trait;
			}
			else
				$this->error("Unknown scalar node: ",$node);
		}
		// elseif ($node instanceof Node\Expr\ArrayItem); //this is handled in Array_ implicitly
		
		elseif ($node instanceof Node\Expr\Variable)
		{
		    $target_var = $this->variable_get($node); //should not be created on access
            if ($target_var instanceof SymbolicVariable) {
                $is_symbolic = true;
            }
			return $target_var;
		}
		elseif ($node instanceof Node\Expr\ConstFetch)
		{
			return $this->constant_get($this->name($node->name));

		}
		elseif ($node instanceof Node\Expr\ErrorSuppress)
		{
			// $error_reporting=error_reporting();
			// error_reporting(0);
			$this->error_silence();
			$res=$this->evaluate_expression($node->expr, $is_symbolic);
			$this->error_restore(); 
			return $res;
		} 
		elseif ($node instanceof Node\Expr\Exit_)
		{
			$this->verbose(sprintf("Terminated at %s:%d.\n",substr($this->current_file,strlen($this->folder)),$this->current_line));
			if (isset($node->expr))
			{
				$res=$this->evaluate_expression($node->expr, $is_symbolic);
				if (!is_int($res))
					$this->output($res);
				else
					$this->termination_value=$res;
			}
			else
				$res=null;

			$this->terminated=true;	
			return $res;
		}
		elseif ($node instanceof Node\Expr\Empty_)
		{
            $this->error_silence();
		    $expr_value = $this->evaluate_expression($node->expr, $is_symbolic);
		    if ($expr_value instanceof SymbolicVariable) {
                $this->error_restore();
		        return !$expr_value->isset;
            }
			//return true if not isset, or if false. only supports variables, and not expressions
            $res = false;
		    if (is_array($expr_value))
            {
                $res = count($expr_value) == 0;
            } else
            {
                $res = (!$this->variable_isset($node->expr) or ($expr_value == null));
            }
			$this->error_restore();
			return $res;
		}
		elseif ($node instanceof Node\Expr\Isset_)
		{
			#FIXME: if the name expression is multipart, and one part of it also doesn't exist this warns. Does PHP too?
			//return false if not isset, or if null
			$res=true;
			$result_is_symbolic = false;
			foreach ($node->vars as $var)
			{
				$var_isset = $this->variable_isset($var);
                if ($var_isset instanceof SymbolicVariable) {
                    $res = $var_isset->isset;
                    $result_is_symbolic = true;
                }
				elseif (!$var_isset)
				{
					$res=false;
					break;
				}
			}
			if ($res && $result_is_symbolic) {
			    // If all the concolic variables are set, and we also have a Symbolic variable, return Symbolic variable
                $is_symbolic = true;
                return $res;
            }
            return $res;
		}
		elseif ($node instanceof Node\Expr\Eval_)
		{
			
			$this->eval_depth++;
			$this->verbose("Now running Eval code...".PHP_EOL);
			$code=$this->evaluate_expression($node->expr);
			
			$bu=$this->current_namespace;
			$this->current_namespace="";	
			
			$ast=$this->parser->parse('<?php '.$code);
			$res=$this->run_code($ast);
			#TODO: (not important) check whether active namespaces (i.e. uses) are discarded in eval as well or not
			$this->current_namespace=$bu;
			$this->eval_depth--;
			return $res;
		}
		elseif ($node instanceof Node\Expr\ShellExec)
		{
				$res="";
				foreach ($node->parts as $part)	
					if (is_string($part))
						$res.=$part;
					else
						$res.=$this->evaluate_expression($part, $is_symbolic);

				return shell_exec($res);
		}
		elseif ($node instanceof Node\Expr\Instanceof_)
		{
			$var=$this->evaluate_expression($node->expr, $is_symbolic);
			$classname=$this->name($node->class);
			return $var instanceof $classname;
		}
		elseif ($node instanceof Node\Expr\Print_)
		{
			$out=$this->evaluate_expression($node->expr, $is_symbolic);
			$this->output($out);	
			return $out;
		}
		elseif ($node instanceof Node\Expr\Include_)
		{
			$type=$node->type; //1:include,2:include_once,3:require,4:require_once
			$names=[null,'include','include_once','require','require_once'];
			$name=$names[$type];
			$file = $this->evaluate_expression($node->expr, $is_symbolic);
			$realfiles = array();

			if ($file instanceof SymbolicVariable) {
                // Get the list of candidate files for include statement
                $candidates = $this->get_candidate_files($file);
			    foreach ($candidates as $candid)
                {
                    array_push($realfiles, $this->get_include_file_path($candid));
                }
            } else {
                $realfiles = array($this->get_include_file_path($file));
            }

			$candidate_files = count($realfiles);
			$processed_files = 1;
			foreach ($realfiles as $realfile)
            {
                if (!$file instanceof SymbolicVariable) {
                    /* Symbolic files are always mapped to existing files on the file system as per
                     * "get_candidate_files"s implementation
                     */
                    if ($type%2==0) //once
                        if (isset($this->included_files[$realfile])) {
                            return true;
                        }
                    if (!file_exists($realfile) or !is_file($realfile)) {
                        if ($type <= 2) { // include
                            $this->warning("{$name}({$file}): failed to open stream: No such file or directory");
                        } else { // require
                            $this->error("{$name}({$file}): failed to open stream: No such file or directory");
                        }
                        return false;
                    }
                }

                if ($processed_files === $candidate_files) {
                    // No need to fork for single or last file to be included
                    array_push($this->trace, (object)array("type"=>"","function"=>$name,"file"=>$this->current_file,"line"=>$this->current_line,
                        "args"=>[$realfile]));
                    $r=$this->run_file($realfile);
                    array_pop($this->trace);
                    return $r;
                } else {
                    $forked_process_info = $this->fork_execution([$realfile => []]);
                    list($pid, $child_pid) = $forked_process_info;
                    if ($child_pid === 0) {
                        array_push($this->trace, (object)array("type"=>"","function"=>$name,"file"=>$this->current_file,"line"=>$this->current_line,
                            "args"=>[$realfile]));
                        $r=$this->run_file($realfile);
                        array_pop($this->trace);
                        return $r;
                    }
                    else {
                        $processed_files += 1;
                    }
                }
            }
		}
		elseif ($node instanceof Node\Expr\Ternary)
		{
		    $expr_result = $this->evaluate_expression($node->cond);
		    if ($expr_result instanceof SymbolicVariable) {
                if ($this->execution_mode === ExecutionMode::OFFLINE) {
                    if ($this->checkpoint_restore_mode) {
                        $this->checkpoint_restore_mode = false;
                        return $this->evaluate_expression($node->if);
                    }
                    else {
                        if (LineLogger::has_covered_new_lines($this->lineLogger->coverage_info, $this->overall_coverage_info)) {
                            $this->checkpoints[] = new Checkpoint($this->last_checkpoint, $this->current_file, $node->if);
                            return $this->evaluate_expression($node->else);
                        }
                        else {
                            $this->terminated = true;
                            return;
                        }
                    }
                }
                elseif ($this->execution_mode === ExecutionMode::ONLINE) {
                    $forked_process_info = $this->fork_execution($this->get_next_branch_lines($node, $node->else));
                    if ($forked_process_info !== false) {
                        list($pid, $child_pid) = $forked_process_info;
                        if ($child_pid === 0) {
                            return $this->evaluate_expression($node->if);
                        } else {
                            return $this->evaluate_expression($node->else);
                        }
                    } else {
                        // Terminated
                        return;
                    }
                }
            }
		    else {
                if ($this->evaluate_expression($node->cond))
                    return $this->evaluate_expression($node->if);
                else
                    return $this->evaluate_expression($node->else);
            }

		}
		elseif ($node instanceof Node\Expr\Closure)
		{
			// print_r($node);
			$this->verbose("Closure found, emulating...\n",3);
			$uses=[];
			foreach ($node->uses as $use)
			{
			    $var_name = $this->get_variableـname($use->var);
				if ($use->byRef)
					$uses[$var_name]=&$this->variable_reference($use->var);
				else
					$uses[$var_name]=$this->variable_get($use->var);
			}
			$context=new EmulatorExecutionContext(['function'=>"{closure}"
				,'namespace'=>$this->current_namespace,'active_namespaces'=>$this->current_active_namespaces
				,'file'=>$this->current_file,'line'=>$this->current_line]);

            $closure = new EmulatorClosure("{closure}", $node->stmts, $node->params, $node->static, $node->byRef, $uses, $node->returnType, $context);
			return $closure;

		}
		elseif (!$node instanceof Node)
            return $node;
		else
            $this->error("Unknown expression node: ",$node);

		return null;
	}
}
