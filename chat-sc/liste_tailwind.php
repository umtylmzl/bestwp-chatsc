<?php
require_once 'islemler/baglan.php';
require_once 'fonksiyonlar.php';
oturumkontrol();

$me = (int) $_SESSION['kul_id'];

if (isset($_GET['sayi'])) {
	if ($_GET['sayi'] === 'son50') {
		$lim = 50;
	} elseif ($_GET['sayi'] === 'son100') {
		$lim = 100;
	} else {
		$lim = 5000;
	}
} else {
	$lim = 100;
}

$thread = isset($_GET['thread']) ? $_GET['thread'] : 'genel';
$peer = 0;
if ($thread !== 'genel' && ctype_digit((string) $thread)) {
	$peer = (int) $thread;
}

if ($peer > 0 && $peer !== $me) {
	$sorgu = $db->prepare(
		"SELECT mesajlar.*, g.kul_isim AS kul_isim
		FROM mesajlar
		LEFT JOIN kullanicilar g ON g.kul_id = mesajlar.mesaj_gonderen
		WHERE mesajlar.mesaj_alici IS NOT NULL
		  AND ((mesajlar.mesaj_gonderen = :me AND mesajlar.mesaj_alici = :peer)
		    OR (mesajlar.mesaj_gonderen = :peer2 AND mesajlar.mesaj_alici = :me2))
		ORDER BY mesajlar.mesaj_id DESC
		LIMIT " . (int) $lim
	);
	$sorgu->execute(array(
		'me' => $me,
		'peer' => $peer,
		'peer2' => $peer,
		'me2' => $me,
	));
} else {
	$sorgu = $db->prepare(
		"SELECT mesajlar.*, kullanicilar.kul_isim
		FROM mesajlar
		LEFT JOIN kullanicilar ON kullanicilar.kul_id = mesajlar.mesaj_gonderen
		WHERE mesajlar.mesaj_alici IS NULL
		ORDER BY mesajlar.mesaj_id DESC
		LIMIT " . (int) $lim
	);
	$sorgu->execute();
}

$mesajlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
$mesajlar = array_reverse($mesajlar);

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$last_date = '';

foreach ($mesajlar as $mesaj) {
	$benim = ((int) $mesaj['mesaj_gonderen'] === $me);
	$raw_tarih = $mesaj['mesaj_eklenme_tarih'] ?? '';
	$msg_date = substr($raw_tarih, 0, 10);
	if ($msg_date && $msg_date !== $last_date) {
		$last_date = $msg_date;
		if ($msg_date === $today) {
			$etiket = 'Bugün';
		} elseif ($msg_date === $yesterday) {
			$etiket = 'Dün';
		} else {
			$etiket = $msg_date;
		}
		echo '<div class="bw-day"><span>' . htmlspecialchars($etiket) . '</span></div>';
	}

	$time = '';
	if (strlen($raw_tarih) >= 16) {
		$time = substr($raw_tarih, 11, 5);
	} elseif (strlen($raw_tarih) >= 5) {
		$time = htmlspecialchars($raw_tarih);
	}

	$icerik = nl2br(htmlspecialchars($mesaj['mesaj_detay'] ?? '', ENT_QUOTES, 'UTF-8'));
	$isim = htmlspecialchars($mesaj['kul_isim'] ?? 'Kullanıcı', ENT_QUOTES, 'UTF-8');

	if ($benim) {
		echo '<div class="bw-row bw-row--sent"><div class="bw-bubble bw-bubble--sent">';
		echo '<p class="m-0" style="white-space:pre-wrap">' . $icerik . '</p>';
		echo '<div class="bw-meta"><span>' . htmlspecialchars($time) . '</span></div>';
		echo '<div class="bw-tail-sent"></div></div></div>';
	} else {
		echo '<div class="bw-row bw-row--recv"><div class="bw-bubble bw-bubble--recv">';
		if ($peer === 0) {
			echo '<div class="bw-name">' . $isim . '</div>';
		}
		echo '<p class="m-0" style="white-space:pre-wrap">' . $icerik . '</p>';
		echo '<div class="bw-meta bw-meta--recv"><span>' . htmlspecialchars($time) . '</span></div>';
		echo '<div class="bw-tail-recv"></div></div></div>';
	}
}
