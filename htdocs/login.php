<?php

# запуск сессии, если небыло запуска
if (!defined("IS_SESSION")) {
	session_start();
	define("IS_SESSION", TRUE);
}

# модуль вспомогательных функций + работа с БД
require_once("db.php");

$m = GetSafe("m"); # 'm' - method - метод
if (strlen($m) > 0) {

	$userName = trim(GetSafe("userName"));
	$userPassword = GetSafe("userPassword");
	$userPasswordReply  = GetSafe("userPasswordReply");
	$is_male = trim(GetSafe("is_male"));
	$userEmail = trim(GetSafe("userEmail"));
	$userPhone = trim(GetSafe("userPhone"));

	if ($m == "auth") {

		if ( (strlen($userName) == 0) || (strlen($userPassword) == 0) ) {
			http_response_code(404);
			print("Ошибка параметров авторизации");
			return;
		}

		$res = getUser($userName, $userPassword);
		if (count($res) == 0) {
			http_response_code(404);
			print("Ошибка авторизации");
			return;
		}
		$_SESSION['userID'] = $res['rowid'];
		print("OK");

	} elseif ($m == "reg") {

		$userName = StrClear($userName);
		if (strlen($userName) < 5) {
			http_response_code(404);
			print("Имя должно быть не менее 5 символов");
			return;
		}
		$a = explode(" ", $userName);
		if (count($a) < 2) {
			http_response_code(404);
			print("Необходимо ввести имя и отчетсво, или имя и фамилию");
			return;
		}

		if ( (strlen($userPassword) < 5) || ($userPassword != $userPasswordReply) ) {
			http_response_code(404);
			print("Ошибка в пароле, минимум 5 знаков, или павтор пороля не совпадает");
			return;
		}

		if ($is_male == "0") {
			$is_male = 0;
		} else {
			$is_male = 1;
		}

		$res = getUser($userName);
		if (count($res) > 0) {
			http_response_code(404);
			print("Пользователь с таким именем уже существует");
			return;
		}
		$z = addUser($userName, $userPassword, $is_male, $userEmail, $userPhone);
		if (IsStr($z)) {
			http_response_code(404);
			print($z);
			return;
		}
		$_SESSION['userID'] = $z;
		prtin("OK");

	} else {
		http_response_code(404);
		print("Ошибка параметров !");
	}
	return;
}

# вывод заголовока главной страницы
require_once("index_header.php");

?>

<table id="idHeaderTop">
	<tr>
		<td>
			<span style="margin-left: 16px;">Календарь здоровья</span>
		</td>
		<td width="100px" style="text-align: right;">
			<span>Гость</span>
		</td>
	</tr>
</table>

<div style="margin: 16px;">

<h2>Авторизация</h2>

<form>
	<table class="tbAuth">
		<tr>
			<td width="100px" style="text-align: right;">
				<span>Имя Фамилия:</span>
			</td>
			<td>
				<input type="text" id="authUserName" value="" style="width: 100%"/>
			</td>
		</tr>
		<tr>
			<td style="text-align: right;">
				<span>Пароль:</span>
			</td>
			<td>
				<input type="password" id="authUserPassword" value="" style="width: 100%" />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<br /><input type="button" value="OK" onclick="onClickAuth()"/>
			</td>
		</tr>
	</table>
</form>

<br />
<br />
<hr />

<h2>Регистрация</h2>

<form>
	<table class="tbAuth">
		<tr>
			<td width="100px" style="text-align: right;">
				<span>Имя Фамилия:</span>
			</td>
			<td>
				<input id="regUserName" type="text" value="" style="width: 100%" />
			</td>
		</tr>
		<tr>
			<td style="text-align: right;">
				<span>Пароль
					(Мин.5.символов):</span>
			</td>
			<td>
				<input id="regUserPassword" type="password" value="" style="width: 100%" />
			</td>
		</tr>
		<tr>
			<td style="text-align: right;">
				<span>Пароль (подтв.):</span>
			</td>
			<td>
				<input id="regUserPasswordReply" type="password" value="" style="width: 100%" />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<input id="is_male" type="radio" name="is_male" value="1" checked >Мужчина<br />
				<input type="radio" name="is_male" value="0">Женщина
			</td>
		</tr>
		<tr>
			<td style="text-align: right;">
				<span>Email:</span>
			</td>
			<td>
				<input id="userEmail" type="email" value="" style="width: 100%" />
			</td>
		</tr>
		<tr>
			<td style="text-align: right;">
				<span>Телефон:</span>
			</td>
			<td>
				<input id="userPhone" type="phone" value="" style="width: 100%" />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<br /><input type="button" value="OK" onclick="onClickReg()"/>
			</td>
		</tr>
	</table>
</form>

</div>
<script type="text/javascript">

function onClickAuth() {
	let _user_name = $("#authUserName").val();
	let _user_pass = $("#authUserPassword").val();
	let data = new URLSearchParams();
	data.append("m", "auth");
	data.append("tick", Date.now());
	data.append("userName", _user_name);
	data.append("userPassword", _user_pass);
	fetch("/login.php", {
		method: 'POST',
		body: data
	}).then(function(response) {
		if (response.ok) {
			openLink("/");
		} else {
			response.text().then(function(text) {
				alert(text);
			});
		}
	});
}

function onClickReg() {
	let _user_name = $("#regUserName").val();
	let _user_pass = $("#regUserPassword").val();
	let _user_pass_reply = $("#regUserPasswordReply").val();
	let _is_male = $("#is_male").val();
	let _user_email = $("#userEmail").val();
	let _user_phone = $("#userPhone").val();
	let data = new URLSearchParams();
	data.append("m", "reg");
	data.append("tick", Date.now());
	data.append("userName", _user_name);
	data.append("userPassword", _user_pass);
	data.append("userPasswordReply", _user_pass_reply);
	data.append("is_male", is_male);
	data.append("userEmail", _user_email);
	data.append("userPhone", _user_phone);
	fetch("/login.php", {
		method: 'POST',
		body: data
	}).then(function(response) {
		if (response.ok) {
			openLink("/");
		} else {
			response.text().then(function(text) {
				alert(text);
			});
		}
	});
}

</script>