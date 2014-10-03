## Installazione
Scarica e installa [CakePHP](http://cakephp.org).  
Rendi scrivibile la directory temporanea (`tmp/`):

	$ chmod -R 777 tmp/
	
Modifica il file `Config/core.php`, cambiando i valori delle opzioni `Security.salt` e `Security.cipherSeed`.  

Crea un nuovo database, rinomina il file `Config/database.php.default` in `Config/database.php` e modificalo 
inserendo i dati per la connessione al database.

Scarica ed estrai nella directory `Plugin/` i plugin [MeTools](//github.com/mirko-pagliai/MeTools) e MeCms, 
opzionalmente anche [DebugKit](//github.com/cakephp/debug_kit/releases).  
Nella webroot dell'applicazione copia o meglio crea un link simbolico alle webroot dei plugin. Ad esempio:

	$ cd webroot/
	$ ln -s ../Plugin/MeCms/webroot/ MeCms
	$ ln -s ../Plugin/MeTools/webroot/ MeTools
	
Abilita i plugin, aggiungendo in `Config/bootstrap.php`:

	CakePlugin::loadAll(array(
		array('routes' => TRUE, 'bootstrap' => TRUE, 'ignoreMissing' => TRUE)
	));
	
Modifica il file `Config/routes.php`, eliminando le due rotte presenti. Alla fine, il file dovrà contenere solo:

	CakePlugin::routes();
	require CAKE . 'Config' . DS . 'routes.php';
	
Ora è possibile installare MeCms, eseguendo nella shell:

	$ cake MeCms.install

Vengono create le tabelle necessarie, con alcuni dati di esempio, il primo utente amministratore e le directory 
necessarie.

L'installazione è terminata e ora puoi accedere al sito. Per accedere al pannello di amministrazione, devi andare 
all'indirizzo `http://localhost/your-site/admin` ed effettuare il login con i dati indicati durante l'installazione.

## Configurazione
È possibile impostare il funzionamento di MeCms modificando il file di configurazione `APP/Plugin/MeCms/Config.mecms.php`.  
Tuttavia, è consigliato non modificare direttamente il file, ma copiarlo nella configurazione della propria applicazione 
(`APP/Config`). MeCms cercherà il file di configurazione prima nell'applicazione, poi nel plugin.

## Temi
Per utilizzare un tema, creare o installare il tema in `APP/View/Themed/`. Ad esempio, per utilizzare il tema 
`AnotherTheme` e riscrivere il layout del frontend, bisognerà creare il file 
`APP/View/Themed/AnotherTheme/Layout/frontend.ctp`.

Per abilitare il tema, modifica la configurazione di MeCms.

## Pagine statiche
MeCms può utilizzare le pagine statiche, oltre a quelle gestite dal database.

È sufficiente creare la directory `APP/View/StaticPages/`.  
Ad esempio, richiamando l'url `http://localhost/your-site/page/about/our-staff` verrà mostrata la view 
`APP/View/StaticPages/about/our-staff.ctp`.

MeCms darà sempre la precedenza alle pagine statiche. Questo significa che se esistono una pagina statica e una pagina nel
database con lo stesso nome, MeCms utilizzerà la pagina statica.

## Librerie e script
MeCms utilizza le librerie e gli script inclusi con [MeTools](//github.com/mirko-pagliai/MeTools#libraries-and-script).  
Oltre questi, MeCms include:

- fancyBox 2.1.5 ([sito](http://fancyapps.com/fancybox)).