<?php
function Fcompare($incident1, $incident2, $ORP)
{
    global $link, $nalehavost, $jistota, $barvy, $kod_barvy, $sent, $maxdoba, $obrazky, $obrazky2;
    $out    = "";
    $rozsah = date("(d)H:i", $maxdoba);

    $query7 = "SELECT ceiling, altitude FROM area WHERE incident_id = '$incident1' AND ORP = '$ORP';";
    if ($result7 = mysqli_query($link, $query7)) {
        while ($row7 = mysqli_fetch_row($result7)) {
            $ceiling1  = $row7[0];
            $altitude1 = $row7[1];
        }
    }

    if ($ceiling1 > "0") {
        $ceiling1 = round($ceiling1 * 0.3048);
        $ceiling1 = "Platnost jevu do $ceiling1 m n.m.";
    } else {
        $ceiling1 = "";
    }
    if ($altitude1 > "0") {
        $altitude1 = round($altitude1 * 0.3048);
        $altitude1 = "Platnost jevu nad $altitude1 m n.m.";
    } else {
        $altitude1 = "";
    }

    $query15 = "SELECT ceiling, altitude FROM area WHERE incident_id = '$incident2' AND ORP = '$ORP';";
    if ($result15 = mysqli_query($link, $query15)) {
        while ($row15 = mysqli_fetch_row($result15)) {
            $ceiling2  = $row15[0];
            $altitude2 = $row15[1];
        }
    }

    if ($ceiling2 > "0") {
        $ceiling2 = round($ceiling2 * 0.3048);
        $ceiling2 = "Platnost jevu do $ceiling2 m n.m.";
    } else {
        $ceiling2 = "";
    }
    if ($altitude2 > "0") {
        $altitude2 = round($altitude2 * 0.3048);
        $altitude2 = "Platnost jevu nad $altitude2 m n.m.";
    } else {
        $altitude2 = "";
    }

    $query6 = "SELECT * FROM incidents WHERE id = '$incident1';";
    if ($result6 = mysqli_query($link, $query6)) {
        while ($row6 = mysqli_fetch_row($result6)) {
            $event1        = $row6[4];
            $urgency1      = $row6[6];
            $severity1     = $row6[7];
            $certainty1    = $row6[8];
            $onset1        = $row6[9];
            $expires1      = $row6[10];
            $codeSIVS1     = $row6[11];
            $codeHPPS1     = $row6[12];
            $codeSVRS1     = $row6[13];
            $description1  = $row6[15];
            $instruction1  = $row6[16];
            $situation1    = $row6[18];
            $hydroOutlook1 = $row6[22];

            $code1 = $codeSVRS1;
            if ($codeSIVS1 != "0") {
                $code1 = $codeSIVS1;
            }
            if ($codeHPPS1 != "0") {
                $code1 = $codeHPPS1;
            }

            if ($certainty1 == "Observed") {
                $description1 = "VÝSKYT JEVU – " . $description1;
            }

            $kod_severity1 = $kod_barvy[$severity1];
            $zahajeni1     = date("(d)H:i", $onset1);
            $ukonceni1     = date("(d)H:i", $expires1);
        }
    }

    if ($incident1 == "0") {
        $event1        = "";
        $urgency1      = "";
        $severity1     = "";
        $certainty1    = "";
        $onset1        = "";
        $expires1      = "";
        $codeSIVS1     = "";
        $codeHPPS1     = "";
        $codeSVRS1     = "";
        $description1  = "";
        $instruction1  = "";
        $situation1    = "";
        $hydroOutlook1 = "";
        $ceiling1      = "";
        $altitude1     = "";
    }

    if ($expires1 < $onset1) {
        $ukonceni1 = "......";
        $expires1  = $maxdoba;
    }

    $query71 = "SELECT * FROM incidents WHERE id = '$incident2';";
    if ($result71 = mysqli_query($link, $query71)) {
        while ($row71 = mysqli_fetch_row($result71)) {
            $event2        = $row71[4];
            $urgency2      = $row71[6];
            $severity2     = $row71[7];
            $certainty2    = $row71[8];
            $onset2        = $row71[9];
            $expires2      = $row71[10];
            $codeSIVS2     = $row71[11];
            $codeHPPS2     = $row71[12];
            $codeSVRS2     = $row71[13];
            $description2  = $row71[15];
            $instruction2  = $row71[16];
            $situation2    = $row71[18];
            $hydroOutlook2 = $row71[22];

            $code2 = $codeSVRS2;
            if ($codeSVRS2 != "0") {
                $prikaz114 = mysqli_query($link, "INSERT INTO temp (code) VALUES ('SVRS');");
            }
            if ($codeSIVS2 != "0") {
                $code2     = $codeSIVS2;
                $prikaz118 = mysqli_query($link, "INSERT INTO temp (code) VALUES ('SIVS');");
            }
            if ($codeHPPS2 != "0") {
                $code2     = $codeHPPS2;
                $prikaz122 = mysqli_query($link, "INSERT INTO temp (code) VALUES ('HPPS');");
            }

            if ($certainty2 == "Observed") {
                $description2 = "VÝSKYT JEVU – " . $description2;
            }

            $kod_severity2 = $kod_barvy[$severity2];
            $zahajeni2     = date("(d)H:i", $onset2);
            $ukonceni2     = date("(d)H:i", $expires2);
        }
    }

    if ($incident2 == "0") {
        $event2        = "";
        $urgency2      = "";
        $severity2     = "";
        $certainty2    = "";
        $onset2        = "";
        $expires2      = "";
        $codeSIVS2     = "";
        $codeHPPS2     = "";
        $codeSVRS2     = "";
        $description2  = "";
        $instruction2  = "";
        $situation2    = "";
        $hydroOutlook2 = "";
        $ceiling2      = "";
        $altitude2     = "";
    }

    if ($expires2 < $onset2) {
        $ukonceni2 = "......";
        $expires2  = $maxdoba;
    }

//    FineDiff::$paragraphGranularity | FineDiff::$sentenceGranularity | FineDiff::$wordGranularity | FineDiff::$characterGranularity (default)
    $different = $different_onset = $different_expire = $different_severity = 0;
    if ($severity1 != $severity2) {
        $different          = 1;
        $different_severity = 1;
    }
    if ($onset1 != $onset2 && ($urgency1 != "Immediate" || $urgency2 != "Immediate")) {
        $different       = 1;
        $different_onset = 1;
    }
    if ($expires1 != $expires2) {
        $different         = 1;
        $different_expires = 1;
    }
    if ($description1 != $description2) {
        $different    = 1;
        $opcodes      = FineDiff::getDiffOpcodes($description1, $description2, FineDiff::$wordGranularity);
        $too_text     = FineDiff::renderDiffToHTMLFromOpcodes($description1, $opcodes);
        $description2 = $too_text;
    }
    if ($instruction1 != $instruction2) {
        $opcodes      = FineDiff::getDiffOpcodes($instruction1, $instruction2, FineDiff::$wordGranularity);
        $too_text     = FineDiff::renderDiffToHTMLFromOpcodes($instruction1, $opcodes);
        $instruction2 = $too_text;
    }
    if ($situation1 != $situation2) {
        $opcodes    = FineDiff::getDiffOpcodes($situation1, $situation2, FineDiff::$wordGranularity);
        $too_text   = FineDiff::renderDiffToHTMLFromOpcodes($situation1, $opcodes);
        $situation2 = $too_text;
    }
    if ($hydroOutlook1 != $hydroOutlook2) {
        $different     = 1;
        $opcodes       = FineDiff::getDiffOpcodes($hydroOutlook1, $hydroOutlook2, FineDiff::$wordGranularity);
        $too_text      = FineDiff::renderDiffToHTMLFromOpcodes($hydroOutlook1, $opcodes);
        $hydroOutlook2 = $too_text . "<br/>";
    }
    if ($altitude1 != $altitude2) {
        $different = 1;
        $opcodes   = FineDiff::getDiffOpcodes($altitude1, $altitude2, FineDiff::$wordGranularity);
        $too_text  = FineDiff::renderDiffToHTMLFromOpcodes($altitude1, $opcodes);
        $altitude2 = $too_text . "<br/>";
    }
    if ($ceiling1 != $ceiling2) {
        $different = 1;
        $opcodes   = FineDiff::getDiffOpcodes($ceiling1, $ceiling2, FineDiff::$wordGranularity);
        $too_text  = FineDiff::renderDiffToHTMLFromOpcodes($ceiling1, $opcodes);
        $ceiling2  = $too_text . "<br/>";
    }
    if ($code2 == '') {
        $code2 = $code1;
    }

    $pozadi  = $barvy[$severity2];
    $zaslani = date("(d)H:i", $sent);
    $trvani  = $maxdoba - $sent;
    if ($onset2 != "" && $expires2 != "") {
        $start  = $onset2 - $sent;
        $end    = $expires2 - $onset2;
        $dojezd = $maxdoba - $expires2;
    } else {
        $start  = $onset1 - $sent;
        $end    = $expires1 - $onset1;
        $dojezd = $maxdoba - $expires1;
    }

    $before = round(($start / $trvani) * 100);
    $jazda  = round(($end / $trvani) * 100);
    $after  = round(($dojezd / $trvani) * 100);

    $code2   = str_replace("<ins>", "", $code2);
    $code2   = str_replace("</ins>", "", $code2);
    $code2   = str_replace("<del>", "", $code2);
    $code2   = str_replace("</del>", "", $code2);
    $codeArr = explode(".", $code2);
    $cat     = $codeArr[0];

    $image = $obrazky[$code2];

//    if ($different == 0) {return $out;}

    $out .= "<div><table class=\"tg\" width=\"100%\">";
    $out .= "<tr><td width=\"30\" rowspan=\"2\" style=\"background-color: $pozadi;\"><img src=\"svg/" . $image . ".svg\" height = \"30\"></td><td width=\"50\" style=\"text-align: center;\"";
    if ($different_severity == 0 || $kod_severity1 == "" || $kod_severity2 == "") {
        $out .= " rowspan=\"2\"";
    }

    if ($different_severity == 1 && $kod_severity1 != "") {
        $out .= "><del>$kod_severity1</del>";
    }

    if ($different_severity == 0) {
        $out .= ">$kod_severity2";
    }

    if ($different_severity == 1 && $kod_severity1 == "") {
        $out .= "><ins>$kod_severity2</ins>";
    }

    $out .= "</td><td rowspan=\"2\"><table class=\"no\" width=\"100%\"><tr><td width=\"$before%\" rowspan=\"2\"></td><td width=\"55\" style=\"text-align: center;\"";

    if ($different_onset == 0 || $zahajeni1 == "" || $zahajeni2 == "") {
        $out .= " rowspan=\"2\"";
    }

    if ($different_onset == 1 && $zahajeni1 != "") {
        $out .= "><del>$zahajeni1</del>";
    }

    if ($different_onset == 0) {
        $out .= ">$zahajeni2";
    }

    if ($different_onset == 1 && $zahajeni1 == "") {
        $out .= "><ins>$zahajeni2</ins>";
    }
    $out .= "</td><td width=\"$jazda%\" rowspan=\"2\"><img src=\"$pozadi.png\" height=\"20\" width=\"100%\"></td><td width=\"55\" style=\"text-align: center;\"";

    if ($different_expires == 0 || $ukonceni1 == "" || $ukonceni2 == "") {
        $out .= " rowspan=\"2\"";
    }

    if ($different_expires == 1 && $ukonceni1 != "") {
        $out .= "><del>$ukonceni1</del>";
    }

    if ($different_expires == 0) {
        $out .= ">$ukonceni2";
    }

    if ($different_expires == 1 && $ukonceni1 == "") {
        $out .= "><ins>$ukonceni2</ins>";
    }
    $out .= "</td><td width=\"$after%\" rowspan=\"2\"></td></tr>";

    $out .= "<tr>";
    if ($different_onset == 1 && $zahajeni1 != "" && $zahajeni2 != "") {
        $out .= "<td width=\"50\" style=\"text-align: center;\"><ins>$zahajeni2</ins></td>";
    }
    if ($different_expires == 1 && $ukonceni1 != "" && $ukonceni2 != "") {
        $out .= "<td width=\"50\" style=\"text-align: center;\"><ins>$ukonceni2</ins></td>";
    }
    $out .= "</tr></table></td></tr>";
    $out .= "<tr>";
    if ($different_severity == 1 && $kod_severity1 != "" && $kod_severity2 != "") {
        $out .= "<td width=\"50\" style=\"text-align: center;\"><ins>$kod_severity2</ins></td>";
    }
    $out .= "</tr>";
    $out .= "<tr><td colspan=\"3\"><b>Popis</b>: $description2 <i>$ceiling2 $altitude2</i></td></tr>";
    if ($hydroOutlook2) {
        $out .= "<tr><td colspan=\"3\"><b>Hydrologická informační zpráva:</b> $hydroOutlook2</td></tr>";
    }
    $out .= "<tr><td colspan=\"3\"><b>Doporučení:</b> $instruction2</td></tr>";
    $out .= "</table></div>";
    return $out;
}

