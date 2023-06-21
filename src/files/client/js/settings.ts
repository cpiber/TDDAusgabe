import $ from 'jquery';
import { cardWindow } from '../../card';
import request, { apiData } from './api';
import { alert } from './helpers';

export type JPromise<T> = JQuery.PromiseBase<T, never, never, never, never, never, never, never, never, never, never, never>;

export interface OrtList extends Array<any> {
  loading?: JPromise<void>;
  maxGruppen?: number;
}


export const settings = {
  preis: "",
  designs: "",
  syncUrl: "",
  _loading: false
};

export const orte: OrtList = [];
orte.loading = null;
orte.maxGruppen = 0;

let frameloaded = false;


export function optionsSettingsUpdate($frame: JQuery<HTMLIFrameElement>) {
  const $in = $('#settings :input');
  const $preis = $in.eq(0).data('name', 'Preis').data('prop', 'preis');
  const $designs = $in.eq(1).data('name', 'Kartendesigns').data('prop', 'designs');
  const $sync = $in.eq(2).data('name', 'SyncServer').data('prop', 'syncUrl');
  $in.eq(2).on('click', update.bind(null, null, $preis, $designs, $sync));

  const card = $frame.on('load', () => {
    frameloaded = true;
    if (!settings.designs || settings._loading) return;

    // set designs on load in case the frame takes longer to load than requesting settings
    try {
      (card.contentWindow as cardWindow).designs = JSON.parse(settings.designs);
      (card.contentWindow as cardWindow).updateDesigns();
    } catch (e) {
      console.error(`Failed setting 'designs': ${e}`);
      alert(`
          <p>Fehler beim Laden der Designs:<br />${e}</p>
        `, "Fehler");
    }
  }).attr('src', '?page=card').get(0);

  [$preis, $designs, $sync].forEach(element => {
    let timeout: number;
    element.on('keyup', function () {
      if (timeout) clearTimeout(timeout);
      timeout = setTimeout(() => {
        update(element);
        timeout = null;
      }, 400);
    });
  });

  const load = () => {
    if (settings._loading) {
      console.debug("Settings already loading");
      return;
    }
    settings._loading = true;
    request('setting', 'Fehler beim laden der Einstellungen').then((data: apiData) => {
      settings.preis = data.data.Preis;
      settings.designs = data.data.Kartendesigns;
      settings.syncUrl = data.data.SyncServer;

      $preis.val(settings.preis);
      $designs.val(settings.designs);
      $sync.val(settings.syncUrl);
      
      if (!frameloaded) return;
      try {
        (card.contentWindow as cardWindow).designs = JSON.parse(settings.designs);
        (card.contentWindow as cardWindow).updateDesigns();
      } catch (e) {
        console.error(`Failed setting 'designs': ${e}`);
        alert(`
          <p>Fehler beim Laden der Designs:<br />${e}</p>
        `, "Fehler");
      }
    }).always(() => {
      settings._loading = false;
    });
  }
  return load;
}


function update(element: JQuery<HTMLElement> = null, $preis: JQuery<HTMLElement> = null, $designs: JQuery<HTMLElement> = null, $sync: JQuery<HTMLElement> = null) {
  const el = (element: JQuery<HTMLElement>) => {
    const name = element.data('name');
    const val = element.val();
    sett.push({
      Name: name,
      Val: val
    });
    settings[element.data('prop')] = val;
  }
  const sett = [];
  if (element) {
    el(element);
  } else {
    [$preis, $designs, $sync].forEach(el);
  }

  request('setting/update', 'Fehler beim Updaten', {
    settings: sett
  }).then(() => {
    console.debug(`Updated setting(s)`);
  });
}

export default settings;