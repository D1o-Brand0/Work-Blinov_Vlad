<?php

# защита от повторного использования
if (defined("_DB_PHP_")) return;
define("_DB_PHP_", TRUE);

# текущий каталог скрипта (каталог WEB сервера)
define("ROOT", dirname(__FILE__));
# каталог приложения
define("ROOT_APP", dirname(ROOT));

date_default_timezone_set("Etc/GMT-8");

# текущая выбранная дата (для просомтр и ввода данных)
global $MainDate;
$MainDate = TimeToBeginDay(time());
if (isset($_SESSION['MainDate'])) {
	$z = $_SESSION['MainDate'];
	if (is_int($z)) $MainDate = $z;
}

# виды вводимых данных
global $ValueID;
$ValueID = array(
	1 => "Калории (килокалории)",
	2 => "Вес (кг.)",
	3 => "Упражнение подтягивание (шт.)",
	4 => "Упражнение отжимание (шт.)",
	5 => "Упражнение скручивание (шт.)"
);

# текущий тип параметра упражнений
global $IDValue;
$IDValue = 1;
if (isset($_SESSION['IDValue'])) {
	$z = $_SESSION['IDValue'];
	if (is_int($z)) $IDValue = $z;
	if ( ($IDValue <= 0) || ($IDValue > count($ValueID)) ) $IDValue = 1;
}

# подключение к БД
global $DB;
$DB = new SQLite3(ROOT_APP."/db.sqlite");
# создание таблиц в БД, если их нет
$DB->exec('CREATE TABLE IF NOT EXISTS [users] ([name] VARCHAR(100), [is_male] INT, [email] VARCHAR(100), [phone] VARCHAR(100), [password] VARCHAR(100))');
$DB->exec('CREATE TABLE IF NOT EXISTS [statistic] ([userID] INT, [date] INT, [value_id] INT, [value] INT)');

# карточка пользователя
global $User;
$User = array("userID" => 0, "name" => "Гость", "is_male" => 0, "email" => "", "phone" => "");

# проверка "user ID" в сессии
$userID = 0;
if (isset($_SESSION['userID'])) $userID = $_SESSION['userID'];

# если "user ID" есть в сессии - проверяем, ести ли пользователь в БД
if ( (is_int($userID)) && ($userID != 0) ) {

	$res = $DB->query('SELECT [rowid], * FROM [users] WHERE [rowid] = '.$userID);
	if ($row = $res->fetchArray()) {
		$User['userID'] = $row['rowid'];
		$User['name'] = $row['name'];
		$User['is_male'] = $row['is_male'];
		$User['email'] = $row['email'];
		$User['phone'] = $row['phone'];
	}
}

# РАЗДЕЛ ВСПОМОГАТЕЛЬНЫХ ФУНКЦИЙ

# возвращает значение параметра из GET или POST метода HTTP
function GetSafe($name) {
	if (isset($_GET[$name])) return $_GET[$name];
	if (isset($_POST[$name])) return $_POST[$name];
	return "";
}

# функция удаляет двойные пробелы
function StrClear($s) {
	$z = trim($s);
	while (strlen($z) > 0) {
		$n = strlen($z);
		$z = str_replace("  ", " ", $z);
		if (strlen($z) == $n) break;
	}
	return $z;
}

# значение есть BOOL тип ?
function IsBool() {
	$a = func_get_args();
	if (count($a) == 0) return false;
	foreach($a as $v) {
		if (!is_bool($v)) return false;
	}
	return true;
}

# значение есть String тип ?
function IsStr() {
	$a = func_get_args();
	if (count($a) == 0) return false;
	foreach($a as $v) {
		if (!is_string($v)) return false;
	}
	return true;
}

# значение есть Number тип ?
function IsNum() {
	$a = func_get_args();
	if (count($a) == 0) return false;
	foreach($a as $v) {
		if (!is_numeric($v)) return false;
	}
	return true;
}

# значение есть Interger тип ?
function IsInt() {
	$a = func_get_args();
	if (count($a) == 0) return false;
	foreach($a as $v) {
		if (!is_int($v)) if (!is_long($v)) return false;
	}
	return true;
}

# значение есть Float тип ?
function IsFloat() {
	$a = func_get_args();
	if (count($a) == 0) return false;
	foreach($a as $v) {
		if (!is_double($v)) if (!is_float($v)) return false;
	}
	return true;
}

# значение есть Массив ?
function IsArray() {
	$a = func_get_args();
	if (count($a) == 0) return false;
	foreach($a as $v) {
		if (!is_array($v)) return false;
	}
	return true;
}

# значение есть Объект ?
function IsObj() {
	$a = func_get_args();
	if (count($a) == 0) return false;
	foreach($a as $v) {
		if ( (!is_object($v)) || (is_null($v))) return false;
	}
	return true;
}

# значение есть ссылка на Ресурс
function IsRes() {
	$a = func_get_args();
	if (count($a) == 0) return false;
	foreach($a as $v) {
		if ( (!is_resource($v)) || (is_null($v))) return false;
	}
	return true;
}

