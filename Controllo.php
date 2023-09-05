<?php


// Connessione al database
$servername = "localhost";
$username = "vtmod";
$password = "";
$dbname = "my_vtmod";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Crea la tabella circolari se non esiste già
$sql = "CREATE TABLE IF NOT EXISTS circolari (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) NOT NULL,
    descrizione TEXT NOT NULL,
    data varchar(50) NOT NULL,
    link VARCHAR(255) NOT NULL
)";

if ($conn->query($sql) !== TRUE) {
    echo "Errore durante la creazione della tabella circolari: " . $conn->error;
}

// Include il file "index.php" per ottenere l'array delle circolari
include_once('index.php');

// Verifica quali circolari sono presenti nell'array ma non nel database
$nuove_circolari = array();

foreach ($circolare as $c) {
    $numero = mysqli_real_escape_string($conn, $c['numero']);
    $sql = "SELECT numero FROM circolari WHERE numero = '$numero'";
    $result = $conn->query($sql);

    if ($result->num_rows === 0) {
        $nuove_circolari[] = $c;
    }
}

// Se sono state trovate nuove circolari, aggiungile al database e invia un messaggio a tutti gli utenti
if (!empty($nuove_circolari)) {

    $nrpersone = 0;

    $nuove_circolari = array_reverse($nuove_circolari);
    foreach ($nuove_circolari as $c) {
        // Aggiungi la nuova circolare al database
        $numero = mysqli_real_escape_string($conn, $c['numero']);
        $descrizione = mysqli_real_escape_string($conn, $c['descrizione']);
        $data = mysqli_real_escape_string($conn, $c['data']);
        $link = mysqli_real_escape_string($conn, $c['link']);

        $sql = "INSERT INTO circolari (numero, descrizione, data, link) VALUES ('$numero', '$descrizione', '$data', '$link')";
        if ($conn->query($sql) !== TRUE) {
            echo "Errore durante l'inserimento della nuova circolare: " . $conn->error;
        }

        // Invia il messaggio a tutti gli utenti
        $message = "<b>Nuova circolare</b> 📑:"
            . "\n<b>Numero</b>: " . $c['numero']
            . "\n\n<b>Descrizione</b>: " . $c['descrizione']
            . "\n\n<b>Data</b>: " . $c['data'];

        $sql = "SELECT chatID FROM utenti";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $chatIDs = array();
            while ($row = $result->fetch_assoc()) {
                $chatIDs[] = $row['chatID'];
            }

            // Invia il messaggio a tutti gli utenti
            $botToken = "6670254948:AAHiCPDQbLjKxML6QzTMDHGy3kE67LwKCYA";
            $website = "https://api.telegram.org/bot" . $botToken;

            // Invia il documento PDF
            $path_to_pdf = "circolari/Circolare - " . $c["numero"] . ".pdf"; // Percorso al file PDF

            foreach ($chatIDs as $chatID) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $website . "/sendDocument",
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => array(
                        "chat_id" => $chatID,
                        "document" => new CURLFile($path_to_pdf),
                        "caption" => $message,
                        "parse_mode" => "HTML"
                    ),
                    CURLOPT_RETURNTRANSFER => true
                ));

                $response = curl_exec($curl);

                if ($response === false) {
                    echo "Errore nell'invio del messaggio: " . curl_error($curl);
                }

                curl_close($curl);
            }
            $nrpersone++;
        }
    }

    echo "Messaggio inviato a: " . $nrpersone . " persone!";
} else {
    // Nessuna nuova circolare, mostra un messaggio sulla pagina web
    $last_circular = reset($circolare);
    echo "Nessuna nuova circolare.<br>L'ultima circolare rimane la numero: <b>" . $last_circular["numero"] . "</b>";
    $conn->close();
}
