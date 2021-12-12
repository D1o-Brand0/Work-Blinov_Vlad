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
$m = GetSafe("m");
if (strlen($m) > 0) {

	header("Content-Type: application/json; charset=utf-8");
	$data = array("ok" => FALSE, "err" => "не известная ошибка", "data" => array());

	if ($m == "date") {

		$z = intval(GetSafe("date"));
		if ($z > 0) {
			$MainDate = TimeToBeginDay($z);
			$_SESSION['MainDate'] = $MainDate;
			$data["ok"] = TRUE;
			$data["data"] = getUserStatID($User['userID'], $IDValue, $MainDate);
		}

	} elseif ($m == "input") {

		$z1 = intval(GetSafe("valueID"));
		$z2 = intval(GetSafe("value"));
		if ( ($z1 > 0) && ($z2 > 0) ) {
			if ( ($z1 > 0) || ($z1 < count($ValueID)) ) {
				addUserStat($userID, $MainDate, $z1, $z2);
				$data["ok"] = TRUE;
			}
		}

	} elseif ($m == "remove") {

		$z = intval(GetSafe("valueID"));
		if ($z > 0) {
			removeUserStat($userID, $MainDate, $z);
			$data["ok"] = TRUE;
		}

	} elseif ($m == "valueid") {

		$z = intval(GetSafe("valueID"));
		if ( ($z > 0) || ($z < count($ValueID)) ) {
			$IDValue = $z;
			$_SESSION['IDValue'] = $IDValue;
			$data["ok"] = TRUE;
		}
		
	} elseif ($m == "graph") {

		$a = getUserStatID($User["userID"], $IDValue, $MainDate - (30*24*3600), $MainDate);
		if (count($a) == 0) {
			$data["err"] = "нет данных";
		} else {
			foreach($a as $k=>$v) {
				$data["data"][] = $v;
			}
			$data["ok"] = TRUE;
			$data["err"] = "";
		}

	}

	if ($data["ok"]) $data["err"] = "";
	print(json_encode($data));

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

<div id="idInput">
	<h2>Ввод данных</h2>
	<hr />
	<table>
		<tr>
			<td width="50%" style="text-align: right;">
				<span>Тип параметра:</span>
			</td>
			<td>
				<select id="idInputValueID" name="valueID" size="1" onchange="input_form_valueID_change()">
<?php
foreach($ValueID as $i=>$v) {
	print('<option '.($i == $IDValue ? 'selected ' : '').'value="'.$i.'">'.$v.'</option>');
}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td width="150px" style="text-align: right;">
				<span>Значение параметра:</span>
			</td>
			<td>
				<input id="idInputValue" type="number" name="value" value="0" />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<br /><input type="button" value="Добавить значение" onclick="input_form_submit()" />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<br /><input type="button" value="Удалить значение" onclick="input_form_remove_submit()" />
			</td>
		</tr>
	</table>
</div>
<script type="text/javascript">

function input_form_valueID_change(evt) {
	var value_id = str_to_num($('#idInputValueID').val());
	if (value_id > 0) {
		fetch("/input.php?m=valueid&valueID=" + value_id).then(function(response) {
			update_graph();
		});
	}
}

function input_form_submit() {
	var value_id = str_to_num($('#idInputValueID').val());
	var value = str_to_num($('#idInputValue').val());
	if (value <= 0) {
		alert("Введите значение !");
	} else {
		fetch("/input.php?m=input&valueID=" + value_id + "&value=" + value + "&tick=" + Date.now()).then(function(response) {
			$('#idInputValue').val("0");
			update_graph();
		});
	}
}

function input_form_remove_submit() {
	var value_id = str_to_num($('#idInputValueID').val());
	if (confirm("Удалить значение ?")) {
		fetch("/input.php?m=remove&valueID=" + value_id + "&tick=" + Date.now()).then(function(response) {
			$('#idInputValue').val("0");
			update_graph();
		});
	}
}

</script>
