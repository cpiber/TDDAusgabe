import $ from 'jquery';
import { alert } from './helpers';
import { orte, JPromise } from './settings';

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

    if ($this.hasClass('add')) {
      $('<li>').val(-1).insertBefore($this).click();
      return;
    }

    if (open) {
      // save
      const id = $this.val();
      const $inp = $this.find('input');
      let name = $inp.eq(0).val();
      let grp = +$inp.eq(1).val();
      if (isNaN(grp) || grp < 0) grp = 0;

      let req: JPromise<boolean>;
      if (id > 0) {
        req = $.post('?api=ort/update', {
          ID: id,
          Name: name,
          Gruppen: grp
        }).then((data: any) => {
          if (data && data.status === "success") {
            console.debug(`Updated 'Ort' with ID ${id}`);
            // no name supplied means server ignores it
            if (!name) name = $this.data('name');
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
      } else {
        req = $.post('?api=ort/insert', {
          Name: name,
          Gruppen: grp
        }).then((data: any) => {
          if (data && data.status === "success") {
            const id = data.id;
            $this.val(id);
            console.debug(`Inserted 'Ort' with ID ${id}`);
            return true;
          } else {
            console.error(`Failed inserting: ${data.message}`);
            alert(`
            <p>Fehler beim erstellen:<br />${data.message}</p>
          `, "Fehler");
          }
        }).fail((xhr: JQueryXHR, status: string, error: string) => {
          const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
          console.error(xhr.status, error, msg);
          alert(`
            <p>Fehler beim erstellen:<br />${xhr.status} ${error}</p>
            <p>${msg}</p>
          `, "Fehler");
        });
      }

      // update element
      req.then((success: boolean) => {
        if (!success) return;
        $this.empty().data('name', name).data('gruppen', grp)
          .text(text(name as string, grp)).data('open', false);
      });
      return;
    }

    // update
    $this.empty()
      .append($('<input>').val($this.data('name')).addClass('w100pm400px'))
      .append($('<input>').attr('type', 'number').val($this.data('gruppen')).addClass('w100pm400px'))
      .append($('<button>').text('OK'))
      .append($('<a>').text('Löschen').addClass('link-delete').val($this.val()))
      .data('open', true);

  }).on('click', 'a', function (e) {
    const $this = $(this);
    const id = +$this.val();
    if (id === -1) { // doesn't exist yet
      $this.closest('li').remove();
      return;
    }

    $.post('?api=ort/delete', {
      ID: id
    }).then((data: any) => {
      if (data && data.status === "success") {
        console.debug(`Deleted 'Ort' with ID ${id}`);
        $this.closest('li').remove();
      } else {
        console.error(`Failed deleting: ${data.message}`);
        alert(`
          <p>Fehler beim löschen:<br />${data.message}</p>
        `, "Fehler");
      }
    }).fail((xhr: JQueryXHR, status: string, error: string) => {
      const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
      console.error(xhr.status, error, msg);
      alert(`
          <p>Fehler beim löschen:<br />${xhr.status} ${error}</p>
          <p>${msg}</p>
        `, "Fehler");
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