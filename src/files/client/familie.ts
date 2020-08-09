
import jQuery from 'jquery';
import { famdata, famdirty, famelems, fam } from './familie_interfaces';
import { clone, formatDate, preis, numPad, highlightElement, timeout } from './helpers';
import { JsBarcode, changeTab, TabElement } from '../client';
import { orte } from './settings';


export class familie {
  data: famdata = {};
  dirty: famdirty = {};
  barcode: string;

  static elems: famelems;
  static current: familie;
  static barcode: HTMLImageElement;

  constructor (data: any) {
    Object.assign(this.data, fam, data);
    this.generateBarcode();
  }

  static linkHtml () {
    familie.barcode = $('#barcode').get(0) as HTMLImageElement;
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
      if (typeof (el.text) === "function") el.text('');
      if (typeof (el.val) === "function") el.val('');
      el.prop('checked', false);
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

  save() {
    console.log('save');
  }
}


export class ausgabeFam extends familie {
  timeout;
  preis: number;
  schuld = false;
  retry = false;

  static elems: famelems = clone(fam);
  static $expired: JQuery<HTMLElement>;
  static $counter: JQuery<HTMLElement>;
  static $preis: JQuery<HTMLElement>;
  static $anwesend: JQuery<HTMLInputElement>;
  static $geldverg: JQuery<HTMLInputElement>;
  static $schuldbeg: JQuery<HTMLInputElement>;
  static $error: JQuery<HTMLElement>;
  static $verw: JQuery<HTMLElement>;
  static _counter = 0;
  static current: ausgabeFam = null;
  static errors = {
    money_next: false,
    money_now: false,
    already: false,
  };
  static errorMsg = {
    money_next: 'Darf nächstes Mal nur noch nach Begleichen der Schulden hinein.',
    money_now: 'Schulden zu hoch! Muss erst Schulden begleichen!',
    already: 'Hat diese Woche bereits abgeholt!',
  };

  constructor(data: any) {
    super(data);
    if (ausgabeFam.current) ausgabeFam.current.save();
    ausgabeFam.current = this;
    ausgabeFam.enable();
    this.show();
  }

  static linkHtml() {
    const $inputs = jQuery('#tab2 .familie-data :input, #tab2 .familie-data span') as JQuery<HTMLInputElement>;
    this.elems.Name = $inputs.eq(6);
    this.elems.Ort = $inputs.eq(7);
    this.elems.Gruppe = $inputs.eq(8);
    this.elems.Num = $inputs.eq(9);
    this.elems.lAnwesenheit = $inputs.eq(10);
    this.elems.Karte = $inputs.eq(11);
    this.elems.Erwachsene = $inputs.eq(12);
    this.elems.Kinder = $inputs.eq(13);
    this.elems.Schulden = $inputs.eq(15).on('change keyup', function () {
      if (!ausgabeFam.current) return;
      ausgabeFam.$geldverg.prop('disabled', true);
      ausgabeFam.$schuldbeg.prop('disabled', true);
      ausgabeFam.current.errors();
    });
    this.elems.Notizen = $inputs.eq(16);
    this.elems.Adresse = $inputs.eq(18);
    this.elems.Telefonnummer = $inputs.eq(19);
    this.$expired = $inputs.eq(0);
    this.$counter = $inputs.eq(1);
    this.$preis = $inputs.eq(14);
    this.$anwesend = $inputs.eq(20).on('click', function () {
      if (!ausgabeFam.current) return;
      if (ausgabeFam.current.retry) {
        this.checked = false;
        ausgabeFam.current.retry = false;
        highlightElement(ausgabeFam.$error);
        return;
      }
      if (this.checked) {
        ausgabeFam.counter++;
      } else {
        ausgabeFam.counter--;
      }
    });
    this.$geldverg = $inputs.eq(21).on('click', function () {
      if (!ausgabeFam.current) return;
      if (this.checked) {
        ausgabeFam.elems.Schulden.prop('disabled', true);
        if (ausgabeFam.current.preis + (+ausgabeFam.current.data.Schulden) >= ausgabeFam.current.preis * 3) {
          ausgabeFam.errors.money_next = true;
        }
      } else {
        ausgabeFam.elems.Schulden.prop('disabled', false);
      }
    });
    this.$schuldbeg = $inputs.eq(22).on('click', function () {
      if (!ausgabeFam.current) return;
      if (ausgabeFam.current.schuld) {
        if (this.checked) {
          ausgabeFam.$anwesend.prop('disabled', false);
        } {
          ausgabeFam.$anwesend.prop('disabled', true);
        }
      }
    });
    this.$error = $inputs.eq(23);
    this.$verw = $inputs.eq(24);

    const $buttons = $inputs.slice(2, 5);
    $inputs.eq(1).on('click', function () {
      const $this = $(this);
      const open = $this.data('open') || false;
      if (!open) {
        $this.empty().data('open', true);
        $buttons.hide();
        const $input = $('<input>').val(ausgabeFam.counter)
          .attr('type', 'number').attr('min', 0)
          .css('width', 65);
        $('<form>').on('submit focusout', () => {
          ausgabeFam.counter = +$this.find('input').val() || 0;
          $this.empty().text(ausgabeFam.counter).data('open', false);
          $buttons.show();
        }).css('display', 'inline')
          .appendTo($this)
          .append($input);
        $input.focus().select();
      }
    });
    $inputs.eq(2).on('click', () => {
      this.counter--;
    });
    $inputs.eq(3).on('click', () => {
      this.counter = 0;
    });
    $inputs.eq(4).on('click', () => {
      this.counter++;
    });

    const $verw = $('a[href="#tab3"]') as JQuery<TabElement>;
    $inputs.eq(24).on('click', () => {
      if (!this.current) return;
      new verwaltungFam(this.current.data);
      this.current = null;
      changeTab($verw);
      this.clear();
    });

    super.linkHtml();
  }

