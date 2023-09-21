<?php

// LIBRERIE

include_once('Crea_PDF.php');

// GESTIONE DB

/*
        URL per l'attivazione di WEBHOOK
        https://api.telegram.org/bot(BOT_TOKEN)/setWebHook?url=https://tuoHosting/index.php
        */

// Token e sito web per contattare il bot
$botToken = "6670254948:AAHiCPDQbLjKxML6QzTMDHGy3kE67LwKCYA";
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


$keyboardButtons = [
    "Avvia 🚀" => '/start',
    "Elenco Circolari 📚" => '/circolari',
    "Sito Web 🌍" => '/sitoweb',
    "Orario 🕗" => '/orario',
    "Elenco Comandi 📝" => '/comandi',
    "Informazioni BOT 🤖" => '/info',
    "Esci ❌" => '/esci'
];

$keyboard = [
    "keyboard" => [
        ["Elenco Circolari 📚", "Sito Web 🌍"],
        ["Orario 🕗", "Informazioni BOT 🤖",],
        ["Elenco Comandi 📝"],
        ["Esci ❌"]
    ],
    "resize_keyboard" => true,
    "one_time_keyboard" => false
];

$encoded_keyboard = json_encode($keyboard);


// Controlla se il messaggio è un comando diretto o corrisponde a un tasto sulla tastiera Reply

if (substr($message, 0, 1) === '/' || isset($keyboardButtons[$message])) {
    if (substr($message, 0, 1) === '/') {
        $action = $message;
    } else {
        $action = $keyboardButtons[$message];
    }
    switch ($action) {
        case '/start':
            InviaMessaggio(
                $chatID,
                "Benvenuto/a, " . $nome . "! 🤝",
                $encoded_keyboard
            );
            break;

        case '/info':
            InviaMessaggio(
                $chatID,
                "<b>INFORMAZIONI BOT</b>:"
                    . "\n🌟 Il nostro Bot è qui per tenerti aggiornato sull'orario scolastico e per inviarti automaticamente le circolari più importanti. Siamo il Bot ufficiale del Verona Trento."
                    . "\n\n🔄 Con la versione 1.2, abbiamo reso il Bot più efficiente e user-friendly per migliorare la tua esperienza."
                    . "\n\n🚀 Ma non finisce qui! Stiamo lavorando duramente per portarti nuove funzionalità esclusive, tra cui la personalizzazione dell'orario e l'integrazione completa con le potenti API di Google.",
                $encoded_keyboard
            );
            break;

        case '/circolari':
            InviaMessaggio(
                $chatID,
                "Attendere... ⏳",
                $encoded_keyboard
            );
            require 'Crea_PDF.php';
            InviaDocumento($chatID, $NomePDF, $botToken);
            break;

        case '/comandi':
            InviaMessaggio(
                $chatID,
                "<b>ELENCO COMANDI</b>:"
                    . "\n/start - Avvia il BOT"
                    . "\n/circolari - Elenco PDF di tutte le circolari"
                    . "\n/info - Informazioni generali sul BOT"
                    . "\n/orario - WebApp per visualizzare l'orario scolastico"
                    . "\n/sitoweb - Elenco PDF di tutte le circolari"
                    . "\n/comandi - Lista comandi",
                $encoded_keyboard
            );
            break;

        case '/esci':
            InviaMessaggio(
                $chatID,
                "A presto, " . $nome . "! 👋",
                json_encode(['remove_keyboard' => true])
            );

            Avvia($keyboard, $encoded_default_keyboard, $chatID);
            break;

        case '/orario':
            $inline_keyboard = [
                [
                    ['text' => 'Visualizza Orario', 'url' => 'https://t.me/VERONATRENTOBOT/Orario']
                ]
            ];
            $encoded_inline_keyboard = json_encode(['inline_keyboard' => $inline_keyboard]);
            InviaMessaggio(
                $chatID,
                "Clicca sul pulsante '<b>Visualizza Orario</b>' per aprire l'orario nella Telegram Web App.",
                $encoded_inline_keyboard
            );
            break;


            case '/sitoweb':
                // URL della pagina web che desideri aprire nel browser di Telegram
                $webpageURL = 'circolari.veronatrento.it';
            
                // Crea un messaggio di tipo "inline keyboard" con un pulsante che apre la pagina web
                $inline_keyboard = [
                    [
                        ['text' => 'Visualizza Sito Web', 'url' => $webpageURL]
                    ]
                ];
                $encoded_inline_keyboard = json_encode(['inline_keyboard' => $inline_keyboard]);
            
                // Invia il messaggio con il link alla pagina web
                InviaMessaggio(
                    $chatID,
                    "Clicca sul pulsante '<b>Visualizza Sito Web</b>' per aprire il sito web delle circolari nella Telegram Web App.",
                    $encoded_inline_keyboard
                );
                break;

        default:
            Avvia($keyboard, $encoded_default_keyboard, $chatID);
            break;
    }
} else {
    $imagePath = "ERROR.jpg"; // Imposta il percorso dell'immagine corretto
    InviaImmagine($chatID, $imagePath, $botToken);
    Avvia($keyboard, $encoded_default_keyboard, $chatID);
}




function Avvia($keyboard, $encoded_default_keyboard, $chatID)
{
    $keyboard["keyboard"] = [["Avvia 🚀"]];
    $encoded_default_keyboard = json_encode($keyboard);
    InviaMessaggio($chatID, "Premi '<b>Avvia 🚀</b>' per poter utilizzare il BOT!", $encoded_default_keyboard);
}



// GESTIONE INVIO MESSAGGIO E DOCUMENTO TELEGRAM

function InviaMessaggio($chatID, $messaggio, $keyboard)
{
    $url = "$GLOBALS[website]/sendMessage";

    $postData = array(
        'chat_id' => $chatID,
        'parse_mode' => 'HTML',
        'text' => $messaggio,
        'reply_markup' => $keyboard
    );

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        // Gestione dell'errore
        echo 'Errore cURL: ' . curl_error($ch);
    }

    curl_close($ch);

    return $response;
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


function InviaImmagine($chatID, $imagePath, $botToken)
{
    $url = "https://api.telegram.org/bot" . $botToken . "/sendPhoto";

    $postFields = array(
        'chat_id' => $chatID,
        'photo' => new CURLFile(realpath($imagePath))
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Errore cURL: ' . curl_error($ch);
    }

    curl_close($ch);

    return $output;
}
