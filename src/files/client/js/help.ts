import $ from 'jquery';

// Help headings
export default function insert() {
  const $html = $('html');
  const $hs = $('#tab6 h2, #tab6 h3, #tab6 h4, #tab6 h5, #tab6 h6');
  const $ul = $('<ul>').css('marginTop', 0).css('marginBottom', '2.5em');

  $hs.each(function (i, e) {
    const $e = $(e);
    let m: string|number = 0, f = '0.85em';
    switch (e.tagName) {
      case "H2":
        m = "10px";
        break;
      case "H3":
        m = "25px";
        f = "0.85em";
        break;
      case "H4":
        m = "32px";
        f = "0.725em";
        break;
      case "H5":
        m = "36px";
        f = "0.675em";
        break;
      case "H6":
        m = "39px";
        f = "0.65em";
        break;
    }
    const $li = $('<li>').css('marginLeft', m).css('marginRight', m).appendTo($ul);
    const $a = $('<a>').attr('href', '#').addClass('link').text($e.text())
      .css('fontSize', f).data('to', $e).appendTo($li);
  });

  $ul.on('click', 'a', function () {
    const scrollEl: JQuery<HTMLElement> = $(this).data('to');
    const top = scrollEl.offset().top;
    
    $html.animate({
      scrollTop: top
    }, 600, "swing", function () {
      // Anim done, flash heading
      scrollEl.fadeTo(100, 0.2).fadeTo(200, 1.0);
    });
    return false;
  })

  $ul.insertAfter($('#tab6 h1').first());
}