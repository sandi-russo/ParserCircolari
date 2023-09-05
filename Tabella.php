<?php

// LIBRERIE
include_once('index.php');

// TABELLA CON LE INFORMAZIONI SULLE CIRCOLARI

// Creo le colonne della tabella

echo "<table style='width:100%; border: 1px solid black; border-collapse: collapse;'>";
echo "<thead>
        <tr style='border: 1px solid black;'>
          <th colspan='3' style='text-align: center; vertical-align: middle; font-size:16px; border: 1px solid black; position: sticky;'>ELENCO CIRCOLARI</th>
        </tr>
        <tr style='border: 1px solid black;'>
          <th style='text-align: center; vertical-align: middle; font-size:14px; width: 5%; border: 1px solid black; padding: 5px;'><b>NUMERO</b></th>
          <th style='text-align: center; vertical-align: middle; font-size:14px; width: 60%; border: 1px solid black; padding: 5px;'><b>DESCRIZIONE</b></th>
          <th style='text-align: center; vertical-align: middle; font-size:14px; width: 5%; border: 1px solid black; padding: 5px;'><b>DATA</b></th>
        </tr>
      </thead>";

// Ciclo per la stampa dell'array
foreach ($circolare as $item) {
    echo "<tr>";
    echo "<td style='text-align: center; vertical-align: middle; font-size:12px; width: 5%; border: 1px solid black; padding: 5px;'><a href='$item[link]' onclick='window.open(this.href,\"_blank\"); return false;'>$item[numero]</a></td>";
    echo "<td style='text-align: left; vertical-align: middle; font-size:12px; width: 60%; border: 1px solid black; padding: 5px;'>$item[descrizione]</td>";
    echo "<td style='text-align: center; vertical-align: middle; font-size:12px; width: 5%; border: 1px solid black; padding: 5px;'>$item[data]</td>";
    echo "</tr>";
}
echo "</table>";
