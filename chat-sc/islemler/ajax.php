<?php 
require 'baglan.php';
require '../fonksiyonlar.php';

$herkese_acik = isset($_POST['oturumacma']) || isset($_POST['kayitol']) || isset($_POST['totpdogrula']);
if (!$herkese_acik) {
	oturumkontrol("../login.php");
}

if (isset($_GET['okunmadi_json']) && (string) $_GET['okunmadi_json'] === '1') {
	header('Content-Type: application/json; charset=utf-8');
	$me_id = (int) $_SESSION['kul_id'];
	$peer_ids = array();
	try {
		$st = $db->prepare('SELECT kul_id FROM kullanicilar WHERE COALESCE(kul_onay,1) = 1 AND kul_id != :me ORDER BY kul_isim ASC');
		$st->execute(array('me' => $me_id));
		while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
			$peer_ids[] = (int) $r['kul_id'];
		}
	} catch (Exception $e) {
	}
	$map = bestwp_okunmadi_harita($db, $me_id, $peer_ids);
	echo json_encode(array('ok' => true, 'counts' => $map));
	exit;
}

if (isset($_POST['sohbet_okundu'])) {
	header('Content-Type: application/json; charset=utf-8');
	$me = (int) $_SESSION['kul_id'];
	$th = isset($_POST['thread']) ? trim((string) $_POST['thread']) : '';
	if ($th === 'genel') {
		$key = bestwp_okuma_thread_genel();
		$mx = bestwp_okuma_max_genel($db);
		bestwp_okuma_mark_upto($db, $me, $key, $mx);
	} elseif (preg_match('/^peer:(\d+)$/', $th, $m)) {
		$pid = (int) $m[1];
		if ($pid > 0 && $pid !== $me) {
			$key = bestwp_okuma_thread_peer($pid);
			$mx = bestwp_okuma_max_dm($db, $me, $pid);
			bestwp_okuma_mark_upto($db, $me, $key, $mx);
		}
	}
	echo json_encode(array('ok' => true));
	exit;
}

if (isset($_POST['sohbet_arsiv'])) {
	header('Content-Type: application/json; charset=utf-8');
	$me = (int) $_SESSION['kul_id'];
	$th = isset($_POST['thread']) ? trim((string) $_POST['thread']) : '';
	$arsiv = isset($_POST['arsiv']) ? (int) $_POST['arsiv'] : 0;
	$arsiv = $arsiv ? 1 : 0;
	$ok = false;
	if ($th === 'genel') {
		$ok = bestwp_okuma_set_arsiv($db, $me, bestwp_okuma_thread_genel(), $arsiv === 1);
	} elseif (preg_match('/^peer:(\d+)$/', $th, $m)) {
		$pid = (int) $m[1];
		if ($pid > 0 && $pid !== $me) {
			$ok = bestwp_okuma_set_arsiv($db, $me, bestwp_okuma_thread_peer($pid), $arsiv === 1);
		}
	}
	echo json_encode(array('ok' => $ok));
	exit;
}

