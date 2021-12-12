<?php

# запуск сессии, если небыло запуска
if (!defined("IS_SESSION")) {
	session_start();
	define("IS_SESSION", TRUE);
}

# модуль вспомогательных функций + работа с БД
require_once("db.php");

# вывод заголовока главной страницы
require_once("index_header.php");

# обработка выхода пользователя
$m = GetSafe("m");
if ($m == "unlogin") {
	$_SESSION['userID'] = 0;
	?> <script type="text/javascript"> openLink("/"); </script> <?php
	return;
}

# переход на авторизацию или регистрацию (если нужно)
if ($User['userID'] == 0) {
	require_once("login.php");
	return;
}

# далее всё то, что касается авторизованного пользователя

?>

<table id="idHeaderTop">
	<tr>
		<td>
			<span style="margin-left: 16px;">Календарь здоровья</span><span id="idDate">&nbsp;</span>
		</td>
		<td width="100px">
			<span><?= $User['name'] ?></span>
		</td>
		<td width="64px">
			<form action="/index.php?tick=<?= date("YmdHis") ?>" method="POST">
				<input type="hidden" name="m" value="unlogin" />
				<input type="submit" value="Выход" />
			</form>
		</td>
	</tr>
</table>

<?php

if (!defined("_INDEX_PHP")) define("_INDEX_PHP", TRUE);

require_once("calendar.php");

require_once("input.php");

?>
