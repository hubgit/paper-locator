<?php

require __DIR__ . '/../include.php';

$query = array(
	'_pdf' => array('$exists' => false),
	'author' => array('$ne' => 'pubmeddev'),
	//'link_relations.alternate' => array('$exists' => true),
);

$cursor = $collection->find($query);

foreach ($cursor as $item) {
	print $item['url'] . "\n";
	//print_r($item);
}
