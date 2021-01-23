<!DOCTYPE html>
<HTML>
<HEAD>
<META charset="utf-8"/>
<TITLE><?php echo $title; ?></TITLE>
<style type="text/css">
    body {
		font-family:serif;
		font-size:13px;
	}
    .header {
		font-size:15px;
		text-align:center;
	}
    .tg {
		border-collapse:collapse;
		border-spacing:0;
	}
	.tg th {
		padding:5px 5px;
		border-style:solid;
		border-width:1px;
		overflow:hidden;
		word-break:normal;
		font-family:serif;
		font-size:12px;
		font-variant:bold;
	}
	.tg td {
		padding:5px 5px;
		border-style:solid;
		border-width:1px;
		overflow:hidden;
		word-break:normal;
		font-family:serif;
		font-size:12px;
	}
    .no {
		border-collapse:collapse;
		border-spacing:0;
	}
	.no th {
		padding:0px 0px;
		border-style:solid;
		border-width:0px;
		overflow:hidden;
		word-break:normal;
		font-family:serif;
		font-size:12px;
		font-variant:bold;
	}
    .no td {
		padding:0px 0px;
		border-style:none;
		border-width:0px;
		overflow:hidden;
		word-break:normal;
		font-family:serif;
		font-size:12px;
	}
    @media print {
		blockquote {
			page-break-inside: avoid;
		}
		div {
			page-break-inside: avoid;
		}
    }
	ins {
		color: green;
		background: #dfd;
		text-decoration: none;
	}
	del {
		color: red;
		background: #fdd;
		text-decoration: line-through;
	}

</style>
</HEAD>
<BODY>
<?php
date_default_timezone_set('Europe/Prague');
require_once 'dbconnect.php';

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$link) {
    echo "Error: Unable to connect to database." . PHP_EOL;
    echo "Reason: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
include 'finediff.php';
include 'variables.php';
include 'functions.php';
