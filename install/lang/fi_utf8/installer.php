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

$string['admindirerror'] = 'Ylläpitohakemisto on määritetty väärin';
$string['admindirname'] = 'Ylläpitohakemisto';
$string['caution'] = 'Varoitus';
$string['closewindow'] = 'Sulje tämä ikkuna';
$string['configfilenotwritten'] = 'Asennus ei pystynyt luomaan automaattisesti config.php tiedostoa, joka olisi sisältänyt valitsemasi asetukset, todennäköisesti koska Moodlen hakemisto on kirjoitussuojattu. Voit manuaalisesti kopioida seuraavan koodin tiedostoon nimeltä config.php Moodlen päähakemiston sisällä.';
$string['configfilewritten'] = 'config.php on luotu.';
$string['continue'] = 'Jatka';
$string['database'] = 'Tietokanta';
$string['dataroot'] = 'Datahakemisto';
$string['datarooterror'] = '\"Datahakemistoa\", jonka määrittelit, ei voitu löytää, eikä luoda. Joko korjaa polku, tai luo hakemisto manuaalisesti.';
$string['dbconnectionerror'] = 'Emme pystyneet kytkeytymään tiedokantaan, jonka määrittelit. Tarkista tietokanta asetuksesi.';
$string['dbcreationerror'] = 'Tietokannan luomisvirhe. Ei pystytty luomaan annettua tietokannan nimeä tarjotuilla asetuksilla.';
$string['dbhost'] = 'Palvelin';
$string['dbprefix'] = 'Taulukon etumerkki';
$string['dbtype'] = 'Tyyppi';
$string['dirroot'] = 'Moodle hakemisto';
$string['dirrooterror'] = '\"Moodle hakemisto\" asetus näyttäisi olevan väärä-emme voi löytää Moodle asennusta sieltä. Arvo alapuolella on nollattu.';
$string['download'] = 'Lataus';
$string['error'] = 'Virhe';
$string['fail'] = 'Virhe';
$string['fileuploads'] = 'Tiedostojen lähettäminen';
$string['fileuploadserror'] = 'Tämän pitäisi olla päällä';
$string['gdversion'] = 'GD versio';
$string['gdversionerror'] = 'GD kirjaston pitäisi olla päällä, että voidaan käsitellä ja luoda kuvia.';
$string['help'] = 'Ohje';
$string['info'] = 'Tiedot';
$string['installation'] = 'asennus';
$string['language'] = 'Kieli';
$string['magicquotesruntime'] = 'Magic quotes ajoaika';
$string['magicquotesruntimeerror'] = 'Tämän pitäisi olla poissa päältä';
$string['memorylimit'] = 'Muistiraja';
$string['memorylimiterror'] = 'PHP muistiraja on asetettu aika alas... Se saattaa aiheuttaa ongelmia myöhemmin.';
$string['memorylimithelp'] = '<p>PHP muistiraja palvelimellesi on tällä hetkellä asetettu $a:han.</p>

<p>Tämä saattaa aiheuttaa Moodlelle muistiongelmia myöhemmin, varsinkin jos sinulla on paljon mahdollisia moduuleita ja/tai paljon käyttäjiä.</p>

<p>Suosittelemme, että valitset asetuksiksi PHP:n korkeimmalla mahdollisella raja-arvolla, esimerkiksi 16M.
On olemassa monia tapoja joilla voit yrittää tehdä tämän:</p>
<ol>
<li>Jos pystyt, uudelleenkäännä PHP <i>--enable-memory-limit</i>. :llä.
Tämä sallii Moodlen asettaa muistirajan itse.</li>
<li>Jos sinulla on pääsy php.ini tiedostoosi, voit muuttaa <b>memory_limit</b> setuksen siellä johonkin kuten 16M. Jos sinulla ei ole pääsyoikeutta, voit kenties pyytää ylläpitäjää tekemään tämän puolestasi.</li>
<li>Joillain PHP palvelimilla voit luoda a .htaccess tiedoston Moodle hakemistossa, sisältäen tämän rivin:
<p><blockquote>php_value memory_limit 16M</blockquote></p>
<p>Kuitenkin, joillain palvelimilla tämä estää  <b>kaikkia</b> PHP sivuja toimimasta (näet virheet, kun katsot sivuja), joten sinun täytyy poistaa .htaccess tiedosto.</p></li>
</ol>';
$string['mysqlextensionisnotpresentinphp'] = 'PHP:tä ei ole kunnolla valittu asetukseksi MySQL laajennuksen kanssa, jotta se voisi kommunikoida MySQL:n kanssa. Tarkista php.ini tiedostosi tai käännä PHP uudelleen.';
$string['name'] = 'Nimi';
$string['next'] = 'Seuraava';
$string['ok'] = 'OK';
$string['pass'] = 'Tarkastettu';
$string['password'] = 'Salasana';
$string['phpversion'] = 'PHP versio';
$string['phpversionerror'] = 'PHP version täytyy olla vähintään 4.1.0';
$string['phpversionhelp'] = '<p>Moodle vaatii vähintään PHP version 4.1.0.</p>
<p>Käytät parhaillaan versiota $a</p>
<p>Sinun täytyy päivittää PHP tai siirtää isäntä uudemman PHP version kanssa!</p>';
$string['previous'] = 'Edellinen';
$string['safemode'] = 'Safe mode';
$string['safemodeerror'] = 'Moodlella saattaa olla ongelmia PHP:n  Safe Moden ollessa päällä';
$string['sessionautostart'] = 'Istunnon automaattinen aloitus';
$string['sessionautostarterror'] = 'Tämän pitäisi olla pois päältä';
$string['status'] = 'Tilanne';
$string['thischarset'] = 'UTF-8';
$string['thislanguage'] = 'Suomi';
$string['user'] = 'Käyttäjä';
$string['wwwroot'] = 'Web-osoite';
$string['wwwrooterror'] = 'Web-osoite ei näyttäisi olevan voimassa- tämä Moodle asennus ei näyttäisi olevan siellä.';
?>