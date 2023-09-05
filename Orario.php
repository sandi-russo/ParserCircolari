<?php


// URL del sito web da controllare
$url = "https://vt2020.myqnapcloud.com:8081/orario/";

// Analizzo l'URL per poter estrarre il suo schema
$url_parsed = parse_url($url);

// Riconosce lo schema del link e che la parte fissa è 'veronatrento.it'
$domain = $url_parsed['scheme'] . '://' . $url_parsed['host'];

// Scarica il codice HTML dal sito web
$html = file_get_html($url);

?>
