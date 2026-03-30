<?php 


function oturumkontrol($yol="login.php"){
	if (!isset($_SESSION['kul_mail']) OR !isset($_SESSION['kul_isim'])  OR !isset($_SESSION['kul_id'])) {
		session_destroy();
		header("location:$yol");
		exit;
	}
}

function guvenlik($gelen)
{
	$giden=strip_tags($gelen);
	$giden=htmlentities($giden);
	return $giden;
}


function yetkikontrol()
{
	return isset($_SESSION['kul_yetki']) && (int) $_SESSION['kul_yetki'] === 1;
}

/** DiceBear initials — isim + id ile herkes için farklı avatar */
function bestwp_avatar_url($kul_id, $kul_isim)
{
	$seed = rawurlencode((string) $kul_id . '|' . (string) $kul_isim);
	return 'https://api.dicebear.com/7.x/initials/svg?seed=' . $seed . '&chars=2&fontWeight=600&backgroundType=gradientLinear';
}

/** Sohbet listesi saat sütunu (bugün saat, dün, dd.mm) */
function bestwp_thread_list_time($raw)
{
	if (!$raw || !is_string($raw) || strlen($raw) < 10) {
		return '';
	}
	$day = substr($raw, 0, 10);
	$today = date('Y-m-d');
	if ($day === $today && strlen($raw) >= 16) {
		return substr($raw, 11, 5);
	}
	if ($day === date('Y-m-d', strtotime('-1 day'))) {
		return 'Dün';
	}
	return substr($raw, 8, 2) . '.' . substr($raw, 5, 2);
}

/** Onay bekleyen kayıt sayısı (kul_onay = 0) */
function bestwp_pending_registrations_count(PDO $db)
{
	try {
		return (int) $db->query('SELECT COUNT(*) FROM kullanicilar WHERE COALESCE(kul_onay,1) = 0')->fetchColumn();
	} catch (Exception $e) {
		return 0;
	}
}

/** TOTP giriş adımı: oturum çerezi kaybolsa bile (alt klasör vb.) POST ile güvenli kullanıcı bağlantısı */
function bestwp_totp_login_token_create($kul_id)
{
	$kul_id = (int) $kul_id;
	$exp = time() + 900;
	$raw = (string) $kul_id . '|' . (string) $exp;
	$key = hash('sha256', 'bestwp_totp_login_v1|' . __DIR__, true);
	$sig = hash_hmac('sha256', $raw, $key);
	$payload = $raw . '|' . $sig;
	return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
}

function bestwp_totp_login_token_read($token)
{
	$token = trim((string) $token);
	if ($token === '') {
		return null;
	}
	$b64 = strtr($token, '-_', '+/');
	$pad = strlen($b64) % 4;
	if ($pad) {
		$b64 .= str_repeat('=', 4 - $pad);
	}
	$dec = base64_decode($b64, true);
	if ($dec === false || $dec === '') {
		return null;
	}
	$parts = explode('|', $dec, 3);
	if (count($parts) !== 3) {
		return null;
	}
	$kid = (int) $parts[0];
	$exp = (int) $parts[1];
	$sig = $parts[2];
	if ($kid <= 0 || $exp < time() || strlen($sig) !== 64) {
		return null;
	}
	$raw = $kid . '|' . $exp;
	$key = hash('sha256', 'bestwp_totp_login_v1|' . __DIR__, true);
	$expected = hash_hmac('sha256', $raw, $key);
	if (!hash_equals($expected, $sig)) {
		return null;
	}
	return array('kid' => $kid);
}

/** Son aktivite zamanı (kul_son_giris sütunu yoksa yutar) */
function bestwp_touch_son_giris(PDO $db, $kul_id)
{
	try {
		$db->prepare('UPDATE kullanicilar SET kul_son_giris = NOW() WHERE kul_id = :id LIMIT 1')->execute(array('id' => (int) $kul_id));
	} catch (Exception $e) {
	}
}

