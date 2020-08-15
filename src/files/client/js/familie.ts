import $ from 'jquery';
import { cardWindow, familie as cardFam } from '../../card';
import { JsBarcode } from '../../client';
import { fam, famdata, famdirty, famelems } from './familie_interfaces';
import { alert, numPad, open_modal, preis } from './helpers';


export class familie {
  data: famdata = {};
  dirty: famdirty = {};
  barcode: string;

  static elems: famelems;
  static current: familie;
  static barcode: HTMLImageElement;
  static frame: HTMLIFrameElement;
  static $card: JQuery<HTMLElement>;

  constructor(data: any) {
    Object.assign(this.data, fam, data);
    this.generateBarcode();
  }

  static linkHtml($card: JQuery<HTMLElement>) {
    familie.barcode = $('#barcode').get(0) as HTMLImageElement;
    familie.frame = $card.find('.card-frame').get(0) as HTMLIFrameElement;
    familie.$card = $card;
    Object.keys(fam).forEach(element => {
      const el = this.elems[element];
      if (!el) return;
      el.on('change keyup', () => {
        if (!this.current) return;
        const newval = el.val();
        const changed = this.current.data[element] !== newval;
        this.current.dirty[element] = this.current.dirty[element] || changed;
        this.current.data[element] = newval;
        if (changed) this.current.changed(element);
      });
    });
  }

  static clear() {
    for (const prop in this.elems) {
      const el: JQuery<HTMLInputElement> = this.elems[prop];
      if (!el) continue;
      const tag = el.prop('tagName');
      if (tag === "INPUT" || tag === "SELECT" || tag === "TEXTAREA") {
        el.val('');
        el.prop('checked', false);
      } else {
        el.text('');
      }
    }
    this.disable();
  }

  show(cls: typeof familie) {
    for (const prop in cls.elems) {
      const el: JQuery<HTMLInputElement> = cls.elems[prop];
      if (!el) continue;
      const tag = el.prop('tagName');
      if (tag === "INPUT" || tag === "SELECT" || tag === "TEXTAREA") {
        el.val(this.data[prop]);
        el.prop('checked', false);
      } else {
        el.text(this.data[prop]);
      }
    }
  }

  generateBarcode() {
    if (!this.data.ID) return;
    JsBarcode(familie.barcode, numPad(this.data.ID, 6), {
      height: 28,
      width: 1,
      textMargin: 0,
      fontSize: 11,
      background: 0,
      marginLeft: 15,
      marginRight: 15,
      margin: 0,
      displayValue: true
    });
    this.barcode = familie.barcode.src;
  }

  print() {
    const fam: cardFam = {
      ...this.data,
      Preis: preis(this.data.Erwachsene, this.data.Kinder),
      img: `<img src="${this.barcode}" />`,
      isrc: this.barcode,
    };
    const w = familie.frame.contentWindow as cardWindow;
    w.familie = fam;
    w.updateCanvas();
    open_modal(familie.$card);
  }

  static disable() {
    for (const prop in this.elems) {
      if (this.elems[prop])
        this.elems[prop].prop('disabled', true);
    }
  }

  static enable() {
    for (const prop in this.elems) {
      if (this.elems[prop])
        this.elems[prop].prop('disabled', false);
    }
  }

  changed(property: string) {
  }

  save(cls: typeof familie, additional: { [key: string]: any } = {}) {
    const data: famdata = {};
    for (let prop in this.dirty) {
      if (this.dirty[prop]) data[prop] = this.data[prop];
    }
    if (data.Gruppe && data.Gruppe != 0) data.Gruppe = - (+data.Gruppe);
    if (data.Num && data.Num != 0) data.Num = - (+data.Num);
    if ($.isEmptyObject(data)) return;

    cls.disable();
    return $.post('?api=familie/update', {
      ID: this.data.ID,
      data: data,
      ...additional
    }).then((data: any) => {
      if (data && data.status === "success") {
        if (data.new) {
          this.data.Gruppe = data.new.Gruppe || this.data.Gruppe;
          this.data.Num = data.new.Num || this.data.Num;
        }
        console.debug(`Saved 'familie' with ID ${this.data.ID}`);
      } else {
        console.error(`Failed saving: ${data.message}`);
        alert(`
          <p>Fehler beim speichern:<br />${data.message}</p>
        `, "Fehler");
      }
      return data;
    }).fail((xhr: JQueryXHR, status: string, error: string) => {
      const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
      console.error(xhr.status, error, msg);
      alert(`
        <p>Fehler beim speichern:<br />${xhr.status} ${error}</p>
        <p>${msg}</p>
      `, "Fehler");
    }).always((data) => {
      for (let prop in this.dirty) this.dirty[prop] = false;
      cls.enable();
      return data;
    });
  }

  _save() { }
}
