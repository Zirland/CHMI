<?php
date_default_timezone_set('Europe/Prague');

function remArea($ORP)
{
    global $coverage;

    if (($key = array_search($ORP, $coverage)) !== false) {
        unset($coverage[$key]);
    }

    return $coverage;
}

function insArea($ORP)
{
    global $coverage;

    $coverage[] = $ORP;
    return $coverage;
}

$url = 'http://portal.chmi.cz/files/portal/docs/meteo/om/bulletiny/XOCZ50_OKPR.xml';
$xml = simplexml_load_file($url);

$identifier = $xml->identifier;
$sent       = $xml->sent;
$status     = $xml->status;
$msgType    = $xml->msgType;
$note       = $xml->note;
$references = $xml->references;
$incidents  = $xml->incidents;

$useSIVS = $useHPPS = $useSVRS = "0";
foreach ($xml->code as $system) {
    if ($system == "SIVS") {$useSIVS = "1";}
    if ($system == "SIVS:CHMI/111/89/2018") {$useSIVS = "1";}
    if ($system == "HPPS") {$useHPPS = "1";}
    if ($system == "SVRS") {$useSVRS = "1";}
}

$sent_timestamp = strtotime($sent);
$sent           = $sent_timestamp;

$query42 = "INSERT INTO header (`identifier`, `sent`, `status`, `msgType`, `codeSIVS`, `codeHPPS`, `codeSVRS`, `note`, `references`, `incidents`) VALUES ('$identifier', '$sent_timestamp', '$status', '$msgType', '$useSIVS', '$useHPPS', '$useSVRS', '$note', '$references', '$incidents');";
echo "$query42<br/>";
// $command37 = mysqli_query($link, $query42);
// $header_id = mysqli_insert_id($link);
$header_id = "HEADER_ID";