if (isset($_POST['ayarkaydet'])) {
	$sorgu=$db->prepare("UPDATE ayarlar SET 
		site_baslik=:site_baslik,
		site_aciklama=:site_aciklama,
		site_link=:site_link,
		site_sahip_mail=:site_sahip_mail,
		site_mail_host=:site_mail_host,
		site_mail_mail=:site_mail_mail,
		site_mail_port=:site_mail_port,
		site_mail_sifre=:site_mail_sifre WHERE id=1
		");

	$sonuc=$sorgu->execute(array(
		'site_baslik' => $_POST['site_baslik'],
		'site_aciklama' => $_POST['site_aciklama'],
		'site_link' => $_POST['site_link'],
		'site_sahip_mail' => $_POST['site_sahip_mail'],
		'site_mail_host' => $_POST['site_mail_host'],
		'site_mail_mail' => $_POST['site_mail_mail'],
		'site_mail_port' => $_POST['site_mail_port'],
		'site_mail_sifre' => $_POST['site_mail_sifre']
	));

	if ($_FILES['site_logo']['error']=="0") {
		$gecici_isim=$_FILES['site_logo']['tmp_name'];
		$dosya_ismi=rand(100000,999999).$_FILES['site_logo']['name'];
		move_uploaded_file($gecici_isim,"../dosyalar/$dosya_ismi");

		$sorgu=$db->prepare("UPDATE ayarlar SET 
			site_logo=:site_logo WHERE id=1
			");

		$sonuc=$sorgu->execute(array(
			'site_logo' => $dosya_ismi,

		));
	}

	if ($sonuc) {
		header("location:../index.php?toast=ayar_ok");
	} else {
		header("location:../index.php?toast=ayar_hata");
	}
	exit;
}


/********************************************************/

if (isset($_POST['oturumacma'])) {
	$sorgu=$db->prepare("SELECT * FROM kullanicilar WHERE kul_mail=:kul_mail AND kul_sifre=:kul_sifre");
	$sorgu->execute(array(
		'kul_mail' => trim($_POST['kul_mail'] ?? ''),
		'kul_sifre' => md5($_POST['kul_sifre'] ?? '')
	));
	$kullanici=$sorgu->fetch(PDO::FETCH_ASSOC);

	if (!$kullanici) {
		header("location:../login.php?durum=no");
		exit;
	}

	$onayli = !isset($kullanici['kul_onay']) || (int) $kullanici['kul_onay'] === 1;
	if (!$onayli) {
		header("location:../login.php?durum=beklemede");
		exit;
	}

	$totp_on = isset($kullanici['kul_totp_enabled']) && (int) $kullanici['kul_totp_enabled'] === 1
		&& !empty($kullanici['kul_totp_secret']);
	if ($totp_on) {
		$kid = (int) $kullanici['kul_id'];
		$_SESSION['totp_pending_kul_id'] = $kid;
		$_SESSION['totp_pending_mail'] = (string) $kullanici['kul_mail'];
		$t_signed = bestwp_totp_login_token_create($kid);
		$_SESSION['totp_pending_signed'] = $t_signed;
		session_write_close();
		header('location:../login.php?step=totp&t=' . rawurlencode($t_signed));
		exit;
	}

	$_SESSION['kul_isim'] = $kullanici['kul_isim'];
	$_SESSION['kul_mail'] = $kullanici['kul_mail'];
	$_SESSION['kul_id'] = $kullanici['kul_id'];
	$_SESSION['kul_yetki'] = $kullanici['kul_yetki'];
	bestwp_touch_son_giris($db, (int) $kullanici['kul_id']);
	if ((int) $kullanici['kul_yetki'] === 1) {
		header('location:../index.php?toast=ok');
	} else {
		header('location:../messenger.php');
	}
	exit;
}


/********************************************************/

if (isset($_POST['totpdogrula'])) {
	require_once __DIR__ . '/totp.php';
	$t_in = isset($_POST['totp_state']) ? trim((string) $_POST['totp_state']) : '';
	$from_t = $t_in !== '' ? bestwp_totp_login_token_read($t_in) : null;
	$kid = 0;
	if ($from_t) {
		$kid = (int) $from_t['kid'];
	} elseif (!empty($_SESSION['totp_pending_kul_id'])) {
		$kid = (int) $_SESSION['totp_pending_kul_id'];
	}
	if ($kid <= 0) {
		header('location:../login.php?durum=totp_session');
		exit;
	}
	$code = preg_replace('/\D/', '', (string) ($_POST['totp_code'] ?? ''));
	$sorgu = $db->prepare('SELECT * FROM kullanicilar WHERE kul_id = :id LIMIT 1');
	$sorgu->execute(array('id' => $kid));
	$kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);
	$secret = $kullanici['kul_totp_secret'] ?? '';
	if (!$kullanici || $secret === '' || !BestWpTotp::verify($code, $secret)) {
		$retry = $t_in !== '' ? '&t=' . rawurlencode($t_in) : '';
		header('location:../login.php?step=totp&durum=totp_hata' . $retry);
		exit;
	}
	$onayli = !isset($kullanici['kul_onay']) || (int) $kullanici['kul_onay'] === 1;
	if (!$onayli) {
		unset($_SESSION['totp_pending_kul_id'], $_SESSION['totp_pending_mail'], $_SESSION['totp_pending_signed']);
		header('location:../login.php?durum=beklemede');
		exit;
	}
	unset($_SESSION['totp_pending_kul_id'], $_SESSION['totp_pending_mail'], $_SESSION['totp_pending_signed']);
	$_SESSION['kul_isim'] = $kullanici['kul_isim'];
	$_SESSION['kul_mail'] = $kullanici['kul_mail'];
	$_SESSION['kul_id'] = $kullanici['kul_id'];
	$_SESSION['kul_yetki'] = $kullanici['kul_yetki'];
	bestwp_touch_son_giris($db, (int) $kullanici['kul_id']);
	if ((int) $kullanici['kul_yetki'] === 1) {
		header('location:../index.php?toast=ok');
	} else {
		header('location:../messenger.php');
	}
	exit;
}


/********************************************************/

if (isset($_POST['kayitol'])) {
	$kul_isim = trim($_POST['kul_isim'] ?? '');
	$kul_mail = trim($_POST['kul_mail'] ?? '');
	$kul_telefon = trim($_POST['kul_telefon'] ?? '');
	$kul_sifre = $_POST['kul_sifre'] ?? '';
	$kul_sifre_tekrar = $_POST['kul_sifre_tekrar'] ?? '';

	if ($kul_isim === '' || $kul_mail === '' || $kul_sifre === '') {
		header("location:../login.php?durum=kayit_eksik");
		exit;
	}

	if (!filter_var($kul_mail, FILTER_VALIDATE_EMAIL)) {
		header("location:../login.php?durum=kayit_mail");
		exit;
	}

	if (strlen($kul_sifre) < 6) {
		header("location:../login.php?durum=kayit_sifre_kisa");
		exit;
	}

	if ($kul_sifre !== $kul_sifre_tekrar) {
		header("location:../login.php?durum=kayit_sifre_uyusmaz");
		exit;
	}

	$varmi = $db->prepare("SELECT kul_id FROM kullanicilar WHERE kul_mail = :m LIMIT 1");
	$varmi->execute(array('m' => $kul_mail));
	if ($varmi->fetch()) {
		header("location:../login.php?durum=mail_var");
		exit;
	}

	$sorgu = $db->prepare("INSERT INTO kullanicilar SET 
		kul_isim=:kul_isim,
		kul_mail=:kul_mail,
		kul_telefon=:kul_telefon,
		kul_sifre=:kul_sifre,
		kul_yetki=0,
		kul_onay=0
		");

	$sonuc = $sorgu->execute(array(
		'kul_isim' => $kul_isim,
		'kul_mail' => $kul_mail,
		'kul_telefon' => $kul_telefon,
		'kul_sifre' => md5($kul_sifre)
	));

	if ($sonuc) {
		header("location:../login.php?durum=kayit_ok");
	} else {
		header("location:../login.php?durum=kayit_hata");
	}
	exit;
}


/********************************************************/

if (isset($_POST['kulonayla'])) {
	if (!yetkikontrol()) {
		header("location:../index.php");
		exit;
	}
	$kul_id = (int) ($_POST['kul_id'] ?? 0);
	if ($kul_id < 1) {
		header("location:../onay-bekleyenler.php?durum=no");
		exit;
	}
	$sorgu = $db->prepare("UPDATE kullanicilar SET kul_onay=1 WHERE kul_id=:id AND kul_onay=0");
	$sorgu->execute(array('id' => $kul_id));
	header("location:../index.php?toast=" . ($sorgu->rowCount() ? "onay_ok" : "admin_hata"));
	exit;
}


/********************************************************/

if (isset($_POST['kulreddet'])) {
	if (!yetkikontrol()) {
		header("location:../index.php");
		exit;
	}
	$kul_id = (int) ($_POST['kul_id'] ?? 0);
	if ($kul_id < 1) {
		header("location:../onay-bekleyenler.php?durum=no");
		exit;
	}
	$sorgu = $db->prepare("DELETE FROM kullanicilar WHERE kul_id=:id AND kul_onay=0");
	$sorgu->execute(array('id' => $kul_id));
	header("location:../index.php?toast=" . ($sorgu->rowCount() ? "red_ok" : "admin_hata"));
	exit;
}


/********************************************************/

if (isset($_POST['profilkaydet'])) {
	$kul_id = (int) $_SESSION['kul_id'];

	$sorgu = $db->prepare('UPDATE kullanicilar SET kul_telefon=:kul_telefon WHERE kul_id=:kul_id');
	$sonuc = $sorgu->execute(array(
		'kul_telefon' => $_POST['kul_telefon'] ?? '',
		'kul_id' => $kul_id,
	));

	if (strlen($_POST['kul_sifre'] ?? '') > 0) {
		$sorgu = $db->prepare('UPDATE kullanicilar SET kul_sifre=:kul_sifre WHERE kul_id=:kul_id');
		$sonuc = $sorgu->execute(array(
			'kul_sifre' => md5($_POST['kul_sifre']),
			'kul_id' => $kul_id,
		));
	}

	$hedef = ((int) ($_SESSION['kul_yetki'] ?? 0) === 1) ? '../profil.php' : '../messenger.php';
	if ($sonuc) {
		header('location:' . $hedef . '?toast=profil_ok');
	} else {
		header('location:' . $hedef . '?toast=profil_hata');
	}

	exit;

}


/********************************************************/

if (isset($_POST['totp_kurulum_onay'])) {
	require_once __DIR__ . '/totp.php';
	$kul_id = (int) $_SESSION['kul_id'];
	$secret = isset($_SESSION['totp_setup_secret']) ? (string) $_SESSION['totp_setup_secret'] : '';
	$code = preg_replace('/\D/', '', (string) ($_POST['totp_code'] ?? ''));
	if ($secret === '' || !BestWpTotp::verify($code, $secret)) {
		header('location:../profil.php?totp_setup=1&durum=totp_hata');
		exit;
	}
	$sorgu = $db->prepare('UPDATE kullanicilar SET kul_totp_secret = :s, kul_totp_enabled = 1 WHERE kul_id = :id');
	$ok = $sorgu->execute(array('s' => $secret, 'id' => $kul_id));
	unset($_SESSION['totp_setup_secret']);
	if ($ok) {
		header('location:../profil.php?toast=totp_ok');
	} else {
		header('location:../profil.php?toast=totp_db_hata');
	}
	exit;
}


/********************************************************/

if (isset($_POST['totp_kapat'])) {
	require_once __DIR__ . '/totp.php';
	$kul_id = (int) $_SESSION['kul_id'];
	$sorgu = $db->prepare('SELECT * FROM kullanicilar WHERE kul_id = :id LIMIT 1');
	$sorgu->execute(array('id' => $kul_id));
	$u = $sorgu->fetch(PDO::FETCH_ASSOC);
	if (!$u || (int) ($u['kul_totp_enabled'] ?? 0) !== 1) {
		header('location:../profil.php?durum=totp_kapat_yok');
		exit;
	}
	$pw = (string) ($_POST['kul_sifre_totp'] ?? '');
	if (md5($pw) !== ($u['kul_sifre'] ?? '')) {
		header('location:../profil.php?durum=totp_sifre_hata');
		exit;
	}
	$code = preg_replace('/\D/', '', (string) ($_POST['totp_code_kapat'] ?? ''));
	if (!BestWpTotp::verify($code, $u['kul_totp_secret'] ?? '')) {
		header('location:../profil.php?durum=totp_hata');
		exit;
	}
	$upd = $db->prepare('UPDATE kullanicilar SET kul_totp_secret = NULL, kul_totp_enabled = 0 WHERE kul_id = :id');
	$upd->execute(array('id' => $kul_id));
	header('location:../profil.php?toast=totp_kapat_ok');
	exit;
}


/********************************************************/


if (isset($_POST['kulekle'])) {
	$sorgu=$db->prepare("INSERT INTO kullanicilar SET 
		kul_isim=:kul_isim,
		kul_mail=:kul_mail,
		kul_telefon=:kul_telefon,
		kul_sifre=:kul_sifre,
		kul_onay=1
		");

	$sonuc=$sorgu->execute(array(
		'kul_isim' => $_POST['kul_isim'],
		'kul_mail' => $_POST['kul_mail'],
		'kul_telefon' => $_POST['kul_telefon'],
		'kul_sifre' => md5($_POST['kul_sifre'])
	));

	if ($sonuc) {
		header("location:../index.php?toast=kullanici_ok");
	} else {
		header("location:../index.php?toast=admin_hata");
	}

	exit;

}


/********************************************************/


if (isset($_POST['kulduzenle'])) {
	$sorgu=$db->prepare("UPDATE kullanicilar SET 
		kul_isim=:kul_isim,
		kul_mail=:kul_mail,
		kul_telefon=:kul_telefon WHERE kul_id=:kul_id
		");

	$sonuc=$sorgu->execute(array(
		'kul_isim' => $_POST['kul_isim'],
		'kul_mail' => $_POST['kul_mail'],
		'kul_telefon' => $_POST['kul_telefon'],
		'kul_id' => $_POST['kul_id']
	));

	if (strlen($_POST['kul_sifre'] ?? '') > 0) {
		$sorgu=$db->prepare("UPDATE kullanicilar SET 
			kul_sifre=:kul_sifre WHERE 
			kul_id=:kul_id
			");
		$sonuc=$sorgu->execute(array(
			'kul_sifre' => md5($_POST['kul_sifre']),
			'kul_id' => $_POST['kul_id']
		));
	}

	if ($sonuc) {
		header("location:../index.php?toast=admin_ok");
	} else {
		header("location:../index.php?toast=admin_hata");
	}

	exit;

}


/********************************************************/


if (isset($_POST["kulsilme"])) {
	$sorgu=$db->prepare("DELETE FROM kullanicilar WHERE kul_id=:id");
	$sonuc=$sorgu->execute(array('id' => (int) $_POST['kul_id']));

	if ($sonuc) {
		header("location:../index.php?toast=admin_ok");
	} else {
		header("location:../index.php?toast=admin_hata");
	}

	exit;
}


/********************************************************/

if (isset($_POST['mesajekle'])) {
	$gonderen = (int) $_SESSION['kul_id'];
	$detay = isset($_POST['mesaj_detay']) ? trim((string) $_POST['mesaj_detay']) : '';
	$alici_raw = isset($_POST['mesaj_alici']) ? trim((string) $_POST['mesaj_alici']) : '';

	if ($detay === '') {
		echo json_encode(array('sonuc' => 'no'));
		exit;
	}

	$alici = null;
	if ($alici_raw !== '' && ctype_digit($alici_raw)) {
		$alici = (int) $alici_raw;
	}

	if ($alici !== null) {
		if ($alici === $gonderen) {
			echo json_encode(array('sonuc' => 'no'));
			exit;
		}
		$chk = $db->prepare('SELECT kul_id FROM kullanicilar WHERE kul_id=:id AND COALESCE(kul_onay,1)=1 LIMIT 1');
		$chk->execute(array('id' => $alici));
		if (!$chk->fetch()) {
			echo json_encode(array('sonuc' => 'no'));
			exit;
		}
	}

	$sorgu = $db->prepare('INSERT INTO mesajlar SET mesaj_gonderen=:g, mesaj_detay=:d, mesaj_alici=:a');
	$sonuc = $sorgu->execute(array(
		'g' => $gonderen,
		'd' => $detay,
		'a' => $alici,
	));

	if ($sonuc) {
		bestwp_touch_son_giris($db, $gonderen);
		echo json_encode(array('sonuc' => 'ok'));
	} else {
		echo json_encode(array('sonuc' => 'no'));
	}
	exit;
}


?>