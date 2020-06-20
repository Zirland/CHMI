<?php
include 'header.php';

$query4 = "SELECT header FROM pair LIMIT 1;";
if ($result4 = mysqli_query($link, $query4)) {
    while ($row4 = mysqli_fetch_row($result4)) {
        $header_id = $row4[0];
    }
}

echo "$header_id<br/>";

$query259 = "SELECT header.references FROM header WHERE id = '$header_id';";
if ($result259 = mysqli_query($link, $query259)) {
    while ($row259 = mysqli_fetch_row($result259)) {
        $references = $row259[0];
        $vazby      = explode(",", $references);
        $old_id     = $vazby[1];
        $query54    = "SELECT id FROM header WHERE identifier = '$old_id';";
        if ($result54 = mysqli_query($link, $query54)) {
            while ($row54 = mysqli_fetch_row($result54)) {
                $old_header_id = $row54[0];
            }
        }
    }
}

unset($new_ids);
unset($old_ids);
unset($pairs);
unset($codes);
unset($investigate);

$query5 = "SELECT code FROM orp;";
if ($result5 = mysqli_query($link, $query5)) {
    while ($row5 = mysqli_fetch_row($result5)) {
        $codes[] = $row5[0];
    }
}

echo "<table><tr><td width=\"50%\">";
$query10 = "SELECT id, onset FROM incidents WHERE header_id = '$old_header_id' ORDER BY onset;";
if ($result10 = mysqli_query($link, $query10)) {
    while ($row10 = mysqli_fetch_row($result10)) {
        $old_incident_id = $row10[0];
        $old_ids[]       = $row10[0];

        echo output($old_incident_id, 1);
    }
}
$staryseznam = implode(",", $old_ids);
echo "$staryseznam<br/>";

echo "</td><td width=\"50%\">";
$query35 = "SELECT id, onset FROM incidents WHERE header_id = '$header_id' ORDER BY onset;";
if ($result35 = mysqli_query($link, $query35)) {
    while ($row35 = mysqli_fetch_row($result35)) {
        $incident_id = $row35[0];
        $new_ids[]   = $row35[0];

        echo output($incident_id, 1);
    }
}
$novyseznam = implode(",", $new_ids);
echo "$novyseznam<br/>";

echo "</td></tr></table>";

foreach ($codes as $ORP) {
    unset($noveORP);
    unset($stareORP);

    $query60 = "SELECT incident_id FROM area WHERE incident_id IN ($novyseznam) AND ORP = '$ORP';";
    if ($result60 = mysqli_query($link, $query60)) {
        while ($row60 = mysqli_fetch_row($result60)) {
            $noveORP[] = $row60[0];
        }
    }

    $query63 = "SELECT incident_id FROM area WHERE incident_id IN ($staryseznam) AND ORP = '$ORP';";
    if ($result63 = mysqli_query($link, $query63)) {
        while ($row63 = mysqli_fetch_row($result63)) {
            $stareORP[] = $row63[0];
        }
    }

    if ($stareORP && $noveORP) {
        $investigate[] = $ORP;
    }
}

