import $ from 'jquery';
import { alert } from './helpers';
import { DEBUG } from '../client';

export type JPromise<T> = JQuery.PromiseBase<T, never, never, never, never, never, never, never, never, never, never, never>;

export interface OrtList extends Array<any> {
  loading?: JPromise<void>;
  maxGruppen?: number;
}


export const settings = {
  preis: "",
  designs: "",
  _loading: false
};

export const orte: OrtList = [];
orte.loading = null;
orte.maxGruppen = 0;


export function optionsSettingsUpdate() {
  const $in = $('#settings :input');
  const $preis = $in.eq(0).data('name', 'Preis').data('prop', 'preis');
  const $designs = $in.eq(1).data('name', 'Kartendesigns').data('prop', 'designs');
  $in.eq(2).on('click', update.bind(null, null, $preis, $designs));

  [$preis, $designs].forEach(element => {
    let timeout;
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
    $.post('?api=setting').then((data: any) => {
      if (data && data.status === "success") {
        settings.preis = data.data.Preis;
        settings.designs = data.data.Kartendesigns;

        $preis.val(settings.preis);
        $designs.val(settings.designs);
      } else {
        console.error(`Failed getting settings: ${data.message}`);
        alert(`
          <p>Fehler beim laden der Einstellungen:<br />${data.message}</p>
        `, "Fehler");
      }
    }).fail((xhr: JQueryXHR, status: string, error: string) => {
      const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
      console.error(xhr.status, error, msg);
      alert(`
        <p>Fehler beim laden der Einstellungen:<br />${xhr.status} ${error}</p>
        <p>${msg}</p>
      `, "Fehler");
    }).always(() => {
      settings._loading = false;
    });
  }
  return load;
}


function update(element: JQuery<HTMLElement> = null, $preis: JQuery<HTMLElement> = null, $designs: JQuery<HTMLElement> = null) {
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
    [$preis, $designs].forEach(el);
  }

  $.post('?api=setting/update', {
    settings: sett
  }).then((data: any) => {
    if (data && data.status === "success") {
      console.debug(`Updated setting ${name}`);
      return true;
    } else {
      console.error(`Failed updating: ${data.message}`);
      alert(`
            <p>Fehler beim updaten:<br />${data.message}</p>
          `, "Fehler");
    }
  }).fail((xhr: JQueryXHR, status: string, error: string) => {
    const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
    console.error(xhr.status, error, msg);
    alert(`
            <p>Fehler beim updaten:<br />${xhr.status} ${error}</p>
            <p>${msg}</p>
          `, "Fehler");
  });
}

export default settings;