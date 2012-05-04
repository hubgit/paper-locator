<?php

ini_set('auto_detect_line_endings', true);

$input = fopen('pmids.txt', 'r');
if (!$input) exit('Unable to open input file');

$csv = fopen('urls.csv', 'a');
if (!$csv) exit('Unable to open output file');

$curl = curl_init();

while(!feof($input)) {
	$pmid = preg_replace('/\D/', '', trim(fgets($input)));

	if (!$pmid) continue;

	$output = __DIR__ . '/data/html/' . $pmid . '.html';
	if (
		file_exists($output)
		//&& filesize($output)
	) continue;

	$params = array(
		'dbfrom' => 'pubmed',
		'retmode' => 'ref',
		'cmd' => 'prlinks',
		'id' => $pmid,
	);

	$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?' . http_build_query($params);

	print "\nFetching $url\n";

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_TIMEOUT => 10,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => array('Accept: text/html;q=1.0, application/xhtml+xml;q=0.5'),
		CURLOPT_USERAGENT => 'Paper Locator',
		CURLOPT_VERBOSE => true,
		CURLOPT_NOPROGRESS => false,
		CURLOPT_HEADERFUNCTION => 'headerCallback'
		//CURLOPT_NOBODY => true, // HEAD
	));

	$result = curl_exec($curl);

	$data = array(
		'pmid' => $pmid,
		'url' => curl_getinfo($curl, CURLINFO_EFFECTIVE_URL),
		'status' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
		'mime' => curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
	);

	fputcsv($csv, $data);

	file_put_contents($output, $result);

	print_r($data);
}

function headerCallback($curl, $header) {
	// Content-Type header contains "pdf", so don't fetch
	if (stripos($header, 'Content-Type') === 0 && stripos($header, 'pdf') !== false) return 0;
	return strlen($header);
}
