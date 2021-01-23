<?php
$title = "Výstražná informace";
include 'header.php';

$header_id = $_GET["header"];
$getORP    = $_GET["ORP"];
$getF      = $_GET["F"];
echo $getF;

if ($getORP != "") {
    $initORP = $getORP;
} else {
    $initORP = "1";
}

$vystup  = "";
$useHPPS = $useSIVS = $useSVRS = 0;

$cisti16 = mysqli_query($link, "TRUNCATE TABLE temp;");

$ORP = $initORP;

$query55 = "SELECT sent,`references` FROM header WHERE id = '$header_id';";
if ($result55 = mysqli_query($link, $query55)) {
    while ($row55 = mysqli_fetch_row($result55)) {
        $sent        = $row55[0];
        $references  = $row55[1];
        $previous    = explode(",", $references);
        $previous_ID = $previous[1];
    }
}

$query192 = "SELECT MAX(expires) FROM incidents WHERE header_id = '$header_id';";
if ($result192 = mysqli_query($link, $query192)) {
    while ($row192 = mysqli_fetch_row($result192)) {
        $maxdoba = $row192[0];
        if (!$maxdoba) {$maxdoba = strtotime(date("d.m.Y H:i:s", $sent) . "+ 3 days");}
    }
}

$vystup .= "<blockquote>";

unset($used);
$celkem = 0;

unset($abandoned);
unset($striked);
$striked[]   = 0;
$abandoned[] = 0;

$query14 = "SELECT id FROM header WHERE identifier = '$previous_ID';";
if ($result14 = mysqli_query($link, $query14)) {
    while ($row14 = mysqli_fetch_row($result14)) {
        $old_header_id = $row14[0];
    }
}

$query96 = "SELECT DISTINCT incident_id, onset FROM area WHERE header_id = '$old_header_id' AND next = '0' AND (expires > '$sent' OR expires = '0') ORDER BY onset;";
if ($result96 = mysqli_query($link, $query96)) {
    while ($row96 = mysqli_fetch_row($result96)) {
        $abandoned[] = $row96[0];
    }
}

$abandonlist = implode(",", $abandoned);

$query122 = "SELECT up_level FROM orp WHERE code = '$ORP';";
if ($result122 = mysqli_query($link, $query122)) {
    while ($row122 = mysqli_fetch_row($result122)) {
        $uplevel = $row122[0];
    }
}

$query129 = "SELECT code FROM orp WHERE up_level = '$ORP';";
if ($result129 = mysqli_query($link, $query129)) {
    while ($row129 = mysqli_fetch_row($result129)) {
        $downlevel[] = $row129[0];
    }
}

$nazev = $prvky[$ORP];

if ($getF == "1") {
    $strikedlist = implode(",", $striked);
    $query123    = "SELECT incident_id FROM area WHERE ORP = '$ORP' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0;";
    if ($result123 = mysqli_query($link, $query123)) {
        $count123 = mysqli_num_rows($result123);
    }

    $query225 = "SELECT incident_id, prev, onset, severity FROM area WHERE header_id = '$header_id' AND ORP = '$ORP' ORDER BY onset, FIELD(severity, 'Extreme', 'Severe', 'Moderate');";
    if ($result225 = mysqli_query($link, $query225)) {
        $count225 = mysqli_num_rows($result225);
        $celkem   = $celkem + $count225;
        $count225 = $count123 + $count225;

        if ($count225 > 0) {
            $vystup .= "</blockquote><blockquote><u><b>$nazev</b></u><br/>";
            $strikedlist = implode(",", $striked);
            $query144    = "SELECT incident_id, onset FROM area WHERE ORP = '1' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0 ORDER BY onset;";
            if ($result144 = mysqli_query($link, $query144)) {
                while ($row144 = mysqli_fetch_row($result144)) {
                    $old = $row144[0];
                    $vystup .= Fcompare($old, 0, '1');
                    $striked[] = $old;
                }
            }

            if ($result152 = mysqli_query($link, $query225)) {
                while ($row152 = mysqli_fetch_row($result152)) {
                    $incident_id = $row152[0];
                    $prev        = $row152[1];

                    $used[] = $incident_id;
                    $vystup .= Fcompare($prev, $incident_id, $ORP);
                }
            }
        }
    }
} elseif ($ORP != "1") {
    $strikedlist = implode(",", $striked);
    $query123    = "SELECT incident_id FROM area WHERE ORP = '1' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0;";
    if ($result123 = mysqli_query($link, $query123)) {
        $count123 = mysqli_num_rows($result123);
    }

    $query225 = "SELECT incident_id, prev, onset, severity FROM area WHERE header_id = '$header_id' AND ORP = '1' ORDER BY onset, FIELD(severity, 'Extreme', 'Severe', 'Moderate');";
    if ($result225 = mysqli_query($link, $query225)) {
        $count225 = mysqli_num_rows($result225);
        $celkem   = $celkem + $count225;
        $count225 = $count123 + $count225;

        if ($count225 > 0) {
            $vystup .= "</blockquote><blockquote><u><b>Česká republika</b></u><br/>";
            $strikedlist = implode(",", $striked);
            $query144    = "SELECT incident_id, onset FROM area WHERE ORP = '1' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0 ORDER BY onset;";
            if ($result144 = mysqli_query($link, $query144)) {
                while ($row144 = mysqli_fetch_row($result144)) {
                    $old = $row144[0];
                    $vystup .= Fcompare($old, 0, '1');
                    $striked[] = $old;
                }
            }

            if ($result152 = mysqli_query($link, $query225)) {
                while ($row152 = mysqli_fetch_row($result152)) {
                    $incident_id = $row152[0];
                    $prev        = $row152[1];

                    $used[] = $incident_id;
                    $vystup .= Fcompare($prev, $incident_id, $ORP);
                }
            }
        }
    }
}