foreach ($xml->info as $jev) {
    $situation = $eventEndingTime = $hydroOutlook = "";
    $codeSIVS  = $codeSVRS  = $codeHPPS  = "0";

    $lang = $jev->language;
    $lang = substr($lang, 0, 2);

    $category = $jev->category;
    $event    = $jev->event;

    $responses = $jev->responseType;
    $respmatrx = "000000000";

    foreach ($responses as $response) {
        switch ($response) {
            case 'Shelter':
                $respmatrx[0] = 1;
                break;
            case 'Evacuate':
                $respmatrx[1] = 1;
                break;
            case 'Prepare':
                $respmatrx[2] = 1;
                break;
            case 'Execute':
                $respmatrx[3] = 1;
                break;
            case 'Avoid':
                $respmatrx[4] = 1;
                break;
            case 'Monitor':
                $respmatrx[5] = 1;
                break;
            case 'Assess':
                $respmatrx[6] = 1;
                break;
            case 'AllClear':
                $respmatrx[7] = 1;
                break;
            case 'None':
                $respmatrx[8] = 1;
                break;
        }
    }

    $urgency   = $jev->urgency;
    $severity  = $jev->severity;
    $certainty = $jev->certainty;

    foreach ($jev->eventCode as $eventcode) {
        if ($eventcode->valueName == "SIVS") {$codeSIVS = $eventcode->value;}
        if ($eventcode->valueName == "HPPS") {$codeHPPS = $eventcode->value;}
        if ($eventcode->valueName == "SVRS") {$codeSVRS = $eventcode->value;}
    }

    $onset       = $jev->onset;
    $expires     = $jev->expires;
    $headline    = $jev->headline;
    $description = $jev->description;
    $instruction = $jev->instruction;
    $web         = $jev->web;

    foreach ($jev->parameter as $kontrola) {
        ${$kontrola->valueName} .= $kontrola->value . "|";
    }

    $eventEndingTime = substr($eventEndingTime, 0, -1);
    $situation       = substr($situation, 0, -1);
    $hydroOutlook    = substr($hydroOutlook, 0, -1);

    $onset_timestamp   = strtotime($onset);
    $expires_timestamp = strtotime($eventEndingTime);
    if ($expires_timestamp == "") {
        $expires_timestamp = 0;
    }

    if ($lang == "cs" && $respmatrx != "000000010" && $respmatrx != "000000001") {
        $query114 = "INSERT INTO incidents (`header_id`, `language`, `category`, `event`, `responseType`, `urgency`, `severity`, `certainty`, `onset`, `expires`, `codeSIVS`, `codeHPPS`, `codeSVRS`, `headline`, `description`, `instruction`, `web`, `situation`, `hydroOutlook`) VALUES ('$header_id', '$lang', '$category', '$event',  '$respmatrx',  '$urgency', '$severity', '$certainty', '$onset_timestamp', '$expires_timestamp', '$codeSIVS', '$codeHPPS', '$codeSVRS', '$headline', '$description', '$instruction', '$web', '$situation', '$hydroOutlook');";
        echo "$query114<br/>";
        // $command114  = mysqli_query($link, $query114);
        //$incident_id = mysqli_insert_id($link);

        unset($coverage);

        $uzemi = $jev->area;
        foreach ($uzemi as $kraj) {
            $geo      = $kraj->geocode;
            $ceiling  = $kraj->ceiling;
            $altitude = $kraj->altitude;
            if ($ceiling == "") {
                $ceiling = 0;
            }
            if ($altitude == "") {
                $altitude = 0;
            }

            foreach ($geo as $ORP) {
                $kodorp     = strval($ORP->value);
                $coverage[] = $kodorp;
            }
        }

        if (in_array("1100", $coverage)) {
            remArea("1100");
            insArea("CZ0100");
        }

        if (in_array("CZ0100", $coverage)) {
            remArea("CZ0100");
            insArea("010");
        }

        if (in_array("2101", $coverage) && in_array("2125", $coverage) && in_array("2126", $coverage)) {
            remArea("2101");
            remArea("2125");
            remArea("2126");
            insArea("CZ0201");
        }

        if (in_array("2102", $coverage) && in_array("2108", $coverage)) {
            remArea("2102");
            remArea("2108");
            insArea("CZ0202");
        }

        if (in_array("2109", $coverage) && in_array("2124", $coverage)) {
            remArea("2109");
            remArea("2124");
            insArea("CZ0203");
        }

        if (in_array("2106", $coverage) && in_array("2110", $coverage)) {
            remArea("2106");
            remArea("2110");
            insArea("CZ0204");
        }

        if (in_array("2104", $coverage) && in_array("2112", $coverage)) {
            remArea("2104");
            remArea("2112");
            insArea("CZ0205");
        }

        if (in_array("2111", $coverage) && in_array("2114", $coverage) && in_array("2117", $coverage)) {
            remArea("2111");
            remArea("2114");
            remArea("2117");
            insArea("CZ0206");
        }

        if (in_array("2115", $coverage) && in_array("2116", $coverage)) {
            remArea("2115");
            remArea("2116");
            insArea("CZ0207");
        }

        if (in_array("2113", $coverage) && in_array("2118", $coverage) && in_array("2119", $coverage)) {
            remArea("2113");
            remArea("2118");
            remArea("2119");
            insArea("CZ0208");
        }

        if (in_array("2103", $coverage) && in_array("2122", $coverage)) {
            remArea("2103");
            remArea("2122");
            insArea("CZ0209");
        }

        if (in_array("2105", $coverage)) {
            remArea("2105");
            insArea("CZ020A");
        }

        if (in_array("2107", $coverage) && in_array("2120", $coverage) && in_array("2123", $coverage)) {
            remArea("2107");
            remArea("2120");
            remArea("2123");
            insArea("CZ020B");
        }

        if (in_array("2121", $coverage)) {
            remArea("2121");
            insArea("CZ020C");
        }

        if (in_array("CZ0201", $coverage) && in_array("CZ0202", $coverage) && in_array("CZ0203", $coverage) && in_array("CZ0204", $coverage) && in_array("CZ0205", $coverage) && in_array("CZ0206", $coverage) && in_array("CZ0207", $coverage) && in_array("CZ0208", $coverage) && in_array("CZ0209", $coverage) && in_array("CZ020A", $coverage) && in_array("CZ020B", $coverage) && in_array("CZ020C", $coverage)) {
            remArea("CZ0201");
            remArea("CZ0202");
            remArea("CZ0203");
            remArea("CZ0204");
            remArea("CZ0205");
            remArea("CZ0206");
            remArea("CZ0207");
            remArea("CZ0208");
            remArea("CZ0209");
            remArea("CZ020A");
            remArea("CZ020B");
            remArea("CZ020C");
            insArea("020");
        }

        if (in_array("3102", $coverage) && in_array("3113", $coverage) && in_array("3115", $coverage)) {
            remArea("3102");
            remArea("3113");
            remArea("3115");
            insArea("CZ0311");
        }

        if (in_array("3103", $coverage) && in_array("3106", $coverage)) {
            remArea("3103");
            remArea("3106");
            insArea("CZ0312");
        }

        if (in_array("3104", $coverage) && in_array("3105", $coverage) && in_array("3114", $coverage)) {
            remArea("3104");
            remArea("3105");
            remArea("3114");
            insArea("CZ0313");
        }

        if (in_array("3107", $coverage) && in_array("3108", $coverage)) {
            remArea("3107");
            remArea("3108");
            insArea("CZ0314");
        }

        if (in_array("3109", $coverage) && in_array("3116", $coverage)) {
            remArea("3109");
            remArea("3116");
            insArea("CZ0315");
        }

        if (in_array("3101", $coverage) && in_array("3111", $coverage) && in_array("3117", $coverage)) {
            remArea("3101");
            remArea("3111");
            remArea("3117");
            insArea("CZ0316");
        }

        if (in_array("3110", $coverage) && in_array("3112", $coverage)) {
            remArea("3110");
            remArea("3112");
            insArea("CZ0317");
        }

        if (in_array("CZ0311", $coverage) && in_array("CZ0312", $coverage) && in_array("CZ0313", $coverage) && in_array("CZ0314", $coverage) && in_array("CZ0315", $coverage) && in_array("CZ0316", $coverage) && in_array("CZ0317", $coverage)) {
            remArea("CZ0311");
            remArea("CZ0312");
            remArea("CZ0313");
            remArea("CZ0314");
            remArea("CZ0315");
            remArea("CZ0316");
            remArea("CZ0317");
            insArea("031");
        }

        if (in_array("3202", $coverage) && in_array("3204", $coverage)) {
            remArea("3202");
            remArea("3204");
            insArea("CZ0321");
        }

        if (in_array("3203", $coverage) && in_array("3205", $coverage) && in_array("3214", $coverage)) {
            remArea("3203");
            remArea("3205");
            remArea("3214");
            insArea("CZ0322");
        }

        if (in_array("3201", $coverage) && in_array("3207", $coverage) && in_array("3210", $coverage) && in_array("3212", $coverage)) {
            remArea("3201");
            remArea("3207");
            remArea("3210");
            remArea("3212");
            insArea("CZ0323");
        }

        if (in_array("3209", $coverage)) {
            remArea("3209");
            insArea("CZ0324");
        }

        if (in_array("3206", $coverage) && in_array("3208", $coverage)) {
            remArea("3206");
            remArea("3208");
            insArea("CZ0325");
        }

        if (in_array("3211", $coverage)) {
            remArea("3211");
            insArea("CZ0326");
        }

        if (in_array("3213", $coverage) && in_array("3215", $coverage)) {
            remArea("3213");
            remArea("3215");
            insArea("CZ0327");
        }

        if (in_array("CZ0321", $coverage) && in_array("CZ0322", $coverage) && in_array("CZ0323", $coverage) && in_array("CZ0324", $coverage) && in_array("CZ0325", $coverage) && in_array("CZ0326", $coverage) && in_array("CZ0327", $coverage)) {
            remArea("CZ0321");
            remArea("CZ0322");
            remArea("CZ0323");
            remArea("CZ0324");
            remArea("CZ0325");
            remArea("CZ0326");
            remArea("CZ0327");
            insArea("032");
        }

        if (in_array("4101", $coverage) && in_array("4102", $coverage) && in_array("4105", $coverage)) {
            remArea("4101");
            remArea("4102");
            remArea("4105");
            insArea("CZ0411");
        }

        if (in_array("4103", $coverage) && in_array("4106", $coverage)) {
            remArea("4103");
            remArea("4106");
            insArea("CZ0412");
        }

        if (in_array("4104", $coverage) && in_array("4107", $coverage)) {
            remArea("4104");
            remArea("4107");
            insArea("CZ0413");
        }

        if (in_array("CZ0411", $coverage) && in_array("CZ0412", $coverage) && in_array("CZ0413", $coverage)) {
            remArea("CZ0411");
            remArea("CZ0412");
            remArea("CZ0413");
            insArea("041");
        }

        if (in_array("4202", $coverage) && in_array("4212", $coverage) && in_array("4215", $coverage)) {
            insArea("CZ0421");
        }

        if (in_array("4203", $coverage) && in_array("4204", $coverage)) {
            insArea("CZ0422");
        }

        if (in_array("4205", $coverage) && in_array("4208", $coverage) && in_array("4211", $coverage)) {
            insArea("CZ0423");
        }

        if (in_array("4207", $coverage) && in_array("4210", $coverage) && in_array("4216", $coverage)) {
            insArea("CZ0424");
        }

        if (in_array("4206", $coverage) && in_array("4209", $coverage)) {
            insArea("CZ0425");
        }

        if (in_array("4201", $coverage) && in_array("4213", $coverage)) {
            insArea("CZ0426");
        }

        if (in_array("4214", $coverage)) {
            insArea("CZ0427");
        }

        if (in_array("CZ0421", $coverage) && in_array("CZ0422", $coverage) && in_array("CZ0423", $coverage) && in_array("CZ0424", $coverage) && in_array("CZ0425", $coverage) && in_array("CZ0426", $coverage) && in_array("CZ0427", $coverage)) {
            remArea("CZ0421");
            remArea("CZ0422");
            remArea("CZ0423");
            remArea("CZ0424");
            remArea("CZ0425");
            remArea("CZ0426");
            remArea("CZ0427");
            insArea("042");
        }

        if (in_array("5101", $coverage) && in_array("5106", $coverage)) {
            insArea("CZ0511");
        }

        if (in_array("5103", $coverage) && in_array("5108", $coverage) && in_array("5110", $coverage)) {
            insArea("CZ0512");
        }

        if (in_array("5102", $coverage) && in_array("5105", $coverage)) {
            insArea("CZ0513");
        }

        if (in_array("5104", $coverage) && in_array("5107", $coverage) && in_array("5109", $coverage)) {
            insArea("CZ0514");
        }

        if (in_array("CZ0511", $coverage) && in_array("CZ0512", $coverage) && in_array("CZ0513", $coverage) && in_array("CZ0514", $coverage)) {
            remArea("CZ0511");
            remArea("CZ0512");
            remArea("CZ0513");
            remArea("CZ0514");
            insArea("051");
        }

        if (in_array("5205", $coverage) && in_array("5212", $coverage)) {
            insArea("CZ0521");
        }

        if (in_array("5207", $coverage) && in_array("5210", $coverage) && in_array("5204", $coverage)) {
            insArea("CZ0522");
        }

        if (in_array("5206", $coverage) && in_array("5201", $coverage) && in_array("5211", $coverage) && in_array("5209", $coverage)) {
            insArea("CZ0523");
        }

        if (in_array("5208", $coverage) && in_array("5202", $coverage) && in_array("5213", $coverage)) {
            insArea("CZ0524");
        }

        if (in_array("5203", $coverage) && in_array("5214", $coverage) && in_array("5215", $coverage)) {
            insArea("CZ0525");
        }

        if (in_array("CZ0521", $coverage) && in_array("CZ0522", $coverage) && in_array("CZ0523", $coverage) && in_array("CZ0524", $coverage) && in_array("CZ0525", $coverage)) {
            remArea("CZ0521");
            remArea("CZ0522");
            remArea("CZ0523");
            remArea("CZ0524");
            remArea("CZ0525");
            insArea("052");
        }

        if (in_array("5302", $coverage) && in_array("5304", $coverage)) {
            insArea("CZ0531");
        }

        if (in_array("5303", $coverage) && in_array("5309", $coverage) && in_array("5311", $coverage)) {
            insArea("CZ0532");
        }

        if (in_array("5307", $coverage) && in_array("5308", $coverage) && in_array("5310", $coverage) && in_array("5312", $coverage)) {
            insArea("CZ0533");
        }

        if (in_array("5301", $coverage) && in_array("5305", $coverage) && in_array("5306", $coverage) && in_array("5313", $coverage) && in_array("5314", $coverage) && in_array("5315", $coverage)) {
            insArea("CZ0534");
        }

        if (in_array("CZ0531", $coverage) && in_array("CZ0532", $coverage) && in_array("CZ0533", $coverage) && in_array("CZ0534", $coverage)) {
            remArea("CZ0531");
            remArea("CZ0532");
            remArea("CZ0533");
            remArea("CZ0534");
            insArea("053");
        }

        if (in_array("6102", $coverage) && in_array("6104", $coverage) && in_array("6111", $coverage)) {
            insArea("CZ0631");
        }

        if (in_array("6105", $coverage) && in_array("6112", $coverage)) {
            insArea("CZ0632");
        }

        if (in_array("6103", $coverage) && in_array("6109", $coverage) && in_array("6110", $coverage)) {
            insArea("CZ0633");
        }

        if (in_array("6106", $coverage) && in_array("6107", $coverage) && in_array("6113", $coverage)) {
            insArea("CZ0634");
        }

        if (in_array("6101", $coverage) && in_array("6108", $coverage) && in_array("6114", $coverage) && in_array("6115", $coverage)) {
            insArea("CZ0635");
        }

        if (in_array("CZ0631", $coverage) && in_array("CZ0632", $coverage) && in_array("CZ0633", $coverage) && in_array("CZ0634", $coverage) && in_array("CZ0635", $coverage)) {
            remArea("CZ0631");
            remArea("CZ0632");
            remArea("CZ0633");
            remArea("CZ0634");
            remArea("CZ0635");
            insArea("063");
        }

        if (in_array("6201", $coverage) && in_array("6202", $coverage)) {
            insArea("CZ0641");
        }

        if (in_array("6203", $coverage)) {
            insArea("CZ0642");
        }

        if (in_array("6208", $coverage) && in_array("6209", $coverage) && in_array("6213", $coverage) && in_array("6214", $coverage) && in_array("6216", $coverage) && in_array("6217", $coverage) && in_array("6221", $coverage)) {
            insArea("CZ0643");
        }

        if (in_array("6204", $coverage) && in_array("6207", $coverage) && in_array("6211", $coverage)) {
            insArea("CZ0644");
        }

        if (in_array("6206", $coverage) && in_array("6210", $coverage) && in_array("6218", $coverage)) {
            insArea("CZ0645");
        }

        if (in_array("6219", $coverage) && in_array("6205", $coverage) && in_array("6215", $coverage)) {
            insArea("CZ0646");
        }

        if (in_array("6220", $coverage) && in_array("6212", $coverage)) {
            insArea("CZ0647");
        }

        if (in_array("CZ0641", $coverage) && in_array("CZ0642", $coverage) && in_array("CZ0643", $coverage) && in_array("CZ0644", $coverage) && in_array("CZ0645", $coverage) && in_array("CZ0646", $coverage) && in_array("CZ0647", $coverage)) {
            remArea("CZ0641");
            remArea("CZ0642");
            remArea("CZ0643");
            remArea("CZ0644");
            remArea("CZ0645");
            remArea("CZ0646");
            remArea("CZ0647");
            insArea("064");
        }

        if (in_array("7102", $coverage)) {
            insArea("CZ0711");
        }

        if (in_array("7107", $coverage) && in_array("7105", $coverage) && in_array("7112", $coverage) && in_array("7110", $coverage)) {
            insArea("CZ0712");
        }

        if (in_array("7108", $coverage) && in_array("7103", $coverage)) {
            insArea("CZ0713");
        }

        if (in_array("7101", $coverage) && in_array("7104", $coverage) && in_array("7109", $coverage)) {
            insArea("CZ0714");
        }

        if (in_array("7106", $coverage) && in_array("7111", $coverage) && in_array("7113", $coverage)) {
            insArea("CZ0715");
        }

        if (in_array("CZ0711", $coverage) && in_array("CZ0712", $coverage) && in_array("CZ0713", $coverage) && in_array("CZ0714", $coverage) && in_array("CZ0715", $coverage)) {
            remArea("CZ0711");
            remArea("CZ0712");
            remArea("CZ0713");
            remArea("CZ0714");
            remArea("CZ0715");
            insArea("071");
        }

        if (in_array("7201", $coverage) && in_array("7202", $coverage) && in_array("7203", $coverage)) {
            insArea("CZ0721");
        }

        if (in_array("7207", $coverage) && in_array("7208", $coverage)) {
            insArea("CZ0722");
        }

        if (in_array("7212", $coverage) && in_array("7209", $coverage) && in_array("7206", $coverage)) {
            insArea("CZ0723");
        }

        if (in_array("7204", $coverage) && in_array("7205", $coverage) && in_array("7209", $coverage) && in_array("7211", $coverage) && in_array("7213", $coverage)) {
            insArea("CZ0724");
        }

        if (in_array("CZ0721", $coverage) && in_array("CZ0722", $coverage) && in_array("CZ0723", $coverage) && in_array("CZ0724", $coverage)) {
            remArea("CZ0721");
            remArea("CZ0722");
            remArea("CZ0723");
            remArea("CZ0724");
            insArea("072");
        }

        if (in_array("8103", $coverage) && in_array("8120", $coverage) && in_array("8114", $coverage)) {
            insArea("CZ0801");
        }

        if (in_array("8106", $coverage) && in_array("8107", $coverage) && in_array("8110", $coverage) && in_array("8121", $coverage)) {
            insArea("CZ0802");
        }

        if (in_array("8102", $coverage) && in_array("8104", $coverage) && in_array("8108", $coverage) && in_array("8111", $coverage) && in_array("8118", $coverage)) {
            insArea("CZ0803");
        }

        if (in_array("8101", $coverage) && in_array("8105", $coverage) && in_array("8112", $coverage) && in_array("8115", $coverage) && in_array("8116", $coverage)) {
            insArea("CZ0804");
        }

        if (in_array("8109", $coverage) && in_array("8113", $coverage) && in_array("8117", $coverage) && in_array("8122", $coverage)) {
            insArea("CZ0805");
        }

        if (in_array("8119", $coverage)) {
            insArea("CZ0806");
        }

        if (in_array("CZ0801", $coverage) && in_array("CZ0802", $coverage) && in_array("CZ0803", $coverage) && in_array("CZ0804", $coverage) && in_array("CZ0805", $coverage) && in_array("CZ0806", $coverage)) {
            remArea("CZ0801");
            remArea("CZ0802");
            remArea("CZ0803");
            remArea("CZ0804");
            remArea("CZ0805");
            remArea("CZ0806");
            insArea("080");
        }

        if (in_array("010", $coverage) && in_array("020", $coverage) && in_array("031", $coverage) && in_array("032", $coverage) && in_array("041", $coverage) && in_array("042", $coverage) && in_array("051", $coverage) && in_array("052", $coverage) && in_array("053", $coverage) && in_array("063", $coverage) && in_array("064", $coverage) && in_array("071", $coverage) && in_array("072", $coverage) && in_array("080", $coverage)) {
            remArea("010");
            remArea("020");
            remArea("031");
            remArea("032");
            remArea("041");
            remArea("042");
            remArea("051");
            remArea("052");
            remArea("053");
            remArea("063");
            remArea("064");
            remArea("071");
            remArea("072");
            remArea("080");
            insArea("1");
        }

        print_r($coverage);
        echo "<br/>";
    }
}

include 'header.php';

echo "$header_id<br/>";
