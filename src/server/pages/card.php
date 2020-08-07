<?php

// Karte
function page_card() {
  global $conn;
  
  echo "<!DOCTYPE html><html>\n<head>\n<title>Tischlein Deck Dich</title>\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";

  $d = isset( $_POST['designs'] ) ? $_POST['designs'] : '%5B%5D';
  $f = isset( $_POST['familie'] ) ? $_POST['familie'] : '%7B%7D';
  $designs = iconv( "iso8859-9", "utf-8", rawurldecode( $d ) ); $designs = preg_replace( "/[\n\r]/", "", $designs );
  $familie = iconv( "iso8859-9", "utf-8", rawurldecode( $f ) ); $familie = preg_replace( "/[\n\r]/", "", $familie );
  ?><style>html, body { margin: 0 } #testCanvas { border: 1px solid black }</style>
  <script type="text/javascript">
    var designs = JSON.parse( "<?php echo addslashes($designs); ?>" );
    var familie = JSON.parse( "<?php echo addslashes($familie); ?>" );
    for ( var i in familie ) {
      familie[i] = unescape( familie[i] );
    }

    var formats = {
      A3: [ 297, 420 ],
      A4: [ 210, 297 ],
      A5: [ 148, 210 ]
    };
    var pxConst = 3.7795275591;

    window.onload = function() {
      var d = document.getElementById( 'design-select' );

      for ( var i = 0; i < designs.length; i++ ) {
        var e = document.createElement( 'option' );
        e.value = i;
        var t = document.createTextNode( designs[i].name );
        e.appendChild( t );
        d.appendChild( e );
      }

      d.addEventListener( 'change', changeDesign );
      changeDesign();

      var b = document.getElementById( 'drucken' );
      b.addEventListener( 'click', function() {
        var c = document.getElementById( 'testCanvas' ),
          m = document.getElementById( 'menu' );
        c.style.border = "none";
        m.style.display = "none";
        window.print();
        c.style.border = "";
        m.style.display = "";
      } );
    };

    function changeDesign() {
      var d = document.getElementById( 'design-select' ),
        c = document.getElementById( 'testCanvas' );
      if ( d.value === "" ) return;
      design = designs[d.value];

      c.innerHTML = "";

      //Set canvas size
      var f = formats[design.format];
      var format = design.format;
      if ( typeof(f) !== "undefined" ) {
        c.style.height = f[0] * pxConst;
        c.style.width = f[1] * pxConst;
        c.style.border = "";
      } else if ( typeof(format) !== "undefined" && (format.match(/x/g) || []).length == 1 ) {
        f = design.format.split( "x" );
        c.style.height = f[0] * pxConst;
        c.style.width = f[1] * pxConst;
        c.style.border = "";
      } else if ( (typeof(format) !== "undefined" && (format === "none" || format === "")) || typeof(format) === "undefined" ) {
        c.style.height = "";
        c.style.width = "";
        c.style.border = "none";
      } else {
        console.debug( "Invalid format!" );
        return;
      }

      //Add specified elements
      if ( typeof(design.elements) !== "undefined" && design.elements.constructor.name === "Array" ) {
        for ( var i = 0; i < design.elements.length; i ++ ) {
          var e = design.elements[i];
          var h = "<div style=\"";

          if ( typeof(e.css) !== "undefined" && typeof(e.position) === "undefined" ) {
            h += ";" + e.css + "\"";
          }
          if ( typeof(e.css) !== "undefined" && typeof(e.position) !== "undefined" ) {
            if ( e.position.constructor.name === "Array" && e.position.length == 2 ) {
              h += ";" + e.css + ";position:absolute;top:" + (e.position[0] * pxConst) + "px;left:" + (e.position[1] * pxConst) + "px";
            }
          }
          if ( typeof(e.css) === "undefined" && typeof(e.position) !== "undefined" ) {
            if ( e.position.constructor.name === "Array" && e.position.length == 2 ) {
              h += ";position:absolute;top:" + (e.position[0] * pxConst) + "px;left:" + (e.position[1] * pxConst) + "px";
            }
          }
          h += "\" >";
          if ( typeof(e.html) !== "undefined" ) {
            html = unescape(e.html);
            for ( var prop in familie ) {
              var rg = new RegExp( "\(\?:\^\\$\|\(\?:\(\[\^\\\\\]\)\\$\)\)" + prop, "g" );
              html = html.replace( rg, "$1" + familie[prop] );
            }
            html = html.replace( /\n/g, '<br>' );
            html = html.replace( /\\/g, '' );
            h += html;
          }
          h += "</div>";

          c.innerHTML += h;
        }
      }
    }

    function escapeRegExp( s ) {
      return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }
  </script>
  <?php
  echo "</head>\n<body>\n";

  echo "<div id=\"testCanvas\" style=\"position:relative\"></div>\n";
  echo "<div id=\"menu\">\n<select id=\"design-select\">\n";
  //echo "<option value=\"\" selected disabled hidden></option>";
  echo "</select>\n";
  echo "<button id=\"drucken\" value=\"Drucken\">Drucken</button>\n</div>\n";
  echo "</body>\n</html>";
}

?>