import jQuery from 'jquery';
import JsBarcode from 'jsbarcode';
import { delFamDate, resetFam } from './client/js/actions';
import request from './client/js/api';
import { ausgabeFam } from './client/js/familie_ausgabe';
import { verwaltungFam } from './client/js/familie_verwaltung';
import insertHelpHeadings from './client/js/help';
import { alert, tabH } from './client/js/helpers';
import initLogs from './client/js/log';
import ortGenerate from './client/js/orte';
import polyfills from './client/js/polyfills';
import searchGenerate from './client/js/search';
import settings, { optionsSettingsUpdate, orte } from './client/js/settings';
import { optionsOrteUpdate } from './client/js/settings_orte';
import { karte_designs_help, preis_help } from './client/js/texts';
export { JsBarcode };


polyfills();


export const DEBUG = false;



// @ts-ignore
window.$ = jQuery;
// debug
if (DEBUG) {
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
  // @ts-ignore
  window.request = request;
}


export interface TabElement extends HTMLAnchorElement {
  onClose: () => void;
  onOpen: () => void;
}



// load window
jQuery(($) => {
  $('button, [type="button"]').on('mouseup', function () {
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
  const $card = $('#card-modal');
  const $cardframe = $card.find('.card-frame') as JQuery<HTMLIFrameElement>;

  const $os_select = $('#tab2 .search-header select, #tab3 .familie-data select') as JQuery<HTMLSelectElement>;
  const $forms = $('#tab2 .search-header form, #tab2 .select-list ul, #tab3 .search-header form, #tab3 .select-list ul');

  const loadOrte = ortGenerate($os_select);
  const { ausgSearch, verwSearch } = searchGenerate($os_select.slice(0, 2), $forms, loadOrte);
  tabLinks.get(1).onOpen = ausgSearch;
  tabLinks.get(2).onOpen = verwSearch;

  // load forms and reset
  ausgabeFam.linkHtml($card);
  ausgabeFam.clear();
  verwaltungFam.linkHtml($card);
  verwaltungFam.setSearch(verwSearch);
  verwaltungFam.clear();

  // Logs tab
  const { info: updateLogInfo, logs: updateLogs } = initLogs();
  tabLinks.get(3).onOpen = () => { updateLogInfo(); updateLogs(); };

  // Settings tab
  const updateOrte = optionsOrteUpdate(loadOrte);
  const updateSettings = optionsSettingsUpdate($cardframe);
  tabLinks.get(4).onClose = () => { updateOrte(); updateSettings(); };

  const $sett_help = $('#tab5 .help');
  $sett_help.eq(0).on('click', () => alert(preis_help, "Hilfe zur Preisformel"));
  $sett_help.eq(1).on('click', () => alert(karte_designs_help, "Hilfe zu Kartendesigns"));
  const $sett_actions = $('#actions button');
  $sett_actions.eq(0).on('click', () => delFamDate());
  $sett_actions.eq(1).on('click', () => delFamDate(-1, 'Karte'));
  $sett_actions.eq(2).on('click', () => resetFam());
  $sett_actions.eq(3).on('click', () => window.open('?page=backup%2Fcreate'));
  $sett_actions.eq(4).on('click', () => window.open('?page=backup%2Fload'));

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
  $tabHs.on('click', 'a', function () { changeTab($(this)) });
  tabH();


  // register handlers
  $(window).resize(tabH).on('keydown', keyboardHandler);

  const $cardwindow = $($cardframe.get(0).contentWindow);
  const $cardbody = $($cardframe.get(0).contentWindow.document.body);
  const $cardwrapper = $card.find('.card-frame-wrapper');
  $cardwindow.on('load resize', () => {
    const h = $cardbody.innerHeight();
    if (h) $cardwrapper.css('padding-bottom', Math.max(300, h));
  });

  // keyboard navigation
  const $search_field = $('#tab2 .search-header input:first') as JQuery<HTMLInputElement>;
  const $search_button = $('#tab2 .search-header input:last') as JQuery<HTMLInputElement>;
  const $list = $('#tab2 .select-list');
  const $more = $list.find('.more');
  const prev = prevFam.bind(null, $forms.eq(1), $list);
  const next = nextFam.bind(null, $forms.eq(1), $list);
  function keyboardHandler(event: JQuery.KeyDownEvent) {
    // console.log(event, event.which, event.key);
    if (!$current_tab) return true;
    if ($current_tab.attr('href') !== '#tab2') return true;
    if (!event.altKey) return true;
    switch (event.key) {
      case 'n': $os_select.eq(0).focus(); break;
      case 'm': $os_select.eq(1).focus(); break;
      case 'j': ausgabeFam.elems.Karte.focus(); break;
      case 'k': ausgabeFam.elems.Schulden.focus(); break;
      case 'l': ausgabeFam.elems.Notizen.focus(); break;
      case 'u': ausgabeFam.$anwesend.click(); break;
      case 'i': ausgabeFam.$geldverg.click(); break;
      case 'o': ausgabeFam.$schuldbeg.click(); break;
      case ',': $search_field.focus().select(); break;
      case '.': $search_button.click(); break;
      case 'ArrowUp': prev(); break;
      case 'ArrowDown': next(); break;
      default: return true;
    }
    return false;
  }
  function prevFam($f: JQuery<HTMLElement>) {
    let i = +$f.find('.selected').data('idx');
    if (i === 0) return;
    if (!i) i = 0;
    const $n = $f.children(':not([value="-1"])').eq(i - 1).click();
    const h = $list.innerHeight();
    const cs = $list.scrollTop();
    const ns = $n.position().top;
    if (ns < cs || ns > cs + h) $list.scrollTop(ns > 40 ? ns : 0);
  }
  function nextFam($f: JQuery<HTMLElement>) {
    let i = +$f.find('.selected').data('idx');
    if (!i && i !== 0) i = -1;
    const $n = $f.children(':not([value="-1"])').eq(i + 1);
    if ($n.length) {
      $n.click();
      const h = $list.innerHeight();
      const cs = $list.scrollTop();
      const ns = $n.position().top - h + $n.innerHeight();
      if (ns > cs || ns < cs + h) $list.scrollTop(ns);
    } else {
      $more.click();
    }
  }
});
export let changeTab: ($link: JQuery<TabElement>) => void;

