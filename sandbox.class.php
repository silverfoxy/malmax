<?php
use PhpParser\Node;
require_once __DIR__ . "/phpanalyzer.php";

ini_set("allow_url_fopen",0); //sandbox
ini_set("allow_url_include",0); //sandbox
function ereg_replace($emul, $pattern, $replacement, $subject) {
	return preg_replace($pattern, $replacement, $subject);
}
function sandbox_preg_replace($emul,$pattern,$replacement,$subject) {
	if (substr_count($replacement,"&")>4)
		return $subject;
	return proxy();
}

function sandbox_mysqli_connect($emul) {
	$emul->inc("sandbox/suspicion/mysqli_connect");
	$emul->suspicion_score++;
	return proxy();
}
function sandbox_mysqli_init() {}
function sandbox_mysqli_real_connect() {}
function sandbox_mysqli_affected_rows() {}
function sandbox_mysqli_select_db() {}
function sandbox_mysqli_query() {}
function proxy($fname=null)
{
	$args=debug_backtrace()[1]['args'];
	array_shift($args); //remove emulator
	// var_dump($args);
	// die();
	if ($fname===null)
	{
		$fname=debug_backtrace()[1]['function'];
		$fname=substr($fname,strlen("sandbox_"));
	}
	return call_user_func_array($fname, $args);
}



function sandbox_unlink($emul) {
	$emul->inc("sandbox/suspicion/unlink");
	$emul->inc("sandbox/ignored/unlink");
	$emul->inc("sandbox/ignored/total");
	return true;
}
function sandbox_rmdir($emul) {
	$emul->inc("sandbox/suspicion/rmdir");
	$emul->inc("sandbox/ignored/rmdir");
	$emul->inc("sandbox/ignored/total");
	return true;
}
function sandbox_mkdir($emul) {
	$emul->inc("sandbox/suspicion/mkdir");
	$emul->inc("sandbox/ignored/mkdir");
	$emul->inc("sandbox/ignored/total");
	return true;
}

function sandbox_getmypid($emul) {
	$emul->suspicion_score++;
	$emul->inc("sandbox/suspicion/getmypid");
	return proxy();
}
function sandbox_getcwd($emul) {
	$emul->inc("sandbox/suspicion/getcwd");
	return proxy();
}
function sandbox_opendir($emul) {
	$emul->inc("sandbox/suspicion/opendir");
	return proxy();
}

function sandbox_file_get_contents($emul,$file) {
	if (substr($file,0,7)=="http://"
		or
		substr($file,0,8)=="https://"
		or
		substr($file,0,6)=="ftp://"
		or strpos($file,"://")!==false
		)
	{

		$emul->inc("sandbox/suspicion/file_get_contents");
		$emul->inc("sandbox/ignored/file_get_contents");
		$emul->inc("sandbox/ignored/total");
		$emul->suspicion_score++;
		return null;
	}
	return proxy();
}
function sandbox_fread($emul,$file,$mode) {
	if ($file===@STDIN)
		return null;
	return proxy();
}
function sandbox_fopen($emul,$file,$mode) {
	$emul->inc("sandbox/suspicion/fopen");
	if (strpos($mode,"w")!==false)
	{
		$emul->inc("sandbox/suspicion/fopen-write");
		$emul->inc("sandbox/ignored/fopen");
		$emul->inc("sandbox/ignored/total");
		return null;
	}
	return proxy();
}
// function sandbox_fwrite($emul) {
// 	$emul->inc("sandbox/suspicion/fwrite");
// 	return proxy();
// }

function sandbox_curl_init($emul) {
	$emul->inc("sandbox/suspicion/curl_init");
	$emul->inc("sandbox/ignored/curl_init");
	$emul->inc("sandbox/ignored/total");
	return null;
}
function sandbox_curl_exec($emul) {
	$emul->suspicion_score++;
	$emul->inc("sandbox/suspicion/curl_exec");
	$emul->inc("sandbox/ignored/curl_exec");
	$emul->inc("sandbox/ignored/total");
	return null;
}

function sandbox_mail($emul) {
	$emul->suspicion_score++;
	$emul->inc("sandbox/suspicion/mail");
	$emul->inc("sandbox/ignored/mail");
	$emul->inc("sandbox/ignored/total");
	return true;
}
function sandbox_ini_set($emul) {
	$emul->inc("sandbox/suspicion/ini_set");
	$emul->inc("sandbox/ignored/ini_set");
	$emul->inc("sandbox/ignored/total");
	return true;
}

