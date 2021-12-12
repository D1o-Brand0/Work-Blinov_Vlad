
var mesos = [
 'Январь',
 'Февраль',
 'Март',
 'Апрель',
 'Май',
 'Июнь',
 'Июль',
 'Август',
 'Сентябрь',
 'Октябрь',
 'Ноябрь',
 'Декабрь'
];

var dies = [
'Воскресенье',
'Понедельник',
'Вторник',
'Среда',
'Четверг',
'Пятница',
'Суббота'
];

var dies_abr = [
'вс',
'пн',
'вт',
'ср',
'чт',
'пт',
'сб'
];

Number.prototype.pad = function(num) {
  var str = '';
  for(var i = 0; i < (num-this.toString().length); i++)
      str += '0';
  return str += this.toString();
}

function calendari(widget, data) {

  var original = widget.getElementsByClassName('actiu')[0];

  if(typeof original === 'undefined') {
      original = document.createElement('table');
      original.setAttribute('data-actual',
          data.getFullYear() + '/' +
          data.getMonth().pad(2) + '/' +
          data.getDate().pad(2))
      widget.appendChild(original);
  }

  var diff = data - new Date(original.getAttribute('data-actual'));

  diff = new Date(diff).getMonth();

  var e = document.createElement('table');

  e.className = diff  === 0 ? 'amagat-esquerra' : 'amagat-dreta';
  e.innerHTML = '';

  widget.appendChild(e);

  e.setAttribute('data-actual',
                 data.getFullYear() + '/' +
                 data.getMonth().pad(2) + '/' +
                 data.getDate().pad(2))

  var fila = document.createElement('tr');
  var titol = document.createElement('th');
  titol.setAttribute('colspan', 7);

  var boto_prev = document.createElement('button');
  boto_prev.className = 'boto-prev';
  boto_prev.innerHTML = '&#9666;';

  var boto_next = document.createElement('button');
  boto_next.className = 'boto-next';
  boto_next.innerHTML = '&#9656;';

  titol.appendChild(boto_prev);
  titol.appendChild(document.createElement('span')).innerHTML = 
      '<span style="color: #FFF;">' + mesos[data.getMonth()] + '<span class="any">' + data.getFullYear() + '</span></span>';

  titol.appendChild(boto_next);

  boto_prev.onclick = function() {
      data.setMonth(data.getMonth() - 1);
      calendari(widget, data);
  };

  boto_next.onclick = function() {
      data.setMonth(data.getMonth() + 1);
      calendari(widget, data);
  };

  fila.appendChild(titol);
  e.appendChild(fila);

  fila = document.createElement('tr');

  for(var i = 1; i < 7; i++) {
      fila.innerHTML += '<th>' + dies_abr[i] + '</th>';
  }

    fila.innerHTML += '<th>' + dies_abr[0] + '</th>';
    e.appendChild(fila);

    var inici_mes = new Date(data.getFullYear(), data.getMonth(), -1).getDay();

    var actual = new Date(data.getFullYear(), data.getMonth(), -inici_mes);

    var func_span_onlcik = function(evt) {
	    if ( (evt) && (evt.target) ) {
			onChangeDate(str_to_num(evt.target.dataset.dt));
		}
	};
	for (var s = 0; s < 6; s++) {
		var fila = document.createElement('tr');
		for (var d = 1; d < 8; d++) {
			var cela = document.createElement('td');
			var span = document.createElement('span');
			span.dataset.dt = "" + DateToUnix(actual);
			span.onclick = func_span_onlcik;
			cela.appendChild(span);
			span.innerHTML = actual.getDate();
			if (actual.getMonth() !== data.getMonth()) cela.className = 'fora';
			if (data.getDate() == actual.getDate() && data.getMonth() == actual.getMonth()) cela.className = 'avui';
			actual.setDate(actual.getDate() + 1);
			fila.appendChild(cela);
		}
		e.appendChild(fila);
	}

  	setTimeout(function() {
		e.className = 'actiu';
		original.className +=
		diff === 0 ? ' amagat-dreta' : ' amagat-esquerra';
	}, 20);

	original.className = 'inactiu';

	setTimeout(function() {
		var inactius = document.getElementsByClassName('inactiu');
		for(var i = 0; i < inactius.length; i++) widget.removeChild(inactius[i]);
	}, 1000);

}

calendari(_ID('calendari'), new Date(MainDate * 1000));

function onChangeDate(_dt) {
    MainDate = _dt;
    UpdateDateOnTop();
    calendari(document.getElementById('calendari'), UnixToDate(MainDate));
    var _uri = "/input.php?m=date&date=" + MainDate + "&tick=" + Date.now();
    fetch(_uri).then( function(response) {
		update_graph();
	});
}

// обновить дату в "TOP" панель страницы
function UpdateDateOnTop() {
    $("#idDate").text(" - " + DateToStrShort(MainDate));
}

$(function() {
    UpdateDateOnTop();
});
