<?php

// <link rel="full_text_pdf" href>, with +html removed from the end of the URL

require __DIR__ . '/../include.php';

$query = array(
	'_pdf' => array('$exists' => false),
	'link_relations.full_text_pdf' => array('$exists' => true),
);

$cursor = $collection->find($query);

foreach ($cursor as $item) {
	$url = $item['link_relations']['full_text_pdf'][0]['href'];
	$url = preg_replace('/\+html$/', '', $url);

	$data = array('_pdf' => rel2abs($url, $item['url']));
	$collection->update(array('_id' => $item['_id']), array('$set' => $data));

	print_r($data);
}