  static clear() {
    super.clear();
    this.$expired.text('');
    this.$error.text('');
    [this.$anwesend, this.$geldverg, this.$schuldbeg].forEach((el) => {
      el.prop('checked', false);
    });
  }

  show() {
    super.show(ausgabeFam);

    const i = orte.findIndex(val => val.ID == this.data.Ort);
    const ortname = orte[i] ? orte[i].Name : 'Unbekannt';
    ausgabeFam.elems.Ort.text(ortname);

    this.preis = preis(+this.data.Erwachsene, +this.data.Kinder);
    let lAnw = this.data.lAnwesenheit;
    try {
      lAnw = (new Date(this.data.lAnwesenheit)).toLocaleDateString();
    } catch (e) { }
    const addr = this.data.Adresse.replace(/\n/g, '<br />');

    ausgabeFam.elems.lAnwesenheit.text(lAnw);
    ausgabeFam.elems.Adresse.html(addr);
    
    const schuld = this.preis + (+this.data.Schulden);
    ausgabeFam.$preis.html(`${this.preis.toFixed(2)}€ &nbsp; &nbsp; &nbsp; &rarr; ${schuld.toFixed(2)}`);

    const lToday = this.data.lAnwesenheit === formatDate(new Date());
    ausgabeFam.$anwesend.prop('checked', lToday).prop('disabled', lToday);
    
    // Karte abgelaufen
    if (!this.data.Karte || this.data.Karte === "0000-00-00") {
      ausgabeFam.$expired.text('Ablaufdatum eingeben!');
    } else {
      const expires = new Date(this.data.Karte);
      const today = new Date();
      const diff = (today.getTime() - expires.getTime());
      const days = Math.ceil(diff / (1000 * 3600 * 24));
      if (days > 0) {
        ausgabeFam.$expired.text('Karte abgelaufen!');
      } else {
        ausgabeFam.$expired.text('');
      }
    }

    this.errors();

    // if (!ausgabeFam.errors.already && !ausgabeFam.errors.money_now && !lToday)
    //   ausgabeFam.$anwesend.click();
  }

  errors() {
    const s = +this.data.Schulden;
    const hasS = this.schuld || false;
    let days = 0;
    if (!this.data.lAnwesenheit || this.data.lAnwesenheit === "0000-00-00") {
      ausgabeFam.errors.already = false;
    } else {
      const ab = new Date(this.data.lAnwesenheit);
      const today = new Date();
      const diff = (today.getTime() - ab.getTime());
      days = Math.ceil(diff / (1000 * 3600 * 24));
      
      // Bereits abgeholt diese Woche?
      if (days <= 7 && days > 1) {
        ausgabeFam.errors.already = true;
        this.retry = true;
      } else {
        ausgabeFam.errors.already = false;
      }
    }
    
    // Schulden zu hoch
    if (s !== 0 && s >= this.preis * 3) {
      if (days > 1) {
        ausgabeFam.$anwesend.prop('disabled', true);
        this.schuld = true;
        ausgabeFam.errors.money_now = true;
        ausgabeFam.errors.money_next = false;
      } else {
        this.schuld = false;
        ausgabeFam.errors.money_now = false;
        ausgabeFam.errors.money_next = true;
      }
    } else {
      this.schuld = false;
      ausgabeFam.errors.money_now = false;
      ausgabeFam.errors.money_next = false;
    }
    if (hasS) {
      if (s === 0) {
        ausgabeFam.$anwesend.prop('disabled', false);
        this.schuld = false;
        ausgabeFam.errors.money_now = false;
      } else {
        ausgabeFam.errors.money_now = true;
      }
    }

    ausgabeFam.showErrors();
  }

