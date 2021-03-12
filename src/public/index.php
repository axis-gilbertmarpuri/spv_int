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

function isValidDate ($date_from, $date_to) {

    $DATE_FORMAT_DEFAULT_SIZE = 3;

    $dateFromConverted = strtotime($date_from);
    $dateToConverted = strtotime($date_to);

    $tempDate = explode('-', $date_from);

    try {

        if (sizeof($tempDate) != $DATE_FORMAT_DEFAULT_SIZE){
            return false;
        }

        if (checkdate($tempDate[1], $tempDate[2], $tempDate[0]) == false){
            return false;
        }

        if (DateTime::createFromFormat('Y-m-d', '2021-02-21') != true) {
            return false;
        }

    } catch (Exception $e) {
        return false;
    }

    return true;
}

$app->post('/downloadplants', function () use ($app) {
    $request = $app->request->post();

    $date_from = $request['date_from'];
    $date_to = $request['date_to'];
    $plant_uri = $request['plants'];

    $val = 1;

    if (isValidDate($date_from, $date_to) != true) {
        $app->render('error.phtml');
        return;
    }

    $download_report_link = $plant_uri."ac/download_measurement.php?freq=daily&target_date_ge=".$date_from."&target_date_lt=".$date_to."&format=csv";
    
    $app->redirect($download_report_link);
    
    // ob_start(); 
    // $url = $download_report_link;

    // while (ob_get_status()) 
    // {
    //     // if (ob_get_contents()) 
    //     ob_end_clean();
    // }

    // //redirect for download
    // header( "Location: $url" );
    // $app->render('plant_download.phtml', [ 
    //     'date_from' => $request['date_from'],
    //     'date_to' => $request['date_to'],
    //     'plant_uri' => $request['plants'],
    // ]);

})->setName('downloadplants');

$app->get('/plants(/:stat)', function () use ($app) {

    $cookieJar = CookieJar::fromArray(['cookie_name' => 'cookie_value'], 'https://dev-integration.sp-viewer.net');
    $client = new \GuzzleHttp\Client(["base_uri" => "https://dev-integration.sp-viewer.net", 'cookies' => true]);

    $params = [
        "loginid" => "test",
        "password" => "A1X2I3S4"
    ];

    // 笠岡		http://180.178.84.94/index.html
    // 木城町		http://ichigo.delightviewer.jp/front/
    // 世羅津口		http://202.229.41.205/icg/pg/spv/h2/solar/front/
    // 世羅青水		http://202.229.41.205/icg/pg/spv/h3/solar/front/
    // 芽室西士狩	http://202.229.41.205/icg/pg/spv/h4/solar/front/
    // 常陸大宮		http://202.229.41.205/icg/pg/spv/h6/solar/front/
    // 世羅下津田	No site
    // 都城東霧島	No site
    // えびの末永	No site
    
    $plantsToSkip = array ('笠岡', '木城町', '世羅津口', '世羅青水', '芽室西士狩', '常陸大宮', '世羅下津田', '都城東霧島', 'えびの末永', '米子泉');
    // var_dump($plantt);

    $response = $client->post("/api/v1/login", ['json' => $params, 'cookies' => $cookieJar]);
    
    $response = $client->get("/api/v1/plants", ['cookies' => $cookieJar]);
    $plantJsonDecoded = json_decode($response->getBody(), true);
    $plants = [];
    $plantsNonG = [];

    foreach ($plantJsonDecoded as $plant) {
        
        if ($plant['plant_name'][0] == 'G') {
            $newPair = [ 
                'plant_name' => $plant['plant_name'],
                'plant_uri' => $plant['plant_uri'],
            ];
            array_push($plants, $newPair);
        }
        else {
            $matched = false;
            foreach ($plantsToSkip as $plantToSkip) {
                if ($plant['plant_name'] == $plantToSkip) {
                    $matched = true;
                    break;
                }
            }

            if ($matched == false) {
                $newPair = [ 
                    'plant_name' => $plant['plant_name'],
                    'plant_uri' => $plant['plant_uri'],
                ];
                array_push($plantsNonG, $newPair);
            }
        }
    }

    // echo "<pre>";
    // var_dump($plants);
    // var_dump($plantsNonG);

    // var_dump($stat);

    $app->render('plants.phtml', ['plants' => $plants, 'plantsNonG' => $plantsNonG]);
})->setName('plants');


$app->run();
