import jQuery from 'jquery';
// import 'JsBarcode';
import { ausgabeFam, verwaltungFam } from './client/familie';
import generate from './client/orte';



// debug
// @ts-ignore
window.$ = jQuery;
// @ts-ignore
window.ausgabeFam = ausgabeFam;
// @ts-ignore
window.verwaltungFam = verwaltungFam;


export interface TabElement extends HTMLAnchorElement {
  onClose: () => void;
  onOpen: () => void;
}



// load window
jQuery(($) => {
  // init tabs
  const tabHs = $('#tab-head li');
  let current_tab: JQuery<TabElement>;
  const orte = [];
  
  tabHs.find('a').each((_, element: TabElement) => {
    element.onClose = () => { };
    element.onOpen = () => { };
  });

  const changeTab = (link: JQuery<TabElement>) => {
    if (current_tab) {
      // close tab and hide
      current_tab.get(0).onClose();
      $(current_tab.attr('href')).css('display', 'none');
      current_tab.removeClass('selected');
    }

    // open tab and show
    $(link.attr('href')).css('display', 'block')
    link.addClass('selected');
    link.get(0).onOpen();
    current_tab = link;

    // if (c.getAttribute('href') == '#tab2' && typeof (selected_fam) !== "undefined") {
    //   selected_fam.save();
    // }
    // if (c.getAttribute('href') == '#tab5') { getOrte(); }
    // location.href = h;

    // if (h == '#tab2') {
    //   if (typeof (tdd_orte) !== "undefined") { gruppeChange(); }
    // }
    // if (h == '#tab3') { searchV(searchFamV); }
    // if (h == '#tab4') { var p = jQuery('#log-pagination').val(); getLogs(p); }
    // if (h == '#tab5') { getOrte(); }
  };
  if (window.location.hash == "") {
    changeTab(tabHs.first().find('a') as JQuery<TabElement>);
  } else {
    changeTab(tabHs.find(`a[href="${window.location.hash}"]`) as JQuery<TabElement>);
  }
  tabHs.on('click', 'a', function(e) { changeTab($(this)); });
  tabH();

  // load forms and reset
  ausgabeFam.linkHtml();
  ausgabeFam.clear();
  ausgabeFam.disable();
  verwaltungFam.linkHtml();
  verwaltungFam.clear();
  verwaltungFam.disable();

  const ausgabe_sh = $('#tab2 .search-header select');

  // register handlers
  jQuery(window).resize(tabH).on('keydown', keyboardHandler);

  // Orte
  const { loadOrte, ortChange } = generate(orte, ausgabe_sh);
  // @ts-ignore
  window.loadOrte = loadOrte;
  // @ts-ignore
  window.orte = orte;

  ausgabe_sh.first().on('change', () => ortChange());
  loadOrte();
  ortChange();




  // Minimum-height for tabs
  function tabH() {
    var b = document.getElementById('tab-body'),
      w = window.outerWidth,
      h = document.getElementById('tab-head').offsetHeight;
    if (w >= 1160) {
      b.style.minHeight = h + 'px';
    } else {
      b.style.minHeight = '';
    }
  }

  // keyboard navigation
  function keyboardHandler(event: JQuery.KeyDownEvent) {
    console.log(event, event.which, event.key);
  }
});