/** kul_son_giris için kısa Türkçe metin (gün + saat) */
function bestwp_son_giris_metni($raw)
{
	if ($raw === null || $raw === '') {
		return '';
	}
	$s = is_string($raw) ? trim($raw) : '';
	if (strlen($s) < 10) {
		return '';
	}
	$ts = strtotime($s);
	if ($ts === false) {
		return '';
	}
	$today = date('Y-m-d');
	$yesterday = date('Y-m-d', strtotime('-1 day'));
	$d = date('Y-m-d', $ts);
	$hm = date('H:i', $ts);
	if ($d === $today) {
		return 'bugün ' . $hm;
	}
	if ($d === $yesterday) {
		return 'dün ' . $hm;
	}
	$months = array(1 => 'Oca', 2 => 'Şub', 3 => 'Mar', 4 => 'Nis', 5 => 'May', 6 => 'Haz', 7 => 'Tem', 8 => 'Ağu', 9 => 'Eyl', 10 => 'Eki', 11 => 'Kas', 12 => 'Ara');
	$mi = (int) date('n', $ts);
	$mo = isset($months[$mi]) ? $months[$mi] : date('m', $ts);
	if ((int) date('Y', $ts) === (int) date('Y')) {
		return (string) (int) date('j', $ts) . ' ' . $mo . ' ' . $hm;
	}
	return date('d.m.Y', $ts) . ' ' . $hm;
}

/** Okuma anahtarı: genel forum */
function bestwp_okuma_thread_genel()
{
	return 'g';
}

/** Okuma anahtarı: özel sohbet (karşı kullanıcı id) */
function bestwp_okuma_thread_peer($peer_id)
{
	return 'p:' . (int) $peer_id;
}

function bestwp_okuma_max_genel(PDO $db)
{
	try {
		return (int) $db->query('SELECT COALESCE(MAX(mesaj_id), 0) FROM mesajlar WHERE mesaj_alici IS NULL')->fetchColumn();
	} catch (Exception $e) {
		return 0;
	}
}

function bestwp_okuma_max_dm(PDO $db, $me, $peer)
{
	try {
		$st = $db->prepare(
			'SELECT COALESCE(MAX(mesaj_id), 0) FROM mesajlar WHERE mesaj_alici IS NOT NULL
			AND ((mesaj_gonderen = :m AND mesaj_alici = :p) OR (mesaj_gonderen = :p2 AND mesaj_alici = :m2))'
		);
		$st->execute(array('m' => (int) $me, 'p' => (int) $peer, 'p2' => (int) $peer, 'm2' => (int) $me));
		return (int) $st->fetchColumn();
	} catch (Exception $e) {
		return 0;
	}
}

/**
 * Kayıt yoksa oluşturur.
 * Genel: max ile başlar (eski forum mesajları okunmuş sayılır).
 * Özel sohbet (p:…): 0 ile başlar; yoksa ilk yüklemede karşı tarafın mesajları max’a çekilip rozet hiç görünmezdi.
 */
function bestwp_okuma_ensure_last(PDO $db, $kul_id, $thread_key, $max_in_thread)
{
	try {
		$st = $db->prepare('SELECT son_mesaj_id FROM mesaj_okuma WHERE kul_id = :k AND thread = :t LIMIT 1');
		$st->execute(array('k' => (int) $kul_id, 't' => $thread_key));
		$row = $st->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			$initial = ($thread_key === bestwp_okuma_thread_genel())
				? (int) $max_in_thread
				: 0;
			$ins = $db->prepare('INSERT INTO mesaj_okuma (kul_id, thread, son_mesaj_id) VALUES (:k, :t, :m)');
			$ins->execute(array('k' => (int) $kul_id, 't' => $thread_key, 'm' => $initial));
			return $initial;
		}
		return (int) $row['son_mesaj_id'];
	} catch (Exception $e) {
		return ($thread_key === bestwp_okuma_thread_genel())
			? (int) $max_in_thread
			: 0;
	}
}

function bestwp_okuma_mark_upto(PDO $db, $kul_id, $thread_key, $mesaj_id)
{
	try {
		$mid = (int) $mesaj_id;
		$st = $db->prepare(
			'INSERT INTO mesaj_okuma (kul_id, thread, son_mesaj_id) VALUES (:k, :t, :m)
			ON DUPLICATE KEY UPDATE son_mesaj_id = :m2'
		);
		$st->execute(array('k' => (int) $kul_id, 't' => $thread_key, 'm' => $mid, 'm2' => $mid));
		return true;
	} catch (Exception $e) {
		return false;
	}
}

