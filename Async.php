<?php
/*
 *  Данный код не является действующим
 *  производственным решением или решением для реального проекта.
 *  Но идея обертки методов из рефлексии в процессы может быть использована
 *  для выполнения непоследовательных вычислений.
 */

// Async PHP runner by proc_open
class Async {

    public $functions = [];

    // adding function into array
    public function add($func):void {
        // get function code
        $funcCode = $this->getFunctionCode($func);
        // add function code to array
        $this->functions[] = $funcCode;
        // start runner
    }

    // get function code
    public function getFunctionCode($func):string
    {
        $reflection = new ReflectionFunction($func);
        $file = $reflection->getFileName();
        $line = $reflection->getStartLine();
        $code = file($file);
        $functionCode = implode("", array_slice($code, $line, $reflection->getEndLine() - $line - 1));
        //$functionCode = str_replace("'","\\'",$functionCode);
        $functionCode = $this->wrapeSingleQuotes($functionCode);
        return "'$functionCode'";
    }

    public function run():void {

        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w")   // stderr
        );

        $process = [];
        $pipes = [];

        foreach ($this->functions as $key => $function) {
            $process[$key] = proc_open("php -r $function", $descriptorspec, $pipes[$key]);
        }

        while(true)
        {

            foreach ($process as $key=>$proces) {
                if (!proc_get_status($proces)['running']) {
                    for ($i=0;$i<=2;$i++) {
                        fclose($pipes[$key][$i]);
                    }
                    unset($process[$key]);
                    proc_close($proces);
                }

            }

            if(count($process) < 1) break; // if all process have done stop while;

            foreach ($process as $key=>$proces) {

                $piep = $pipes[$key][1];
                while(!feof($piep)) {
                    $line = fgets($piep);
                    echo $line;
                }

                $errors = $pipes[$key][2];

                while(!feof($errors)) {
                    $line = fgets($errors);
                    echo $line;
                }

            }

        }

        echo 'all runner have done';
    }

    public function showFunctions():void {
        foreach ($this->functions as $key => $function) {
            echo '----' . $key . '----' . "\n";
            echo $function;
            echo "\n";
        }
    }

    /**
     * for wrapper use brake string by '   php -r 'echo '\''Hello, World!'\'';'
     * @param string|null $str
     * @return array|string|string[]|null
     */
    function wrapeSingleQuotes(?string $str) {
        $pattern = "/'([^']*)'/";  // Регулярное выражение для поиска текста в двойных кавычках

        $modifiedString = preg_replace_callback($pattern, function($matches) {
            return "'\\'" . $matches[0] . "\\''";  // Оборачиваем найденный текст в дополнительные кавычки
        }, $str);

        return $modifiedString;
    }


}

// example in use

$Async = new Async();

$Async->add(function() {

    for($i=1;$i<12;$i++) {
        sleep(1);
        print("work1");
        file_put_contents('file1.txt',"$i",FILE_APPEND);
    }
    print("success1 \n");

});

$Async->add(function () {

    for($i=1;$i<20;$i++) {
        sleep(1);
        file_put_contents("file2.txt",$i . "\n",FILE_APPEND);
    }
    print("success2 \n");
    die;

});

$Async->add(function() {

    try {
        $dbh = new PDO('mysql:host=127.0.0.1;dbname=laravel',"root", "asdfasdf");
    } catch (PDOException $e) {
        echo 'Connection error: ' . $e->getMessage();
    }

    $result = $dbh->query("SELECT users.name FROM users WHERE id>1 AND id<40");
    while($row = $result->fetch()){
        echo $row["name"] . "\n";
        file_put_contents('pdo1.txt',$row["name"],FILE_APPEND);
    }

    $dbh=null;
});

$Async->add(function() {

    try {
        $dbh = new PDO('mysql:host=127.0.0.1;dbname=laravel',"root", "asdfasdf");
    } catch (PDOException $e) {
        echo 'Connection error: ' . $e->getMessage();
    }

    $result = $dbh->query("SELECT users.name FROM users WHERE id>1 AND id<40");
    while($row = $result->fetch()){
        echo $row["name"] . "\n";
        file_put_contents('pdo2.txt',$row["name"],FILE_APPEND);
    }

    $dbh=null;
});

// problem with comments into functions
$Async->showFunctions();

// problems with quotes '
$Async->run();

die;