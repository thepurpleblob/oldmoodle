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

$string['admindirerror'] = 'ไดเรกทอรี admin ที่ระบุไม่ถูกต้อง';
$string['admindirname'] = 'ไดเรกทอรี admin';
$string['caution'] = 'คำเตือน';
$string['closewindow'] = 'ปิดหน้าต่าง';
$string['configfilenotwritten'] = 'ตัวติดตั้งอัตโนมัติไม่สามารถสร้างไฟล์ config.php ได้ อาจเป็นเพราะว่าไม่สามารถเขียนลงไดเรกทอรี moodle ได้ คุณสามารถสร้างไฟล์ดังกล่าวได้เองโดยการก้อปปี้โค้ดต่อไปนี้ลงในไฟล์ที่ต้องการสร้างใหม่';
$string['configfilewritten'] = 'สร้าง config.php เรียบร้อยแล้ว';
$string['continue'] = 'ขั้นต่อไป';
$string['database'] = 'ฐานข้อมูล';
$string['dataroot'] = 'ไดเรกทอรีข้อมูล';
$string['datarooterror'] = 'ไม่พบไดเรกทอรีข้อมูลที่คุณระบุไว้หรือไม่สามารถสร้างได้ กรุณาแก้ไข Path ให้ถูกต้องหรือสร้างไดเรกทอรีนี้ใหม่';
$string['dbconnectionerror'] = 'ไม่สามารถติดต่อฐานข้อมูลที่คุณระบุไว้ได้ กรุณาตรวจสอบค่าที่ตั้งไว้ของฐานข้อมูล';
$string['dbcreationerror'] = 'มีข้อผิดพลาดในการสร้างฐานข้อมูล ไม่สามารถสร้างฐานข้อมูลที่ระบุด้วยค่าที่ให้ไว้ได้';
$string['dbhost'] = 'โฮสต์เซิร์ฟเวอร์';
$string['dbprefix'] = 'คำนำหน้าตาราง (Table Prefix)';
$string['dbtype'] = 'ประเภท';
$string['dirroot'] = 'Moodle ไดเรกทอรี';
$string['dirrooterror'] = 'การตั้งค่า ไดเรกทอรี moodle ไม่ถูกต้อง ไม่พบไฟล์ติดตั้งที่ระบุ  ระบบทำการรีเซ็ตค่าด้านล่างนี้ ';
$string['download'] = 'ดาวน์โหลด';
$string['error'] = 'มีข้อผิดพลาด';
$string['fail'] = 'ล้มเหลว';
$string['fileuploads'] = 'ไฟล์อัพโหลด';
$string['fileuploadserror'] = 'ควรจะเปิด(on)';
$string['gdversion'] = 'GD  เวอร์ชัน';
$string['gdversionerror'] = 'เซิร์ฟเวอร์ควรมีการใช้ GD library เพื่อที่ใช้';
$string['help'] = 'ช่วยเหลือ';
$string['info'] = 'ข้อมูล';
$string['installation'] = 'การติดตั้ง';
$string['language'] = 'ภาษาที่ใช้ในเว็บ';
$string['magicquotesruntime'] = 'Magic Quotes Run Time';
$string['magicquotesruntimeerror'] = 'ควรจะปิด (off)';
$string['memorylimit'] = 'ความจำสูงสุด (Memory Limit)';
$string['memorylimiterror'] = 'ความจำสูงสุดที่คุณตั้งไว้ค่อนข้างต่ำ อาจมีปัญหาในภายหลังค่ะ';
$string['memorylimithelp'] = '<p>ค่าความจำสูงสุดของเซิร์ฟเวอร์ของคุณตั้งไว้ที่  $a</p>

<p>ความจำดังกล่าวมีค่าน้อยไปค่ะอาจทำให้มีปัญหาในการใช้งาน moodle ในภายหลังโดยเฉพาะเมื่อคุณใช้โมดูลหลาย ๆ ตัวรวมไปถึงมีสมาชิกจำนวนมาก

<p>ค่าที่ตั้งไว้นี้ควรตั้งให้มากที่สุดเท่าที่จะมากได้ ค่าทั่วไปแนะนำไว้ที่ 16M มีอยู่หลายวิธีในการเพิ่มค่าความจำสูงสุด กล่าวคือ:

<ol>

<li>รีคอมไพล์ PHP ใหม่ โดยเพิ่มคำสั่ง <i>--enable-memory-limit</i> ซึ่งเป็นการตั้งค่าให้ moodle กำหนดขีดจำกัดค่าสูงสุดเอง 

<li>ถ้าคุณสามารถแก้ไขไฟล์  php.ini ได้ด้วยตนเองก็สามารถเปลี่ยนค่า <b>memory_limit</b> ให้เป็นค่าอื่นได้เช่น  16M แต่ถ้าไม่สามารถเปลี่ยนค่านี้ได้ด้วยตนเองให้แจ้งผู้ดูแลระบบแก้ไข

<li>ในเซิร์ฟเวอร์ PHP บางตัวคุณสามารถสร้าง ไฟล์ .htaccess ภายใต้ไดเรกทอรี moodle ซึ่งมีบรรทัดต่อไปนี้อยู่:

<p><blockquote>php_value memory_limit 16M</blockquote></p>

<p>อย่างไรก็ตามในบางเซิร์ฟเวอร์คุณไม่สามารถใช้ วิธีนี้ได้ โดยจะมีการแสดง error ขึ้นมาคุณจำเป็นต้องลบไฟล์ดังกล่าวนี้ทิ้ง 
</ol>';
$string['mysqlextensionisnotpresentinphp'] = 'การตั้งค่า  PHP ให้ใช้กับ MySQL ไม่ถูกต้องกรุณาตรวจสอบใน php.ini อีกครั้งหรือรีคอมไฟล์ php';
$string['name'] = 'ชื่อ';
$string['next'] = 'ต่อไป';
$string['ok'] = 'เรียบร้อย';
$string['pass'] = 'สำเร็จ';
$string['password'] = 'รหัสผ่าน';
$string['phpversion'] = 'PHP เวอร์ชัน';
$string['phpversionerror'] = 'เวอร์ชันของ PHP ควรเป็นอย่างน้อย 4.1.0';
$string['phpversionhelp'] = '<p>Moodle จำเป็นต้องใช้ PHP เวอร์ชัน 4.1.0 เป็นอย่างน้อย</p>

<p>คุณกำลังใช้เวอร์ชัน $a</p>

<p>คุณต้องอัพเกรด  PHP หรือย้ายโฮสต์ใหม่ที่มี PHP เวอร์ชันใหม่กว่า</p>';
$string['previous'] = 'หน้าก่อน';
$string['safemode'] = 'Safe Mode';
$string['safemodeerror'] = 'moodle อาจมีปัญหาหาก safe mode on';
$string['sessionautostart'] = 'Session Auto Start';
$string['sessionautostarterror'] = 'ควรจะปิด (off)';
$string['status'] = 'สถานะ';
$string['thischarset'] = 'UTF-8';
$string['thislanguage'] = 'Thai';
$string['user'] = 'สมาชิก';
$string['wwwroot'] = 'ที่อยู่ของเว็บ';
$string['wwwrooterror'] = 'ที่อยู่ของเว็บไม่ถูกต้อง ระบบไม่พบว่ามี Moodle อยู่ที่นั่น';
?>