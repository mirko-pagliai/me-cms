# 2.x ramo
## 2.1 ramo
### 2.1.1-RC3
* nel pannello di amministrazione, alcune viste sono state collegate tra loro;
* i tag possono contenere il trattino;
* migliorata la console d'installazione;
* risolto bug nella visualizzazione dei tag nel frontend;
* corretto il titolo di alcune azioni;
* risolto bug filtrando gli utenti per gruppo.

### 2.1.0-RC2
* è possibile aggiungere tag agli articoli;
* è possibile elencare gli articoli per data;
* aggiunto il supporto per Shareaholic;
* jQuery-cookie, Fancybox e KCFinder sono installati tramite Composer;
* migliorata la gestione dei file di log;
* aggiunto layout e template per gli errori;
* sistemati piccoli bug.

### 2.0.1-RC1
* risolto bug, ora la cache viene svuotata automaticamente, se c'è un articolo post-datato da pubblicare;
* risolto bug nel login con i cookie;
* permessi sistemati;
* aggiunge automaticamente i meta tag per le risorse RSS;
* i menu del backend vengono generati in maniera totalmente automaticamente. Non è più necessaria nessuna configurazione;
* ora è possibile scegliere quali dettagli degli articoli mostri usando il file di configurazione;
* ora è possibile impostare la timezone usando il file di configurazione;
* sistemato un bug con la data degli articoli.

### 2.0.0-alpha
* tutto il codice è stato interamente riscritto per CakePHP 3.x. Sono state applicate svariate ottimizzazioni;
* il caricamento/aggiunta di file (ad esempio, banner e foto) è stato fortemente semplificato e ottimizzato;
* l'applicazione può ora riscrivere la configurazione della cache;
* il sistema e la configurazione dei widget sono stati semplificati;
* il plugin accede più facilmente alla configurazione;
* ogni layout dispone di una copia ottimizzata di Bootstrap;
* il backend fa un uso maggiore delle cache;
* aggiornato Bootstrap alla versione 3.3.5.

# 1.x ramo
## 1.2 ramo
### 1.2.3
* aggiunto il widget "album";
* migliorato il codice dei widget. I widget chiamano alcuni metodi per recuperare i dati;
* migliorato il checkup del sistema, ora vengono visualizzate le path delle directory.

### 1.2.2
* aggiunto un form per filtrare i banner, le pagine, gli articoli e gli utenti;
* aggiunto un form di contatto;
* gli utenti in attivazione possono richiedere il rinvio della email di attivazione;
* è possibile passare opzioni ai widget;
* alcuni widget accettano l'opzione `limit`, che indica il numero di record da mostrare.

### 1.2.1
* supporto completo per reCAPTCHA. Viene utilizzato per la registrazione e per reimpostare la password;
* gli utenti possono iscriversi. È possibile impostare la modalità con cui deve essere attivato un account;
* gli utenti possono reimpostare la propria password;
* viene inviata una email quando l'utente cambia la propria password;
* gli amministratori possono attivare manualmente gli account;
* è stata migliorata l'organizzazione della configurazione.

### 1.2.0
* aggiunto visualizzatore di log e il visualizzatore dei changelog;
* risolto bug per la topbar del backend. La topbar viene interamente mostrata solo su dispositivi mobili. Aggiunta la sidebar;
* i widget vengono nascosti sulle pagine che contengono le stesse informazioni o gli stessi dati;
* viene verificata se l'ultima ricerca è stata effettuata dopo l'intervallo minimo;
* viene mostrata la versione di Apache e di PHP;
* i permessi di KCFinder sono basati sui permessi di MeCms;
* alcuni pulsanti vengono disabilitati dopo il click, per evitare che alcune azioni vengano eseguite più volte;
* il nome degli utenti e dei gruppi utente non può essere modificato. Migliorati i permessi su utenti e gruppi utente;
* aggiunto il file di changelog.