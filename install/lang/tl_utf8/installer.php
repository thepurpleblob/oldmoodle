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

$string['admindirerror'] = 'Malî ang ibinigay na direktoryong pang-admin';
$string['admindirname'] = 'Pang-Admin na Direktoryo';
$string['admindirsettinghead'] = 'Itinatakda ang direktoryong pang-admin...';
$string['admindirsettingsub'] = 'May ilang webhost na ginagamit ang /admin bilang isang espesyal na URL, halimbawa ay para makapasok sa isang kontrol panel.  Nguni\'t nakakagulo ito sa istandard na lokasyon ng mga pahinang pang-admin ng Moodle.  Malulutas ninyo ito sa pamamagitan ng pagbabago ng pangalan ng direktoryong pang-admin sa instalasyon ninyo, tapos ay isulat ang bagong pangalan dito.  Halimbawa:  <br /> <br /><b>moodleadmin</b><br /> <br />
Maayos nito ang mga link na pang-admin sa Moodle.';
$string['bypassed'] = 'Nilagpasan';
$string['caution'] = 'Mag-ingat';
$string['check'] = 'Suriin';
$string['chooselanguagehead'] = 'Pumilì ng wika';
$string['chooselanguagesub'] = 'Pumili po ng wika para sa pag-iinstol LAMANG.  Sa mga susunod na screen ay makakapili ka ng wika para sa site o user.';
$string['closewindow'] = 'Isara ang bintanang ito';
$string['compatibilitysettingshead'] = 'Sinusuri ang iyong kaayusan ng PHP...';
$string['compatibilitysettingssub'] = 'Kailangang pumasa ang server mo sa lahat ng pagsubok upang mapatakbo nang mahusay ang Moodle';
$string['configfilenotwritten'] = 'Hindi awtomatikong nakalikha ang installer script ng config.php file na siyang naglalaman ng mga pinilì mong kaayusan.  Marahil ay dahil sa hindi masulatan ang direktoryo ng Moodle.  Maaari mong kopyahin nang mano-mano ang sumusunod na code sa isang file na nagngangalang config.php sa loob ng punong direktoryo ng Moodle.';
$string['configfilewritten'] = 'matagumpay na nalikha ang config.php';
$string['configurationcompletehead'] = 'Nakumpleto na ang pagsasaayos';
$string['configurationcompletesub'] = 'Tinangka ng Moodle na isave ang kaayusan mo sa isang file sa root ng instalasyon mo ng Moodle.';
$string['continue'] = 'Ituloy';
$string['database'] = 'Database';
$string['databasecreationsettingshead'] = 'Ngayon ay kailangan mo namang isaayos ang mga kaayusan ng database kung saan nalalagak ang karamihan sa datos ng Moodle.  Ang database na ito ay awtomatikong lilikhain ng pang-instol, at itatakda nito ang sumusunod na kaayusan.';
$string['databasecreationsettingssub'] = '<b>Uri:</b> ipinirmi ng pang-instol sa \"mysql\"<br />
<b>Host:</b> ipinirmi ng pang-instol sa \"localhost\"<br />
<b>Pangalan:</b> pangalan ng database, hal. moodle<br />
<b>User:</b> ipinirmi ng pang-instol sa \"root\"<br />
<b>Password:</b> ang password ng database mo<br />
<b>Unlapi ng Teybol:</b> opsiyonal na unlapi na gagamitin sa lahat ng pangalan ng teybol';
$string['databasesettingshead'] = 'Ngayon naman ay kailangan mong isaayos ang database kung saan iimbakin
    ang karamihan sa datos ng Moodle.  Dapat ay nalikha na ang database na ito
    at may username at password na upang mapasok ito.';
$string['databasesettingssub'] = '<b>Uri:</b> mysql o postgres7<br />
       <b>Host:</b> eg localhost o db.isp.com<br />
       <b>Pangalan:</b> pangalan ng database, eg moodle<br />
       <b>User:</b> ang iyong database username<br />
       <b>Password:</b> ang iyong database password<br />
       <b>Unlapi ng mga Teybol:</b> opsiyonal na prefix na gagamitin sa lahat ng pangalan ng teybol';
