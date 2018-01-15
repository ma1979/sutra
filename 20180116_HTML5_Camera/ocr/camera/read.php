<?php
//ini_set('display_errors', 1 );
//ini_set('error_reporting', E_ALL);

$str="hoge";

$data = $_POST['image'];
$tempfile = tmpfile();

if (is_writable('/tmp')){
    $filename = tempnam('/tmp', 'ocr');
    file_put_contents($filename, base64_decode($data));
}else{
    $str="error";
}
// $str = (new TesseractOCR($filename))
//     ->quietMode(true)
//     ->lang('eng', 'jpn')->psm(4)->run();

echo $str;
//unlink($filename);