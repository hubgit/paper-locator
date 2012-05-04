<?php

require __DIR__ . '/include.php';

$collection->drop();

$error_log = fopen('errors.log', 'a');

$csv = fopen('urls.csv', 'r');
if (!$csv) exit('Unable to open input file');

while (($line = fgetcsv($csv)) !== false) {
	//print_r($line);

	list($pmid, $url, $status, $mime) = $line;

	if ($status != 200) continue; // only use successful responses
	if (strpos($mime, 'html') === false) continue; // only include items that are HTML

	$input = __DIR__ . '/data/html/' . $pmid . '.html';

	// check for HTML file
	if (!file_exists($input) || !filesize($input)) {
		fwrite($error_log, "$input\n");
		continue;
	}

	$dom = new DOMDocument;
	@$dom->loadHTMLFile($input);

	// check for problems with the data
	if (!is_object($dom)) {
		fwrite($error_log, "$input\n");
		continue;
	}

	$xpath = new DOMXPath($dom);

	$meta = read_meta($xpath);

	$meta['pmid'] = $pmid;
	$meta['url'] = $url;
	$meta['_id'] = $pmid;

	$collection->insert($meta);

	//print_r($meta);
}

function read_meta($xpath) {
	$meta = array();

	$nodes = $xpath->query('//meta');
	if ($nodes->length) {
		foreach ($nodes as $node) {
			$name = null;

			foreach (array('name', 'property') as $field) {
				if ($node->hasAttribute($field)) {
					$name = trim(strtolower($node->getAttribute($field)));
					break;
				}
			}

			if ($name) {
				$name = preg_replace('/[\.:-\s]/', '_', $name); // MongoDB doesn't allow dots in key names

				$content = trim($node->getAttribute('content'));
				if ($content) $meta[$name][] = $content;
			}
		}
	}

	$nodes = $xpath->query('//*[@rel]');
	if ($nodes->length) {
		foreach ($nodes as $node) {
			$name = trim(strtolower($node->getAttribute('rel')));

			if ($name) {
				$name = preg_replace('/[\.:-\s]/', '_', $name); // MongoDB doesn't allow dots in key names

				$href = trim($node->getAttribute('href'));
				if ($href) $meta['link_relations'][$name][] = array('type' => $node->getAttribute('type'), 'href' => $href);
			}
		}
	}

	ksort($meta);

	return $meta;
}