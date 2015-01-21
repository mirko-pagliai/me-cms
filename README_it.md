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
	
Modifica il file `Config/routes.php`, eliminando le rotte di default presenti. Alla fine, il file dovrà contenere solo:

	<?php

		CakePlugin::routes();
		require CAKE . 'Config' . DS . 'routes.php';

La classe `AppController` presente in `APP/Controller` deve estendere `MeToolsAppController`:

	<?php

		App::uses('MeToolsAppController', 'MeTools.Controller');

		class AppController extends MeToolsAppController {

Invece, se nella tua applicazione devi creare nuovi controller, questi dovranno estendere `MeCmsAppController`. Ad esempio:

	<?php

		App::uses('MeCmsAppController', 'MeCms.Controller');
	
		class StuffController extends MeCmsAppController {

Opzionalmente, impostare un nuovo prefisso per i file della cache modificando la relativa riga nel file `Config/core.php`:

	$prefix = 'myapp_';
	
Ora è possibile installare MeCms, eseguendo nella shell:

	$ cake MeCms.install

Vengono create le tabelle necessarie, con alcuni dati di esempio, il primo utente amministratore e le directory 
necessarie.

L'installazione è terminata e ora puoi accedere al sito. Per accedere al pannello di amministrazione, devi andare 
all'indirizzo `http://localhost/your-site/admin` ed effettuare il login con i dati indicati durante l'installazione.

## Configurazione
La configurazione di MeCms si trova in `APP/Plugin/MeCms/Config/mecms.php`.  
Non modificare questo file! Se vuoi modificare la configurazione, copia il file all'intero della tua applicazione, 
in `APP/Config`. È sufficiente impostare solo le opzioni che si desidera sovrascrivere.  
MeCms caricherà prima il proprio file di configurazione, successivamente quello presente nell'applicazione, se esiste.
I valori impostati nel file presente nell'applicazione sovrascriveranno quelli impostati nel file di MeCms.

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

Per un maggiore controllo delle pagine statiche, puoi estendere la view presente in MeCms. Ad esempio:

	<?php
		$this->set('title_for_layout', 'My custom page');
		$page['Page']['title'] = 'My custom page';
		ob_start();
	?>
	<p>This is my custom page</p>
	<?php
		$page['Page']['text'] = ob_get_clean();
		echo $this->Html->div('pages view', $this->element('view/page', compact('page')));
	?>

## KCFinder
Se si desidera utilizzare [KCFinder](http://kcfinder.sunhater.com), scaricare e scompattare il pacchetto in 
`APP/webroot/kcfinder`. 

Aggiungere alla fine del file `APP/webroot/kcfinder/core/autoload.php`:

	ini_set('session.cache_limiter', "must-revalidate");
	ini_set('session.cookie_httponly', "On");
	ini_set('session.cookie_lifetime', "14400");
	ini_set('session.gc_maxlifetime', "14400");
	ini_set('session.name', "CAKEPHP");

Per gli upload verrà utilizzata la directory `APP/webroot/files`. Verificare che la directory esista e che sia leggibile e 
scrivibile.

### KCFinder con CKEditor
Copia il file `APP/Plugin/MeTools/webroot/ckeditor/ckeditor_init.js` in `APP/webroot/js`. Modifica il file, decommentando le
ultime linee e modificando la posizione di KCFinder:

	filebrowserBrowseUrl:		'../../kcfinder/browse.php?type=files',
	filebrowserImageBrowseUrl:	'../../kcfinder/browse.php?type=images',
	filebrowserFlashBrowseUrl:	'../../kcfinder/browse.php?type=flash',
	filebrowserUploadUrl:		'../../kcfinder/upload.php?type=files',
	filebrowserImageUploadUrl:	'../../kcfinder/upload.php?type=images',
	filebrowserFlashUploadUrl:	'../../kcfinder/upload.php?type=flash'

## Librerie e script
MeCms utilizza le librerie e gli script inclusi con [MeTools](//github.com/mirko-pagliai/MeTools#libraries-and-script).  
Oltre questi, MeCms include:

- fancyBox 2.1.5 ([sito](http://fancyapps.com/fancybox));
- Roboto font ([sito](http://google.com/fonts#UsePlace:use/Collection:Roboto)).