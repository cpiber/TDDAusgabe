import $ from 'jquery';
import { changeTab, TabElement } from "../../client";
import { apiData } from "./api";
import { familie } from "./familie";
import { fam, famelems } from "./familie_interfaces";
import { verwaltungFam } from "./familie_verwaltung";
import { clone, formatDate, highlightElement, preis } from "./helpers";
import { orte } from "./settings";

export class ausgabeFam extends familie {
  timeout: number | null = null;
  priotimeout = false;
  preis: number;
  orig_schuld: number;
  schuld = false;
  retry = false;

  static elems: Required<Omit<famelems, 'ID'>> = clone(fam);
  static $counter: JQuery<HTMLElement>;
  static $preis: JQuery<HTMLElement>;
  static $anwesend: JQuery<HTMLInputElement>;
  static $geldverg: JQuery<HTMLInputElement>;
  static $schuldbeg: JQuery<HTMLInputElement>;
  static $error: JQuery<HTMLElement>;
  static $warn: JQuery<HTMLElement>;
  static $verw: JQuery<HTMLElement>;
  static $light: JQuery<HTMLElement>;
  static _counter = 0;
  static current: ausgabeFam = null;
  static errors = {
    money_now: false,
    already: false,
    expired: false,
  };
  static warnings = {
    money_next: false,
    expiration_date_missing: false,
    expires_soon: false,
  };
  static errorMsg: Record<keyof typeof ausgabeFam['errors'], string> = {
    money_now: 'Schulden zu hoch! Muss erst Schulden begleichen!',
    already: 'Hat diese Woche bereits abgeholt!',
    expired: 'Karte abgelaufen!',
  };
  static warnMsg: Record<keyof typeof ausgabeFam['warnings'], string> = {
    money_next: 'Darf nächstes Mal nur noch nach Begleichen der Schulden hinein.',
    expiration_date_missing: 'Ablaufdatum eintragen!',
    expires_soon: 'Karte läuft bald ab!',
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
    $inputs.filter('.print').on('click', () => {
      if (!ausgabeFam.current) return;
      ausgabeFam.current.print();
    });
    for (const prop in this.elems) {
      this.elems[prop] = $inputs.filter(`.${prop}`);
    }

    this.elems.Schulden.on('change keyup', function () {
      if (!ausgabeFam.current) return;
      ausgabeFam.$geldverg.prop('disabled', true);
      ausgabeFam.$schuldbeg.prop('disabled', true);
      ausgabeFam.allow_sb_gv = false;
      ausgabeFam.current.errors();
    });
    this.$counter = $inputs.filter('.counter');
    this.$preis = $inputs.filter('.preis');
    this.$anwesend = $inputs.filter('.anwesend').on('click', function () {
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
    this.$geldverg = $inputs.filter('.geldverg').on('click', function () {
      if (!ausgabeFam.current) return;
      if (this.checked) {
        ausgabeFam.elems.Schulden.prop('disabled', true);
        if (ausgabeFam.current.preis + (+ausgabeFam.current.data.Schulden) >= ausgabeFam.current.preis * 3) {
          ausgabeFam.warnings.money_next = true;
        }
      } else {
        if (!ausgabeFam.$schuldbeg.prop('checked')) ausgabeFam.elems.Schulden.prop('disabled', false);
      }
    });
    this.$schuldbeg = $inputs.filter('.schuldbeg').on('click', function () {
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
    this.$error = $inputs.filter('.err-box');
    this.$warn = $inputs.filter('.warn-box');
    this.$light = $inputs.filter('.light');
    this.$verw = $inputs.filter('.verw');
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

    const $buttons = $inputs.filter('button.o');
    $inputs.filter('.counter').on('click', function () {
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
        $input.trigger('focus').trigger('select');
      }
    });
    $buttons.eq(0).on('click', () => {
      this.counter--;
    });
    $buttons.eq(1).on('click', () => {
      this.counter = 0;
    });
    $buttons.eq(2).on('click', () => {
      this.counter++;
    });

    const $verw = $('a[href="#tab3"]') as JQuery<TabElement>;
    $inputs.filter('.verw').on('click', () => {
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
    this.$error.text('');
    this.$warn.text('');
    this.$light.removeClass('light-red light-orange light-green');
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

    this.errors();

    // if (!ausgabeFam.errors.already && !ausgabeFam.errors.money_now && !lToday)
    //   ausgabeFam.$anwesend.click();
  }

  errors() {
    // Karte abgelaufen
    if (!this.data.Karte || this.data.Karte === "0000-00-00") {
      ausgabeFam.warnings.expiration_date_missing = true;
      ausgabeFam.errors.expired = ausgabeFam.warnings.expires_soon = false;
    } else {
      ausgabeFam.warnings.expiration_date_missing = false;
      const expires = new Date(this.data.Karte);
      const today = new Date();
      const diff = (today.getTime() - expires.getTime());
      const days = Math.ceil(diff / (1000 * 3600 * 24));
      ausgabeFam.errors.expired = days > 0;
      ausgabeFam.warnings.expires_soon = days > -7 && days <= 0;
    }

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
        ausgabeFam.warnings.money_next = false;
      } else {
        this.schuld = false;
        ausgabeFam.errors.money_now = false;
        ausgabeFam.warnings.money_next = true;
      }
    } else {
      this.schuld = false;
      ausgabeFam.errors.money_now = false;
      ausgabeFam.warnings.money_next = false;
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

    const w = [];
    for (const prop in this.warnings) {
      if (this.warnings[prop])
        w.push(this.warnMsg[prop]);
    }
    this.$warn.empty().html(w.join('<br />'));

    this.$light.removeClass('light-red light-orange light-green');
    if (err.length) this.$light.addClass('light-red');
    else if (w.length) this.$light.addClass('light-orange');
    else this.$light.addClass('light-green');
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
      this.timeout = null;
      const elem = $(':focus');
      this.save().then(() => elem.trigger('select')); // refocus after saving
    }, 800);
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
      if (!ausgabeFam.current || this.data.ID != ausgabeFam.current.data.ID) return data;
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