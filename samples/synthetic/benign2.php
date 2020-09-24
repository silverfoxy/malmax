<?php
function curl($url,$postparams=[],$headers=[],$additional_opts=[])
{
	$opts=[
		CURLOPT_COOKIEJAR => 'stdlib.cookie',
		CURLOPT_COOKIEFILE => 'stdlib.cookie',
    	CURLOPT_RETURNTRANSFER => 1,
    	CURLOPT_URL => $url,
    	CURLOPT_TIMEOUT => 5,
    	CURLOPT_CONNECTTIMEOUT => 1,
    	CURLOPT_USERAGENT => 'cURL'
	];
	$opts[CURLOPT_HTTPHEADER]=$headers;
	foreach ($additional_opts as $k=>$v)
		$opts[$k]=$v;
	if ($postparams)
	{
		$opts[CURLOPT_POST]=1;
		if (is_string($postparams))
			$opts[CURLOPT_POSTFIELDS]=$postparams;
		else
			$opts[CURLOPT_POSTFIELDS]=http_build_query($postparams);
	}
	$curl = curl_init();
	curl_setopt_array($curl, $opts);
	$res = curl_exec($curl);
	curl_close($curl);
	return $res;
}
echo curl("abiusx.com/ip.txt");