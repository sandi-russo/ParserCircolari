<?php

$filename = $_GET["nome"]; // Il nome del file da scaricare
$file_path = "circolari/Circolare - $filename.pdf"; // Il percorso completo al file PDF
echo($file_path);
// Verifica che il file esista
if (file_exists($file_path)) {
    // Imposta gli header per forzare il download
    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=" . "Circolare - " . $filename. ".pdf");
    
    // Leggi il file e invialo in output
    readfile($file_path);
} else {
    // Il file non esiste
    echo "Il file non esiste.";
}
?>