# значение есть NULL ?
function IsNull() {
	$a = func_get_args();
	if (count($a) == 0) return false;
	foreach($a as $v) {
		if (!is_null($v)) return false;
	}
	return true;
}

# значение(я) есть String и НЕ пустая строка(и)
function siz() {
	$a = func_get_args();
	if (count($a) == 0) return true;
	$f = true;
	foreach($a as $v) {
		if (!is_string($v)) continue;
		if (strlen(trim($v)) > 0) $f = false;
	}
	return $f;
}

function TimeToBeginDay($dt) {
	$a = getdate($dt);
	return mktime(0, 0, 0, $a['mon'], $a['mday'], $a['year']);
}

// получить статистику пользователя за период
// $date = unix time stamp
function getUserStat($userID, $dateBegin, $dateEnd = 0) {

	global $DB;
	
	$result = array();
	if ($dateEnd == 0) $dateEnd = $dateBegin;
	$dateBegin = TimeToBeginDay($dateBegin);
	$dateEnd = TimeToBeginDay($dateEnd);
	$res = $DB->query('SELECT [date], [value_id], [value] FROM [statistic] WHERE [userID] = '.$userID.' AND [date] >= '.$dateBegin.' AND [date] <= '.$dateEnd.' ORDER BY [date], [value_id], [rowid]');
	while ($row = $res->fetchArray()) {
		$result[] = $row;
	}
	return $result;

}

// получить статистику пользователя за период
// $date = unix time stamp
function getUserStatID($userID, $value_id, $dateBegin, $dateEnd = 0) {

	global $DB;

	$result = array();
	if ($dateEnd == 0) $dateEnd = $dateBegin;
	$dateBegin = TimeToBeginDay($dateBegin);
	$dateEnd = TimeToBeginDay($dateEnd);
	$res = $DB->query('SELECT [date], [value] FROM [statistic] WHERE [userID] = '.$userID.' AND [value_id] = '.$value_id.' AND [date] >= '.$dateBegin.' AND [date] <= '.$dateEnd.' ORDER BY [date], [value_id], [rowid]');
	while ($row = $res->fetchArray()) {
		$result[] = $row;
	}
	return $result;

}

// добавить одну запись в статистику пользователя
// $date = unix time stamp
function addUserStat($userID, $date, $value_id, $value) {

	global $DB;
	
	$st = $DB->prepare('DELETE FROM [statistic] WHERE [userID]=:userID AND [date]=:date AND [value_id]=:value_id');
	$st->bindValue("userID", $userID);
	$st->bindValue("date", TimeToBeginDay($date));
	$st->bindValue("value_id", $value_id);
	$st->execute();
	$st->close();

	$st = $DB->prepare('INSERT INTO [statistic] ([userID],[date],[value_id],[value]) VALUES(:userID, :date, :value_id, :value)');
	$st->bindValue("userID", $userID);
	$st->bindValue("date", TimeToBeginDay($date));
	$st->bindValue("value_id", $value_id);
	$st->bindValue("value", $value);
	$st->execute();
	$st->close();
	return TRUE;

}

// удалить одну запись из статистикти пользователя
// $date = unix time stamp
function removeUserStat($userID, $date, $value_id) {

	global $DB;
	
	$st = $DB->prepare('DELETE FROM [statistic] WHERE [userID]=:userID AND [date]=:date AND [value_id]=:value_id');
	$st->bindValue("userID", $userID);
	$st->bindValue("date", TimeToBeginDay($date));
	$st->bindValue("value_id", $value_id);
	$st->execute();
	$st->close();
	return TRUE;

}

// получить профиль пользователя, возможно указав его пароль
function getUser($name, $pwd = "") {

	global $DB;

	$result = array();
	$filter = "";
	$pwd_md5 = md5("myProject".$pwd."Vlad");
	if (strlen($pwd) > 0) {
		$filter = " AND [password]=:password";
	}
	$st = $DB->prepare("SELECT [rowid], * FROM [users] WHERE [name]=:name".$filter);
	$st->bindValue("name", $name);
	if (strlen($filter) > 0) $st->bindValue("password", $pwd_md5);
	$res = $st->execute();
	while ($row = $res->fetchArray()) {
		$result = $row;
		break;
	}
	$st->close();
	return $result;
}

// добавить пользователя
function addUser($name, $pwd, $is_male, $email, $phone) {

	global $DB;

	$r = getUser($name);
	if (count($r) > 0) {
		return "Пользователь уже существует";
	} else {
		$pwd_md5 = md5("myProject".$pwd."Vlad");
		$st = $DB->prepare("INSERT INTO [users] ([name],[is_male],[email],[phone],[password]) VALUES(:name,:is_male,:email,:phone,:password)");
		$st->bindValue("name", $name);
		$st->bindValue("is_male", $is_male);
		$st->bindValue("email", $email);
		$st->bindValue("phone", $phone);
		$st->bindValue("password", $pwd_md5);
		$st->execute();
		$st->close();
		$r = getUser($name, $pwd);
		return $r['rowid'];
	}
}
