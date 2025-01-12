<?php


//function myFunctin() {
//    echo 'hi';
//}
//$reflection = new ReflectionFunction('myFunctin');
//$file = $reflection->getFileName();
//$line = $reflection->getStartLine();
//$code = file($file);
//$functionCode = implode("", array_slice($code, $line - 1, $reflection->getEndLine() - $line + 1));
//echo $file;
//echo $functionCode;

//function go() {
//    echo 'go';
//}
//
//$echo="echo 'hi';";


$function1 = '\'
    for($i=1;$i<12;$i++) {
        sleep(1);
        print("work1");
    }
    
    $out = fopen("php://output", "w"); 
    fwrite($out, "success1,");
    fclose($out);
    die;
    
\'';

$function2 = '\'
    for($i=1;$i<20;$i++) {
        sleep(1);
        file_put_contents("file2.txt",$i . "\n",FILE_APPEND);
    }
    print("success2 \n");
    die;
\'';

$functions = [$function1,$function2];

// proc_open
$descriptorspec = array(
    0 => array("pipe", "r"),  // stdin
    1 => array("pipe", "w"),  // stdout
    2 => array("pipe", "w")   // stderr
);

$process = [];
$pipes = [];

foreach ($functions as $key => $function) {
    $process[$key] = proc_open("php -r $function", $descriptorspec, $pipes[$key]);
}

while(true)
{
    sleep(1);

    foreach ($process as $key=>$proces) {
        if (!proc_get_status($proces)['running']) {
            for ($i=0;$i<=2;$i++) {
                fclose($pipes[$key][$i]);
            }
            unset($process[$key]);
            proc_close($proces);
        }

    }

    if(count($process) < 1) break;

    foreach ($process as $key=>$proces) {

        $piepe = $pipes[$key][1];
        while(!feof($piepe)) {
            $line = fgets($piepe);
            echo $line;
        }
        $errors = $pipes[$key][2];
        while(!feof($piepe)) {
            $line = fgets($piepe);
            echo $line;
        }

    }

}

echo 'all runner have done';
die;



