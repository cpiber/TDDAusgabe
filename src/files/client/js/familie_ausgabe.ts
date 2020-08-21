import $ from 'jquery';
import { changeTab, TabElement } from "../../client";
import { familie } from "./familie";
import { fam, famelems } from "./familie_interfaces";
import { verwaltungFam } from "./familie_verwaltung";
import { clone, formatDate, highlightElement, preis } from "./helpers";
import { orte } from "./settings";
import { apiData } from "./api";

export class ausgabeFam extends familie {
  timeout;
  priotimeout = false;
  preis: number;
  orig_schuld: number;
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
  static allow_sb_gv = true;
  static allow_an = true;

  constructor(data: any) {
    super(data);
    if (verwaltungFam.current && verwaltungFam.current.data.ID === this.data.ID) {
      verwaltungFam.clear();
      verwaltungFam.current = null;
    }

    this.orig_schuld = this.data.Schulden;
    if (ausgabeFam.current) ausgabeFam.current.save();
    ausgabeFam.allow_sb_gv = true;
    ausgabeFam.current = this;
    this.show();
    ausgabeFam.enable();
  }

  static linkHtml($card: JQuery<HTMLElement>) {
    const $inputs = $('#tab2 .familie-data :input, #tab2 .familie-data span') as JQuery<HTMLInputElement>;
    $inputs.eq(5).on('click', () => {
      if (!ausgabeFam.current) return;
      ausgabeFam.current.print();
    });
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
      ausgabeFam.allow_sb_gv = false;
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
      if (this.disabled) return;
      if (ausgabeFam.current.retry) {
        this.checked = false;
        ausgabeFam.current.retry = false;
        highlightElement(ausgabeFam.$error);
        return;
      }
      if (this.checked) {
        ausgabeFam.counter++;
        if (ausgabeFam.allow_sb_gv) {
          ausgabeFam.$geldverg.prop('disabled', false);
          ausgabeFam.$schuldbeg.prop('disabled', false);
        }
      } else {
        ausgabeFam.counter--;
        ausgabeFam.$geldverg.prop('disabled', true);
        ausgabeFam.$schuldbeg.prop('disabled', true);
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
        if (!ausgabeFam.$schuldbeg.prop('checked')) ausgabeFam.elems.Schulden.prop('disabled', false);
      }
    });
    this.$schuldbeg = $inputs.eq(22).on('click', function () {
      if (!ausgabeFam.current) return;
      if (this.checked) {
        ausgabeFam.elems.Schulden.prop('disabled', true);
      } else {
        if (!ausgabeFam.$geldverg.prop('checked')) ausgabeFam.elems.Schulden.prop('disabled', false);
      }
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
    this.$anwesend.add(this.$geldverg).add(this.$schuldbeg).on('click', function () {
      const cur = ausgabeFam.current;
      if (!cur) return;
      if (cur.timeout) clearTimeout(cur.timeout);
      cur.priotimeout = true;
      cur.timeout = setTimeout(() => {
        if (!cur) return;
        cur.save();
        cur.priotimeout = false;
        cur.timeout = null;
      }, 10000);
    });

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

    super.linkHtml($card);
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

    ausgabeFam.elems.Name.html(`${this.data.Name} <i class="smaller">(ID: ${this.data.ID})</i>`);

    const i = orte.findIndex(val => val.ID == this.data.Ort);
    const ortname = orte[i] ? orte[i].Name : 'Unbekannt';
    ausgabeFam.elems.Ort.text(ortname);

    this.preis = preis(+this.data.Erwachsene, +this.data.Kinder);
    let lAnw = this.data.lAnwesenheit;
    if (lAnw !== "" && lAnw !== null) {
      try {
        lAnw = (new Date(this.data.lAnwesenheit)).toLocaleDateString();
      } catch (e) { }
    }
    const addr = this.data.Adresse ? this.data.Adresse.replace(/\n/g, '<br />') : '';

    ausgabeFam.elems.lAnwesenheit.text(lAnw);
    ausgabeFam.elems.Adresse.html(addr);

    const schuld = this.preis + (+this.data.Schulden);
    ausgabeFam.$preis.html(`${this.preis.toFixed(2)}€ &nbsp; &nbsp; &nbsp; &rarr; ${schuld.toFixed(2)}`);

    const lToday = this.data.lAnwesenheit === formatDate(new Date());
    ausgabeFam.$anwesend.prop('checked', lToday).prop('disabled', lToday);
    ausgabeFam.allow_an = lToday;

    ausgabeFam.$geldverg.prop('checked', false);
    ausgabeFam.$schuldbeg.prop('checked', false);

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
    this.$anwesend.prop('disabled', ausgabeFam.allow_an);
    this.$verw.prop('disabled', false)
    const allow = this.$anwesend.prop('checked') && this.allow_sb_gv;
    [this.$geldverg, this.$schuldbeg].forEach((el) => {
      el.prop('disabled', !allow);
    });
  }

  changed(property: string) {
    super.changed(property);
    if (this.timeout && this.priotimeout) return; // priority running
    if (this.timeout) clearTimeout(this.timeout);
    this.timeout = setTimeout(() => {
      if (!this) return;
      this.save();
      this.timeout = null;
    }, 400);
  }

  save() {
    if (this.timeout) {
      clearTimeout(this.timeout);
      this.timeout = null;
      this.priotimeout = false;
    }

    const additional: {
      attendance?: string,
      money?: string,
    } = {};
    let m = 0;

    this.data.Schulden = +this.data.Schulden;
    if (ausgabeFam.$anwesend.prop('checked') && !ausgabeFam.$anwesend.prop('disabled')) {
      additional.attendance = `${this.data.Erwachsene}/${this.data.Kinder}`;
      this.data.lAnwesenheit = formatDate(new Date());
      this.dirty.lAnwesenheit = true;
      m += this.preis;
    }
    if (ausgabeFam.$geldverg.prop('checked')) {
      console.log('verg');
      this.data.Schulden += this.preis;
      this.dirty.Schulden = true;
    }
    if (ausgabeFam.$schuldbeg.prop('checked')) {
      this.data.Schulden = 0;
      this.dirty.Schulden = true;
    }
    m -= (this.data.Schulden - this.orig_schuld);

    if (m !== 0) {
      additional.money = m.toFixed(2);
    }

    return super.save(ausgabeFam, additional).then((data: apiData) => {
      this.orig_schuld = this.data.Schulden;
      if (!ausgabeFam.current || this.data.ID != ausgabeFam.current.data.ID) return;
      ausgabeFam.allow_sb_gv = true;
      this.show();
      ausgabeFam.enable(); // re-enable after setting allow_sb_gv and showing
      return data;
    });
  }

  _save() {
    this.save();
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