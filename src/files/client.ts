import jQuery from 'jquery';
// @ts-ignore
export const JsBarcode = require('jsbarcode');

import polyfills from './client/polyfills';
import ortGenerate from './client/orte';
import { ausgabeFam, verwaltungFam } from './client/familie';
import initLogs from './client/log';
import { delFamDate, resetFam } from './client/actions';
import settings, { optionsSettingsUpdate } from './client/settings';
import { optionsOrteUpdate } from './client/settings_orte';
import insertHelpHeadings from './client/help';
import { tabH, alert } from './client/helpers';
import { karte_designs_help, preis_help } from './client/texts';

polyfills();


export const DEBUG = true;



// debug
if ( DEBUG ) {
  // @ts-ignore
  window.$ = jQuery;
  // @ts-ignore
  window.JsBarcode = JsBarcode;
  // @ts-ignore
  window.ausgabeFam = ausgabeFam;
  // @ts-ignore
  window.verwaltungFam = verwaltungFam;
  // @ts-ignore
  window.alert = alert;
  // @ts-ignore
  window.settings = settings;
}


export interface TabElement extends HTMLAnchorElement {
  onClose: () => void;
  onOpen: () => void;
}



// load window
jQuery(($) => {
  // init tabs
  const $tabHs = $('#tab-head li');
  let $current_tab: JQuery<TabElement>;
  
  const tabLinks = $tabHs.find('a') as JQuery<TabElement>
  tabLinks.each((_, element) => {
    element.onClose = () => { };
    element.onOpen = () => { };
  });

  const changeTab = (link: JQuery<TabElement>) => {
    if ($current_tab) {
      // close tab and hide
      $current_tab.get(0).onClose();
      $($current_tab.attr('href')).css('display', 'none');
      $current_tab.removeClass('selected');
    }

    // open tab and show
    $(link.attr('href')).css('display', 'block')
    link.addClass('selected');
    link.get(0).onOpen();
    $current_tab = link;

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

  // Ausgabe + Verwaltung Tabs

  // load forms and reset
  ausgabeFam.linkHtml();
  ausgabeFam.clear();
  ausgabeFam.disable();
  verwaltungFam.linkHtml();
  verwaltungFam.clear();
  verwaltungFam.disable();

  const $os_select = $('#tab2 .search-header select, #tab3 .familie-data select');
  
  // Logs tab
  const { info: updateLogInfo, logs: updateLogs } = initLogs();
  tabLinks.get(3).onOpen = () => { updateLogInfo(); updateLogs(); };
  
  // Settings tab
  const loadOrte = ortGenerate($os_select);
  const updateOrte = optionsOrteUpdate(loadOrte);
  const updateSettings = optionsSettingsUpdate();
  tabLinks.get(4).onClose = () => {updateOrte(); updateSettings();};

  const $sett_help = $('#tab5 .help');
  $sett_help.eq(0).on('click', () => alert(preis_help, "Hilfe zur Preisformel"));
  $sett_help.eq(1).on('click', () => alert(karte_designs_help, "Hilfe zu Kartendesigns"));
  const $sett_actions = $('#actions button');
  $sett_actions.eq(0).on('click', () => delFamDate());
  $sett_actions.eq(1).on('click', () => delFamDate(-1, 'Karte'));
  $sett_actions.eq(2).on('click', () => resetFam());
  $sett_actions.eq(3).on('click', () => window.open('?page=backup/create'));
  $sett_actions.eq(4).on('click', () => window.open('?page=backup/load'));

  updateOrte();
  updateSettings();

  // Help tab
  insertHelpHeadings();


  // Init tab
  if (window.location.hash == "") {
    changeTab($tabHs.first().find('a') as JQuery<TabElement>);
  } else {
    changeTab($tabHs.find(`a[href="${window.location.hash}"]`) as JQuery<TabElement>);
  }
  $tabHs.on('click', 'a', function (e) { changeTab($(this)); });
  tabH();


  // register handlers
  $(window).resize(tabH); //.on('keydown', keyboardHandler);

  // keyboard navigation
  function keyboardHandler(event: JQuery.KeyDownEvent) {
    console.log(event, event.which, event.key);
  }
});


/*


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

*/