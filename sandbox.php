<?php
require_once 'sandbox.class.php';

function find_all_php_files($folder,$emulator)
{
	$files=[];
	$suspicion=0;
	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS)) as $file)
	{
		if (!$file->isDir())
		{
			$filename=$file->getPathname();
			$extension = pathinfo($filename, PATHINFO_EXTENSION);

			if ($extension!="php" and $extension!="inc")
			{
				if (filesize($filename)>1 * 1024 * 1024)
					continue;
				// Non-php files can still be PHP code.
				if (strpos(file_get_contents($filename), '<?')===false)
					continue;
				// Has the PHP tag. Make sure has code
				try {
					$emulator->verbose("Parsing '".substr($filename,strlen($folder))."'.\n",2);
					$ast=$emulator->parse($filename);
					$isphp=false;
					foreach ($ast as $node)
					{
						if (!$node instanceof Node\Stmt\InlineHTML)
						{
							$isphp=true;
							// echo get_class($node),PHP_EOL,PHP_EOL;
							break;
					}
					}
					if (!$isphp) continue;
				}
				catch (Exception $e) {
					continue;
				}
				// print_r($ast);
				$emulator->verbose("Found a non-php file with PHP code '".substr($filename,strlen($folder))."'.\n",1);
				$suspicion++;
			}
			$files[]=$filename;
		}
	}
	return [$files,$suspicion];
}
function new_sandbox($options)
{
	$x=new PHPSandbox;
	$x->strict=isset($options['strict']);
	$x->direct_output=isset($options['output']);
	if (isset($options['v'])) $x->verbose=$options['v'];
	$x->concolic=isset($options['concolic']);
	$x->diehard=isset($options['diehard']);
	$x->post_processing_isolation=isset($options['ppisolation']);
	$x->quiet=isset($options['q']);
	return $x;
}
$usage="Usage: php main.php -f file.php [-v verbosity --output --strict --concolic --diehard --postprocessing]\n";
if (isset($argc))// and realpath($argv[0])==__FILE__)
{
	$options=getopt("f:v:o",['strict','output','concolic','postprocessing','diehard','ppisolation']);
	if (!isset($options['f']))
		die($usage);
	$current_dir=getcwd();
	$entry_file=$options['f'];
	ini_set("memory_limit",-1);

	$x=new_sandbox($options);

	if (is_file($entry_file))
	{
		$x->verbose("Single file scan mode:\n",0);
		$x->start($entry_file);
	}
	else // Directory scan mode
	{

		$x->quiet=$options['q']=true;
		$x->verbose("Directory scan mode:\n",0);
		$x->verbose("Listing files...\n",1);
		timer(0);
		list($files,$suspicion)=find_all_php_files($entry_file,$x);
		$time=timer();
		$x->verbose("({$time}s) Found ".count($files)." files, {$suspicion} of which are non-php (suspicion) files.\n",0);
		$x->verbose("Starting scan...\n",1);

		$total_included_files=[];
		$malware=[];
		$count=0;
		// force start from index.php
		// FIXME: which index.php?
		foreach ($files as $k=>$file)
		{
			if (basename($file)=="index.php")
			{
				unset($files[$k]);
				array_unshift($files, $file);
				break;
			}
		}
		foreach ($files as $file)
		{
			if (isset($total_included_files[$file]))
			{
				$x->verbose("File '{$file_print}' is already covered, skipping.\n",-1);
				continue;
			}
			$x=new_sandbox($options);
			$x->included_files=$total_included_files;
			// $x->reset(); #FIXME: do some better isolation
			$x->verbose_base=3;

			$file_print=substr($file,strlen($entry_file));
			$x->verbose("Starting a new scan on '{$file_print}'...\n",-2);
			$count++;
			timer(0);
			try {
				$x->start($file);
			}
			catch (Exception $e) {
				$x->verbose("Failed '{$file_print}' with an exception.\n",-2);
			}
			$time=timer();
			$total_included_files=array_merge($total_included_files,$x->included_files);
			$x->verbose("({$time}s) ".count($total_included_files)."/".count($files)." files covered so far.\n",-2);
			// var_dump(count($total_included_files));
			if ($x->suspicion_score() or $x->suspicion_percentage())
			{
				$malware[$file]=["score"=>$x->suspicion_score(),
					"percentage"=>$x->suspicion_percentage(),
					"stats"=>$x->stats,
					"track"=>@$x->score_track,
					];
			}
			# limit for testing
			// if ($count>10) break;
		}
		$x->verbose("Successfully scanned {$count} entry points covering ".
			count($total_included_files)." files.\n",-3);
		$x->verbose("There are ".count($malware)." potential malwares.\n",-3);

		uasort($malware,function($x,$y) {
			if ($x["score"]>$y["score"])
				return -1;
			elseif ($x["score"]<$y["score"])
				return 1;
			elseif ($x["percentage"]>$y["percentage"])
				return -1;
			elseif ($x["percentage"]<$y["percentage"])
				return 1;
			else
				return 0;
		});

		$x->verbose("Scan results:\n",-3);
		file_put_contents("{$current_dir}/sandbox.txt", json_encode($malware,JSON_PRETTY_PRINT));
		$out=[];
		foreach ($malware as $k=>$m)
		{
			unset($m['stats']);
			$out[$k]=$m;
		}
		print_r($out);

	}


	if (isset($x->termination_value))
		exit($x->termination_value);
	else
		exit(0);
}
else
	die($usage);
