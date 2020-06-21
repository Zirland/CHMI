<?php
date_default_timezone_set('Europe/Prague');
require_once 'dbconnect.php';

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$link) {
    echo "Error: Unable to connect to database." . PHP_EOL;
    echo "Reason: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

echo "<table><tr><th>ID</th><th>Identifier</th><th>Sent</th><th>Status</th><th>MsgType</th><th>SIVS</th><th>HPPS</th><th>SVRS</th><th>Note</th><th>References</th><th>Incidents</th><th>Detail</th></tr>";
$query11 = "SELECT * FROM header ORDER BY sent;";
if ($result11 = mysqli_query($link, $query11)) {
    while ($row11 = mysqli_fetch_row($result11)) {
        $id         = $row11[0];
        $identifier = $row11[1];
        $sent       = $row11[2];
        $status     = $row11[3];
        $msgType    = $row11[4];
        $useSIVS    = $row11[5];
        $useHPPS    = $row11[6];
        $useSVRS    = $row11[7];
        $note       = $row11[8];
        $references = $row11[9];
        $incidents  = $row11[10];

        $ID     = substr($identifier, -3);
        $rozpad = explode(".", $identifier);
        $typ    = $rozpad[8];
        $typ    = substr($typ, 0, 6);

        $identifier = "$typ $ID";
        $ref        = explode(",", $references);
        $references = $ref[1];
        $references = substr($references, -3);
        $datum      = date("d.m.Y H:i", $sent);

        echo "<tr><td>$id</td><td>$identifier</td><td>$datum</td><td>$status</td><td>$msgType</td><td>$useSIVS</td><td>$useHPPS</td><td>$useSVRS</td><td>$note</td><td>$references</td><td>$incidents</td><td><a href=\"report.php?header=$id\" target=\"_blank\">Detail</a></td></tr>";
    }
}
echo "</table>";

mysqli_close($link);
