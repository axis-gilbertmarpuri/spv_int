<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \GuzzleHttp\Cookie\CookieJar;

require '../vendor/autoload.php';

ini_set("error_log",__DIR__ . '/../logs/app.log');
$app = new \Slim\Slim();

$app->notFound(function () use ($app) {
   $app->render('404.html');
});

$view = $app->view();
$view->setTemplatesDirectory('../templates/');
$view->parserExtensions = [
   new \Slim\Views\TwigExtension()
];

$app->post('/downloadplants', function () use ($app) {
    $request = $app->request->post();

    $date_from = $request['date_from'];
    $date_to = $request['date_to'];
    $plant_uri = $request['plants'];

    $download_report_link = $plant_uri."ac/download_measurement.php?freq=daily&target_date_ge=".$date_from."&target_date_lt=".$date_to."&format=csv";
    ob_start(); 
    $url = $download_report_link;

    while (ob_get_status()) 
    {
        // if (ob_get_contents()) 
        ob_end_clean();
    }

    //redirect for download
    header( "Location: $url" );
    $app->render('plant_download.phtml', [ 
        'date_from' => $request['date_from'],
        'date_to' => $request['date_to'],
        'plant_uri' => $request['plants'],
    ]);

})->setName('downloadplants');

$app->get('/plants', function () use ($app) {

   $cookieJar = CookieJar::fromArray(['cookie_name' => 'cookie_value'], 'https://dev-integration.sp-viewer.net');
   $client = new \GuzzleHttp\Client(["base_uri" => "https://dev-integration.sp-viewer.net", 'cookies' => true]);

   $params = [
      "loginid" => "test",
      "password" => "A1X2I3S4"
   ];

   $response = $client->post("/api/v1/login", ['json' => $params, 'cookies' => $cookieJar]);
   
   $response = $client->get("/api/v1/plants", ['cookies' => $cookieJar]);
   $plantJsonDecoded = json_decode($response->getBody(), true);
   $plants = [];

   foreach ($plantJsonDecoded as $plant) {
      $newPair = [ 
         'plant_name' => $plant['plant_name'],
         'plant_uri' => $plant['plant_uri'],
      ];
      array_push($plants, $newPair);
   
   }

   $app->render('plants.phtml', ['plants' => $plants]);
})->setName('plants');


$app->run();
