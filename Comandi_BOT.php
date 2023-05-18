<?php

// LIBRERIE

    include_once('Crea_PDF.php');

// GESTIONE DB

    /*
                URL per l'attivazione di WEBHOOK
            https://api.telegram.org/bot(BOT_TOKEN)/setWebHook?url=https://tuoHosting/index.php
        */

    // Token e sito web per contattare il bot
    $botToken = "6056287611:AAFZoCB83drny4Cep2SLbV6vFuH7EWUu3Ks";
    $website = "https://api.telegram.org/bot" . $botToken;

    // Ottengo le informazioni quando un utente scrive al bot
    $update = file_get_contents('php://input');

    // Aggiorna il documento
    $updateraw = $update;

    // Salva tutto in stile JSON
    $update = json_decode($update, TRUE);

    // Le informazioni che mi estraggo
    $chatID = $update["message"]["chat"]["id"];
    $message = $update["message"]["text"];
    $nome = $update["message"]["chat"]["first_name"];
    $cognome = $update["message"]["chat"]["last_name"];
    $username = $update["message"]["chat"]["username"];

    // Connessione al DB
    $conn = mysqli_connect('localhost', 'vtmod', '', 'my_vtmod');

    // Verifica la connessione
    if (!$conn) {
        die("Connessione fallita: " . mysqli_connect_error());
    }

    // Crea la tabella "utenti" se non esiste già
    $sql = "CREATE TABLE IF NOT EXISTS utenti (
            chatID VARCHAR(10) NOT NULL PRIMARY KEY,
            nome VARCHAR(30) NOT NULL,
            cognome VARCHAR(30) NOT NULL,
            username VARCHAR(30) NOT NULL
        )";

    // Verifico se riesce a creare la tabella o se ci sono evenutali errori
    if (mysqli_query($conn, $sql)) {
        echo "Tabella 'utenti' creata con successo!";
    } else {
        echo "Errore nella creazione della tabella: " . mysqli_error($conn);
    }

    // Verifica se il campo chatID è vuoto
    if (empty($chatID)) {
        echo "<br>";
        die('Errore: il campo chatID è obbligatorio!');
    }

    // Rimuovo eventuali comandi nella stringa chatID
    $chatID = mysqli_real_escape_string($conn, $chatID);

    // Query per verificare se l'utente esiste già nella tabella
    $sql = "SELECT * FROM utenti WHERE chatID = '$chatID'";
    $result = mysqli_query($conn, $sql);
    // Se l'utente esiste già, aggiorna i suoi dati
    if (mysqli_num_rows($result) > 0) {
        $sql = "UPDATE utenti SET nome = '$nome', cognome = '$cognome', username = '$username' WHERE chatID = '$chatID'";
        if (mysqli_query($conn, $sql)) {
            echo "<br>Dati utente aggiornati con successo!";
        } else {
            echo "<br>Errore nell'aggiornamento dei dati utente: " . mysqli_error($conn);
        }
    } else {
        // Se l'utente non esiste, inserisci i suoi dati nella tabella
        $sql = "INSERT INTO utenti (chatID, nome, cognome, username) VALUES ('$chatID', '$nome', '$cognome', '$username')";
        if (mysqli_query($conn, $sql)) {
            echo "<br>Nuovo utente inserito con successo!";
        } else {
            echo "<br>Errore nell'inserimento del nuovo utente: " . mysqli_error($conn);
        }
    }

    // Chiudi la connessione al database
    mysqli_close($conn);

// GESTIONE COMANDI BOT

    // Per ogni messaggio diverso che ottengo, l'utente riceve una risposta diversa
    switch ($message) {
        case '/start':
            InviaMessaggio(
                $chatID,
                "Benvenuto, " . $nome . "!"
            );
            break;

        case '/info':
            InviaMessaggio(
                $chatID,
                "<b>INFORMAZIONI BOT</b>:"
                    . "\nQuesto BOT invia automaticamente un messaggio contenente le informazioni sulle circolari uscite."
                    . "\nNome: VTBOT"
                    . "\nUsername: @VERONATRENTOBOT"
                    . "\nDescrizione: Circolari Verona Trento"
                    . "\nVersione: 2.1"
            );
            break;

        case '/circolari':
            InviaMessaggio(
                $chatID,
                "Attendere..."
            );
            require 'Crea_PDF.php';
            InviaDocumento($chatID, 'Elenco Circolari.pdf', $botToken);
            break;

        case '/comandi':
            InviaMessaggio(
                $chatID,
                "<b>ELENCO COMANDI</b>:"
                    . "\n/start - Avvia il BOT"
                    . "\n/info - Informazioni generali sul BOT"
                    . "\n/circolari - Elenco PDF di tutte le circolari"
                    . "\n/comandi - Lista comandi"
            );
            break;

        default:
            InviaMessaggio(
                $chatID,
                "Non capisco cosa vuoi fare."
                    . "\nTi ricordo che per visualizzare la lista dei comandi, puoi scrivere '/comandi'"
            );
            break;
    }


// GESTIONE INVIO MESSAGGIO E DOCUMENTO TELEGRAM

    function InviaMessaggio($chatID, $messaggio)
    {
        $url = "$GLOBALS[website]/sendMessage?chat_id=$chatID&parse_mode=HTML&text=" . urlencode($messaggio);
        file_get_contents($url);
    }

    function InviaDocumento($chat_id, $document, $botToken)
    {
        $api_url = "https://api.telegram.org/bot$botToken/sendDocument";
        $data = array(
            'chat_id' => $chat_id,
            'document' => new CURLFile(realpath($document))
        );

        $options = array(
            CURLOPT_URL => $api_url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $data
        );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