foreach ($investigate as $ORP) {
    unset($noveORP);
    unset($stareORP);
    $stareORP[] = 0;

    $query60 = "SELECT incident_id FROM area WHERE incident_id IN ($novyseznam) AND ORP = '$ORP';";
    if ($result60 = mysqli_query($link, $query60)) {
        while ($row60 = mysqli_fetch_row($result60)) {
            $noveORP[] = $row60[0];
        }
    }

    $query63 = "SELECT incident_id FROM area WHERE incident_id IN ($staryseznam) AND ORP = '$ORP';";
    if ($result63 = mysqli_query($link, $query63)) {
        while ($row63 = mysqli_fetch_row($result63)) {
            $stareORP[] = $row63[0];
        }
    }

    foreach ($noveORP as $nove) {
        $old_incident_id = 0;
        $query48         = "SELECT * FROM incidents WHERE id = '$nove';";
        if ($result48 = mysqli_query($link, $query48)) {
            while ($row48 = mysqli_fetch_row($result48)) {
                $urgency  = $row48[6];
                $onset    = $row48[9];
                $expires  = $row48[10];
                $codeSIVS = $row48[11];
                $codeHPPS = $row48[12];
                $codeSVRS = $row48[13];

                $code = $codeSVRS;
                if ($codeSIVS != "0") {$code = $codeSIVS;}
                if ($codeHPPS != "0") {$code = $codeHPPS;}

                if ($urgency == "Immediate") {$onset1 = 0;}
            }
        }

        $free = implode(",", $stareORP);

        $cat_rozpad = explode(".", $code);
        $cat        = $cat_rozpad[0];
        $value      = $cat_rozpad[1];

        $stop = 0;

        $query64 = "SELECT id, onset, expires, urgency FROM incidents WHERE header_id = '$old_header_id' AND (codeSIVS = '$code' OR codeHPPS = '$code' OR codeSVRS = '$code') AND id IN ($free);";
        if ($result64 = mysqli_query($link, $query64)) {
            $pocet64 = mysqli_num_rows($result64);
            while ($row64 = mysqli_fetch_row($result64)) {
                $old_inc_id = $row64[0];
                $oldonset   = $row64[1];
                $oldexpires = $row64[2];
                $oldurgency = $row64[3];
                if ($oldurgency == "Immediate" && $onset < $oldexpires) {$oldonset1 = 0;}

                if ($oldonset1 == $onset1 && $oldexpires == $expires) {
                    $old_incident_id = $old_inc_id;
                    $stop            = 1;
                    $pozice          = array_search($old_incident_id, $stareORP);
                    if ($pozice) {
                        unset($stareORP[$pozice]);
                    }
                }
            }
        }

        if ($stop == 0 && $result101 = mysqli_query($link, $query64)) {
            while ($row101 = mysqli_fetch_row($result101)) {
                $old_inc_id = $row101[0];
                $oldonset   = $row101[1];
                $oldurgency = $row101[3];
                if ($oldurgency == "Immediate") {$oldonset1 = 0;}

                if ($oldonset1 == $onset1) {
                    $old_incident_id = $old_inc_id;
                    $stop            = 1;
                    $pozice          = array_search($old_incident_id, $stareORP);
                    if ($pozice) {
                        unset($stareORP[$pozice]);
                    }
                }
            }
        }

        if ($stop == 0 && $result119 = mysqli_query($link, $query64)) {
            while ($row120 = mysqli_fetch_row($result64)) {
                $old_inc_id = $row120[0];
                $oldexpires = $row120[2];

                if ($oldexpires == $expires) {
                    $old_incident_id = $old_inc_id;
                    $pozice          = array_search($old_incident_id, $stareORP);
                    if ($pozice) {
                        unset($stareORP[$pozice]);
                    }
                }
            }
        }

        $stop = 0;

        $query64 = "SELECT id, onset, expires, urgency FROM incidents WHERE header_id = '$old_header_id' AND (codeSIVS LIKE '$cat.%' OR codeHPPS LIKE '$cat.%' OR codeSVRS LIKE '%.$value') AND id IN ($free);";
        if ($result64 = mysqli_query($link, $query64)) {
            $pocet64 = mysqli_num_rows($result64);
            while ($row64 = mysqli_fetch_row($result64)) {
                $old_inc_id = $row64[0];
                $oldonset   = $row64[1];
                $oldexpires = $row64[2];
                $oldurgency = $row64[3];
                if ($oldurgency == "Immediate" && $onset < $oldexpires) {$oldonset1 = 0;}

                if ($oldonset1 == $onset1 && $oldexpires == $expires) {
                    $old_incident_id = $old_inc_id;
                    $stop            = 1;
                    $pozice          = array_search($old_incident_id, $stareORP);
                    if ($pozice) {
                        unset($stareORP[$pozice]);
                    }
                }
            }
        }

        if ($stop == 0 && $result101 = mysqli_query($link, $query64)) {
            while ($row101 = mysqli_fetch_row($result101)) {
                $old_inc_id = $row101[0];
                $oldonset   = $row101[1];
                $oldurgency = $row101[3];
                if ($oldurgency == "Immediate") {$oldonset1 = 0;}

                if ($oldonset1 == $onset1) {
                    $old_incident_id = $old_inc_id;
                    $stop            = 1;
                    $pozice          = array_search($old_incident_id, $stareORP);
                    if ($pozice) {
                        unset($stareORP[$pozice]);
                    }
                }
            }
        }

        if ($stop == 0 && $result119 = mysqli_query($link, $query64)) {
            while ($row120 = mysqli_fetch_row($result64)) {
                $old_inc_id = $row120[0];
                $oldexpires = $row120[2];

                if ($oldexpires == $expires) {
                    $old_incident_id = $old_inc_id;
                    $pozice          = array_search($old_incident_id, $stareORP);
                    if ($pozice) {
                        unset($stareORP[$pozice]);
                    }
                }
            }
        }
        if ($oldexpires > $onset || $oldexpires == "0") {
            $pairs[] = "$ORP|$old_incident_id|$nove";
        }
    }
}

foreach ($pairs as $trojice) {
    $rozpad = explode("|", $trojice);
    $ORP    = $rozpad[0];
    $old    = $rozpad[1];
    $new    = $rozpad[2];

    $query210  = "UPDATE area SET prev = '$old' WHERE incident_id = '$new' AND ORP = '$ORP';";
    $prikaz210 = mysqli_query($link, $query210);

    $query213  = "UPDATE area SET next = '$new' WHERE incident_id = '$old' AND ORP = '$ORP';";
    $prikaz213 = mysqli_query($link, $query213);
}

$query207  = "DELETE FROM pair WHERE header = '$header_id';";
$prikaz207 = mysqli_query($link, $query207);

mysqli_close($link);
echo "<meta http-equiv=\"refresh\" content=\"5\">";
