<?php
date_default_timezone_set('Europe/Prague');

function insArea($ORP)
{
    global $link, $header_id, $incident_id, $onset_timestamp, $expires_timestamp, $severity, $ceiling, $altitude, $coverage;

    $query135   = "INSERT INTO area (`header_id`, `incident_id`, `ORP`, `onset`, `expires`, `severity`, `ceiling`, `altitude`) VALUES ('$header_id','$incident_id','$ORP', '$onset_timestamp', '$expires_timestamp', '$severity', '$ceiling', '$altitude');";
    $zapis135   = mysqli_query($link, $query135);
    $coverage[] = $ORP;
    return $coverage;
}

require_once 'dbconnect.php';

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$link) {
    echo "Error: Unable to connect to database." . PHP_EOL;
    echo "Reason: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

$files = glob("XOCZ*");
usort($files, function ($a, $b) {
    return filemtime($a) > filemtime($b);
});

$pocet = count($files);

echo "Working on $pocet files<br/>";

if ($pocet > 10) {
    $pocet = 10;
}

if ($files) {
    for ($i = 0; $i < $pocet; $i++) {
        $file = $files[$i];

        $xml = simplexml_load_file($file);

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

        $query42   = "INSERT INTO header (`identifier`, `sent`, `status`, `msgType`, `codeSIVS`, `codeHPPS`, `codeSVRS`, `note`, `references`, `incidents`) VALUES ('$identifier', '$sent_timestamp', '$status', '$msgType', '$useSIVS', '$useHPPS', '$useSVRS', '$note', '$references', '$incidents');";
        $command37 = mysqli_query($link, $query42);
        $header_id = mysqli_insert_id($link);

        foreach ($xml->info as $jev) {
            $situation = $criterion = $eventEndingTime = $hydroOutlook = "";
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

            $situation    = substr($situation, 0, -1);
            $hydroOutlook = substr($hydroOutlook, 0, -1);

            $onset_timestamp   = strtotime($onset);
            $expires_timestamp = strtotime($expires);
            if ($expires_timestamp == "") {
                $expires_timestamp = 0;
            }

            if ($lang == "cs" && $respmatrx != "000000010" && $respmatrx != "000000001" && $respmatrx != "000000000" && $codeSIVS != "OUTLOOK") {
                $query114    = "INSERT INTO incidents (`header_id`, `language`, `category`, `event`, `responseType`, `urgency`, `severity`, `certainty`, `onset`, `expires`, `codeSIVS`, `codeHPPS`, `codeSVRS`, `headline`, `description`, `instruction`, `web`, `situation`, `hydroOutlook`) VALUES ('$header_id', '$lang', '$category', '$event',  '$respmatrx',  '$urgency', '$severity', '$certainty', '$onset_timestamp', '$expires_timestamp', '$codeSIVS', '$codeHPPS', '$codeSVRS', '$headline', '$description', '$instruction', '$web', '$situation', '$hydroOutlook');";
                $command114  = mysqli_query($link, $query114);
                $incident_id = mysqli_insert_id($link);

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
                        $kodorp = $ORP->value;

                        $query127   = "INSERT INTO area (`header_id`, `incident_id`, `ORP`, `onset`, `expires`, `severity`, `ceiling`, `altitude`) VALUES ('$header_id', '$incident_id', '$kodorp', '$onset_timestamp', '$expires_timestamp', '$severity', '$ceiling', '$altitude');";
                        $command127 = mysqli_query($link, $query127);
                    }
                }

                $query125 = "SELECT ORP FROM area WHERE incident_id = '$incident_id';";
                if ($result125 = mysqli_query($link, $query125)) {
                    while ($row125 = mysqli_fetch_assoc($result125)) {
                        $coverage[] = $row125['ORP'];
                    }
                }

                // Praha
                if (in_array("1100", $coverage)) {
                    insArea("CZ0100");
                }

                // Hl. m. Praha
                if (in_array("CZ0100", $coverage)) {
                    insArea("010");
                }

                // Benešov
                if (in_array("2101", $coverage) && in_array("2126", $coverage) && in_array("2125", $coverage)) {
                    insArea("CZ0201");
                }

                // Beroun
                if (in_array("2102", $coverage) && in_array("2108", $coverage)) {
                    insArea("CZ0202");
                }

                // Kladno
                if (in_array("2109", $coverage) && in_array("2124", $coverage)) {
                    insArea("CZ0203");
                }

                // Kolín
                if (in_array("2110", $coverage) && in_array("2106", $coverage)) {
                    insArea("CZ0204");
                }

                // Kutná Hora
                if (in_array("2112", $coverage) && in_array("2104", $coverage)) {
                    insArea("CZ0205");
                }

                // Mělník
                if (in_array("2114", $coverage) && in_array("2111", $coverage) && in_array("2117", $coverage)) {
                    insArea("CZ0206");
                }

                // Mladá Boleslav
                if (in_array("2115", $coverage) && in_array("2116", $coverage)) {
                    insArea("CZ0207");
                }

                // Nymburk
                if (in_array("2118", $coverage) && in_array("2113", $coverage) && in_array("2119", $coverage)) {
                    insArea("CZ0208");
                }

                // Praha-východ
                if (in_array("2103", $coverage) && in_array("2122", $coverage)) {
                    insArea("CZ0209");
                }

                // Praha-západ
                if (in_array("2105", $coverage)) {
                    insArea("CZ020A");
                }

                // Příbram
                if (in_array("2120", $coverage) && in_array("2107", $coverage) && in_array("2123", $coverage)) {
                    insArea("CZ020B");
                }

                // Rakovník
                if (in_array("2121", $coverage)) {
                    insArea("CZ020C");
                }

                // Středočeský kraj
                if (in_array("CZ0201", $coverage) && in_array("CZ0202", $coverage) && in_array("CZ0203", $coverage) && in_array("CZ0204", $coverage) && in_array("CZ0205", $coverage) && in_array("CZ0206", $coverage) && in_array("CZ0207", $coverage) && in_array("CZ0208", $coverage) && in_array("CZ0209", $coverage) && in_array("CZ020A", $coverage) && in_array("CZ020B", $coverage) && in_array("CZ020C", $coverage)) {
                    insArea("020");
                }

                // České Budějovice
                if (in_array("3102", $coverage) && in_array("3113", $coverage) && in_array("3115", $coverage)) {
                    insArea("CZ0311");
                }

                // Český Krumlov
                if (in_array("3103", $coverage) && in_array("3106", $coverage)) {
                    insArea("CZ0312");
                }

                // Jindřichův Hradec
                if (in_array("3105", $coverage) && in_array("3104", $coverage) && in_array("3114", $coverage)) {
                    insArea("CZ0313");
                }

                // Písek
                if (in_array("3108", $coverage) && in_array("3107", $coverage)) {
                    insArea("CZ0314");
                }

                // Prachatice
                if (in_array("3109", $coverage) && in_array("3116", $coverage)) {
                    insArea("CZ0315");
                }

                // Strakonice
                if (in_array("3111", $coverage) && in_array("3101", $coverage) && in_array("3117", $coverage)) {
                    insArea("CZ0316");
                }

                // Tábor
                if (in_array("3110", $coverage) && in_array("3112", $coverage)) {
                    insArea("CZ0317");
                }

                // Jihočeský kraj
                if (in_array("CZ0311", $coverage) && in_array("CZ0312", $coverage) && in_array("CZ0313", $coverage) && in_array("CZ0314", $coverage) && in_array("CZ0315", $coverage) && in_array("CZ0316", $coverage) && in_array("CZ0317", $coverage)) {
                    insArea("031");
                }

                // Domažlice
                if (in_array("3202", $coverage) && in_array("3204", $coverage)) {
                    insArea("CZ0321");
                }

                // Klatovy
                if (in_array("3205", $coverage) && in_array("3203", $coverage) && in_array("3214", $coverage)) {
                    insArea("CZ0322");
                }

                // Plzeň-jih
                if (in_array("3212", $coverage) && in_array("3210", $coverage) && in_array("3201", $coverage) && in_array("3207", $coverage)) {
                    insArea("CZ0323");
                }

                // Plzeň-město
                if (in_array("3209", $coverage)) {
                    insArea("CZ0324");
                }

                // Plzeň-sever
                if (in_array("3208", $coverage) && in_array("3206", $coverage)) {
                    insArea("CZ0325");
                }

                // Rokycany
                if (in_array("3211", $coverage)) {
                    insArea("CZ0326");
                }

                // Tachov
                if (in_array("3213", $coverage) && in_array("3215", $coverage)) {
                    insArea("CZ0327");
                }

                // Plzeňský kraj
                if (in_array("CZ0321", $coverage) && in_array("CZ0322", $coverage) && in_array("CZ0323", $coverage) && in_array("CZ0324", $coverage) && in_array("CZ0325", $coverage) && in_array("CZ0326", $coverage) && in_array("CZ0327", $coverage)) {
                    insArea("032");
                }

                // Cheb
                if (in_array("4101", $coverage) && in_array("4102", $coverage) && in_array("4105", $coverage)) {
                    insArea("CZ0411");
                }

                // Karlovy Vary
                if (in_array("4103", $coverage) && in_array("4106", $coverage)) {
                    insArea("CZ0412");
                }

                // Sokolov
                if (in_array("4104", $coverage) && in_array("4107", $coverage)) {
                    insArea("CZ0413");
                }

                // Karlovarský kraj
                if (in_array("CZ0411", $coverage) && in_array("CZ0412", $coverage) && in_array("CZ0413", $coverage)) {
                    insArea("041");
                }

                // Děčín
                if (in_array("4202", $coverage) && in_array("4212", $coverage) && in_array("4215", $coverage)) {
                    insArea("CZ0421");
                }

                // Chomutov
                if (in_array("4203", $coverage) && in_array("4204", $coverage)) {
                    insArea("CZ0422");
                }

                // Litoměřice
                if (in_array("4205", $coverage) && in_array("4208", $coverage) && in_array("4211", $coverage)) {
                    insArea("CZ0423");
                }

                // Louny
                if (in_array("4207", $coverage) && in_array("4210", $coverage) && in_array("4216", $coverage)) {
                    insArea("CZ0424");
                }

                // Most
                if (in_array("4206", $coverage) && in_array("4209", $coverage)) {
                    insArea("CZ0425");
                }

                // Teplice
                if (in_array("4201", $coverage) && in_array("4213", $coverage)) {
                    insArea("CZ0426");
                }

                // Ústí nad Labem
                if (in_array("4214", $coverage)) {
                    insArea("CZ0427");
                }

                // Ústecký kraj
                if (in_array("CZ0421", $coverage) && in_array("CZ0422", $coverage) && in_array("CZ0423", $coverage) && in_array("CZ0424", $coverage) && in_array("CZ0425", $coverage) && in_array("CZ0426", $coverage) && in_array("CZ0427", $coverage)) {
                    insArea("042");
                }

                // Česká Lípa
                if (in_array("5101", $coverage) && in_array("5106", $coverage)) {
                    insArea("CZ0511");
                }

                // Jablonec nad Nisou
                if (in_array("5103", $coverage) && in_array("5108", $coverage) && in_array("5110", $coverage)) {
                    insArea("CZ0512");
                }

                // Liberec
                if (in_array("5102", $coverage) && in_array("5105", $coverage)) {
                    insArea("CZ0513");
                }

                // Turnov
                if (in_array("5104", $coverage) && in_array("5107", $coverage) && in_array("5109", $coverage)) {
                    insArea("CZ0514");
                }

                // Liberecký kraj
                if (in_array("CZ0511", $coverage) && in_array("CZ0512", $coverage) && in_array("CZ0513", $coverage) && in_array("CZ0514", $coverage)) {
                    insArea("051");
                }

                // Hradec Králové
                if (in_array("5205", $coverage) && in_array("5212", $coverage)) {
                    insArea("CZ0521");
                }

                // Jičín
                if (in_array("5207", $coverage) && in_array("5210", $coverage) && in_array("5204", $coverage)) {
                    insArea("CZ0522");
                }

                // Náchod
                if (in_array("5206", $coverage) && in_array("5201", $coverage) && in_array("5211", $coverage) && in_array("5209", $coverage)) {
                    insArea("CZ0523");
                }

                // Rychnov nad Kněžnou
                if (in_array("5208", $coverage) && in_array("5202", $coverage) && in_array("5213", $coverage)) {
                    insArea("CZ0524");
                }

                // Trutnov
                if (in_array("5203", $coverage) && in_array("5214", $coverage) && in_array("5215", $coverage)) {
                    insArea("CZ0525");
                }

                // Královéhradecký kraj
                if (in_array("CZ0521", $coverage) && in_array("CZ0522", $coverage) && in_array("CZ0523", $coverage) && in_array("CZ0524", $coverage) && in_array("CZ0525", $coverage)) {
                    insArea("052");
                }

                // Chrudim
                if (in_array("5302", $coverage) && in_array("5304", $coverage)) {
                    insArea("CZ0531");
                }

                // Pardubice
                if (in_array("5303", $coverage) && in_array("5309", $coverage) && in_array("5311", $coverage)) {
                    insArea("CZ0532");
                }

                // Svitavy
                if (in_array("5307", $coverage) && in_array("5308", $coverage) && in_array("5310", $coverage) && in_array("5312", $coverage)) {
                    insArea("CZ0533");
                }

                // Ústí nad Orlicí
                if (in_array("5301", $coverage) && in_array("5305", $coverage) && in_array("5306", $coverage) && in_array("5313", $coverage) && in_array("5314", $coverage) && in_array("5315", $coverage)) {
                    insArea("CZ0534");
                }

                // Pardubický kraj
                if (in_array("CZ0531", $coverage) && in_array("CZ0532", $coverage) && in_array("CZ0533", $coverage) && in_array("CZ0534", $coverage)) {
                    insArea("053");
                }

                // Havlíčkův Brod
                if (in_array("6102", $coverage) && in_array("6104", $coverage) && in_array("6111", $coverage)) {
                    insArea("CZ0631");
                }

                // Jihlava
                if (in_array("6105", $coverage) && in_array("6112", $coverage)) {
                    insArea("CZ0632");
                }

                // Pelhřimov
                if (in_array("6103", $coverage) && in_array("6109", $coverage) && in_array("6110", $coverage)) {
                    insArea("CZ0633");
                }

                // Třebíč
                if (in_array("6106", $coverage) && in_array("6107", $coverage) && in_array("6113", $coverage)) {
                    insArea("CZ0634");
                }

                // Žďár nad Sázavou
                if (in_array("6101", $coverage) && in_array("6108", $coverage) && in_array("6114", $coverage) && in_array("6115", $coverage)) {
                    insArea("CZ0635");
                }

                // Kraj Vysočina
                if (in_array("CZ0631", $coverage) && in_array("CZ0632", $coverage) && in_array("CZ0633", $coverage) && in_array("CZ0634", $coverage) && in_array("CZ0635", $coverage)) {
                    insArea("063");
                }

                // Blansko
                if (in_array("6201", $coverage) && in_array("6202", $coverage)) {
                    insArea("CZ0641");
                }

                // Brno-město
                if (in_array("6203", $coverage)) {
                    insArea("CZ0642");
                }

                // Brno-venkov
                if (in_array("6208", $coverage) && in_array("6209", $coverage) && in_array("6213", $coverage) && in_array("6214", $coverage) && in_array("6216", $coverage) && in_array("6217", $coverage) && in_array("6221", $coverage)) {
                    insArea("CZ0643");
                }

                // Břeclav
                if (in_array("6204", $coverage) && in_array("6207", $coverage) && in_array("6211", $coverage)) {
                    insArea("CZ0644");
                }

                // Hodonín
                if (in_array("6206", $coverage) && in_array("6210", $coverage) && in_array("6218", $coverage)) {
                    insArea("CZ0645");
                }

                // Vyškov
                if (in_array("6219", $coverage) && in_array("6205", $coverage) && in_array("6215", $coverage)) {
                    insArea("CZ0646");
                }

                // Znojmo
                if (in_array("6220", $coverage) && in_array("6212", $coverage)) {
                    insArea("CZ0647");
                }

                // Jihomoravský kraj
                if (in_array("CZ0641", $coverage) && in_array("CZ0642", $coverage) && in_array("CZ0643", $coverage) && in_array("CZ0644", $coverage) && in_array("CZ0645", $coverage) && in_array("CZ0646", $coverage) && in_array("CZ0647", $coverage)) {
                    insArea("064");
                }

                // Jeseník
                if (in_array("7102", $coverage)) {
                    insArea("CZ0711");
                }

                // Olomouc
                if (in_array("7107", $coverage) && in_array("7105", $coverage) && in_array("7112", $coverage) && in_array("7110", $coverage)) {
                    insArea("CZ0712");
                }

                // Prostějov
                if (in_array("7108", $coverage) && in_array("7103", $coverage)) {
                    insArea("CZ0713");
                }

                // Přerov
                if (in_array("7101", $coverage) && in_array("7104", $coverage) && in_array("7109", $coverage)) {
                    insArea("CZ0714");
                }

                // Šumperk
                if (in_array("7106", $coverage) && in_array("7111", $coverage) && in_array("7113", $coverage)) {
                    insArea("CZ0715");
                }

                // Olomoucký kraj
                if (in_array("CZ0711", $coverage) && in_array("CZ0712", $coverage) && in_array("CZ0713", $coverage) && in_array("CZ0714", $coverage) && in_array("CZ0715", $coverage)) {
                    insArea("071");
                }

                // Kroměříž
                if (in_array("7201", $coverage) && in_array("7202", $coverage) && in_array("7203", $coverage)) {
                    insArea("CZ0721");
                }

                // Uherské Hradiště
                if (in_array("7207", $coverage) && in_array("7208", $coverage)) {
                    insArea("CZ0722");
                }

                // Vsetín
                if (in_array("7212", $coverage) && in_array("7209", $coverage) && in_array("7206", $coverage)) {
                    insArea("CZ0723");
                }

                // Zlín
                if (in_array("7204", $coverage) && in_array("7205", $coverage) && in_array("7209", $coverage) && in_array("7211", $coverage) && in_array("7213", $coverage)) {
                    insArea("CZ0724");
                }

                // Zlínský kraj
                if (in_array("CZ0721", $coverage) && in_array("CZ0722", $coverage) && in_array("CZ0723", $coverage) && in_array("CZ0724", $coverage)) {
                    insArea("072");
                }

                // Bruntál
                if (in_array("8103", $coverage) && in_array("8120", $coverage) && in_array("8114", $coverage)) {
                    insArea("CZ0801");
                }

                // Frýdek-Místek
                if (in_array("8106", $coverage) && in_array("8107", $coverage) && in_array("8110", $coverage) && in_array("8121", $coverage)) {
                    insArea("CZ0802");
                }

                // Karviná
                if (in_array("8102", $coverage) && in_array("8104", $coverage) && in_array("8108", $coverage) && in_array("8111", $coverage) && in_array("8118", $coverage)) {
                    insArea("CZ0803");
                }

                // Nový Jičín
                if (in_array("8101", $coverage) && in_array("8105", $coverage) && in_array("8112", $coverage) && in_array("8115", $coverage) && in_array("8116", $coverage)) {
                    insArea("CZ0804");
                }

                // Opava
                if (in_array("8109", $coverage) && in_array("8113", $coverage) && in_array("8117", $coverage) && in_array("8122", $coverage)) {
                    insArea("CZ0805");
                }

                // Ostrava-město
                if (in_array("8119", $coverage)) {
                    insArea("CZ0806");
                }

                // Moravskoslezský kraj
                if (in_array("CZ0801", $coverage) && in_array("CZ0802", $coverage) && in_array("CZ0803", $coverage) && in_array("CZ0804", $coverage) && in_array("CZ0805", $coverage) && in_array("CZ0806", $coverage)) {
                    insArea("080");
                }

                // Česká republika
                if (in_array("010", $coverage) && in_array("020", $coverage) && in_array("031", $coverage) && in_array("032", $coverage) && in_array("041", $coverage) && in_array("042", $coverage) && in_array("051", $coverage) && in_array("052", $coverage) && in_array("053", $coverage) && in_array("063", $coverage) && in_array("064", $coverage) && in_array("071", $coverage) && in_array("072", $coverage) && in_array("080", $coverage)) {
                    insArea("1");
                }
                unset($coverage);
            }
        }

        $query651  = "INSERT INTO pair (header) VALUES ($header_id);";
        $prikaz651 = mysqli_query($link, $query651);

        unlink($file);
        echo "Unlink $file<br/>";
    }
}

if ($pocet > 0) {
    echo "<meta http-equiv=\"refresh\" content=\"5\">";
} else {
    echo "<meta http-equiv=\"refresh\" content=\"5; url=diff.php\">";
}

mysqli_close($link);
