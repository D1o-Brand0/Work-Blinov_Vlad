<?php
if (defined("_INDEX_HEADER_PHP_")) return;
define("_INDEX_HEADER_PHP_", TRUE);
?><!DOCTYPE html />
<html lang="ru">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html charset=utf-8">
	<meta http-equiv="Content-Language" content="ru">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=no" />
	<meta name="keywords" content="здоровье"/>
	<title>Программа здоровья</title>
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="/styles.css" />
	<link rel="stylesheet" type="text/css" href="/lib/fontawesome/all.min.css" />
	<script type="text/javascript" src="/lib/jquery.min.js"></script>
	<script type="text/javascript" src="/lib/vue.min.js"></script>
	<script type="text/javascript" src="/lib/chart.min.js"></script>
	<script type="text/javascript" src="/core.js"></script>
</head>
<body>
<script type="text/javascript">
var MainDate = <?= $MainDate ?>;
var IDValue = <?= $IDValue ?>;
</script>
<div id="idPAGE">
<?php
function INDEX_DONE() {
	require_once("index_footer.php");
}
register_shutdown_function("INDEX_DONE");