/*
window.onload = function () {

  keyboard_timeout = true;
  keyboard_timeout_ = undefined;
  fam_timeout = undefined;

  jQuery('#ort-select').change(ortChange);
  jQuery('#gruppe-select').change(gruppeChange);
  jQuery('#verw-ort').change(ortChangeV);

  tdd_orte = { length: 0 };
  tdd_ort = {};
  tdd_familien = { query: { length: 0 } };
  tdd_fam_curr = { query: { length: 0 } };
  tdd_fam_neu = { query: { length: 0 } };
  tdd_unsaved_queue = [];
  tdd_unsaved_queue.callback = function (data) {
    var pc = data.post.set;
    var nd = { ID: data.post.meta.value };
    //jQuery.each( pc, function(i,e) { s = e; nd[i] = s.replaceAll( "'", "" ); } );
    jQuery.each(pc, function (i, e) { nd[i] = e; });
    console.debug(nd);

    var index = -1;
    jQuery.each(tdd_unsaved_queue, function (i, e) { if (JSON.stringify(e.newdata) == JSON.stringify(nd)) { index = i; } });

    var f = tdd_unsaved_queue[index];
    if (data.status == "success") {
      if (JSON.stringify(tdd_fam_curr[f.index]) == JSON.stringify(f.data)) {
        tdd_fam_curr[f.index] = clone(f.newdata);
        if (typeof (selected_fam) != "undefined" && f.index == selected_fam.index) {
          var new_fam = new familie(tdd_fam_curr[f.index], f.index);
          selected_fam = new_fam;
          console.debug(selected_fam, 'saved');
          displayFam();
        } else {
          console.debug(data, 'saved');
        }
      } else {
        console.debug(data, 'saved');
      }
      if (typeof (fam_for_v) != "undefined") {
        var nd = { ID: data.post.id };
        jQuery.each(data.post.set, function (i, e) {
          nd[i] = e;
        });
        fam_for_v = { data: nd };
        jumpToV();
      }
    } else {
      f.error = clone(data);
      tdd_save_error.push(f);
      alert("<p>Fehler beim Speichern:</p><p><b>" + data.post.set.Name + "</b></p><p>Mehr in der Konsole</p>", "Achtung!");
      console.debug('Fehler:', tdd_save_error);
    }
    tdd_unsaved_queue.splice(index, 1);
    var e = document.getElementById('familie-search');
    e.focus();
    e.select();
    e.setSelectionRange(0, e.value.length);
  };
  tdd_save_error = [];

  getOrte();
  getSettings();
  displayLogs();

  fam = 0;
  jQuery('.fam-count, #fam-anw').each(function (i, e) {
    e.addEventListener('click', function () {
      var el = document.getElementById('familie-count');
      el.innerText = fam;
    });
  });
  jQuery('#familie-count').on('click', function () {
    if (this.firstChild.tagName != "INPUT") {
      var e = document.createElement('input');
      e.type = "number";
      e.value = fam;
      e.style.width = "55px";
      this.replaceChild(e, this.firstChild);
      e.focus();
      e.select();
    }
  })
    .on('keyup', function () {
      var code = event.keyCode ? event.keyCode : event.which;
      if (code == 13) {
        var v = this.firstChild.value;
        fam = (v == "" ? 0 : v);
        this.innerHTML = fam;
      }
    })
    .on('focusout', function () {
      var v = this.firstChild.value;
      fam = (v == "" ? 0 : v);
      this.innerHTML = fam;
    });

  jQuery('#fam-reload').on('click', function () {
    if (typeof (tdd_orte) !== "undefined") { gruppeChange(); }
  });
  jQuery('#fam-anw').on('click', function () {
    if (selected_fam.retry) {
      this.checked = false;
      selected_fam.retry = false;
      highlightElement(jQuery('#fam-szh'));
    }
  });
  jQuery('#verw-bneu').on('click', verwFamNeu);
  jQuery('#verw-del').on('click', function () {
    jQuery('#verw-save, #verw-del').prop('disabled', true);
    remove({ table: 'familien', meta: { key: 'ID', value: verw_fam.data.ID } }, delFamV);
  });
  jQuery('#fam-schuld').on('keyup', schuldfieldChange).on('change', schuldfieldChange);
  jQuery('#fam-gv').on('click', function () {
    jQuery('#fam-schuld').prop('disabled', true);
    if (+selected_fam.data.Schulden + selected_fam.preis >= selected_fam.preis * 3) {
      jQuery('#fam-szh').html("Darf nächstes Mal nur noch nach Begleichen der Schulden hinein.");
    }
  });
  jQuery('#fam-sb').on('click', function () {
    jQuery('#fam-schuld').prop('disabled', true);
    if (selected_fam.schuld) {
      jQuery('#fam-anw').prop('disabled', false);
      jQuery('#fam-szh').html("");
    }
  });

  jQuery('#log-go').on('click', getEinnahmen);

  jQuery('#sett-save').on('click', function () { saveSettings() });
  jQuery('#settings input').each(function (i, e) {
    jQuery(e).on('keypress', function (event) { var code = event.keyCode ? event.keyCode : event.which; if (code == 13) { saveSettings(this); } });
  });

  //Fancy alert
  var modal = document.getElementById('modal');
  var span = modal.getElementsByClassName("close")[0];

  span.onclick = function () {
    modal.style.display = "none";
    document.body.style.overflow = "";
  };
  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = "none";
      document.body.style.overflow = "";
    }
  };

  //Help headings
  var hs = jQuery('#tab6 h2, #tab6 h3, #tab6 h4, #tab6 h5, #tab6 h6');
  var ul = document.createElement('ul');
  ul.style.marginTop = '0';
  ul.style.marginBottom = '2.5em';
  hs.each(function (i, e) {
    var m = 0, f = '0.85em';
    switch (e.tagName) {
      case "H2":
        m = "10px";
        break;
      case "H3":
        m = "25px";
        f = "0.85em";
        break;
      case "H4":
        m = "32px";
        f = "0.725em";
        break;
      case "H5":
        m = "36px";
        f = "0.675em";
        break;
      case "H6":
        m = "39px";
        f = "0.65em";
        break;
    }
    var li = document.createElement('li'),
      a = document.createElement('a');

    a.href = '#';
    a.classList.add('link');
    a.innerHTML = e.innerHTML;
    a.style.fontSize = f;
    a.toEl = jQuery(e);

    a.onclick = function () { return false };
    a.addEventListener('click', function () {
      scrollEl = this.toEl;
      jQuery("body").animate({
        scrollTop: this.toEl.offset().top
      }, 600, "swing", function () {
        //Anim done, flash heading
        scrollEl.fadeTo(100, 0.2).fadeTo(200, 1.0),
          scrollEl = undefined
      });
    });

    li.appendChild(a);
    li.style.marginLeft = m, li.style.marginRight = m;
    ul.appendChild(li);
  });
  jQuery(ul).insertAfter(jQuery('#tab6 h1').first());
};

window.onkeydown = function (evt) {
  evt = evt || window.event;

  var charCode = evt.keyCode || evt.charCode || evt.which;
  var charStr = String.fromCharCode(charCode);

  if (currentTab.getAttribute('href') == "#tab2" && evt.altKey == true && evt.key !== "Alt") {
    switch (charStr) {
      case "N":
        document.getElementById('ort-select').focus();
        break;
      case "M":
        document.getElementById('gruppe-select').focus();
        break;
      case "J":
        document.getElementById('fam-karte').focus();
        break;
      case "K":
        document.getElementById('fam-schuld').focus();
        break;
      case "L":
        document.getElementById('fam-notiz').focus();
        break;
      case "U":
        document.getElementById('fam-anw').click();
        break;
      case "I":
        document.getElementById('fam-gv').click();
        break;
      case "O":
        document.getElementById('fam-sb').click();
        break;
      case "¼": //Komma
        var e = document.getElementById('familie-search');
        e.focus();
        e.select();
        e.setSelectionRange(0, e.value.length);
        break;
      case "¾": //Punkt
        document.getElementById('fam-reload').click();
        break;
      case "&": //Pfeil auf
        document.getElementById('familie-list').focus();
        var i = tdd_fam_curr.length - 1;
        if (typeof (selected_fam) != "undefined") { i = selected_fam.index - 1; }
        var e = jQuery('#familie-list li[value="' + i + '"]').get(0);
        if (typeof (e) != "undefined") {
          selectFam.call(e);
          jQuery('#familie-list').scrollTo(e);
        }
        break;
      case "(": //Pfeil ab
        document.getElementById('familie-list').focus();
        var i = 0;
        if (typeof (selected_fam) != "undefined") { i = selected_fam.index + 1; }
        var e = jQuery('#familie-list li[value="' + i + '"]').get(0);
        if (typeof (e) != "undefined") {
          selectFam.call(e);
          jQuery('#familie-list').scrollTo(e);
        }
        break;
      default:
        //console.debug( evt, charCode, charStr );
        break;
    }
  }

  keyboard_timeout = false;
  if (typeof (keyboard_timeout_) != "undefined") { clearTimeout(keyboard_timeout_); }
  keyboard_timeout_ = setTimeout(function () { keyboard_timeout = true; }, 1500);

}


//Serverrequests
function getOrte() {
  jQuery.post('?post&getOrte', { callback: 'orte' }, postC);
}

function orte(data) {
  if (data.status == "success") {
    var oa = data.query;
    tdd_orte = oa;

    for (var i = 0; i < oa.length; i++) {
      tdd_ort[oa[i].Name] = i;
    }

    var o = document.getElementById('ort-select');
    while (o.lastChild) {
      o.removeChild(o.lastChild);
    }
    var g = document.getElementById('gruppe-select');
    while (g.lastChild) {
      g.removeChild(g.lastChild);
    }

    for (var i = 0; i < oa.length; i++) {
      var e = document.createElement('option');
      e.value = i;
      var t = document.createTextNode(unescape(oa[i].Name));
      e.appendChild(t);
      o.appendChild(e);
    }

    ortChange();
    displayOrte();
    //getFamilien();
  } else {
    console.debug('Orte failed: ', data);
  }
}

function getFamilien() {
  get({ table: "Familien" }, fam);
}

function fam(data) {
  if (data.status == "success") {
    var fa = data.query;
    tdd_familien = fa;
  } else { console.debug(data); }
}

function getSettings() {
  get({ table: "Einstellungen" }, sett);
}

function sett(data) {
  if (data.status == "success") {
    var q = data.query;
    var s = { query: q };
    jQuery.each(q, function (i, e) {
      s[e.Name] = unescape(e.Val);
    });
    tdd_settings = s;

    displaySettings();
  } else { console.debug(data); }
}

function getSearch(string, callback) {
  var meta = [], byid = false;
  string = string.replace(/^(?: )+|(?: )+$/g, '').replace(/(?: ){2,}/g, ' ');
  var a = string.split(/ (?=(?:[^'|"]*(?:'|")[^'|"]*(?:'|"))*[^'|"]*$)/g);
  // regex by https://stackoverflow.com/a/3147901

  if (Number.isInteger(+string) && string != "") {
    meta.push({ value: string });
    byid = true;
  } else {
    for (var i = 0; i < a.length; i++) {
      var str = a[i], c = "LIKE", con = "";
      if (str.slice(0, 1) === "!") {
        str = str.slice(1);
        con = "NOT";
      }
      if (str.slice(0, 1) === "=") {
        str = str.slice(1);
        c = "=";
      }
      str = escape(str.replace(/^(?:'|")|(?:'|")$/g, ''));
      if (c === "LIKE") {
        str = "%" + str + "%";
      }
      meta.push({ compare: c, value: str, connect: con });
    }
  }

  post('?post&getSearch', { meta: meta, byid: byid }, callback);
}

//Handling requests to server
function get(postparam = {}, callback = "") {
  post('?post&get', postparam, callback);
}

function update(postparam = {}, callback = "") {
  post('?post&update', postparam, callback);
}

function insert(postparam = {}, callback = "") {
  post('?post&insert', postparam, callback);
}

function remove(postparam = {}, callback = "") {
  post('?post&delete', postparam, callback);
}

function post(url, postparam = {}, callback = "") {
  if (typeof (callback) == "string" || typeof (callback) == "object") {
    postparam.callback = callback;
  } else if (typeof (callback) == "function") {
    postparam.callback = callback.name;
  } else {
    postparam.callback = "";
  }
  jQuery.post(url, postparam, postC);
}

function postC(data) {
  if (typeof (data.callback) == 'string') {
    var fn = window[data.callback];
  } else if (typeof (data.callback == 'object')) {
    var fn = window;
    jQuery.each(data.callback, function (i, e) { fn = fn[e]; });
  }
  if (typeof fn === 'function') {
    fn(data);
  } else {
    console.debug(data.callback, 'is no function\n', data);
  }
}


//Make familien-list + selecting functions
function famList(first = false) {
  var l = document.getElementById('familie-list');
  var lis = l.children;
  for (var i = 0; i < lis.length; i++) {
    lis[i].removeEventListener('click', selectFam);
    if (lis[i].getAttribute('value') !== null) {
      lis[i].addEventListener('click', selectFam);
      // if ( first ) {
      //     //Only one: set directly as present and prepare for next search
      //     lis[i].click();
      //     document.getElementById( 'fam-anw' ).click();
      // }
    }
  }

  var e = document.getElementById('familie-search');
  e.focus();
  e.select();
  e.setSelectionRange(0, e.value.length);

}

function selectFam() {
  if (typeof (selected_fam) !== "undefined") {
    var cf = selected_fam.index;
    var ce = jQuery('ul#familie-list li[value=' + cf + ']')[0];
    if (typeof (ce) !== "undefined") { ce.classList.remove('selected'); }
    selected_fam.save();
  }
  this.classList.add('selected');
  var new_fam = new familie(tdd_fam_curr[this.value], this.value);
  selected_fam = new_fam;
  displayFam();
}

function displayFam() {
  var f = getFamForm(),
    d = selected_fam.data;

  f[0].html(unescape(d.Ort));
  f[1].html(d.Gruppe);
  if (d.lAnwesenheit != "0000-00-00" && d.lAnwesenheit != "") {
    var date = new Date(d.lAnwesenheit);
    var date = date.toLocaleDateString();
  } else {
    var date = "";
  }
  f[2].html(date);
  var karte = (d.Karte != "0000-00-00" ? d.Karte : "");
  f[3].val(karte);
  f[4].html(d.Erwachsene);
  f[5].html(d.Kinder);
  var pr = +preis(+d.Erwachsene, +d.Kinder);
  selected_fam.preis = pr;
  f[6].html(pr.toFixed(2) + "€&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&rarr; " + (pr + +d.Schulden).toFixed(2));
  f[7].val((+d.Schulden).toFixed(2));
  f[8].val(unescape(d.Notizen));
  var lt = (date == new Date().toLocaleDateString());
  f[9].prop("checked", lt);
  f[12].html(d.Num);
  f[13].html("(" + unescape(d.Name) + ", " + d.ID + ")</span>");
  f[14].html(unescape(d.Adresse).replace(/\n/g, '<br />'));
  f[15].html(unescape(d.Telefonnummer));

  jQuery(f).each(function (i, e) { e.prop('disabled', false); });
  f[9].prop("disabled", lt);

  jQuery('#barcode').JsBarcode(num_pad(d.ID, 6), { height: 28, width: 1, textMargin: 0, fontSize: 11, background: 0, marginLeft: 15, marginRight: 15, margin: 0, displayValue: true });

  //Karte abgelaufen?
  var date = new Date(karte);
  var t = new Date();
  var diff = (t.getTime() - date.getTime());
  var days = Math.ceil(diff / (1000 * 3600 * 24));
  if (days > 0) {
    jQuery('#fam-ab').html("Karte abgelaufen!");
  } else if (karte == "") {
    jQuery('#fam-ab').html("Ablaufdatum eingeben!");
  } else {
    jQuery('#fam-ab').html("");
  }

  //Schulden zu hoch?
  var date = new Date(d.lAnwesenheit);
  var t = new Date();
  var diff = (t.getTime() - date.getTime());
  var days = Math.ceil(diff / (1000 * 3600 * 24));
  if (+d.Schulden >= pr * 3 && days != 1) {
    f[9].prop('disabled', true);
    selected_fam.schuld = true;
    jQuery('#fam-szh').html("Schulden zu hoch! Muss erst Schulden begleichen!");
  } else if (+d.Schulden >= pr * 3 && days == 1) {
    selected_fam.schuld = false;
    jQuery('#fam-szh').html("Darf nächstes Mal nur noch nach Begleichen der Schulden hinein.");
  } else {
    selected_fam.schuld = false;
    jQuery('#fam-szh').html("");
  }

  //Bereits abgeholt diese Woche?
  if (days <= t.getDay()) {
    var t = jQuery('#fam-szh').html();
    if (t != "") t += '<br>';
    jQuery('#fam-szh').html(t + "Hat diese Woche bereits abgeholt!");
    selected_fam.retry = true;
  }

  if (typeof (fam_timeout) != "undefined") { clearTimeout(fam_timeout); }
  var timeout = setTimeout(saveTimeout, 20000);
  fam_timeout = timeout;
}

function insertFam(data, list = "") {
  if (data.status == "success") {
    var f = document.getElementById('familie-list'),
      q = data.query;

    while (f.lastChild) {
      f.lastChild.removeEventListener('click', selectFam);
      f.removeChild(f.lastChild);
    }
    tdd_fam_curr = data.query;
    if (list !== "") { q = list.query; tdd_fam_curr = q; }

    for (var i = 0; i < q.length; i++) {
      var e = document.createElement('li');
      e.value = i;
      var name = unescape(q[i].Name);
      if (name.trim() == "") { name = " - "; }
      if (q[i].Num) name = q[i].Num + "/ " + name;
      var t = document.createTextNode(name);
      e.appendChild(t);
      f.appendChild(e);
    }

    famList();

  } else { console.debug(data); }
}


//Verwaltung-tab
function verwList() {
  var l = document.getElementById('verwaltung-list');
  var lis = l.children;
  for (var i = 0; i < lis.length; i++) {
    lis[i].removeEventListener('click', selectFamV);
    if (lis[i].getAttribute('value') !== null) {
      lis[i].addEventListener('click', selectFamV);
    }
  }
}

function selectFamV() {
  if (typeof (verw_index) != "undefined") {
    var cf = verw_index;
    var ce = jQuery('ul#verwaltung-list li[value=' + cf + ']')[0];
    if (typeof (ce) !== "undefined") { ce.classList.remove('selected'); }
  }
  if (typeof (verw_fam) == "undefined" || (typeof (verw_fam) != "undefined" && verw_fam.saved)) {
    if (this !== window) {
      this.classList.add('selected');
      verw_fam = new familie(tdd_fam_curr[this.value], this.value);
      verw_index = this.value;
    }
    displayFamV(verw_fam);
    var bs = jQuery('#verw-save');
    bs.off('click');
    bs.on('click', function () { verw_fam.save('verwaltung'); });
  }
}

function displayFamV(e) {
  var f = getVerwForm(),
    d = e.data;

  if (typeof (d) == "undefined") { return; }
  jQuery('#verw-save, #verw-del').prop('disabled', false)
    .css('display', 'inline-block');
  jQuery('#verw-neu').css('display', 'none');

  var ort = -1;
  jQuery.each(tdd_orte, function (i, e) { if (e.Name == d.Ort) { ort = i; } });
  e.ort = ort;

  var o = f[0].get(0);

  var g = o;
  while (g.lastChild) {
    g.lastChild.removeEventListener('click', selectFamV);
    g.removeChild(g.lastChild);
  }

  var oa = tdd_orte;
  for (var i = 0; i < oa.length; i++) {
    var el = document.createElement('option');
    el.value = i;
    var t = document.createTextNode(unescape(oa[i].Name));
    el.appendChild(t);
    o.appendChild(el);
  }

  ortChangeV();

  f[0].val(e.ort);
  if (d.Gruppe != 0) { f[1].val(d.Gruppe) };
  if (d.lAnwesenheit != "0000-00-00") {
    var date = d.lAnwesenheit;
  } else { var date = ""; }
  f[2].val(date);
  f[3].val(d.Karte);
  f[4].val(d.Erwachsene);
  f[5].val(d.Kinder);
  f[6].html(d.ID);
  f[7].val(d.Schulden);
  f[8].val(unescape(d.Notizen));
  f[9].val(unescape(d.Name));
  f[10].val(d.Num);
  f[11].val(unescape(d.Adresse));
  f[12].val(unescape(d.Telefonnummer));
  jQuery('#barcode').JsBarcode(num_pad(d.ID, 6), { height: 28, width: 1, textMargin: 0, fontSize: 11, background: 0, marginLeft: 15, marginRight: 15, margin: 0, displayValue: true });

  jQuery(f).each(function (i, e) { e.prop('disabled', false); });
}

function savedVerw(data) {
  if (data.status == "success") {
    searchV(searchFamV);

    verw_fam = new familie(verw_neu.newdata, verw_neu.index);
    verw_fam.saved = true;
    console.debug(verw_fam, 'saved');

    tdd_fam_curr[verw_fam.index] = clone(verw_fam.data);

    displayFamV(verw_fam);
    verw_neu = undefined;

    jQuery('#verw-save, #verw-del').prop('disabled', false);
  } else { console.debug(data); saveErrorV(data); }
}

function savedVerwN(data) {
  if (data.status == "success") {
    console.debug(data);
    if (data.ID) {
      searchV(searchFamV);

      verw_fam = new familie({ ID: data.ID }, -1);
      delete verw_neu.newdata.ID;
      jQuery.each(verw_neu.newdata, function (i, e) { verw_fam.data[i] = e; });
      verw_fam.saved = true;
      console.debug(verw_fam, 'saved');

      try {
        tdd_orte[tdd_ort[verw_fam.data.Ort]].Personen[verw_fam.data.Gruppe]++;
      } catch (e) { }
      displayFamV(verw_fam);
      verw_neu = undefined;

      var i = tdd_fam_neu.query.length;
      tdd_fam_neu.query[i] = verw_fam.data;
      tdd_fam_neu.query.length = ++i;

      jQuery('#verw-save, #verw-del, #verw-neu').prop('disabled', false)
        .css('display', 'inline-block');
      jQuery('#verw-neu').css('display', '');
    } else {
      alert("<p>Keine ID bekommen...<br>Bitte neu probieren.</p>", "Fehler");
    }
  } else { saveErrorV(data); }
}

function saveErrorV(data) {
  if (data.status == "success") {
    console.debug("Something went wrong.......\n", data);
  } else { console.debug(data); alert("<p>Fehler beim Speichern:</p><p><b>" + data.post.set.Name + "</b></p><p>Mehr in der Konsole</p>", "Achtung!"); }
}

function delFamV(data) {
  if (data.status == "success") {
    if (data.rows == 1) {
      var f = getVerwForm();
      resetForm(f);
      jQuery('#verw-barcode').attr('src', '');
      searchV(searchFamV);
    } else { console.debug(data); }
  } else { console.debug(data); }
}

function famInV() {
  if (selected_fam != "undefined") {
    fam_for_v = selected_fam;
    var tabHs = document.getElementById('tab-head').firstElementChild.children;
    a = tabHs[2].getElementsByTagName('a')[0];
    changeTab(a);
  }
}
function jumpToV() {
  verw_fam = new familie(fam_for_v.data, -1);
  selectFamV();
  fam_for_v = undefined;
}

function verwFamNeu() {
  var f = getVerwForm();
  resetForm(f);
  jQuery(f).each(function (i, e) { e.prop('disabled', false); });

  var bn = jQuery('#verw-neu');
  jQuery('#verw-save, #verw-del').css('display', 'none');
  bn.css('display', 'inline-block');
  bn.prop('disabled', false);
  bn.off('click');
  bn.on('click', function () { verw_fam.save('verwaltung-neu'); });

  var ort = -1;

  var o = f[0].get(0);

  var g = o;
  while (g.lastChild) {
    g.lastChild.removeEventListener('click', selectFamV);
    g.removeChild(g.lastChild);
  }

  var oa = tdd_orte;
  for (var i = 0; i < oa.length; i++) {
    var el = document.createElement('option');
    el.value = i;
    var t = document.createTextNode(unescape(oa[i].Name));
    el.appendChild(t);
    o.appendChild(el);
  }

  ortChangeV();

  if (typeof (verw_index) !== "undefined") {
    var cf = verw_index;
    var ce = jQuery('ul#verwaltung-list li[value=' + cf + ']')[0];
    if (typeof (ce) !== "undefined") { ce.classList.remove('selected'); }
  }
  verw_fam = new familie({}, -1);
  verw_index = undefined;
}


//Logstab
function displayLogs() {
  var lf = jQuery('#log-from'), lt = jQuery('#log-to');

  var d = new Date();
  d.setUTCDate(1); d.setUTCHours(0); d.setUTCMinutes(0); d.setUTCSeconds(0);
  lf.val(d.toISOString().replace(/\.[0-9]{3}Z/, ""));

  var d = new Date();
  d.setUTCMonth(d.getUTCMonth() + 1); d.setUTCDate(0); d.setUTCHours(23); d.setUTCMinutes(59); d.setUTCSeconds(59);
  lt.val(d.toISOString().replace(/\.[0-9]{3}Z/, ""));

  getEinnahmen();
  getLogs();
}

function getEinnahmen() {
  var lf = jQuery('#log-from'), lt = jQuery('#log-to');
  var d1 = lf.val().replaceAll(':', '.');
  var d2 = lt.val().replaceAll(':', '.');
  get({ table: 'logs', meta: [{ key: 'date_time', value: d1, compare: '>=' }, { key: 'date_time', value: d2, compare: '<=' }, { key: 'aff_table', value: 'familien' }, { key: 'action', value: 'UPDATE' }] }, einnahmenC);
}
function einnahmenC(data) {
  var ein = jQuery('#einnahmen'),
    kinder = jQuery('#log_kinder'),
    erw = jQuery('#log_erw');

  if (data.status == "success") {
    var g = 0, k = 0, e = 0;

    for (var i = 0; i < data.query.length; i++) {
      var j = JSON.parse(data.query[i].message);
      if (typeof (j.geld) != "undefined" && j.geld != "NaN") {
        g += +j.geld;
      }
      if (j.post && typeof (j.post.anw) != "undefined" && j.post.anw == "true") {
        // only count if properly set to "anwesend"
        if (j.post && j.post.set) {
          if (j.post.set.Kinder && +j.post.set.Kinder != NaN) {
            k += +j.post.set.Kinder;
          }
          if (j.post.set.Erwachsene && +j.post.set.Erwachsene != NaN) {
            e += +j.post.set.Erwachsene;
          }
        }
      }
    }

    ein.html(g.toFixed(2) + "€");
    kinder.html(k);
    erw.html(e);

  } else { ein.html("<i>Fehler</i>"); console.debug(data); }
}

function getLogs(page = 0, search = []) {
  if (page != null && typeof (page) == "object" && page.target) {
    //page is change event
    page = page.target.value;
  }
  var lgs = document.getElementById('complete-log');
  if ((!Array.isArray(search) || search.length == 0) && typeof (lgs.search) !== "undefined") {
    search = lgs.search;
  }
  get({ table: 'logs', meta: search, limit: 20, offset: page * 20, order_by: 'date_time', page: page, meta_connection: "OR" }, logs);
}
function searchLogs(element) {
  var meta = [],
    string = escape(element[0].value),
    h = element.headings,
    lgs = document.getElementById('complete-log');

  string = string.replace(/^(?:%20)+|(?:%20)+$/g, '').replace(/(?:%20){2,}/g, '%20');
  var a = string.split('%20');

  for (var i = 0; i < a.length; i++) {
    var str = a[i];
    str = "%" + str + "%";
    for (var j = 0; j < h.length; j++) {
      meta.push({ key: h[j], value: str, compare: "LIKE" });
    }
  }

  lgs.search = meta;
  getLogs(0, meta);
}
function logs(data) {
  var p = jQuery('#log-pagination'), lgs = jQuery('#complete-log'),
    page = data.post.page, pages = Math.ceil(data.rows / 20);

  if (p.children().length != pages || data.status != "success") {
    var o = p.get(0);
    while (o.lastChild) {
      o.removeChild(o.lastChild);
    }
    for (var i = 0; i < pages; i++) {
      var e = document.createElement('option');
      e.value = i;
      var t = document.createTextNode('Seite ' + (i + 1));
      e.appendChild(t);
      p.append(e);
    }
    o.removeEventListener('click', getLogs);
    o.addEventListener('change', getLogs);
    p.val(page);
  }
  if (p.val() != page) { p.val(page) };

  lgs.html('');
  if (data.status == "success") {
    if (data.query.length > 0) {
      var form = document.createElement('form');
      form.action = "#";
      form.onsubmit = function () { searchLogs(this); return false };
      form.innerHTML = "<input type=\"text\" placeholder=\"Suchen\"><input type=\"submit\" value=\"Suchen\">";
      form.headings = [];
      var table = document.createElement('table');
      table.classList.add('logs');
      var tbody = document.createElement('tbody');
      table.appendChild(tbody);
      var tr = document.createElement('tr');
      jQuery.each(data.query[0], function (e) {
        var th = document.createElement('th');
        var t = document.createTextNode(e);
        form.headings.push(e);
        th.appendChild(t);
        tr.appendChild(th);
      });
      tbody.appendChild(tr);
      for (var i = 0; i < data.query.length; i++) {
        var tr = document.createElement('tr');
        jQuery.each(data.query[i], function (e, n) {
          var td = document.createElement('td');
          var t = document.createTextNode(n);
          td.appendChild(t);
          tr.appendChild(td);
        });
        tbody.appendChild(tr);
      }
      lgs.append(form);
      lgs.append(table);
    }
  }
}


//Settingstab
function displaySettings() {
  var f = getSettForm();
  var s = tdd_settings;
  for (var i = 0; i < f.length; i++) {
    var n = f[i].data("name");
    if (typeof (n) !== "undefined") {
      if (typeof (f[i].html) == "function") { f[i].html(unescape(s[n])); }
      if (typeof (f[i].val) == "function") { f[i].val(unescape(s[n])); }
    }
  }
}

function displayOrte() {
  var o = document.getElementById('orte');
  //o.style.height = '';

  while (o.lastChild) {
    o.lastChild.removeEventListener('click', selectOrt);
    o.removeChild(o.lastChild);
  }

  var os = tdd_orte;
  for (var i = 0; i < os.length; i++) {
    var el = document.createElement('li');
    el.value = i;
    var t = document.createTextNode(unescape(os[i].Name));
    el.appendChild(t);

    var sp = document.createElement('span');
    sp.classList.add('list-add');
    var it = document.createTextNode('ID:' + os[i].ID);
    sp.appendChild(it);
    el.appendChild(sp);

    var div = document.createElement('div');
    div.classList.add('expand');
    var inp = document.createElement('input');
    inp.type = 'number';
    inp.value = os[i].Gruppen;
    inp.placeholder = "Gruppen";
    div.appendChild(inp);
    div.appendChild(document.createElement('br'));

    var b = document.createElement('button');
    b.addEventListener('click', ortSave);
    var t = document.createTextNode('Speichern');
    b.appendChild(t);
    div.appendChild(b);

    var b = document.createElement('a');
    b.addEventListener('click', ortDel);
    b.classList.add('link-delete');
    b.classList.add('ml15px');
    b.href = '#';
    var t = document.createTextNode('Löschen');
    b.appendChild(t);
    div.appendChild(b);

    el.appendChild(div);
    el.name = unescape(os[i].Name);
    o.appendChild(el);
  }

  var el = document.createElement('li');
  el.value = -1;
  el.style.textAlign = 'center';
  var t = document.createTextNode('+');
  el.appendChild(t);
  o.appendChild(el);

  //o.style.height = jQuery(o).outerHeight();

  var lis = o.children;
  for (var i = 0; i < lis.length; i++) {
    lis[i].removeEventListener('click', selectOrt);
    if (lis[i].getAttribute('value') !== null) {
      lis[i].addEventListener('click', selectOrt);
    }
  }
}

function saveSettings(element = null) {
  if (element == null) {
    var f = getSettForm();
  } else {
    var f = [jQuery(element)];
  }
  for (var i = 0; i < f.length; i++) {
    var n = f[i].data("name");
    var v = "";
    if (typeof (n) !== "undefined") {
      if (typeof (f[i].html) == "function") { v = escape(f[i].html()); }
      if (typeof (f[i].val) == "function") { v = escape(f[i].val()); }
    }
    update({ table: "Einstellungen", meta: { key: "Name", value: n }, set: { Val: v } }, savedSett);
  }
}

function savedSett(data) {
  if (data.status == "success") {
    console.debug(data, 'saved');
    getSettings();
  } else { console.debug(data); }
}

function selectOrt(event) {
  if (event.target == this && event.target.value == -1) {
    insert({ table: 'Orte', set: { ID: 'NULL', Name: "" } }, ortInsert);
    return;
  }
  if (event.target == this || event.target.tagName == "DIV") {
    if (!this.classList.contains('expanded')) {
      var t = this.firstChild;
      var inp = document.createElement('input');
      inp.value = this.name;
      inp.placeholder = "Name";
      this.replaceChild(inp, t);
    } else {
      var i = this.firstChild;
      var t = document.createTextNode(this.name);
      this.replaceChild(t, i);
    }
    this.classList.toggle('expanded');
  }
}

function ortInsert(data) {
  if (data.status == "success") {
    getOrte();
    displayOrte();
    changeTab(currentTab);
  } else { console.debug(data); }
}

function ortSave() {
  var li = this.parentNode.parentNode;
  var i = li.value;
  var id = tdd_orte[i].ID;

  var set = {};
  var ins = li.getElementsByTagName('input');
  set['Name'] = escape(ins[0].value);
  set['Gruppen'] = ins[1].value;

  console.debug(set, 'saving');
  update({ table: "Orte", meta: { key: "ID", value: id }, set: set, val: i }, savedOrt);
}

function savedOrt(data) {
  if (data.status == "success") {
    console.debug(data, 'saved');
    var li = jQuery('ul#orte li[value=' + data.post.val + ']');
    li.prop('name', unescape(data.post.set.Name));
    li.children()[2].children[0].value = +data.post.set.Gruppen;
    li.click();
  } else { console.debug(data); }
}

function ortDel(event) {
  var li = this.parentNode.parentNode;
  var i = li.value;
  var id = tdd_orte[i].ID;

  remove({ table: "Orte", meta: { key: "ID", value: id }, val: i }, removedOrt);
  event.preventDefault();
}

function removedOrt(data) {
  if (data.status == "success") {
    var i = data.post.val;
    delete tdd_orte[i];

    var li = jQuery('#orte li[value="' + i + '"]');
    li = li.get(0);
    li.parentElement.removeChild(li);
  } else { console.debug(data); }
}

function delFamDate(date = -1, column = 'lAnwesenheit') {
  if (date == -1) {
    date = new Date();
    //8 weeks
    date.setTime(date.getTime() - 8 * 7 * 24 * 1000 * 3600);
  }
  if (typeof (date) == "number") {
    var n = date;
    date = new Date();
    //n days
    date.setTime(date.getTime() - n * 24 * 1000 * 3600);
  }
  if (typeof (date) == "object") {
    date = formatDate(date);
  }
  if (typeof (date) == "string") {
    remove({ table: 'Familien', meta: [{ key: column, value: date, compare: "<=" }, { key: column, value: "0000-00-00", compare: "<>" }] }, famBulkDel);
  } else {
    console.debug(date, "is no string");
  }
}

function famBulkDel(data) {
  if (data.status == "success") {
    alert(data.rows + " Einträge gelöscht.", "Fertig")
  } else { console.debug(data); }
}


//Eventlisteners dropdown-menus
function ortChange() {
  var g = document.getElementById('gruppe-select');
  while (g.lastChild) {
    g.removeChild(g.lastChild);
  }

  var os = document.getElementById('ort-select').selectedOptions[0];
  if (typeof (os) != "undefined") {
    var o = tdd_orte[os.value];
    ort = o.Name;

    for (var i = 1; i <= +o.Gruppen; i++) {
      var e = document.createElement('option');
      e.value = i;
      var t = document.createTextNode('Gruppe ' + i);
      e.appendChild(t);
      g.appendChild(e);
    }
    var e = document.createElement('option');
    e.value = "0";
    var t = document.createTextNode('Neu');
    e.appendChild(t);
    g.appendChild(e);

    gruppeChange();
  }
}

function gruppeChange() {
  if (typeof (selected_fam) !== "undefined") {
    selected_fam.save();
  }
  var o = typeof (document.getElementById('ort-select').selectedOptions[0]),
    g = typeof (document.getElementById('gruppe-select').selectedOptions[0]);

  var f = document.getElementById('familie-list');
  while (f.lastChild) {
    f.lastChild.removeEventListener('click', selectFam);
    f.removeChild(f.lastChild);
  }

  if (currentTab.getAttribute('href') == '#tab2') {
    if (o !== "undefined" && g !== "undefined") {
      gruppe = document.getElementById('gruppe-select').selectedOptions[0].value;
      if (gruppe == "0") {
        insertFam({ status: "success" }, tdd_fam_neu);
        return;
      }

      get({ table: "familien", meta: [{ key: "Ort", value: ort }, { key: "Gruppe", value: gruppe }], order_by: "Num, ID" }, insertFam);
    } else {
      search(searchFamA);
    }
  }
}

function ortChangeV() {
  var f = getVerwForm();

  var o = f[1].get(0),
    ort = f[0].val();

  while (o.lastChild) {
    o.removeChild(o.lastChild);
  }

  var oa = tdd_orte[ort] && tdd_orte[ort].Gruppen || 0;
  var g = 1, p = Infinity;
  for (var i = 1; i <= oa; i++) {
    var el = document.createElement('option');
    el.value = i;
    var t = document.createTextNode('Gruppe ' + i);
    el.appendChild(t);
    o.appendChild(el);
    if (tdd_orte[ort].Personen[i] < p) {
      g = i;
      p = tdd_orte[ort].Personen[i];
    }
  }
  o.value = g;
}


//Search for familien and verwaltung tabs
function search(c) {
  if (typeof (selected_fam) !== "undefined") {
    selected_fam.save();
  }

  jQuery('#ort-select').prop("value", -1);
  jQuery('#gruppe-select').prop("value", -1);

  var g = document.getElementById('gruppe-select');
  while (g.lastChild) {
    g.removeChild(g.lastChild);
  }

  var f = document.getElementById('familie-list');
  while (f.lastChild) {
    f.lastChild.removeEventListener('click', selectFam);
    f.removeChild(f.lastChild);
  }

  var f = getFamForm();
  jQuery('#barcode').attr('src', '');
  resetForm(f);
  selected_fam = undefined;

  var s = document.getElementById('familie-search').value;
  getSearch(s, c);
}

function searchV(c) {
  var f = document.getElementById('verwaltung-list');
  while (f.lastChild) {
    f.lastChild.removeEventListener('click', selectFamV);
    f.removeChild(f.lastChild);
  }

  var f = getVerwForm();
  jQuery('#barcode').attr('src', '');
  resetForm(f);
  verw_fam = undefined;

  var s = document.getElementById('verwaltung-search').value;
  getSearch(s, c);
}

//Search-callbacks
function searchFamA(data) {
  var f = document.getElementById('familie-list');
  while (f.lastChild) {
    f.lastChild.removeEventListener('click', selectFam);
    f.removeChild(f.lastChild);
  }
  searchFam(data, f);
  famList((data.post.byid == "true"));
}

function searchFamV(data) {
  var f = document.getElementById('verwaltung-list');
  while (f.lastChild) {
    f.lastChild.removeEventListener('click', selectFam);
    f.removeChild(f.lastChild);
  }
  searchFam(data, f);
  verwList();

  if (typeof (verw_fam) !== "undefined") {
    jQuery('#verwaltung-list li[value="' + verw_fam.index + '"]').addClass('selected');
  }
}

function searchFam(data, f) {
  if (data.status == "success") {
    var q = data.query,
      co = "",
      cg = 0;

    tdd_fam_curr = q;

    for (var i = 0; i < q.length; i++) {
      if (co !== q[i].Ort || cg !== q[i].Gruppe) {
        var e = document.createElement('li');
        e.classList.add('title');
        var t = document.createTextNode(unescape(q[i].Ort) + ", Gruppe " + q[i].Gruppe);
        e.appendChild(t);
        f.appendChild(e);

        co = q[i].Ort;
        cg = q[i].Gruppe;
      }
      var e = document.createElement('li');
      e.value = i;
      var name = unescape(q[i].Name);
      if (name.trim() == "") { name = " - "; }
      if (q[i].Num) name = q[i].Num + "/ " + name;
      var t = document.createTextNode(name);
      e.appendChild(t);
      f.appendChild(e);
    }

  } else { console.debug(data); }
}


function postFamKarte(familie) {
  if (typeof (familie) !== "undefined" && familie !== {}) {
    var e = escape(tdd_settings["Kartendesigns"]);
    var fe = '<input type="hidden" name="designs" value="' + e + '" />';
    var d = familie.data;
    d.Preis = preis(+d.Erwachsene, +d.Kinder).toFixed(2);
    var s = document.getElementById('barcode').src;
    d.isrc = s;
    d.img = "<img src=\"" + s + "\" />";
    e = escape(JSON.stringify(d));
    fe += '<input type="hidden" name="familie" value="' + e + '" />';
    var frmName = "frm" + new Date().getTime();
    var url = "?karte";
    var form = '<form name="' + frmName + '" method="post" target="karte" action="' + url + '">' + fe + '</form>';

    var wrapper = document.createElement("div");
    wrapper.innerHTML = form;
    document.body.appendChild(wrapper);
    document.forms[frmName].submit();
    wrapper.parentNode.removeChild(wrapper);
  }

}


function backupComplete(data) {
  if (data.status == "success") {
    var t = "<p><i>Alles problemlos!</i></p><p>Datenbank " + data.db + " enthält alle Daten.</p>";
    alert(t, "Fertig", "Backup");
  } else {
    var t = "<p><i style='color:red'>Fehler sind aufgetreten!</i></p><br><p>" + JSON.stringify(data) + "</p>";
    alert(t, "FEHLER", "Backup");
    console.debug(data);
  }
}

function resetComplete(data) {
  if (data.status == "success") {
    var t = "<p><i>Alles problemlos!</i></p><p>Alle Familien wurden neu durchnummeriert.</p>";
    alert(t, "Fertig", "Reset Nummern");
  } else {
    var t = "<p><i style='color:red'>Fehler sind aufgetreten!</i></p><br><p>" + JSON.stringify(data) + "</p>";
    alert(t, "FEHLER", "Reset Nummern");
    console.debug(data);
  }
}


function saveTimeout() {
  if (typeof (selected_fam) != "undefined" && keyboard_timeout) {
    selected_fam.save();
    fam_timeout = undefined;
  } else if (typeof (selected_fam) != "undefined" && !keyboard_timeout) {
    fam_timeout = setTimeout(saveTimeout, 500);
  }
}


function schuldfieldChange() {
  jQuery('#fam-gv, #fam-sb').prop('disabled', true);
  if (this.value >= selected_fam.preis * 3 && !selected_fam.schuld) {
    jQuery('#fam-szh').html("Darf nächstes Mal nur noch nach Begleichen der Schulden hinein.");
  } else {
    jQuery('#fam-szh').html("");
  }
  if (selected_fam.schuld) {
    if (this.value == 0) {
      jQuery('#fam-anw').prop('disabled', false);
    } else {
      jQuery('#fam-szh').html("Schulden zu hoch! Muss erst Schulden begleichen!");
    }
  }
}


//Calculate price
function preis(erwachsene = 0, kinder = 0) {
  var s = tdd_settings.Preis;
  if (typeof (s) == "undefined") { return -1; }
  s = s.replaceAll('e', +erwachsene);
  s = s.replaceAll('k', +kinder);
  s = s.replace(/[^0-9\+\-\*\/\(\)\.><=]/g, '');
  try {
    return eval(s);
  } catch (e) {
    console.debug(tdd_settings.Preis + ' invalide Preis-Formel (' + e + ')');
    alert("<p>Fehler in der Preis-Formel!<br>" + e + "</p>", "Fehler");
  }
}


//Familiy-construct
function familie(data, index) {
  this.data = data;
  this.saved = true;
  this.index = index;
}
familie.prototype.save = function (tab = 'familie') {
  if (this.saved) {
    tab = tab.split('-');
    opt = tab.splice(1).join('-');
    tab = tab[0];

    var f;
    if (tab == 'familie') { f = getFamForm(); }
    if (tab == 'verwaltung') { f = getVerwForm(); }
    var d = this.data || {},
      nd = {},
      preist = false;

    //Find new familiy data
    nd.ID = d.ID;
    if (tab == 'familie') {
      nd.Name = d.Name;
      nd.Erwachsene = d.Erwachsene;
      nd.Kinder = d.Kinder;
      nd.Adresse = d.Adresse;
      nd.Telefonnummer = d.Telefonnummer;
    } else if (tab == 'verwaltung') {
      nd.Name = escape(f[9].val());
      nd.Erwachsene = f[4].val();
      nd.Kinder = f[5].val();
      nd.Adresse = escape(f[11].val());
      nd.Telefonnummer = escape(f[12].val());
    }

    if (tab == 'familie') {
      nd.Ort = d.Ort;
      nd.Gruppe = d.Gruppe;
      nd.Num = d.Num;
    } else if (tab == 'verwaltung') {
      try {
        nd.Ort = tdd_orte[f[0].val()].Name;
      } catch (e) { }
      nd.Gruppe = f[1].val();
      nd.Num = f[10].val();

      // if not manually changed but moved to different group/location
      // then let mysql update num
      if (
        nd.Num == "" || nd.Num == "0" ||
        (nd.Num == d.Num && (nd.Ort != d.Ort || nd.Gruppe != d.Gruppe))) {
        nd.Num = "newNum('" + nd.Ort + "'," + nd.Gruppe + ")";
      }
    }

    var s = +f[7].val();
    if (tab == 'familie') {
      // schulden beglichen
      if (f[11].prop("checked")) {
        s = 0;
        f[11].prop("checked", false);
      }
      // geld vergessen
      if (f[10].prop("checked")) {
        s += +this.preis;
        f[10].prop("checked", false);
      }
    }
    nd.Schulden = s.toFixed(2);
    var pr = -(+nd.Schulden - +d.Schulden);

    nd.Karte = f[3].val();

    // anwesend
    if (tab == 'familie' && !f[9].prop("checked")) {
      var date = d.lAnwesenheit;
    } else if (tab == 'familie' && f[9].prop("checked")) {
      var date = formatDate(new Date());
      if (date != d.lAnwesenheit) { preist = true; }
    } else if (tab == 'verwaltung') {
      var date = f[2].val();
    }
    nd.lAnwesenheit = date;

    nd.Notizen = escape(f[8].val());
    if (opt == 'neu') { nd.Notizen = ""; }

    //Save if new/updated
    if (!(JSON.stringify(nd) == JSON.stringify(d)) && opt !== 'neu') {
      if (typeof (d.ID) == "undefined") { alert("<p>Konnte nicht speichern, ID wurde nicht gefunden!</p><p>Möglicherweise hilft es, eine andere Person zu speichern, ansonsten bitte neu laden (Familien-Anzahl nicht vergessen).</p>", "Fehler"); console.debug('Error saving', this, '\nCould not find ID'); return; }
      if (tab == 'familie') {
        clearTimeout(fam_timeout);

        this.newdata = clone(nd);
        this.saved = false;
        console.debug(this, 'saving');
        var i = tdd_unsaved_queue.push(this) - 1;

        delete nd.ID;
        var pr0 = +this.preis;
        if (preist) { pr += pr0; }

        td = { table: "familien", id: d.ID, meta: { key: "ID", value: d.ID }, set: nd, preis: pr, anw: preist };
        update(td, ["tdd_unsaved_queue", "callback"]);
        jQuery(f).each(function (i, e) { e.prop('disabled', true); });

      } else if (tab == 'verwaltung') {
        this.newdata = clone(nd);
        this.saved = false;
        console.debug(this, 'saving');

        delete nd.ID;

        jQuery('#verw-save, #verw-del').prop('disabled', true);
        update({ table: "familien", meta: { key: "ID", value: d.ID }, set: nd, preis: pr, anw: false }, savedVerw);
        jQuery(f).each(function (i, e) { e.prop('disabled', true); });

        verw_neu = this;

      }
    } else if (tab == 'verwaltung' && opt == 'neu') {
      this.newdata = clone(nd);
      this.saved = false;
      console.debug(this, 'saving');

      delete nd.ID;

      jQuery('#verw-neu').prop('disabled', true);
      insert({ table: "familien", set: nd, preis: pr, anw: false }, savedVerwN);
      jQuery(f).each(function (i, e) { e.prop('disabled', true); });

      verw_neu = this;

    } else {
      console.debug(this, 'already saved');
      if (typeof (fam_for_v) != "undefined") { setTimeout(jumpToV, 1); }

    }
  } else { console.debug(this, 'already saving'); }

};




String.prototype.replaceAll = function (search, replacement) {
  var target = this;
  return target.replace(new RegExp(search, 'g'), replacement);
};

function num_pad(num, size) {
  var s = num + "";
  while (s.length < size) s = "0" + s;
  return s;
}

function formatDate(date) {
  var d = new Date(date),
    month = '' + (d.getMonth() + 1),
    day = '' + d.getDate(),
    year = d.getFullYear();

  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;

  return [year, month, day].join('-');
}

function highlightElement(el) {
  el.addClass('highlight');
  setTimeout(function () {
    el.removeClass('highlight');
  }, 400);
}

function alert(text, title = "Meldung:", footer = "") {
  var m = modal;
  var h = m.getElementsByClassName('modal-head')[0];
  var f = m.getElementsByClassName('modal-foot')[0];
  var b = m.getElementsByClassName('modal-body')[0];

  b.innerHTML = text;
  h.innerHTML = title;
  f.innerHTML = footer;

  modal.style.display = "block";
  document.body.style.overflow = "hidden"
}

*/