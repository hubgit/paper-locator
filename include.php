<?php

$mongo = new Mongo();
$db = $mongo->selectDB('2012-05-paper-locator');
$collection = $db->selectCollection('meta');
$collection->ensureIndex(array('_pdf' => 1));

// http://stackoverflow.com/a/4444490/145899
function rel2abs($rel, $base){
    /* return if already absolute URL */
    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

    /* queries and anchors */
    if ($rel[0] == '#' || $rel[0] == '?') return $base . $rel;

    /* parse base URL and convert to local variables: $scheme, $host, $path */
    extract(parse_url($base));

    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if ($rel[0] == '/') $path = '';

    /* dirty absolute URL */
    $abs = $host . $path . '/' . $rel;

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {}

    /* absolute URL is ready! */
    return $scheme . '://' . $abs;
}
