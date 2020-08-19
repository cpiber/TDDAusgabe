import $ from 'jquery';
import request, { apiData } from './api';
import { JPromise, orte } from './settings';

export function optionsOrteUpdate(loadOrte: () => JPromise<void>) {
  const $opt = $('#orte ul');
  const $l = $('<span>').text('Loading...').hide().insertBefore($opt);

  const text = (name: string, gruppen: number) => {
    return `${name}, ${gruppen} Gruppen`;
  }

  $opt.on('click', 'li', function (e) {
    if (e.target !== this && e.target.tagName !== 'BUTTON') return; // text box / link
    const $this = $(this);
    const open: boolean = $this.data('open') || false;

    if ($this.hasClass('button-add')) {
      $('<li>').val(-1).insertBefore($this).click();
      return;
    }

    if (open) {
      // save
      const id = $this.val();
      const $inp = $this.find('input').prop('disabled', true);
      let name = $inp.eq(0).val();
      let grp = +$inp.eq(1).val();
      if (isNaN(grp) || grp < 0) grp = 0;

      let req: JPromise<void>;
      if (id > 0) {
        req = request('ort/update', 'Fehler beim Updaten', {
          ID: id,
          Name: name,
          Gruppen: grp
        }).then(() => {
          console.debug(`Updated 'Ort' with ID ${id}`);
          // no name supplied means server ignores it
          if (!name) name = $this.data('name');
        });
      } else {
        req = request('ort/insert', 'Fehler beim Erstellen', {
          Name: name,
          Gruppen: grp
        }).then((data: apiData) => {
          const id = data.id;
          $this.val(id);
          console.debug(`Inserted 'Ort' with ID ${id}`);
        });
      }

      // update element
      req.then(() => {
        $this.empty().data('name', name).data('gruppen', grp)
          .text(text(name as string, grp)).data('open', false)
          .removeClass('expanded');
      }).fail(() => {
        $inp.prop('disabled', false);
      });
      return;
    }

    // update
    $this.empty()
      .append($('<input>').val($this.data('name')).addClass('w100pm400px'))
      .append($('<input>').attr('type', 'number').val($this.data('gruppen')).addClass('w100pm400px'))
      .append($('<button>').text('OK'))
      .append(' &nbsp; ')
      .append($('<a>').text('Löschen').addClass('link-delete').val($this.val()))
      .data('open', true).addClass('expanded');

  }).on('click', 'a', function (e) {
    const $this = $(this);
    const id = +$this.val();
    if (id === -1) { // doesn't exist yet
      $this.closest('li').remove();
      return;
    }

    request('ort/delete', 'Fehler beim Löschen', {
      ID: id
    }).then(() => {
      console.debug(`Deleted 'Ort' with ID ${id}`);
      $this.closest('li').remove();
    });
  });

  const update = () => {
    $opt.empty();
    $l.show();

    loadOrte().then(() => {
      $l.hide();
      orte.forEach(ort => {
        $('<li>').val(ort.ID).text(text(ort.Name, ort.Gruppen))
          .data('name', ort.Name).data('gruppen', ort.Gruppen).appendTo($opt);
      })
      $('<li>').addClass('button-add').text('+').appendTo($opt);
    });
  }

  return update;
}