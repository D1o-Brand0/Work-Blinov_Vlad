<?php

# запуск сессии, если небыло запуска
if (!defined("IS_SESSION")) {
	session_start();
	define("IS_SESSION", TRUE);
}

# модуль вспомогательных функций + работа с БД
require_once("db.php");

# переход на авторизацию или регистрацию (если нужно)
if ($User['userID'] == 0) {
	require_once("login.php");
	return;
}

# обработка методов
$m = intval(GetSafe("m"));
$dt = intval(GetSafe("dt"));
if ( ($m > 0) && ($dt > 0) ) {
	$a = getUserStatID($User['userID'], $IDValue, $dt);
	$z = json_encode($a);
	header('Content-Type: application/json; charset=utf-8');
	header('Content-Length: '.strlen($z));
	header('Expires: 0');
	header('Cache-Control: no-store, no-cache, max-age=0');
	header('Pragma: public');
	print($z);
	return;
}

# вывод заголовока главной страницы
require_once("index_header.php");

if (!defined("_INDEX_PHP")) {
?>
<script type="text/javascript">
openLink("/");
</script>
<?php
	return;
}
?>
<div id="idCalendarBox">
	<table width="100%">
		<tr>
			<td width="385px">
				<!-- добавить выбор типа вводимых данных -->
				<div id="idCalendar">
					<div class="wrapper">
						<div id="calendari"></div>
					</div>
				</div>
			</td>
			<td>
				<div id="idCanvasGraph" style="background-image: url(/lib/calendar/bkg_grid.jpg); background-repeat: repeat;"></div>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	CSS_Include("/lib/calendar/style.css");
	JS_Include("/lib/calendar/calendar.js");
	JS_Include("/lib/calendar/graph.js");
</script>

<?php