$strikedlist = implode(",", $striked);
$query172    = "SELECT incident_id FROM area WHERE header_id = '$old_header_id' AND ORP = '$ORP' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0;";
if ($result172 = mysqli_query($link, $query172)) {
    $count172 = mysqli_num_rows($result172);
}

$query138 = "SELECT incident_id, prev, onset, severity FROM area WHERE header_id = '$header_id' AND ORP = '$ORP' AND incident_id NOT IN ($abandonlist) ORDER BY onset, FIELD(severity, 'Extreme', 'Severe', 'Moderate');";
if ($result138 = mysqli_query($link, $query138)) {
    $count138 = mysqli_num_rows($result138);
    $celkem   = $celkem + $count138;
    $count138 = $count172 + $count138;
    while ($row138 = mysqli_fetch_row($result138)) {
        $incident_id = $row138[0];
        $query146    = "SELECT id FROM area WHERE incident_id = '$incident_id' AND ORP = '$uplevel';";
        if ($result146 = mysqli_query($link, $query146)) {
            $count146 = mysqli_num_rows($result146);
            $count138 = $count138 - $count146;
        }
    }
    if ($count138 > 0) {
        $vystup .= "</blockquote><blockquote><u><b>$nazev</b></u><br/>";
        $strikedlist = implode(",", $striked);
        $query202    = "SELECT incident_id, onset FROM area WHERE ORP = '$ORP' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0 ORDER BY onset;";
        if ($result202 = mysqli_query($link, $query202)) {
            while ($row202 = mysqli_fetch_row($result202)) {
                $old = $row202[0];
                $vystup .= Fcompare($old, 0, $ORP);
                $striked[] = $old;
            }
        }

        if ($result152 = mysqli_query($link, $query138)) {
            while ($row152 = mysqli_fetch_row($result152)) {
                $incident_id = $row152[0];
                $prev        = $row152[1];

                $query168 = "SELECT id FROM area WHERE header_id = '$header_id' AND incident_id = '$incident_id' AND ORP = '$uplevel';";
                if ($result168 = mysqli_query($link, $query168)) {
                    $count168 = mysqli_num_rows($result168);
                    if ($count168 == 0) {
                        $used[] = $incident_id;
                        $vystup .= Fcompare($prev, $incident_id, $ORP);
                    }
                }
            }
        }
    }
}

$zaloha = $striked;

