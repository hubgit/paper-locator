<?php

// <meta name="citation_pdf_url" content>

require __DIR__ . '/../include.php';

$query = array(
	'_pdf' => array('$exists' => false),
	'citation_pdf_url' => array('$exists' => true),
);

$cursor = $collection->find($query);

foreach ($cursor as $item) {
	if (!$item['citation_pdf_url'][0]) continue;

	$data = array('_pdf' => rel2abs($item['citation_pdf_url'][0], $item['url']));
	$collection->update(array('_id' => $item['_id']), array('$set' => $data));

	print_r($data);
}
