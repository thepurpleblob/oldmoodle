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

$string['admindirerror'] = 'Adresář správy (admin) není určen správně';
$string['admindirname'] = 'Adresář správy (admin)';
$string['caution'] = 'Varování';
$string['closewindow'] = 'Zavřít toto okno';
$string['configfilenotwritten'] = 'Instalační skript nemohl automaticky vytvořit soubor config.php s vaší konfigurací - pravděpodobně z důvodů nastavení práv k zápisu do adresáře Moodle. Můžete ručně zkopírovat následující kód do souboru s názvem config.php v hlavním adresáři vaší instalace Moodle.';
$string['configfilewritten'] = 'config.php byl úspěšně vytvořen';
$string['continue'] = 'Pokračovat';
$string['database'] = 'Databáze';
$string['dataroot'] = 'Datový adresář';
$string['datarooterror'] = 'Vámi specifikovaný datový adresář nebyl nalezen a nemohl být vytvořen. Buď opravte vloženou cestu, nebo vytvořte adresář ručně.';
$string['dbconnectionerror'] = 'Nemůžu se spojit s databází, kterou jste specifikovali. Prosím, zkontrolujte nastavení databáze.';
$string['dbcreationerror'] = 'Chyba při vytváření databáze. Nelze vytvořit databázi daného jména s poskytnutým nastavením';
$string['dbhost'] = 'Hostitelský server';
$string['dbprefix'] = 'Předpona tabulek';
$string['dbtype'] = 'Typ';
$string['dirroot'] = 'Moodle adresář';
$string['dirrooterror'] = 'Hodnota \'Moodle adresář\' nevypadá nastavená správně - nemůžu tam najít Moodle instalaci. Následující hodnota byla resetována.';
$string['download'] = 'Stáhnout';
$string['error'] = 'Chyba';
$string['fail'] = 'Selhalo';
$string['fileuploads'] = 'Nahrané soubory (uploads)';
$string['fileuploadserror'] = 'Mělo by být zapnuto';
$string['gdversion'] = 'Verze GD';
$string['gdversionerror'] = 'Knihovna GD je potřebná ke zpracovávání a tvorbě obrázků (např. fotografie, grafy apod.)';
$string['help'] = 'Nápověda';
$string['info'] = 'Informace';
$string['installation'] = 'Instalace';
$string['language'] = 'Jazyk';
$string['magicquotesruntime'] = 'Magic Quotes Run Time';
$string['magicquotesruntimeerror'] = 'Mělo by být vypnuto';
$string['memorylimit'] = 'Limit paměti';
$string['memorylimiterror'] = 'Limit paměti pro PHP skripty je nastaven relativně nízko ... později vás to může stát problémy.';
$string['memorylimithelp'] = '<p>Limit paměti pro PHP skripty je na vašem serveru momentálně nastaven na hodnotu $a.</p>

<p>Toto může později způsobovat Moodlu problémy, zvláště při větším množství modulů a/nebo uživatelů.</p>

<p>Je-li to možné, doporučujeme vám nakonfigurovat PHP s vyšším limitem - např. 16M. Je několik způsobů, které můžete zkusit:
<ol>
<li>Můžete-li, překompilujte PHP s volbou <i>--enable-memory-limit</i>.
Toto umožní Moodlu nastavit si pro sebe požadovaný limit.</li>
<li>Máte-li přístup k vašemu souboru php.ini, změňte nastavení <b>memory_limit</b>
na hodnotu blízkou 16M. Nemáte-li taková práva, požádejte vašeho správce webového serveru, aby to pro vás udělal.</li>
<li>Na některých PHP serverech můžete v Moodle adresáři vytvořit soubor .htaccess s následujícím řádkem:
<p><blockquote>php_value memory_limit 16M</blockquote></p>
<p>Bohužel, na některých serverech tímto vyřadíte z provozu <b>všechny</b> PHP stránky (při jejich prohlížení uvidíte chybové zprávy), takže budete muset soubor .htaccess odstranit.</li>
</ol>';
$string['mysqlextensionisnotpresentinphp'] = 'PHP nebylo korektně nakonfigurováno pro komunikaci v MySQL. Zkontrolujte váš php.ini nebo překompilujte PHP.';
$string['name'] = 'Název';
$string['next'] = 'Další';
$string['ok'] = 'OK';
$string['pass'] = 'Prošlo';
$string['password'] = 'Heslo';
$string['phpversion'] = 'Verze PHP';
$string['phpversionerror'] = 'Verze PHP musí být alespoň 4.1.0 nebo vyšší';
$string['phpversionhelp'] = '<p>Moodle vyžaduje verzi PHP alespoň 4.1.0.</p>
<p>Vaše stávající PHP má verzi $a</p>
<p>Musíte upgradovat vaše PHP nebo Moodle nainstalovat na hostitele s vyšší verzí!</p>';
$string['previous'] = 'Předchozí';
$string['safemode'] = 'Bezpečný režim (safe mode)';
$string['safemodeerror'] = 'Moodle může mít problémy při zapnutém bezpečném režimu (safe mode)';
$string['sessionautostart'] = 'Session Auto Start';
$string['sessionautostarterror'] = 'Mělo by být vypnuto';
$string['status'] = 'Stav';
$string['thischarset'] = 'UTF-8';
$string['thislanguage'] = 'Cestina';
$string['user'] = 'Uživatel';
$string['wwwroot'] = 'Webová adresa';
$string['wwwrooterror'] = 'Toto nevypadá jako platná webová adresa této instalace Moodle.';
?>