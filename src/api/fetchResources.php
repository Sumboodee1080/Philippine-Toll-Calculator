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
        case 'getTollFee':
            $vehicleClassInput = $_POST['vehicleClass'];
            $entryInput = $_POST['entry'];
            $exitInput = $_POST['exit'];
            $expressLink = $_POST['expressWayLink'];

            $fee = getTollFee($expressLink, $vehicleClassInput, $entryInput, $exitInput);

            if ($fee !== null) {
                http_response_code(200);
                echo json_encode(array(
                    'success' => true,
                    'fee' => $fee
                ));
            } else {
                http_response_code(404);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Invalid Toll Input'
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

function trimWhiteSpaces($string)
{
    return preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string);
}

function fetchVehicleClass()
{
    global $vehicleClass_link;

    $vehicleClass_pattern = '/<span class="rl_sliders-toggle-inner[^"]*">.*?<strong>(.*?)<\/strong><\/span>/i';
    $vehicleClass_data = extract_data_by_pattern(web_scrape($vehicleClass_link), $vehicleClass_pattern, 1);

    $vehicleClass_data = array_values(array_filter($vehicleClass_data, function ($item) {
        return stripos($item, 'Class') !== false;
    }));

    return $vehicleClass_data;
}

function loadTollPlaza($expressLink)
{
    $compiledSourceURL = 'https://trb.gov.ph' . $expressLink;
    $tollPlaza_pattern = '/<td.*?><strong>(.*?)<\/strong><\/td>/';

    // Web scrape the data
    $scrapedData = web_scrape($compiledSourceURL);

    // Extract data using the pattern
    $tollData = array_map('trimWhiteSpaces', extract_data_by_pattern($scrapedData, $tollPlaza_pattern, 1));

    // Remove duplicates
    $uniqueTollData = array_unique($tollData);

    $filtered = array_values(array_filter($uniqueTollData, function ($item) {
        return stripos($item, 'Entry/Exit') === false;
    }));

    return $filtered;
}

function fetchExpressways()
{
    global $expressways_link;
    global $expressWayLinks;

    $webScrape = web_scrape($expressways_link);

    $expressways_pattern = '/<a\s+href="([^"]*toll-rates[^"]*)"[^>]*>(.*?)<\/a>/i';

    $expressways_data = array_unique(extract_data_by_pattern($webScrape, $expressways_pattern, 2));
    $expressWayLinks = array_unique(extract_data_by_pattern($webScrape, $expressways_pattern, 1));

    // throw new Exception(
    //     "expressWayLinks is empty. Debug:\n" . print_r($expressWayLinks, true)
    // );

    return $expressways_data;
}

function getTollFee($expressLink, $vehicleClass, $entryPoint, $exitPoint)
{
    global $base_URL;

    preg_match('/Class\s+(\d+)/i', $vehicleClass, $matches);
    $vehicleClass = (int) $matches[1];
    $entryPoint = trim($entryPoint);
    $exitPoint = trim($exitPoint);
    $expressLink = $base_URL . $expressLink;

    //throw new Exception("webLink=$expressLink vehicleClass=$vehicleClass entry=$entryPoint exit=$exitPoint");

    $html = web_scrape($expressLink);
    if (!$html) return null;

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xp = new DOMXPath($doc);

    // 4) find the H2 for "(CLASS-X)" (case-insensitive); also accept the "Toll Rate Matrix" phrasing
    $u = strtoupper((string)$vehicleClass);
    $h2 = $xp->query(
        sprintf(
            "//h2[
            (
                contains(
                    translate(., 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                    'CLASS-%s'
                )
                or
                contains(
                    translate(., 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                    'CLASS %s'
                )
            )
            or
            (
                contains(
                    translate(., 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                    'TOLL RATE MATRIX'
                )
                and (
                    contains(
                        translate(., 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                        'CLASS-%s'
                    )
                    or
                    contains(
                        translate(., 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                        'CLASS %s'
                    )
                )
            )
        ]",
            $u,
            $u,
            $u,
            $u
        )
    )->item(0);
    if (!$h2) return null;

    // 5) first table after that H2
    $table = $xp->query("following::table[1]", $h2)->item(0);
    if (!($table instanceof DOMElement)) return null;

    // 6) read rows via XPath (avoid getElementsByTagName to prevent the "undefined method" error)
    $rows = $xp->query(".//tr", $table);
    if ($rows->length === 0) return null;

    // header row (exits), skipping first cell
    $hdrCells = $xp->query(".//td", $rows->item(0));
    $exitHeaders = [];
    for ($i = 1; $i < $hdrCells->length; $i++) {
        $exitHeaders[] = clean_cell($hdrCells->item($i)->textContent);
    }

    // 7) build [entry][exit] matrix; mirror values
    $matrix = [];
    for ($r = 1; $r < $rows->length; $r++) {
        $cells = $xp->query(".//td", $rows->item($r));
        if ($cells->length === 0) continue;

        $entryName = clean_cell($cells->item(0)->textContent);
        if ($entryName === '') continue;

        for ($c = 1; $c < $cells->length && $c <= count($exitHeaders); $c++) {
            $exitName = $exitHeaders[$c - 1];
            $fee = parse_fee($cells->item($c)->textContent);
            if ($fee !== null) {
                $matrix[$entryName][$exitName] = $fee;
                $matrix[$exitName][$entryName] = $fee; // mirror so direction doesn't matter
            }
        }
    }

    // 8) exact lookup (same cleaning as table text)
    if (isset($matrix[$entryPoint][$exitPoint])) return $matrix[$entryPoint][$exitPoint];
    if (isset($matrix[$exitPoint][$entryPoint])) return $matrix[$exitPoint][$entryPoint];

    return null;
}

function clean_cell($s)
{
    $s = html_entity_decode((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = preg_replace('/\s+/u', ' ', trim($s));
    return $s;
}

function parse_fee($s)
{
    $s = (string)$s;
    // allow "₱311", "311", "311.00", "311 " etc.
    $s = preg_replace('/[^\d.]/u', '', $s);
    if ($s === '') return null;
    return is_numeric($s) ? (float)$s : null;
}
