=== Modulo Contatto AI Gemini ===
Contributors: aiagentiintelligenti
Tags: contact form, gemini, ai, lead generation, chatbot
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modulo di contatto che genera automaticamente un report di fattibilità con Google Gemini per ogni richiesta, restando sempre nel piano gratuito grazie a due limiti configurabili.

== Description ==

Ogni volta che un visitatore compila il modulo, il plugin:

1. Invia subito una email di notifica al tuo indirizzo (questo avviene sempre, anche se la parte AI è satura).
2. Chiede a Google Gemini di analizzare la richiesta e generare un mini report: sintesi, categoria di servizio, complessità stimata, tempistiche, prossimi passi.
3. Mostra il report direttamente al visitatore, subito dopo l'invio.

= Restare sempre nel piano gratuito =

Google offre un piano gratuito per i modelli Gemini "Flash", ma il numero esatto di richieste giornaliere concesse varia per account e cambia nel tempo — per questo il plugin non lo dà per scontato. Nelle impostazioni trovi due limiti indipendenti:

* **Limite giornaliero totale**: un tetto massimo di richieste AI al giorno, valido per tutti i visitatori insieme. Si azzera ogni notte. Impostalo prudenzialmente sotto al tuo limite reale (controllabile su Google AI Studio → Rate Limits) e non pagherai mai nulla, qualunque cosa succeda.
* **Limite per IP**: blocca un singolo visitatore (o bot) che invia troppe richieste di fila in pochi secondi.

Se il limite giornaliero è esaurito, il modulo continua a funzionare normalmente come contatto (l'email arriva comunque) ma mostra un messaggio configurabile invece del report AI, fino al giorno dopo.

Include anche un campo honeypot invisibile per scoraggiare i bot di spam, senza servizi esterni.

= Uso =

1. Attiva il plugin.
2. Vai in Impostazioni → Modulo Contatto AI e inserisci la tua API key gratuita di Google Gemini (ottenibile su Google AI Studio).
3. Personalizza categorie di servizio, email di notifica e i due limiti.
4. Inserisci lo shortcode `[modulo_contatto_ai]` in qualsiasi pagina o articolo.

Sviluppato da [AI Agenti Intelligenti](https://www.agentiintelligenti.it), consulenza strategica AI e sviluppo di agenti autonomi.

== Installation ==

1. Carica la cartella del plugin in `/wp-content/plugins/` oppure installalo dalla directory dei plugin di WordPress.
2. Attiva il plugin dal menu Plugin di WordPress.
3. Configura l'API key in Impostazioni → Modulo Contatto AI.
4. Inserisci lo shortcode `[modulo_contatto_ai]` dove vuoi mostrare il modulo.

== Frequently Asked Questions ==

= Come ottengo l'API key di Gemini? =

Gratuitamente su https://aistudio.google.com/apikey con un account Google.

= Come faccio a non pagare mai nulla? =

Imposta il "Limite giornaliero totale" nelle impostazioni a un valore prudenzialmente sotto il tuo limite gratuito reale (verificabile su https://aistudio.google.com/rate-limit, cambia nel tempo e per account). Il plugin si ferma da solo al raggiungimento del tetto e riparte il giorno dopo: non può mai superare quel numero di chiamate, quindi non può mai generare costi se resti sotto il piano gratuito di Google.

= Cosa succede se il limite giornaliero è raggiunto? =

Il modulo continua a funzionare come contatto normale: l'email di notifica arriva comunque, il visitatore vede un messaggio configurabile al posto del report AI.

= Serve un account a pagamento Google Cloud? =

No, il piano gratuito di Gemini non richiede carta di credito.

== External services ==

Questo plugin si collega all'API di Google Gemini (endpoint: https://generativelanguage.googleapis.com) per generare il report di fattibilità mostrato al visitatore dopo l'invio del modulo.

* **Quando**: solo quando un visitatore invia il modulo e il limite giornaliero configurato non è stato raggiunto.
* **Quali dati vengono inviati**: il nome/azienda, il servizio selezionato e la descrizione del progetto inseriti dal visitatore nel modulo. L'indirizzo email NON viene inviato a Google.
* **Autenticazione**: la tua API key personale di Google AI Studio, che configuri nelle impostazioni del plugin.
* Se l'API key non è configurata o la richiesta fallisce, nessun dato viene elaborato da Gemini: il modulo funziona comunque come contatto normale e invia solo l'email di notifica.

Termini di servizio di Google: https://policies.google.com/terms
Termini dell'API Gemini: https://ai.google.dev/gemini-api/terms
Privacy policy di Google: https://policies.google.com/privacy

== Changelog ==

= 1.0.2 =
* Nuovo: il JavaScript del modulo emette eventi pubblici (`mcag:submit`, `mcag:result`, `mcag:error`) per permettere a temi e pagine di mostrare caricamento e risultato in pannelli personalizzati.

= 1.0.1 =
* Nuovo: checkbox obbligatoria di consenso al trattamento dei dati (GDPR), con validazione sia lato client che lato server.

= 1.0.0 =
* Rilascio iniziale: modulo di contatto, report AI Gemini, limite giornaliero globale, rate limit per IP, honeypot anti-spam.