function sandbox_base64_decode($emul,$string,$strict=false) {
	$emul->suspicion_score++;
	$emul->inc("sandbox/suspicion/base64_decode");
	return proxy();
}
function sandbox_str_rot13($emul) {
	$emul->suspicion_score++;
	$emul->inc("sandbox/suspicion/str_rot13");
	return proxy();
}
function sandbox_gzinflate($emul,$string,$length=0) {
	$emul->suspicion_score++;
	$emul->inc("sandbox/suspicion/gzinflate");
	return proxy();
}
function sandbox_gzuncompress($emul) {
	$emul->suspicion_score++;
	$emul->inc("sandbox/suspicion/gzuncompress");
	return proxy();
}
class PHPSandbox extends PHPAnalyzer
{

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
	/**
	 * Holds the suspicion activity done by a program
	 * @var array
	 */
	public $suspicion=[];
	function init($init_environ)
	{
		parent::init($init_environ);
		$this->infinite_loop=1000;
		$this->whitelist=[];
		$list=explode("\n",file_get_contents(__DIR__."/sandbox/functions.txt"));
		$list=array_filter($list);
		$list=array_filter($list,function($x) {return $x[0]!='#';});
		$list=array_map(function($x){
			$pos=strrpos($x," ");
			$pos=max($pos,strrpos($x,"\t"));
			if ($pos===false)
				return $x;
			else
				return trim(substr($x,0,$pos));
		},$list);
		$this->whitelist=array_flip($list);

		foreach(get_defined_functions()['internal'] as $function) //get_defined_functions gives 'internal' and 'user' subarrays.
		{
			if (function_exists("sandbox_".$function))
				$this->mock_functions[strtolower($function)]="sandbox_".$function;
		}
	}
	public $total_core_function=0;
	public $total_suspicion_function=0;
	public $suspicion_score=0;
	protected function run_core_function($name,$args)
	{
		$this->total_core_function++;
		$argValues=$this->core_function_prologue($name,$args); #this has to be before the trace line,
		if ($this->terminated) return null;
		array_push($this->trace, (object)array("type"=>"","function"=>$name,"file"=>$this->current_file,"line"=>$this->current_line,"args"=>$argValues));
		if (array_key_exists($name, $this->whitelist))
		{
			if (isset($this->mock_functions[strtolower($name)])) //mocked
				$ret=$this->run_mocked_core_function($name,$argValues);
			else //original core function
				$ret=$this->run_original_core_function($name,$argValues);
		}
		else
		{
			if (isset($this->mock_functions[strtolower($name)])
				and substr($this->mock_functions[strtolower($name)],0,8)=="sandbox_") //mocked sandbox
				$ret=$this->run_mocked_core_function($name,$argValues);
			else
			{
				$this->verbose(strcolor("Non-whitelisted function '{$name}' called.\n","red"),1);
				$ret=null;
				// $r=file_put_contents(__DIR__."/sandbox/functions.txt", $name."\n",FILE_APPEND);
				// $this->terminated=true;
				$this->inc("sandbox/blacklist/{$name}");
				$this->inc("sandbox/blacklist/total");
				$this->total_suspicion_function++;
				$this->suspicion_score+= max(1,(10*$this->eval_depth));
				if ($this->eval_depth)
					$this->score_track[] = [$name, $this->eval_depth];
			}
		}
		array_pop($this->trace);
		return $ret;
	}
	function suspicion_score()
	{
		return $this->suspicion_score;
	}
	function suspicion_percentage()
	{
		$percentage=($this->total_suspicion_function/max($this->total_core_function,1))*100;
		if ($this->total_suspicion_function<2)
			return 0;
		return $percentage;
	}
	function reset()
	{
		$this->total_suspicion_function=0;
		$this->total_core_function=0;
		$this->suspicion_score=0;
	}
	/**
	 * Suppress error displays
	 * @param  [type]  $msg     [description]
	 * @param  [type]  $node    [description]
	 * @param  boolean $details [description]
	 * @return [type]           [description]
	 */
	protected function _error($msg,$node=null,$details=true)
	{
		if ($this->verbose-$this->verbose_base>0)
			return parent::_error($msg,$node,$details);
		return null;
	}
	/**
	 * Do not go into many nested loops
	 * @param  integer $i [description]
	 * @return [type]     [description]
	 */
	protected function loop_condition($i=0)
	{
		if ($this->loop_depth>10)
			return true;
		return parent::loop_condition($i);
	}


	public $quiet=false;
	function __destruct()
	{
		// if (is_array($this->stats['sandbox']['suspicion']))
		// {
		// 	$this->total_suspicion_function+=array_sum($this->stats['sandbox']['suspicion']);
		// 	// $this->total_suspicion_function+=$this->stats['sandbox']['ignored']['total'];
		// 	$this->suspicion_score+=$this->stats['sandbox']['ignored']['total']*2;
		// 	$this->suspicion_score+=array_sum($this->stats['sandbox']['suspicion']);;
		// }


		// print_r($this->suspicion);
		$percentage_color=$this->suspicion_percentage()<5?"green":"red";
		$score=$this->suspicion_score();#/$this->statement_count *100;
		$score_color=$score>=20?"red":"green";

		if ($this->quiet) return;
		echo "Suspicious behavior: ";
		print_r($this->stats);
		echo strcolor(
			sprintf ("Suspicious activity percentage: %.2f%% (%d/%d functions)\n",
			$this->suspicion_percentage(),$this->total_suspicion_function,$this->total_core_function),$percentage_color);
		echo strcolor(
			sprintf("Suspicion score: %.2f (%d of %d)\n",
				$score,$this->suspicion_score(),$this->statement_count)
				,$score_color);
	}

}

