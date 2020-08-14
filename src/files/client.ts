import jQuery from 'jquery';
// @ts-ignore
export const JsBarcode = require('jsbarcode');

import polyfills from './client/js/polyfills';
import ortGenerate from './client/js/orte';
import searchGenerate from './client/js/search';
import initLogs from './client/js/log';
import { delFamDate, resetFam } from './client/js/actions';
import settings, { optionsSettingsUpdate, orte } from './client/js/settings';
import { optionsOrteUpdate } from './client/js/settings_orte';
import insertHelpHeadings from './client/js/help';
import { tabH, alert } from './client/js/helpers';
import { karte_designs_help, preis_help } from './client/js/texts';
import { ausgabeFam } from './client/js/familie_ausgabe';
import { verwaltungFam } from './client/js/familie_verwaltung';

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
  // @ts-ignore
  window.orte = orte;
}


export interface TabElement extends HTMLAnchorElement {
  onClose: () => void;
  onOpen: () => void;
}



// load window
jQuery(($) => {
  $('button, [type="button"]').on('mouseup', function (e) {
    $(this).blur(); // remove focus
  });
  
  // init tabs
  const $tabHs = $('#tab-head li');
  let $current_tab: JQuery<TabElement>;

  changeTab = ($link: JQuery<TabElement>) => {
    if ($current_tab) {
      // close tab and hide
      $current_tab.get(0).onClose();
      $($current_tab.attr('href')).css('display', 'none');
      $current_tab.removeClass('selected');
    }

    // open tab and show
    $($link.attr('href')).css('display', 'block')
    $link.addClass('selected');
    $link.get(0).onOpen();
    $current_tab = $link;

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
  
  const tabLinks = $tabHs.find('a') as JQuery<TabElement>
  tabLinks.each((_, element) => {
    element.onClose = () => { };
    element.onOpen = () => { };
  });

  // Ausgabe + Verwaltung Tabs

  // load forms and reset
  ausgabeFam.linkHtml();
  ausgabeFam.clear();
  verwaltungFam.linkHtml();
  verwaltungFam.clear();

  const $os_select = $('#tab2 .search-header select, #tab3 .familie-data select') as JQuery<HTMLSelectElement>;
  const $forms = $('#tab2 .search-header form, #tab2 .select-list ul, #tab3 .search-header form, #tab3 .select-list ul');
  
  const loadOrte = ortGenerate($os_select);
  const { ausgSearch, verwSearch } = searchGenerate($os_select.slice(0, 2), $forms, loadOrte);
  tabLinks.get(1).onOpen = ausgSearch;
  tabLinks.get(2).onOpen = verwSearch;
  
  // Logs tab
  const { info: updateLogInfo, logs: updateLogs } = initLogs();
  tabLinks.get(3).onOpen = () => { updateLogInfo(); updateLogs(); };
  
  // Settings tab
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
export let changeTab: ($link: JQuery<TabElement>) => void;


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

*/