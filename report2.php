<?php
$title = "Výstražná informace";
include 'header.php';

$header_id = $_GET["header"];

$vystup  = "";
$useHPPS = $useSIVS = $useSVRS = 0;

$cisti16 = mysqli_query($link, "TRUNCATE TABLE temp;");

$query192 = "SELECT MAX(expires) FROM incidents WHERE header_id = '$header_id';";
if ($result192 = mysqli_query($link, $query192)) {
    while ($row192 = mysqli_fetch_row($result192)) {
        $maxdoba = $row192[0];
        if (!$maxdoba) {$maxdoba = strtotime(date("d.m.Y H:i:s", $sent) . "+ 3 days");}
    }
}

$celkem  = 0;
$query22 = "SELECT id FROM incidents WHERE header_id = '$header_id';";
if ($result22 = mysqli_query($link, $query22)) {
    $celkem = $celkem + mysqli_num_rows($result22);
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

if ($celkem == 0) {
    echo "Na zvoleném území „Česká republika“ nejsou vyhlášeny meteorologické výstrahy.";
}

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

$query22 = "SELECT id FROM incidents WHERE header_id = '$header_id' ORDER BY onset, FIELD(severity, 'Extreme', 'Severe', 'Moderate');";
if ($result22 = mysqli_query($link, $query22)) {
    while ($row22 = mysqli_fetch_row($result22)) {
        $id = $row22[0];

        $vystup .= output2($id);
    }
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