foreach ($downlevel as $ORP) {
    $striked = $zaloha;

    $query163 = "SELECT up_level FROM orp WHERE code = '$ORP';";
    if ($result163 = mysqli_query($link, $query163)) {
        while ($row163 = mysqli_fetch_row($result163)) {
            $uplevel1 = $row163[0];
        }
    }

    $query170 = "SELECT code FROM orp WHERE up_level = '$ORP';";
    if ($result170 = mysqli_query($link, $query170)) {
        while ($row170 = mysqli_fetch_row($result170)) {
            $downlevel1[] = $row170[0];
        }
    }

    $nazev = $prvky[$ORP];

    $strikedlist = implode(",", $striked);
    $query252    = "SELECT incident_id FROM area WHERE header_id = '$old_header_id' AND ORP = '$ORP' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0;";
    if ($result252 = mysqli_query($link, $query252)) {
        $count252 = mysqli_num_rows($result252);
    }

    $query179 = "SELECT incident_id, prev, onset, severity FROM area WHERE header_id = '$header_id' AND ORP = '$ORP' ORDER BY onset, FIELD(severity, 'Extreme', 'Severe', 'Moderate');";
    if ($result179 = mysqli_query($link, $query179)) {
        $count179 = mysqli_num_rows($result179);
        $celkem   = $celkem + $count179;
        $count179 = $count179 + $count252;
        while ($row179 = mysqli_fetch_row($result179)) {
            $incident_id = $row179[0];
            $query184    = "SELECT id FROM area WHERE header_id = '$header_id' AND incident_id = '$incident_id' AND ORP = '$uplevel1';";
            if ($result184 = mysqli_query($link, $query184)) {
                $count184 = mysqli_num_rows($result184);
                $count179 = $count179 - $count184;
            }
        }
        if ($count179 > 0) {
            $vystup .= "</blockquote><blockquote><u><b>$nazev</b></u><br/>";
            $strikedlist = implode(",", $striked);
            $query277    = "SELECT incident_id, onset FROM area WHERE ORP = '$ORP' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0 ORDER BY onset;";
            if ($result277 = mysqli_query($link, $query277)) {
                while ($row277 = mysqli_fetch_row($result277)) {
                    $old = $row277[0];
                    $vystup .= Fcompare($old, 0, $ORP);
                    $pozice    = array_search($old, $abandoned);
                    $striked[] = $old;
                }
            }

            if ($result193 = mysqli_query($link, $query179)) {
                while ($row193 = mysqli_fetch_row($result193)) {
                    $incident_id = $row193[0];
                    $prev        = $row193[1];

                    $query210 = "SELECT id, prev FROM area WHERE header_id = '$header_id' AND incident_id = '$incident_id' AND ORP = '$uplevel1';";
                    if ($result210 = mysqli_query($link, $query210)) {
                        $count210 = mysqli_num_rows($result210);
                        if ($count210 == 0) {
                            $used[] = $incident_id;
                            $vystup .= Fcompare($prev, $incident_id, $ORP);
                        }
                    }
                }
            }
        }
    }

    $zaloha1 = $striked;
    foreach ($downlevel1 as $ORP) {
        $striked = $zaloha1;

        $query204 = "SELECT up_level FROM orp WHERE code = '$ORP';";
        if ($result204 = mysqli_query($link, $query204)) {
            while ($row204 = mysqli_fetch_row($result204)) {
                $uplevel2 = $row204[0];
            }
        }

        $query211 = "SELECT code FROM orp WHERE up_level = '$ORP';";
        if ($result211 = mysqli_query($link, $query211)) {
            while ($row211 = mysqli_fetch_row($result211)) {
                $downlevel2[] = $row211[0];
            }
        }

        $nazev = $prvky[$ORP];

        $strikedlist = implode(",", $striked);
        $query326    = "SELECT incident_id FROM area WHERE header_id = '$old_header_id' AND ORP = '$ORP' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0;";
        if ($result326 = mysqli_query($link, $query326)) {
            $count326 = mysqli_num_rows($result326);
        }

        $query214 = "SELECT incident_id, prev, onset, severity FROM area WHERE header_id = '$header_id' AND ORP = '$ORP' ORDER BY onset, FIELD(severity, 'Extreme', 'Severe', 'Moderate');";
        if ($result214 = mysqli_query($link, $query214)) {
            $count214 = mysqli_num_rows($result214);
            $celkem   = $celkem + $count214;
            $count214 = $count214 + $count326;
            while ($row214 = mysqli_fetch_row($result214)) {
                $incident_id = $row214[0];
                $query220    = "SELECT id FROM area WHERE header_id = '$header_id' AND incident_id = '$incident_id' AND ORP = '$uplevel2';";
                if ($result220 = mysqli_query($link, $query220)) {
                    $count220 = mysqli_num_rows($result220);
                    $count214 = $count214 - $count220;
                }
            }
            if ($count214 > 0) {
                $vystup .= "</blockquote><blockquote><u><b>$nazev</b></u><br/>";
                $strikedlist = implode(",", $striked);
                $query350    = "SELECT incident_id, onset FROM area WHERE ORP = '$ORP' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0 ORDER BY onset;";
                if ($result350 = mysqli_query($link, $query350)) {
                    while ($row350 = mysqli_fetch_row($result350)) {
                        $old = $row350[0];
                        $vystup .= Fcompare($old, 0, $ORP);
                        $striked[] = $old;
                    }
                }

                if ($result230 = mysqli_query($link, $query214)) {
                    while ($row230 = mysqli_fetch_row($result230)) {
                        $incident_id = $row230[0];
                        $prev        = $row230[1];

                        $query257 = "SELECT id,prev FROM area WHERE header_id = '$header_id' AND incident_id = '$incident_id' AND ORP = '$uplevel2';";
                        if ($result257 = mysqli_query($link, $query257)) {
                            $count257 = mysqli_num_rows($result257);
                            if ($count257 == 0) {
                                $used[] = $incident_id;
                                $vystup .= Fcompare($prev, $incident_id, $ORP);
                            }
                        }
                    }
                }
            }
        }

        $zaloha2 = $striked;
        foreach ($downlevel2 as $ORP) {
            $striked = $zaloha2;

            $query234 = "SELECT up_level FROM orp WHERE code = '$ORP';";
            if ($result234 = mysqli_query($link, $query234)) {
                while ($row234 = mysqli_fetch_row($result234)) {
                    $uplevel3 = $row234[0];
                }
            }

            $nazev = $prvky[$ORP];

            $strikedlist = implode(",", $striked);
            $query392    = "SELECT incident_id FROM area WHERE header_id = '$old_header_id' AND ORP = '$ORP' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0;";
            if ($result392 = mysqli_query($link, $query392)) {
                $count392 = mysqli_num_rows($result392);
            }

            $query250 = "SELECT incident_id, prev, onset, severity FROM area WHERE header_id = '$header_id' AND ORP = '$ORP' ORDER BY onset, FIELD(severity, 'Extreme', 'Severe', 'Moderate');";
            if ($result250 = mysqli_query($link, $query250)) {
                $count250 = mysqli_num_rows($result250);
                $celkem   = $celkem + $count250;
                $count250 = $count250 + $count392;
                while ($row250 = mysqli_fetch_row($result250)) {
                    $incident_id = $row250[0];
                    $query256    = "SELECT id FROM area WHERE header_id = '$header_id' AND incident_id = '$incident_id' AND ORP = '$uplevel3';";
                    if ($result256 = mysqli_query($link, $query256)) {
                        $count256 = mysqli_num_rows($result256);
                        $count250 = $count250 - $count256;
                    }
                }
                if ($count250 > 0) {
                    $vystup .= "</blockquote><blockquote><u><b>$nazev</b></u><br/>";
                    $strikedlist = implode(",", $striked);
                    $query409    = "SELECT incident_id, onset FROM area WHERE ORP = '$ORP' AND incident_id IN ($abandonlist) AND incident_id NOT IN ($strikedlist) AND next = 0 ORDER BY onset;";
                    if ($result409 = mysqli_query($link, $query409)) {
                        while ($row409 = mysqli_fetch_row($result409)) {
                            $old = $row409[0];
                            $vystup .= Fcompare($old, 0, $ORP);
                            $striked[] = $old;
                        }
                    }

                    if ($result275 = mysqli_query($link, $query250)) {
                        while ($row275 = mysqli_fetch_row($result275)) {
                            $incident_id = $row275[0];
                            $prev        = $row275[1];

                            $query297 = "SELECT id, prev FROM area WHERE header_id = '$header_id' AND incident_id = '$incident_id' AND ORP = '$uplevel3';";
                            if ($result297 = mysqli_query($link, $query297)) {
                                $count297 = mysqli_num_rows($result297);
                                if ($count297 == 0) {
                                    $used[] = $incident_id;
                                    $vystup .= Fcompare($prev, $incident_id, $ORP);
                                }
                            }
                        }
                    }
                }
            }
        }
        unset($downlevel2);
    }
    unset($downlevel1);
}
$vystup .= "</table></blockquote>";