  static showErrors() {
    const err = [];
    for (const prop in this.errors) {
      if (this.errors[prop])
        err.push(this.errorMsg[prop]);
    }
    this.$error.empty().html(err.join('<br />'));
  }

  static disable() {
    super.disable();
    [this.$anwesend, this.$geldverg, this.$schuldbeg, this.$verw].forEach((el) => {
      el.prop('disabled', true);
    });
  }

  static enable() {
    super.enable();
    [this.$anwesend, this.$geldverg, this.$schuldbeg, this.$verw].forEach((el) => {
      el.prop('disabled', false);
    });
  }

  changed(property: string) {
    super.changed(property);
    if (this.timeout) clearTimeout(this.timeout);
    this.timeout = setTimeout(() => {
      this.save();
      this.timeout = null;
    }, 400);
  }

  static get counter() {
    return this._counter;
  }

  static set counter(count: number) {
    if (count < 0 || isNaN(+count)) count = 0;
    if (!this.$counter.data('open'))
      this.$counter.text(count);
    this._counter = count;
  }
}


export class verwaltungFam extends familie {
  newFam = false;

  static elems: famelems = clone(fam);
  static $button_save: JQuery<HTMLInputElement>;
  static $button_delete: JQuery<HTMLInputElement>;
  static current: verwaltungFam = null;

  constructor(data: any = null) {
    super(data);
    verwaltungFam.current = this;
    if (data !== undefined && data !== null) {
      this.newFam = false;
      verwaltungFam.editMode();
    } else {
      this.newFam = true;
      verwaltungFam.createMode();
    }
    this.show();
    verwaltungFam.enable();
  }

  static linkHtml() {
    const $inputs = jQuery('#tab3 .familie-data :input, #tab3 .familie-data span') as JQuery<HTMLInputElement>;
    this.elems.ID = $inputs.eq(1);
    this.elems.Name = $inputs.eq(2);
    this.elems.Ort = $inputs.eq(3);
    this.elems.Gruppe = $inputs.eq(4);
    this.elems.Num = $inputs.eq(5);
    this.elems.Erwachsene = $inputs.eq(6);
    this.elems.Kinder = $inputs.eq(7);
    this.elems.lAnwesenheit = $inputs.eq(8);
    this.elems.Karte = $inputs.eq(9);
    this.elems.Schulden = $inputs.eq(10);
    this.elems.Notizen = $inputs.eq(11);
    this.elems.Adresse = $inputs.eq(12);
    this.elems.Telefonnummer = $inputs.eq(13);
    this.$button_save = $inputs.eq(14).on('click', () => {
      if (!this.current) return;
      this.current.save();
    });
    this.$button_delete = $inputs.eq(15).on('click', () => {
      if (!this.current) return;
      // this.current.delete();
    });
    const $list = $('#tab3 .select-list ul');
    $('#tab3 .button-add').on('click', () => {
      $list.find('.selected').removeClass('selected');
      new verwaltungFam();
    });

    super.linkHtml();
  }

  static clear() {
    super.clear();
    [this.$button_save, this.$button_delete].forEach((el) => {
      el.prop('checked', false);
    });
    this.editMode();
  }

  show() {
    super.show(verwaltungFam);
    
    verwaltungFam.elems.Ort.change();
    timeout().then(() => verwaltungFam.elems.Gruppe.val(this.data.Gruppe));
  }

  static disable() {
    super.disable();
    [this.$button_save, this.$button_delete].forEach((el) => {
      el.prop('disabled', true);
    });
  }

  static enable() {
    super.enable();
    [this.$button_save, this.$button_delete].forEach((el) => {
      el.prop('disabled', false);
    });
  }

  static editMode() {
    this.$button_save.text(this.$button_save.data('save'));
    this.$button_delete.show();
  }

  static createMode() {
    this.$button_save.text(this.$button_save.data('create'));
    this.$button_delete.hide();
  }
}