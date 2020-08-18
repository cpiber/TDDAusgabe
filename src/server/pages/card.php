<?php

// Karte
function page_card() {
  global $conn;
  
  echo "<!DOCTYPE html><html>\n<head>\n<title>Tischlein Deck Dich</title><meta charset=\"UTF-8\">\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";

  ?><style>html, body { margin: 0 } body { padding: 1px } #testCanvas { border: 1px solid black }</style>
  <script type="text/javascript" src="?file=cardjs"></script>
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