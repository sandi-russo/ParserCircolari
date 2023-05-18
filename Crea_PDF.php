<?php

// LIBRERIE

// Includi la libreria dompdf
require_once('dompdf/autoload.inc.php');

// URL della pagina web da convertire
$url = 'http://vtmod.altervista.org/ParserCircolari/Tabella.php';

// Opzioni di dompdf
$options = new Dompdf\Options();
$options->set('isRemoteEnabled', true);

// Creazione istanza di dompdf
$dompdf = new Dompdf\Dompdf($options);

// Recupero il contenuto HTML della pagina web
$html = file_get_contents($url);

// Aggiunta del contenuto HTML a dompdf
$dompdf->loadHtml($html);

// Rendering del PDF
$dompdf->render();

// Salvataggio del PDF su file
$server_directory = getcwd();
$dir = $server_directory . "/Elenco Circolari.pdf";
file_put_contents($dir, $dompdf->output());

// Controllo se il file PDF è stato creato con successo
if (is_file("Elenco Circolari.pdf")) {
    // Visualizzazione dell'iframe per il PDF
    $iframe = '<iframe src="Elenco Circolari.pdf" style="width:100%; height:95%;"></iframe>';
    echo '<b>Il file è stato creato!</b>' . $iframe;
} else {
    echo "C'è stato qualche problema nella creazione del file! :(";
}

?>