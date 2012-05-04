<?php

// any link that ends with ".pdf", or contains "/pdf/"

require __DIR__ . '/../include.php';

$query = array(
	'_pdf' => array('$exists' => false),
);

$cursor = $collection->find($query);

foreach ($cursor as $item) {
	$file = __DIR__ . '/../data/html/' . $item['pmid'] . '.html';
	if(!file_exists($file)) continue;

	$dom = new DOMDocument;
	@$dom->loadHTMLFile($file);

	$xpath = new DOMXPath($dom);

	$url = pdf_link($xpath);
	if (!$url) continue;

	$data = array('_pdf' => rel2abs($url, $item['url']));
	$collection->update(array('_id' => $item['_id']), array('$set' => $data));

	print_r($data);
}

function pdf_link($xpath) {
	foreach ($xpath->query('//a') as $node) {
		$url = $node->getAttribute('href');
		if (preg_match('/\.pdf$/i', $url)) return $url;
		if (preg_match('/\/pdf\//i', $url)) return $url;
	}
}