$string['dataroot'] = 'Direktoryo ng Datos';
$string['datarooterror'] = 'Hindi matagpuan o malikha ang \'Direktoryo ng Datos\' na ibinigay mo.  Alin sa dalawa, iwasto mo ang landas o lumikha ng direktoryo nang mano-mano.';
$string['dbconnectionerror'] = 'Hindi kami makakonekta sa ibinigay mong database.  Pakitsek ang kaayusan ng iyong database.';
$string['dbcreationerror'] = 'Nagka-error sa paglikha ng database.  Hindi malikha ang ibinigay na pangalan ng database nang may mga ibinigay na  kaayusan';
$string['dbhost'] = 'Host Server';
$string['dbprefix'] = 'Unlapi ng mga teybol';
$string['dbtype'] = 'Uri';
$string['dbwrongencoding'] = 'Ang piniling database ay gumagana alinsunod sa hindi iminumungkahing encoding ($a).  Mas makabubuti na gamitin ang isa sa mga inencode sa Unicode (UTF-8) na database.  Magkagayunman, maaari mong lagpasan ang pagsubok na ito sa pamamagitan ng pagpili sa tsek ng \"Lagpasan ang Pagsubok ng DB Encoding\" sa ibaba, pero maaari kang makaranas ng mga problema sa hinaharap.';
$string['directorysettingshead'] = 'Pakikumpirma ang mga lokasyon ng instalasyon ng Moodle na ito';
$string['directorysettingssub'] = '<b>Web Address:</b>
Ibigay ang buong web address kung saan papasukin ang Moodle.
Kung ang web site mo ay mapapasok sa pamamagitan ng maraming URL piliin ang
pinakaangkop para sa mga mag-aaral mo.  Huwag lalagyan ng 
slash sa dulo.
<br />
<br />

