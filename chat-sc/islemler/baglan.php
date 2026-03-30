<?php

if (session_status() === PHP_SESSION_NONE) {
	$__bw_secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
	session_set_cookie_params(array(
		'lifetime' => 0,
		'path' => '/',
		'domain' => '',
		'secure' => $__bw_secure,
		'httponly' => true,
		'samesite' => 'Lax',
	));
	session_start();
}

$host = "localhost";
$veritabani_ismi = "bestwpchat";
$kullanici_adi = "root";
$sifre = "";

$db = null;
try {
	$db = new PDO("mysql:host=$host;dbname=$veritabani_ismi;charset=utf8", $kullanici_adi, $sifre);
} catch (PDOException $e) {
	header('HTTP/1.1 503 Service Unavailable');
	exit('Veritabanı bağlantısı kurulamadı.');
}

if (!($db instanceof PDO)) {
	header('HTTP/1.1 503 Service Unavailable');
	exit('Veritabanı bağlantısı kurulamadı.');
}

$sorgu = $db->prepare('SELECT * FROM ayarlar LIMIT 1');
$sorgu->execute();
$ayarcek = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!is_array($ayarcek)) {
	$ayarcek = array(
		'id' => 1,
		'site_baslik' => 'BestWp',
		'site_aciklama' => '',
		'site_link' => '',
		'site_logo' => '',
		'site_sahip_mail' => '',
		'site_mail_host' => '',
		'site_mail_mail' => '',
		'site_mail_port' => '',
		'site_mail_sifre' => '',
	);
}

if (is_array($ayarcek)) {
	$bas = isset($ayarcek['site_baslik']) ? trim((string) $ayarcek['site_baslik']) : '';
	if ($bas === '') {
		$ayarcek['site_baslik'] = 'BestWp';
	}
}


?>