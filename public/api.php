<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App;
$app->get(
  '/hello/{name}',
  function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
  }
);


$app->get(
    '/api/scoreboard',
    function (Request $request, Response $response, array $args) {

        // class MyDB extends SQLite3 {
        //     function __construct() {
        //        $this->open('../scoreboard.db');
        //     }
        //  }
        //  $db = new MyDB();
        //  if(!$db) {
        //     echo $db->lastErrorMsg();
        //     exit();
        //  }


        // $mssqlhost = 'https://serwer2001916.home.pl/sql';
        // $mssqldbname = 'scoreboard';
        // $mssqluser = 'serwer2001916';
        // $mssqlpass = 'Fell!Dell!=';
        // $sql = "SELECT id, name, score, mode FROM scoreboard ORDER BY score DESC LIMIT 10";
        // $array = [];
        // try {
        //   $db = new PDO("odbc:DRIVER=PDO_SQLITE;SERVER=$mssqlhost;UID=$mssqluser;PWD=$mssqlpass;DATABASE=$mssqldbname;Port=1443;");
        //   $query = $db->prepare($sql);
        //   $query->execute();
        //   while($row = $query->fetch(PDO::FETCH_OBJ)){
        //     $array[] = $row;
        //   }
        // } catch (PDOException $exception) {
        //   echo $exception->getMessage();
        //   exit;
        // }

        $servername = "serwer2001916.home.pl";
        $username = "32213694_scoreboard";
        $password = "Fell!Dell!=";
        $dbname = "32213694_scoreboard";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT id, name, score, mode FROM scoreboard ORDER BY score DESC LIMIT 10";
        $result = $conn->query($sql);
        $array = [];

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $array[] = $row;
            }
        } else {
            echo "0 results";
        }
        $conn->close();


         // $sql = "SELECT id, name, score, mode FROM scoreboard ORDER BY score DESC LIMIT 10";
         // $ret = $db->query($sql);
         // $array = [];
         // while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
         //     $array[] = $row;
         // }
         // $db->close();
        return $response->withJson($array);
    }
);

$app->post(
    '/api/addscore',
     function (Request $request, Response $response, array $args) {
    //
    //   class MyDB extends SQLite3 {
    //       function __construct() {
    //          $this->open('../scoreboard.db');
    //       }
    //    }
    //    $db = new MyDB();
    //    if(!$db) {
    //       echo $db->lastErrorMsg();
    //       exit();
    //    }
    //   $requestData = $request->getParsedBody();
    //   $sql = "INSERT INTO scoreboard (name, score, mode) VALUES(:name, :score, :mode)";
    //   $stmt = $db->prepare($sql);
    //   $stmt->bindValue('name', $requestData['name']);
    //   $stmt->bindValue('score', $requestData['score']);
    //   $stmt->bindValue('mode', $requestData['mode']);
    //   $ret = $stmt->execute();
    //
    //   $db->close();
    $servername = "serwer2001916.home.pl";
    $username = "32213694_scoreboard";
    $password = "Fell!Dell!=";
    $dbname = "32213694_scoreboard";

    $requestData = $request->getParsedBody();
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO scoreboard (name, score, mode) VALUES('$requestData[name]', '$requestData[score]', '$requestData[mode]')";

    // $stmt = $conn->prepare($sql);
    // $stmt->bindValue('name', $requestData['name']);
    // $stmt->bindValue('score', $requestData['score']);
    // $stmt->bindValue('mode', $requestData['mode']);
    // $ret = $stmt->execute();

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();


    return $requestData;
  }

);

$app->run();