/** Sohbeti arşivle / arşivden çıkar (arsiv sütunu yoksa false) */
function bestwp_okuma_set_arsiv(PDO $db, $kul_id, $thread_key, $archived)
{
	try {
		$mx = 0;
		if ($thread_key === bestwp_okuma_thread_genel()) {
			$mx = bestwp_okuma_max_genel($db);
		} elseif (preg_match('/^p:(\d+)$/', $thread_key, $m)) {
			$mx = bestwp_okuma_max_dm($db, $kul_id, (int) $m[1]);
		}
		$ar = $archived ? 1 : 0;
		$st = $db->prepare(
			'INSERT INTO mesaj_okuma (kul_id, thread, son_mesaj_id, arsiv) VALUES (:k, :t, :m, :a)
			ON DUPLICATE KEY UPDATE arsiv = :a2'
		);
		$st->execute(array('k' => (int) $kul_id, 't' => $thread_key, 'm' => $mx, 'a' => $ar, 'a2' => $ar));
		return true;
	} catch (Exception $e) {
		return false;
	}
}

function bestwp_okunmadi_genel(PDO $db, $me, $last_read)
{
	try {
		$st = $db->prepare(
			'SELECT COUNT(*) FROM mesajlar WHERE mesaj_alici IS NULL AND mesaj_id > :last AND mesaj_gonderen != :me'
		);
		$st->execute(array('last' => (int) $last_read, 'me' => (int) $me));
		return (int) $st->fetchColumn();
	} catch (Exception $e) {
		return 0;
	}
}

function bestwp_okunmadi_dm(PDO $db, $me, $peer, $last_read)
{
	try {
		$st = $db->prepare(
			'SELECT COUNT(*) FROM mesajlar WHERE mesaj_alici IS NOT NULL
			AND ((mesaj_gonderen = :m AND mesaj_alici = :p) OR (mesaj_gonderen = :p2 AND mesaj_alici = :m2))
			AND mesaj_id > :last AND mesaj_gonderen != :me2'
		);
		$st->execute(array(
			'm' => (int) $me,
			'p' => (int) $peer,
			'p2' => (int) $peer,
			'm2' => (int) $me,
			'last' => (int) $last_read,
			'me2' => (int) $me,
		));
		return (int) $st->fetchColumn();
	} catch (Exception $e) {
		return 0;
	}
}

/** okunmadi_json için: genel + her peer id */
function bestwp_okunmadi_harita(PDO $db, $me_id, array $peer_ids)
{
	$out = array('genel' => 0, 'peers' => array());
	try {
		$mxg = bestwp_okuma_max_genel($db);
		$last_g = bestwp_okuma_ensure_last($db, $me_id, bestwp_okuma_thread_genel(), $mxg);
		$out['genel'] = bestwp_okunmadi_genel($db, $me_id, $last_g);
		foreach ($peer_ids as $pid) {
			$pid = (int) $pid;
			if ($pid <= 0) {
				continue;
			}
			$tkey = bestwp_okuma_thread_peer($pid);
			$mx = bestwp_okuma_max_dm($db, $me_id, $pid);
			$last = bestwp_okuma_ensure_last($db, $me_id, $tkey, $mx);
			$out['peers'][(string) $pid] = bestwp_okunmadi_dm($db, $me_id, $pid, $last);
		}
	} catch (Exception $e) {
	}
	return $out;
}

function bestwp_thread_snippet($text, $max = 42)
{
	$t = trim(preg_replace('/\s+/', ' ', (string) $text));
	if ($t === '') {
		return 'Henüz mesaj yok';
	}
	if (function_exists('mb_strlen') && mb_strlen($t) > $max) {
		return mb_substr($t, 0, $max) . '…';
	}
	if (strlen($t) > $max) {
		return substr($t, 0, $max) . '…';
	}
	return $t;
}















?>