if ($used) {
    sort($used);
    $used = array_unique($used);
}

$query55 = "SELECT * FROM header WHERE id = '$header_id';";
if ($result55 = mysqli_query($link, $query55)) {
    while ($row55 = mysqli_fetch_row($result55)) {
        $identifier     = $row55[1];
        $sent           = $row55[2];
        $status         = $row55[3];
        $msgType        = $row55[4];
        $useSIVS        = $row55[5];
        $useHPPS        = $row55[6];
        $useSVRS        = $row55[7];
        $note           = $row55[8];
        $references     = $row55[9];
        $incidents      = $row55[10];
        $poradove_cislo = substr($identifier, -6);
        $typ_zpravy     = substr($identifier, 31, 6);

        switch ($status) {
            case "Exercise":
            case "System":
            case "Test":
                $header = "ÚČELOVÁ INFORMACE ČHMÚ – TESTOVACÍ ZPRÁVA";
                if ($useSVRS == "1" && $useSIVS == "0" && $useHPPS == "0") {
                    $header .= "<br/>SMOGOVÝ VAROVNÝ A REGULAČNÍ SYSTÉM";
                }
                if ($useSIVS == "1" && $useHPPS == "0") {
                    $header .= "<br/>SYSTÉM INTEGROVANÉ VÝSTRAŽNÉ SLUŽBY";
                }
                if ($useHPPS == "1") {
                    $header .= "<br/>PŘEDPOVĚDNÍ POVODŇOVÁ SLUŽBA ČHMÚ";
                }
                break;
            case "Actual":
            default:
                $header = "VÝSTRAHA ČHMÚ";
                if ($useSVRS == "1" && $useSIVS == "0" && $useHPPS == "0") {
                    $header = "ZPRÁVA SMOGOVÉHO VAROVNÉHO A REGULAČNÍHO SYSTÉMU";
                }
                if ($useSIVS == "1" && $useHPPS == "0") {
                    $header .= "<br/>SYSTÉM INTEGROVANÉ VÝSTRAŽNÉ SLUŽBY";
                }
                if ($useHPPS == "1") {
                    $header .= "<br/>VÝSTRAHA PŘEDPOVĚDNÍ POVODŇOVÉ SLUŽBY ČHMÚ";
                }
                if ($useSVRS == "0" && $useSIVS == "0" && $useHPPS == "0") {
                    $header = "INFORMAČNÍ ZPRÁVA ČHMÚ";
                }
                break;
        }

        echo "<div class=\"header\">$header</div>";
        echo "<br/>";
        echo "Zpráva č. " . $poradove_cislo . "<br/>";
        echo "Odesláno: " . date("d.m.Y H:i:s", $sent) . "<br/>";

        $previous                = explode(",", $references);
        $previous_ID             = $previous[1];
        $previous_poradove_cislo = substr($previous_ID, -6);
        $previous_timestamp      = strtotime($previous[2]);
        $previous_date           = date("d.m.Y", $previous_timestamp);
        $previous_time           = date("H:i:s", $previous_timestamp);

        switch ($msgType) {
            case "Alert":
                break;
            case "Update":
                echo "Zpráva aktualizuje předchozí zprávu č. $previous_poradove_cislo vydanou $previous_date v $previous_time hodin<br/>";
                break;
            case "Cancel":
                echo "Zpráva ruší platnost předchozí zprávy č. $previous_poradove_cislo vydané $previous_date v $previous_time hodin<br/>";
                break;
            default:
                echo "Zpráva obsahuje nestandardní informace, které NEJSOU výstražnými informacemi. Více informací může být uvedeno v poznámce.<br/>";
                break;
        }

        if ($note) {echo "Poznámka: " . $note . "<br/>";}
    }
}

