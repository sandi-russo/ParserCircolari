<?php

include_once('index.php');

// Trasforma l'array PHP in una stringa JSON
$circolare_json = json_encode($circolare);

// Restituisci i dati come risposta al frontend (assicurati di impostare l'intestazione Content-Type su JSON)
header('Content-Type: application/json');
echo $circolare_json;