function output($incident_no, $ORP)
{
    global $link, $nalehavost, $jistota, $barvy, $kod_barvy, $sent, $maxdoba, $obrazky, $obrazky2;
    $out    = $vyska    = "";
    $rozsah = date("(d)H:i", $maxdoba);

    $query329 = "SELECT ceiling, altitude FROM area WHERE incident_id = '$incident_no' AND ORP = '$ORP';";
    if ($result329 = mysqli_query($link, $query329)) {
        while ($row329 = mysqli_fetch_row($result329)) {
            $ceiling  = $row329[0];
            $altitude = $row329[1];
        }
    }

    if ($ceiling > 0) {
        $ceiling = round($ceiling * 0.3048);
        $vyska   = " | Platnost jevu do $ceiling m n.m.";
    }
    if ($altitude > 0) {
        $altitude = round($altitude * 0.3048);
        $vyska    = " | Platnost jevu do $altitude m n.m.";
    }

    $query26 = "SELECT * FROM incidents WHERE id = '$incident_no';";
    if ($result26 = mysqli_query($link, $query26)) {
        while ($row26 = mysqli_fetch_row($result26)) {
            $event        = $row26[4];
            $urgency      = $row26[6];
            $severity     = $row26[7];
            $certainty    = $row26[8];
            $onset        = $row26[9];
            $expires      = $row26[10];
            $codeSIVS     = $row26[11];
            $codeHPPS     = $row26[12];
            $codeSVRS     = $row26[13];
            $description  = $row26[15];
            $instruction  = $row26[16];
            $situation    = $row26[18];
            $hydroOutlook = $row26[22];

            $code = $codeSVRS;
            if ($codeSIVS != "0") {$code = $codeSIVS;}
            if ($codeHPPS != "0") {$code = $codeHPPS;}

            $kod_severity = $kod_barvy[$severity];

            $pozadi   = $barvy[$severity];
            $zaslani  = date("(d)H:i", $sent);
            $zahajeni = date("(d)H:i", $onset);
            $ukonceni = date("(d)H:i", $expires);

            if ($expires < $onset) {
                $ukonceni = "... ...";
                $expires  = $maxdoba;
            }
            $caszah = explode(" ", $zahajeni);
            $casuko = explode(" ", $ukonceni);

            $trvani = $maxdoba - $sent;
            $start  = $onset - $sent;
            $end    = $expires - $onset;
            $dojezd = $maxdoba - $expires;

            $before = floor(($start / $trvani) * 100);
            $gap    = ceil((30 / $trvani) * 100);

            $jazda = round(($end / $trvani) * 100) - $gap;
            $after = floor(($dojezd / $trvani) * 100);

            $codeArr = explode(".", $code);
            $cat     = $codeArr[0];

            $image = $obrazky[$code2];

            $out .= "<table class=\"tg\" width=\"100%\">";
            $out .= "<tr><td width=\"30\" style=\"background-color: $pozadi;\"><img src=\"svg/" . $image . ".svg\"></td><td width=\"50\" style=\"text-align: center;\">$kod_severity</td><td><table class=\"no\" width=\"100%\"><tr><td width=\"$before%\"></td><td width=\"30\" style=\"text-align: right;\">$caszah[0]<br/>$caszah[1]</td><td width=\"$jazda%\"><img src=\"$pozadi.png\" height=\"20\" width=\"100%\"></td><td width=\"30\" style=\"text-align: left;\">$casuko[0]<br/>$casuko[1]</td><td width=\"$after%\"></td></tr></table></td></tr>";
            $out .= "<tr><td colspan=\"3\">$incident_no: <b>Popis</b>: ";
            if ($certainty == "Observed") {$out .= "VÝSKYT JEVU – ";}
            $out .= "$description";
            $out .= $vyska;
            $out .= "</td></tr>";
            if ($hydroOutlook) {
                $out .= "<tr><td colspan=\"3\"><b>Hydrologická informační zpráva:</b> $hydroOutlook</td></tr>";
            }
            $out .= "</table>";

            return $out;
        }
    }
}