echo "</span><hr>";

if ($celkem == 0) {echo "Na zvoleném území „$prvky[$initORP]“ nejsou vyhlášeny meteorologické výstrahy.";}

$query144 = "SELECT DISTINCT situation FROM incidents WHERE header_id = '$header_id';";
if ($result144 = mysqli_query($link, $query144)) {
    while ($row144 = mysqli_fetch_row($result144)) {
        $situace[] = $row144[0];
    }
}

if ($situace) {
    $situace = array_filter($situace);
    echo "<p></p><b>Meteorologická situace:</b> $situace[0]<br/>";
}

echo $vystup;

echo "<hr>";

unset($distribuce);

$query560 = "SELECT kraj FROM orp WHERE code IN (SELECT ORP FROM area WHERE header_id = '$header_id' OR header_id = '$old_header_id');";
if ($result560 = mysqli_query($link, $query560)) {
    while ($row560 = mysqli_fetch_row($result560)) {
        $distribuce[] = $row560[0];
    }
}

if ($distribuce) {
    $distribuce = array_unique($distribuce);
    $distribuce = array_filter($distribuce);

    $krajedistr = "";

    echo "Distribuce: ";
    foreach ($distribuce as $distrkraj) {
        $krajedistr .= "$KRAJE_KODY[$distrkraj], ";
    }

    echo substr($krajedistr, 0, -2);
}

mysqli_close($link);
