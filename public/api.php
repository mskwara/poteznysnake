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

        class MyDB extends SQLite3 {
            function __construct() {
               $this->open('../scoreboard.db');
            }
         }
         $db = new MyDB();
         if(!$db) {
            echo $db->lastErrorMsg();
            exit();
         }

         $sql = "SELECT id, name, score, mode FROM scoreboard ORDER BY score DESC LIMIT 10";
         $ret = $db->query($sql);
         $array = [];
         while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
             $array[] = $row;
         }
         $db->close();
         return $response->withJson($array);
    }
);

$app->post(
    '/addscore',
    function (Request $request, Response $response, array $args) {

      class MyDB extends SQLite3 {
          function __construct() {
             $this->open('../scoreboard.db');
          }
       }
       $db = new MyDB();
       if(!$db) {
          echo $db->lastErrorMsg();
          exit();
       }
      $requestData = $request->getParsedBody();
      $sql = "INSERT INTO scoreboard (name, score, mode) VALUES(:name, :score, :mode)";
      $stmt = $db->prepare($sql);
      $stmt->bindValue('name', $requestData['name']);
      $stmt->bindValue('score', $requestData['score']);
      $stmt->bindValue('mode', $requestData['mode']);
      $ret = $stmt->execute();

      $db->close();
      return $requestData;
  }

);

$app->run();
