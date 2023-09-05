<?php

// LIBRERIE

// Includi la libreria dompdf
require_once('dompdf/autoload.inc.php');

// URL della pagina web da convertire
$url = 'http://vtmod.altervista.org/ParserCircolari/Tabella.php';

// Opzioni di dompdf
$options = new Dompdf\Options();
$options->set('isRemoteEnabled', true);
$options->set('isPhpEnabled', true);

// Creazione istanza di dompdf
$dompdf = new Dompdf\Dompdf($options);


// Recupero il contenuto HTML della pagina web
$html = file_get_contents($url);


// Aggiunta del contenuto HTML a dompdf
$dompdf->loadHtml($html);

// Rendering del PDF
$dompdf->render();


// Instantiate canvas instance 
$canvas = $dompdf->getCanvas();

// Get height and width of page 
$w = $canvas->get_width();
$h = $canvas->get_height();

// Specify watermark image 
$imageURL = 'http://vtmod.altervista.org/ParserCircolari/logo.png';
$imgWidth = 200;
$imgHeight = 200;

// Set image opacity 
$canvas->set_opacity(.1);

// Specify horizontal and vertical position 
$x = (($w - $imgWidth) / 2);
$y = (($h - $imgHeight) / 2);

// Add an image to the pdf 
$canvas->image($imageURL, $x, $y, $imgWidth, $imgHeight);

$Anno_corrente = date("Y");
$Mese_corrente = date("n");
if ($Mese_corrente < 9) {
  $Anno_scolastico = ($Anno_corrente - 1) . "-" . $Anno_corrente;
} else {
  $Anno_scolastico = $Anno_corrente . "-" . ($Anno_corrente + 1);
}
$NomePDF = "Elenco Circolari " . $Anno_scolastico . ".pdf";

// Salvataggio del PDF sul server
$server_directory = getcwd();
$dir = $server_directory . "/" . $NomePDF;
file_put_contents($dir, $dompdf->output());

// Controllo se il file PDF è stato creato con successo
if (is_file($NomePDF)) {
  // Visualizzazione dell'iframe per il PDF
  $iframe = '<iframe src="' . $NomePDF . '" style="width:100%; height:95%;"></iframe>';
  echo '<b>Il file è stato creato!</b>' . $iframe;
} else {
  echo "C'è stato qualche problema nella creazione del file! :(";
}
