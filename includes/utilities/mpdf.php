<?php

require_once __DIR__ . '/mpdf/vendor/autoload.php';

function get_mpdf()
{
    $mpdf = new \Mpdf\Mpdf();
    return $mpdf;
}

// Create an instance of the class:

// Write some HTML code:
// $mpdf->WriteHTML('Hello World');

// Output a PDF file directly to the browser
// $mpdf->Output();