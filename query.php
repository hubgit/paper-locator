<?php

require __DIR__ . '/include.php';

$query = array(
	//'_pdf' => array('$exists' => false),
	//'citation_pdf_url' => array('$exists' => true),
	'link_relations.alternate' => array('$exists' => true),
);

$items = array();

$cursor = $collection->find($query);

foreach ($cursor as $item) {
	//print_r($item);
	$parts = parse_url($item['url']);
	//print_r($parts);
	foreach ($item['link_relations']['alternate'] as $link){
		if (!$link['type']) continue;

		$url = rel2abs($link['href'], $item['url']);
		$items[$link['type']][$parts['host']][$url]++;
	}
}

arsort($items);
print_r($items);
