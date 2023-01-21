<?php

require_once __DIR__ . '/array-to-xml/vendor/autoload.php';

use Spatie\ArrayToXml\ArrayToXml;

function convert_array_to_xml($array, $root)
{
    $result = new ArrayToXml($array, $root);
    return $result;
}
