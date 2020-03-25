#!/usr/bin/env php
<?php
#FIXME: when evaluating branch conditions for off-branching, it should be isolated, otherwise side-effects can apply!
ini_set("memory_limit","1000M");
require_once __DIR__."/analyzer.php";

$usage="Usage: php main.php -f file.php [-v verbosity --output --strict --concolic --diehard --postprocessing]\n";
if (isset($argc))// and realpath($argv[0])==__FILE__)
{
	$options=getopt("f:v:o",['strict','output','concolic','postprocessing','diehard','ppisolation'
		,'combined','static']);
	if (!isset($options['f']))
		die($usage);
	
	ini_set("memory_limit",-1);
	$x=new PHPAnalyzer;
	$x->static=isset($options['static']); //only postprocessing/static
	$x->strict=isset($options['strict']); //die on errors
	$x->direct_output=isset($options['output']);
	if (isset($options['v'])) $x->verbose=$options['v']; 
	$entry_file=$options['f'];
	$x->concolic=isset($options['concolic']); //counterfactual mode
	$x->diehard=isset($options['diehard']); //dont die on error in isolation
	$x->post_processing_isolation=isset($options['ppisolation']); //do post processing in isolation
	$combined=isset($options['combined']); //run on all files (experimental)

	timer(0);
	if (!$x->static)
		$x->start($entry_file);
	else
		$x->parse($entry_file);
	$exec_time=timer();

	function h($x)
	{
		return PHP_EOL."===== {$x} =====".PHP_EOL;
	}
	$stats="Request: {$entry_file}".PHP_EOL;
	$stats.=h("Options");
	$stats.=print_r($options,true);
	$stats.=getcwd().PHP_EOL;
	$stats.=h("General Statistics");
	$stats.="Execution time: {$exec_time}".PHP_EOL;
	$stats.=print_r($x->stats,true);

	$stats.=h("Included Files");
	$stats.=print_r($x->included_files,true);
	if ($x->concolic)
	{
		$stats.=h("Concolic Files");
		$stats.=print_r($x->all_files,true);
	}
	if ($combined)
	{
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname($entry_file),
			 RecursiveDirectoryIterator::SKIP_DOTS));
		$x->Verbose("Starting combined mode...\n",1);
		$combined_time=0;
		$combined_count=0;
		$combined_files=[];
		foreach($it as $path) 
		{
			echo $path,PHP_EOL;
			if (substr($path,-4)==".php" and realpath($path)!==realpath($entry_file))
			{
				$x->Verbose("Combined mode file {$combined_count}...\n",2);
				timer(0);
				$x->off_branch_start();
				$x->start($path);
				$x->off_branch_end();
				$time=timer();
				$combined_time+=$time;
				$combined_count++;
				$combined_files[]=["file"=>$path,"time"=>$time];
			}
		}
		$stats.=h("Combined Mode");
		$stats.="Total time: {$combined_time}\n";
		$stats.="Total files: {$combined_count}\n";
		$stats.=print_r($combined_files,true);

	}

	if (!isset($options['output'])) 
		file_put_contents("output.txt",$x->output);
	
	if (isset($options['postprocessing']))
	{
		$firstinc=$x->all_files;
		timer(0);
		$x->postprocessing();
		$pptime=timer();
		$stats.=h("General Statistics after PostProcessing");
		$stats.="Execution time: {$pptime}".PHP_EOL;
		$stats.=print_r($x->stats,true);
		$stats.=h("PostProcessing Included Files");
		$stats.=print_r(array_diff(array_keys($x->postprocessing['included_files']),array_keys($firstinc)),true);
		$stats.=h("PostProcessing Data");
		$stats.=print_r($x->postprocessing,true);
	}
	$stats.=h("Constants").print_r($x->constants,true);
	file_put_contents("stats.txt", $stats);

	if (isset($x->termination_value))
		exit($x->termination_value);
	else
		exit(0);
}
else
	die($usage);