<b>Direktoryo ng Moodle:</b>
Ibigay ang buong landas ng direktoryo sa instalasyong ito
Tiyakin na ang malaki/maliit na titik ay wasto.
<br />
<br />
<b>Direktoryo ng Datos:</b>
Kailangan mo ng pook kung saan puwedeng magsave ng inaplowd na file ang Moodle.  Ang
direktoryong ito ay dapat na nababasa AT NASUSULATAN ng web server user
(kadalasan ay \'nobody\' o \'apache\'), pero hindi ito dapat mapasok nang
direkta sa pamamagitan ng web.';
$string['dirroot'] = 'Direktoryo ng Moodle';
$string['dirrooterror'] = 'Mukhang mali ang kaayusan ng \'Direktoryo ng Moodle\' - wala kaming matagpuang instalasyon ng Moodle doon.  Inireset ang halaga sa ibaba.';
$string['download'] = 'Idownload';
$string['environmenterrortodo'] = 'Kailangan mo munang lutasin ang lahat ng suliraning pangkapaligiran (mga error) bago mo maituloy ang pag-instol ng bersiyon ng Moodle na ito.';
$string['environmenthead'] = 'Sinusuri ang kapaligiran mo...';
$string['environmentrecommendinstall'] = 'ay iminumungkahing mainstol/mabuhay';
$string['environmentrecommendversion'] = 'ang bersiyon $a->needed ay iminumungkahi at ang pinatatakbo mo ay $a->current';
$string['environmentrequireinstall'] = 'ay kinakailangang mainstol/mabuhay';
$string['environmentrequireversion'] = 'ang bersiyon $a->needed ay kinakailangan at ang pinatatakbo mo ay $a->current';
$string['environmentsub'] = 'Sinusuri namin kung ang iba\'t-ibang piyesa ng sistema mo ay umaayon sa mga kinakailangan na sistema';
$string['environmentxmlerror'] = 'Nagka-error sa pagbasa ng datos na pangkapaligiran ($a->error_code)';
$string['error'] = 'Error';
$string['fail'] = 'Bigô';
$string['fileuploads'] = 'Mga Inaplowd na File';
$string['fileuploadserror'] = 'Dapat ay buhay ito';
$string['gdversion'] = 'Bersiyon ng GD';
$string['gdversionerror'] = 'Dapat ay may GD library para maproseso at makalikha ng mga larawan';
$string['globalsquotes'] = 'Di-ligtas na Paghandle ng mga Global';
$string['globalsquoteserror'] = 'Ayusin ang iyong mga kaayusan ng PHP:  patayin ang register_globals at/o buhayin ang magic_quotes_gpc';
$string['help'] = 'Tulong';
$string['iconvrecommended'] = 'Mahigpit na iminumungkahi ang pag-instol ng opsiyonal na ICONV library upang mapahusay ang paggana ng site, lalupa\'t kung sinusuportahan ng site mo ang mga di-latin na wika.';
$string['info'] = 'Impormasyon';
$string['installation'] = 'Instalasyon';
$string['language'] = 'Wikà';
$string['magicquotesruntime'] = 'Magic Quotes Run Time';
$string['magicquotesruntimeerror'] = 'Dapat ay patay ito';
$string['memorylimit'] = 'Memory Limit';
$string['memorylimiterror'] = 'Labis na mababa ang memory limit ng PHP ... maaaring magkaproblema ka mamaya.';
$string['memorylimithelp'] = '<p>Ang memory limit ng PHP para sa server mo ay kasalukuyang nakatakda sa $a.</p>

<p>Maaaring magdulot ito ng mga problemang pangmemorya sa Moodle sa mga susunod na panahon, lalo na
   kung marami kang binuhay na modyul at/o marami kang user.</p>

<p>Iminumungkahi namin na isaayos mo ang PHP na may mas mataas na limit kung maaari, tulad ng 16M.
    May iba\'t-ibang paraan na magagawa kayo upang ito ay maiisakatuparan:</p>
<ol>
<li>Kunga maaari mong gawin, muling icompile ang PHP na may <i>--enable-memory-limit</i>.  
     Pahihintulutan nito ang Moodle na itakda ang memory limit sa sarili nito.</li>
<li>Kung mapapasok mo ang iyong php.ini file, mababago mo ang <b>memory_limit</b> 
    na kaayusan doon at gawin itong mga 16M.  Kung wala kang karapatang pasukin ito
    baka puwede mong hilingin sa administrador na gawin ito para sa iyo.</li>
<li>Sa ilang PHP serve maaari kang lumikha ng isang .htaccess file sa direktoryo ng Moodle
    na naglalaman ng linyang ito:
    <p><blockquote>php_value memory_limit 16M</blockquote></p>
    <p>Subali\'t sa ilang server ay pipigilin nito ang paggana ng <b>lahat</b> ng pahinang PHP 
    (makakakita ka ng mga error kapag tumingin ka sa mga pahina) kaya\'t kakailanganin mong tanggalin ang .htaccess file.</p></li>
</ol>';
$string['mysql416bypassed'] = 'Magkagayuman, kung TANGING iso-8859-1 (latin) na wika ang ginagamit ng site mo, maaari mong ipagpatuloy ang kasalukuyan mong nakainstol na MySQL 4.1.12 (o mas bago).';
$string['mysql416required'] = 'Ang MySQL 4.1.16 ang minimum na bersiyong kinakailangan ng Moodle 1.6 upang matiyak na lahat ng datos ay makukumberte sa UTF-8, sa hinaharap.';
$string['mysqlextensionisnotpresentinphp'] = 'Hindi isinaayos ang PHP na may MySQL extension para magawa nitong makipag-usap sa MySQL.  Pakitsek ang iyong php.ini file o muling icompile ang PHP.';
$string['name'] = 'Pangalan';
$string['next'] = 'Susunod';
$string['ok'] = 'OK';
$string['pass'] = 'Pasado';
$string['password'] = 'Password';
$string['phpversion'] = 'Bersiyon ng PHP';
$string['phpversionerror'] = 'Ang pinakamababang bersiyon ng PHP na puwedeng gamitin ay 4.1.0';
$string['phpversionhelp'] = '<p>Kinakailangan ng Moodle ang isang bersiyon ng PHP na kahit man lamang 4.1.0.</p>
<p>Sa kasalukuyan ay pinatatakbo mo ang bersiyon $a</p>
<p>Kailangan mong gawing bago ang PHP o lumipat sa isang host na may mas bagong bersiyon ng PHP!</p>';
$string['previous'] = 'Nakaraan';
$string['report'] = 'Ulat';
$string['safemode'] = 'Safe Mode';
$string['safemodeerror'] = 'Maaaring magkaproblema ang moodle kung naka-ON ang safe mode';
$string['sessionautostart'] = 'Session Auto Start';
$string['sessionautostarterror'] = 'Dapat ay patay ito';
$string['skipdbencodingtest'] = 'Lagpasan ang Pagsubok sa DB Encoding';
$string['status'] = 'Katayuan';
$string['thischarset'] = 'UTF-8';
$string['thislanguage'] = 'Tagalog';
$string['user'] = 'User';
$string['welcomep10'] = '$a->installername ($a->installerversion)';
$string['welcomep20'] = 'Nakikita mo ang pahinang ito dahil matagumpay mong naiinstol at napagana ang paketeng <strong>$a->packname $a->packversion</strong> sa iyong kompyuter.  Maligayang bati!';
$string['welcomep30'] = 'Ang lathala ng <strong>$a->installername</strong> na ito ay naglalaman ng mga aplikasyon na lilikha ng kapaligiran na tatakbuhan ng  <strong>Moodle</strong>, ito ay ang mga sumusunod:';
$string['welcomep40'] = 'Nilalaman din ng paketeng ito ang  <strong>Moodle $a->moodlerelease ($a->moodleversion)</strong>.';
$string['welcomep50'] = 'Ang paggamit ng lahat ng aplikasyon sa paketeng ito ay alinsunod sa kani-kaniyang lisensiya.  Ang kumpletong pakete na <strong>$a->installername</strong> ay  <a href=\"http://www.opensource.org/docs/definition_plain.html\">open source</a> at ipinamamahagi alinsunod sa lisensiyang <a href=\"http://www.gnu.org/copyleft/gpl.html\">GPL</a>';
$string['welcomep60'] = 'Dadalhin kayo ng mga sumusunod na pahina sa mga madaling hakbang upang maisaayos at mapatakbo ang <strong>Moodle</strong> sa kompyuter ninyo.  Kung gusto ninyo ay panatilihin ang default o kaya ay baguhin ito ayon sa inyong pangangailangan.';
$string['welcomep70'] = 'Iklik ang \"Susunod\" na buton sa ibaba upang maituloy ang pasasaayos ng <strong>Moodle</strong>.';
$string['wwwroot'] = 'Web address';
$string['wwwrooterror'] = 'Mukhang hindi tanggap ang web address - mukhang wala roon ang instalasyong ito ng Moodle.';
?>