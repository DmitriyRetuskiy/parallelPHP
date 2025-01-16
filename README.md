<b>
 Данный код не является действующим
   производственным решением или решением для реального проекта.
   Но идея обертки методов из рефлексии в процессы может быть использована
   для выполнения непоследовательных вычислений.
   </b> <br>



Building Async.php runners by php -r command

Using for add runner in parallel

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
