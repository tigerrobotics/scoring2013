<?php

$teamName = $_POST['teamName'];
$len = $_POST['len'];
$beginTime = $_POST['beginTime'];
print_r($beginTime);
if ($len == 0) {
    echo "No data, NBD.";
} else {
    $actions = array();
    for ($i=0; $i < intval($len); $i++) {
        $actions[] = $_POST[strval($i)];
    }
    $csvDataString = "" . $beginTime . "|";
    for ($i=0; $i < count($actions); $i++) {
        $csvDataString .= $actions[$i] . "\n";
    }
    //store files to /teamName/#.csv
    $scandirDir = "./" . $teamName . "/";
    $files = scandir($scandirDir);
    //print_r($files);
    $versionNum = count($files) - 1;
    $fileName = "./" . $teamName . "/" . $versionNum . ".csv";
    print_r($fileName);
    print_r($csvDataString);
    file_put_contents($fileName, $csvDataString);
}


?>