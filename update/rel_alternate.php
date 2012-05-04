<?php

// <link rel="alternate" type="application/pdf">

require __DIR__ . '/../include.php';

$query = array(
	'_pdf' => array('$exists' => false),
	'link_relations.alternate' => array('$exists' => true),
);

$cursor = $collection->find($query);

foreach ($cursor as $item) {
	foreach ($item['link_relations']['alternate'] as $link){
		if ($link['type'] !== 'application/pdf') continue;

		$data = array('_pdf' => rel2abs($link['href'], $item['url']));
		$collection->update(array('_id' => $item['_id']), array('$set' => $data));

		print_r($data);
		break;
	}
}
