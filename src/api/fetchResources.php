<?php
include_once 'scraper.php';
include 'resourceLinks.php';

$method = $_POST['method'] ?? '';
$expressWayLinks = '';

if (!empty($method)) {
    switch ($method) {
        case 'loadTollPlaza':
            $expressLink = $_POST['expressLink'];
            $tollPlazas = loadTollPlaza($expressLink);
            
            if (empty($tollPlazas)) {
                http_response_code(404);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'No Toll Plaza data found'
                ));
            } else {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode(array(
                    'success' => true,
                    'data' => $tollPlazas
                ));
            }
            exit();
        case 'vehicleClass':
            $vehicleClass = fetchVehicleClass();

            if (empty($vehicleClass)) {
                http_response_code(404);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'No Vehicle class data found'
                ));
            } else {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode(array(
                    'success' => true,
                    'data' => $vehicleClass
                ));
            }
            exit();
        case 'expressWays':
            $expressWays = fetchExpressways();

            if (empty($expressWays)) {
                http_response_code(404);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'No Expressways data found'
                ));
            } else {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode(array(
                    'success' => true,
                    'data' => $expressWays,
                    'links' => $expressWayLinks,
                ));
            }
            exit();
        default:
            http_response_code(400);
            echo json_encode(array(
                'success' => false,
                'message' => 'Invalid method provided'
            ));
            exit();
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'message' => 'No method provided'
    ));
    exit();
}

function trimWhiteSpaces($string) {
    return preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string);
}

function fetchVehicleClass()
{
    global $vehicleClass_link;

    $vehicleClass_pattern = '/<span class="rl_sliders-toggle-inner[^"]*">.*?<strong>(.*?)<\/strong><\/span>/i';
    $vehicleClass_data = extract_data_by_pattern(web_scrape($vehicleClass_link), $vehicleClass_pattern, 1);

    return $vehicleClass_data;
}

function loadTollPlaza($expressLink) {
    $compiledSourceURL = 'https://trb.gov.ph' . $expressLink;
    $tollPlaza_pattern = '/<td.*?><strong>(.*?)<\/strong><\/td>/';

    // Web scrape the data
    $scrapedData = web_scrape($compiledSourceURL);

    // Extract data using the pattern
    $tollData = array_map('trimWhiteSpaces', extract_data_by_pattern($scrapedData, $tollPlaza_pattern, 1));

    // Remove duplicates
    $uniqueTollData = array_unique($tollData);

    return $uniqueTollData;
}

function fetchExpressways()
{
    global $expressways_link;
    global $expressWayLinks;

    $webScrape = web_scrape($expressways_link);

    $expressways_pattern = '/<a\s+href="([^"]*toll-rates[^"]*)"[^>]*>(.*?)<\/a>/i';
    $expressways_data = array_unique(extract_data_by_pattern($webScrape, $expressways_pattern, 2));
    $expressWayLinks = array_unique(extract_data_by_pattern($webScrape, $expressways_pattern, 1));

    return $expressways_data;
}
