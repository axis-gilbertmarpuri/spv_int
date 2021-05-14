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

        if (checkdate($tempDate[1], $tempDate[2], $tempDate[0]) == false) {
            return false;
        }

        if (DateTime::createFromFormat('Y-m-d', $date_from) != true) {
            return false;
        }

        if (DateTime::createFromFormat('Y-m-d', $date_to) != true) {
            return false;
        }

    } catch (Exception $e) {
        return false;
    }

    return true;
}

// $app->post('/downloadplants', function () use ($app) {
//     $request = $app->request->post();

//     $date_from = $request['date_from'];
//     $date_to_raw = $request['date_to'];
//     $date_to = date('Y-m-d', strtotime("+1 day", strtotime($date_to_raw)));

//     $plant_uri = $request['plants'];

//     $plant_uri_cleaned = substr($plant_uri, 0, -1);
//     $plant_name = explode('https://ichigo.sp-viewer.net/', $plant_uri_cleaned);

//     $csv_name = $plant_name[1];

//     if (isValidDate($date_from, $date_to) != true) {
//         $app->render('error.phtml');
//         return;
//     }

//     $download_report_link = $plant_uri."ac/download_measurement.php?freq=daily&target_date_ge="
//                             .$date_from."&target_date_lt=".$date_to."&format=csv&table="
//                             .$csv_name."-".$date_from."-".$date_to_raw;
    
//     $app->redirect($download_report_link);
// })->setName('downloadplants');

// $app->get('/plants(/:stat)', function () use ($app) {

//     $cookieJar = CookieJar::fromArray(['cookie_name' => 'cookie_value'], 'https://dev-integration.sp-viewer.net');
//     $client = new \GuzzleHttp\Client(["base_uri" => "https://dev-integration.sp-viewer.net", 'cookies' => true]);

//     $params = [
//         "loginid" => "test",
//         "password" => "A1X2I3S4"
//     ];

//     // 笠岡		http://180.178.84.94/index.html
//     // 木城町		http://ichigo.delightviewer.jp/front/
//     // 世羅津口		http://202.229.41.205/icg/pg/spv/h2/solar/front/
//     // 世羅青水		http://202.229.41.205/icg/pg/spv/h3/solar/front/
//     // 芽室西士狩	http://202.229.41.205/icg/pg/spv/h4/solar/front/
//     // 常陸大宮		http://202.229.41.205/icg/pg/spv/h6/solar/front/
//     // 世羅下津田	No site
//     // 都城東霧島	No site
//     // えびの末永	No site
//     // 米子泉	No site
    
//     $plantsToSkip = array ('笠岡', '木城町', '世羅津口', '世羅青水', '芽室西士狩', '常陸大宮', '世羅下津田', '都城東霧島', 'えびの末永', '米子泉');

//     $response = $client->post("/api/v1/login", ['json' => $params, 'cookies' => $cookieJar]);
    
//     $response = $client->get("/api/v1/plants", ['cookies' => $cookieJar]);
//     $plantJsonDecoded = json_decode($response->getBody(), true);
//     $plants = [];
//     $plantsNonG = [];

//     foreach ($plantJsonDecoded as $plant) {
        
//         if ($plant['plant_name'][0] == 'G') {
//             $newPair = [ 
//                 'plant_name' => $plant['plant_name'],
//                 'plant_uri' => $plant['plant_uri'],
//             ];
//             array_push($plants, $newPair);
//         }
//         else {
//             $matched = false;
//             foreach ($plantsToSkip as $plantToSkip) {
//                 if ($plant['plant_name'] == $plantToSkip) {
//                     $matched = true;
//                     break;
//                 }
//             }

//             if ($matched == false) {
//                 $newPair = [ 
//                     'plant_name' => $plant['plant_name'],
//                     'plant_uri' => $plant['plant_uri'],
//                 ];
//                 array_push($plantsNonG, $newPair);
//             }
//         }
//     }

//     $app->render('plants.phtml', ['plants' => $plants, 'plantsNonG' => $plantsNonG]);
// })->setName('plants');

$app->get('/login', function () use($app) {
    $app->render('login.phtml');
});

$app->get('/plants(/:stat)', function () use($app) {
    $app->render('login.phtml');
});

$app->post('/plants(/:stat)', function () use ($app) {

    $request = $app->request->post();

    if (isset($_POST['username'], $_POST['password'])) {
        
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        if ($username == "ichigo" && $password == "Admin_15") {
            $cookieJar = CookieJar::fromArray(['cookie_name' => 'cookie_value'], 'https://dev-integration.sp-viewer.net');
            $client = new \GuzzleHttp\Client(["base_uri" => "https://dev-integration.sp-viewer.net", 'cookies' => true]);

            $params = [
                "loginid" => "ichigo",
                "password" => "Admin_15"
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
            // 米子泉	No site
            
            $plantsToSkip = array ('笠岡', '木城町', '世羅津口', '世羅青水', '芽室西士狩', '常陸大宮', '世羅下津田', '都城東霧島', 'えびの末永', '米子泉');

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

            $app->render('plants.phtml', ['plants' => $plants, 'plantsNonG' => $plantsNonG]);
        }
        else {
            return $app->render('error.phtml');
        }
    } 
    else {
        $app->render('login.phtml');
        echo '';
    }
})->setName('plants');

$app->run();
