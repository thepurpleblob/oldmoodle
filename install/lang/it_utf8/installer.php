<?php
/// Please, do not edit this file manually! It's auto generated from
/// contents stored in your standard lang pack files:
/// (langconfig.php, install.php, moodle.php, admin.php and error.php)
///
/// If you find some missing string in Moodle installation, please,
/// keep us informed using http://moodle.org/bugs Thanks!
///
/// File generated by cvs://contrib/lang2installer/installer_builder
/// using strings defined in installer_strings (same dir)

$string['admindirerror'] = 'La directory di amministrazione specificata non è corretta';
$string['admindirname'] = 'Directory di amministrazione';
$string['admindirsettinghead'] = 'Impostazione la directory admin...';
$string['bypassed'] = 'Aggirato';
$string['caution'] = 'Attenzione';
$string['check'] = 'Controlla';
$string['chooselanguagehead'] = 'Scegli la lingua';
$string['closewindow'] = 'Chiudi questa finestra';
$string['compatibilitysettingshead'] = 'Controllo impostazioni PHP...';
$string['configfilenotwritten'] = 'Il sistema di installazione non è in grado di creare il file config.php contenente le vostre impostazioni, probabilmente perchè la directory di Moodle non è scrivibile. È possbile copiare manualmente il codice seguente in un file chiamato config.php nella directory principale di Moodle.';
$string['configfilewritten'] = 'Il config.php è stato creato correttamente';
$string['configurationcompletehead'] = 'Configurazione completata';
$string['continue'] = 'Continua';
$string['database'] = 'Base di dati';
$string['dataroot'] = 'Directory dati';
$string['datarooterror'] = 'La \'Directory dati\' specificata non può essere trovata o creata. È possibile correggere il percorso o crearla manualmente.';
$string['dbconnectionerror'] = 'Non è possibile connettersi alla base dati specificata. Controllare le impostazioni della base dati.';
$string['dbcreationerror'] = 'Errore durante la creazione della base dati. Non è possibile creare una base dati con le impostazioni fornite.';
$string['dbhost'] = 'Server della base dati';
$string['dbprefix'] = 'Prefisso tabelle';
$string['dbtype'] = 'Tipo';
$string['dirroot'] = 'Directory di Moodle';
$string['dirrooterror'] = 'L\'impostazione \'Directory di Moodle\' sembra essere scorretta - non è possibile trovare un\'installazione di Moodle nel percorso specificato. Il valore sotto è stato ripristinato.';
$string['download'] = 'Download';
$string['environmenterrortodo'] = 'Dovete risolvere tutti i problemi relativi all\'ambiente (errori) trovati qui sopra prima di procedere con l\'installazione di questa versione di Moodle!';
$string['environmentrecommendinstall'] = 'è raccomandata l\'installazione/abilitazione';
$string['environmentrecommendversion'] = 'È raccomandata la versione $a->needed e la vostra versione attuale è $a->current';
$string['environmentrequireinstall'] = 'è necessaria l\'installazione/abilitazione';
$string['environmentrequireversion'] = 'È necessaria la versione $a->needed e la vostra versione attuale è $a->current';
$string['environmentxmlerror'] = 'Errore durante la lettura dei dati dell\'ambiente ($a->error_code)';
$string['error'] = 'Errore';
$string['fail'] = 'Fallito';
$string['fileuploads'] = 'Invio file';
$string['fileuploadserror'] = 'Questo deve essere impostato a on';
$string['gdversion'] = 'Versione GD';
$string['gdversionerror'] = 'La libreria GD deve essere presente per elaborare e creare immagini';
$string['help'] = 'Aiuto';
$string['iconvrecommended'] = 'Installare la libreria opzionale ICONV &egrave; caldamente consigliato per migliorare le prestazioni del sito, in particolare se il vostro sito supporta lingue non latine.';
$string['info'] = 'Informazioni';
$string['installation'] = 'Installazione';
$string['language'] = 'Lingua';
$string['magicquotesruntime'] = 'Magic Quotes Run Time';
$string['magicquotesruntimeerror'] = 'Questo deve essere impostato a off';
$string['memorylimit'] = 'Limite memoria';
$string['memorylimiterror'] = 'Il limite di memoria del PHP è impostato a un valore basso ... potrebbero verificarsi probremi in futuro.';
$string['memorylimithelp'] = '<p>Il limite della memoria assegnata a PHP attualmente è $a.</p>
<p>Questo può dare problemi a Moodle in futuro, specialmente se avete molti moduli abilitati e molti utenti.</p>
<p>Vi raccomandiamo di impostare il PHP con un limite più alto se possibile, ad esempio 16M.
Ci sono diversi modi che potete provare:
<ol>
<li>Se possibile, ricompilare il PHP con l\'opzione <i>--enable-memory-limit</i>.
Questo permetterà  a Moodle di impostare il limite di memoria da solo.</li>
<li>Se avete accesso al file php.ini, è possibile modificare l\'impostazione <b>memory_limit</b> a un valore tipo 16M. Se non avete l\'accesso potete chiedere al vostro amministratore di sistema di farlo.</li>
<li>Su alcuni server PHP è possibile creare un file .htaccess nella Directory di Moodle che contenga questa linea:
<blockquote>php_value memory_limit 16M</blockquote>
<p>Tuttavia, su alcuni server questo impedirà  a <b>tutte</b> le pagine PHP di funzionare (vedrete degli errori quando visualizzerete le pagine) cosi dovrete rimuovere il file .htaccess.</li></ol>';
$string['mysql416bypassed'] = 'Comunque, se il vostro sito sta utilizzando SOLO lingue iso-8859-1 (latin), potete continuare ad utilizzare MySQL 4.1.12 (o successivo) attualmente installato.';
$string['mysql416required'] = 'MySQL 4.1.16 &egrave; la versione minima richiesta per Moodle 1.6 per garantire che tutti i dati possano essere convertiti in UTF-8 in futuro.';
$string['mysqlextensionisnotpresentinphp'] = 'Il PHP non è stato correttamente configurato con l\'estensione di MySQL. Controllate il vostro php.ini o ricompilate il PHP.';
$string['name'] = 'Nome';
$string['next'] = 'Prossimo';
$string['ok'] = 'OK';
$string['pass'] = 'Passato';
$string['password'] = 'Password';
$string['phpversion'] = 'Versione PHP';
$string['phpversionerror'] = 'La versione del PHP deve essere come minimo la 4.1.0';
$string['phpversionhelp'] = '<p>Moodle richiede come minimo la versione 4.1.0 del PHP.</p>
<p>Attualmente state utilizzando la versione $a</p>
<p>È necessario aggiornare il PHP o spostarsi su un server con una versione di PHP più recente!</p>';
$string['previous'] = 'Precedente';
$string['report'] = 'Risultato';
$string['safemode'] = 'Safe Mode';
$string['safemodeerror'] = 'Moodle può avere problemi con il safemode impostato a on';
$string['sessionautostart'] = 'Session Auto Start';
$string['sessionautostarterror'] = 'Questo deve essere off';
$string['status'] = 'Status';
$string['thischarset'] = 'UTF-8';
$string['thislanguage'] = 'Italiano';
$string['user'] = 'Utente';
$string['wwwroot'] = 'Indirizzo web';
$string['wwwrooterror'] = 'L\'indirizzo web sembra non essere valido - questa installazione di Moodle non sembra esere li.';
?>