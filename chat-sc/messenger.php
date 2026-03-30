<?php
require_once 'islemler/baglan.php';
require_once 'fonksiyonlar.php';
oturumkontrol();

bestwp_touch_son_giris($db, (int) $_SESSION['kul_id']);

$kul_sorgu = $db->prepare('SELECT * FROM kullanicilar WHERE kul_id = :id LIMIT 1');
$kul_sorgu->execute(array('id' => $_SESSION['kul_id']));
$me = $kul_sorgu->fetch(PDO::FETCH_ASSOC) ?: array();

$site_baslik = !empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp';
$ben_avatar = bestwp_avatar_url((int) ($me['kul_id'] ?? 0), (string) ($me['kul_isim'] ?? 'User'));
$admin = yetkikontrol();

$me_id = (int) $_SESSION['kul_id'];
$uyeler_raw = array();
try {
	$uye_sorgu = $db->prepare('SELECT kul_id, kul_isim, kul_mail, kul_son_giris FROM kullanicilar WHERE COALESCE(kul_onay,1)=1 AND kul_id != :me ORDER BY kul_isim ASC');
	$uye_sorgu->execute(array('me' => $me_id));
	$uyeler_raw = $uye_sorgu->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
	$uye_sorgu = $db->prepare('SELECT kul_id, kul_isim, kul_mail FROM kullanicilar WHERE COALESCE(kul_onay,1)=1 AND kul_id != :me ORDER BY kul_isim ASC');
	$uye_sorgu->execute(array('me' => $me_id));
	$uyeler_raw = $uye_sorgu->fetchAll(PDO::FETCH_ASSOC);
	foreach ($uyeler_raw as &$___r) {
		$___r['kul_son_giris'] = null;
	}
	unset($___r);
}

$peer_order = array();
try {
	$peer_q = $db->prepare(
		'SELECT peer, MAX(mesaj_id) AS mid FROM (
			SELECT mesaj_id, IF(mesaj_gonderen = :me, mesaj_alici, mesaj_gonderen) AS peer
			FROM mesajlar
			WHERE mesaj_alici IS NOT NULL AND (mesaj_gonderen = :me2 OR mesaj_alici = :me3)
		) AS t
		GROUP BY peer
		ORDER BY mid DESC'
	);
	$peer_q->execute(array('me' => $me_id, 'me2' => $me_id, 'me3' => $me_id));
	while ($pr = $peer_q->fetch(PDO::FETCH_ASSOC)) {
		$peer_order[] = (int) $pr['peer'];
	}
} catch (Exception $e) {
	$peer_order = array();
}

$by_id = array();
foreach ($uyeler_raw as $u) {
	$by_id[(int) $u['kul_id']] = $u;
}
$sorted_uyeler = array();
$seen = array();
foreach ($peer_order as $pid) {
	if (isset($by_id[$pid]) && empty($seen[$pid])) {
		$sorted_uyeler[] = $by_id[$pid];
		$seen[$pid] = true;
	}
}
foreach ($uyeler_raw as $u) {
	$uid = (int) $u['kul_id'];
	if (empty($seen[$uid])) {
		$sorted_uyeler[] = $u;
		$seen[$uid] = true;
	}
}

$peer_ids_okuma = array();
foreach ($sorted_uyeler as $___uo) {
	$peer_ids_okuma[] = (int) $___uo['kul_id'];
}
$okunmadi_map = bestwp_okunmadi_harita($db, $me_id, $peer_ids_okuma);
$okunmadi_genel = (int) ($okunmadi_map['genel'] ?? 0);

$arsiv_keys = array();
try {
	$__aq = $db->prepare('SELECT thread FROM mesaj_okuma WHERE kul_id = :me AND arsiv = 1');
	$__aq->execute(array('me' => $me_id));
	while ($__r = $__aq->fetch(PDO::FETCH_ASSOC)) {
		$arsiv_keys[(string) $__r['thread']] = true;
	}
} catch (Exception $e) {
}

$genel_arsivde = !empty($arsiv_keys[bestwp_okuma_thread_genel()]);
$sorted_main_peers = array();
$sorted_arch_peers = array();
foreach ($sorted_uyeler as $__u) {
	$__pid = (int) $__u['kul_id'];
	$__tk = bestwp_okuma_thread_peer($__pid);
	if (!empty($arsiv_keys[$__tk])) {
		$sorted_arch_peers[] = $__u;
	} else {
		$sorted_main_peers[] = $__u;
	}
}
$arsiv_sayisi = (int) $genel_arsivde + count($sorted_arch_peers);

$genel_snip = 'Forumda henüz mesaj yok';
$genel_time = '';
try {
	$gl = $db->query('SELECT mesaj_detay, mesaj_eklenme_tarih FROM mesajlar WHERE mesaj_alici IS NULL ORDER BY mesaj_id DESC LIMIT 1');
	if ($gl && ($gr = $gl->fetch(PDO::FETCH_ASSOC))) {
		$genel_snip = bestwp_thread_snippet($gr['mesaj_detay'] ?? '');
		$genel_time = bestwp_thread_list_time($gr['mesaj_eklenme_tarih'] ?? '');
	}
} catch (Exception $e) {
}

$dm_previews = array();
try {
	$all_dm = $db->prepare(
		'SELECT mesaj_gonderen, mesaj_alici, mesaj_detay, mesaj_eklenme_tarih FROM mesajlar
		WHERE mesaj_alici IS NOT NULL AND (mesaj_gonderen = :me OR mesaj_alici = :me2)
		ORDER BY mesaj_id DESC'
	);
	$all_dm->execute(array('me' => $me_id, 'me2' => $me_id));
	while ($row = $all_dm->fetch(PDO::FETCH_ASSOC)) {
		$p = (int) $row['mesaj_gonderen'] === $me_id ? (int) $row['mesaj_alici'] : (int) $row['mesaj_gonderen'];
		if (!isset($dm_previews[$p])) {
			$raw = $row['mesaj_detay'] ?? '';
			$sn = bestwp_thread_snippet($raw, 36);
			if ((int) $row['mesaj_gonderen'] === $me_id) {
				$sn = 'Sen: ' . $sn;
			}
			$dm_previews[$p] = array(
				'text' => $sn,
				'time' => bestwp_thread_list_time($row['mesaj_eklenme_tarih'] ?? ''),
			);
		}
	}
} catch (Exception $e) {
}

$toast = isset($_GET['toast']) ? $_GET['toast'] : '';
$toast_metin = '';
if ($toast === 'profil_ok') {
	$toast_metin = 'Profil güncellendi.';
} elseif ($toast === 'profil_hata') {
	$toast_metin = 'Profil kaydedilemedi.';
} elseif ($toast === 'ayar_ok') {
	$toast_metin = 'Site ayarları kaydedildi.';
} elseif ($toast === 'ayar_hata') {
	$toast_metin = 'Ayarlar kaydedilemedi.';
} elseif ($toast === 'admin_ok' || $toast === 'kullanici_ok') {
	$toast_metin = 'İşlem tamamlandı.';
} elseif ($toast === 'admin_hata') {
	$toast_metin = 'İşlem başarısız.';
} elseif ($toast === 'onay_ok') {
	$toast_metin = 'Kullanıcı onaylandı.';
} elseif ($toast === 'red_ok') {
	$toast_metin = 'Kayıt reddedildi.';
}
?>
<!DOCTYPE html>
<html class="light" lang="tr">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title><?php echo htmlspecialchars($site_baslik); ?> | Sohbet</title>
	<?php include __DIR__ . '/partials/head_favicons.php'; ?>
	<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&amp;display=swap" rel="stylesheet"/>
	<script>
		tailwind.config = {
			darkMode: 'class',
			theme: {
				extend: {
					colors: {
						outline: '#6f7976',
						'surface-container-highest': '#e0e3e6',
						'surface-container': '#eceef1',
						background: '#f7f9fc',
						'primary-fixed': '#a8f0e3',
						'surface-container-high': '#e6e8eb',
						'primary-container': '#075e54',
						'on-surface-variant': '#3f4946',
						'outline-variant': '#bec9c5',
						'on-surface': '#191c1e',
						'secondary-container': '#5dfd8a',
						primary: '#00453d',
						'on-primary': '#ffffff',
						secondary: '#006d2f',
						'on-secondary-container': '#007232',
						'surface-container-lowest': '#ffffff',
						'surface-container-low': '#f2f4f7',
						'inverse-surface': '#2d3133',
						'inverse-on-surface': '#eff1f4',
						error: '#ba1a1a',
						'wp-sidebar': '#f0f2f5',
						'wp-header': '#f0f2f5'
					},
					fontFamily: { body: ['Inter', 'sans-serif'] }
				}
			}
		};
	</script>
	<link rel="stylesheet" href="css/messenger-bubbles.css"/>
	<style>
		.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
		.material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
		.chat-pattern {
			background-color: #e5ddd5;
			background-image: repeating-linear-gradient(135deg, rgba(0,0,0,.04) 0, rgba(0,0,0,.04) 1px, transparent 1px, transparent 8px);
		}
		.thread-row { transition: background .12s ease; border-bottom: 1px solid rgba(0,0,0,.04); }
		.thread-row:hover { background: rgba(0, 69, 61, 0.06); }
		.thread-row.is-active { background: rgba(0, 69, 61, 0.1); }
		.icon-btn {
			width: 2.5rem; height: 2.5rem; border-radius: 9999px;
			display: inline-flex; align-items: center; justify-content: center;
			color: #54656f; transition: background .15s ease, color .15s ease;
		}
		.icon-btn:hover { background: rgba(0,0,0,.06); color: #00453d; }
		.icon-btn:disabled { opacity: .35; cursor: not-allowed; }
		.search-pill {
			background: #fff; border-radius: 0.5rem;
			box-shadow: 0 1px 1px rgba(0,0,0,.05);
		}
		.custom-scrollbar::-webkit-scrollbar { width: 6px; }
		.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
		.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,.12); border-radius: 10px; }
		#modal-iframe { border: 0; }
		.welcome-illus { max-width: 320px; width: 100%; height: auto; }
		.thread-trail { min-width: 2.75rem; min-height: 2.5rem; align-items: flex-end; justify-content: flex-end; }
		.thread-unread-badge {
			display: none !important;
			box-sizing: border-box;
			min-width: 1.375rem; min-height: 1.375rem;
			padding: 0 0.4rem;
			border-radius: 9999px;
			background: #00a884 !important;
			color: #fff !important;
			font-size: 12px !important;
			font-weight: 800 !important;
			line-height: 1.2 !important;
			letter-spacing: -0.02em;
			align-items: center;
			justify-content: center;
			border: 2px solid #fff;
			box-shadow: 0 1px 4px rgba(0,0,0,.28);
			flex-shrink: 0;
		}
		.thread-unread-badge.is-on { display: inline-flex !important; }
		#row-archived.is-open .arch-chevron { transform: rotate(180deg); }
		/* Mesaj listesi ile giriş çubuğu arasında yumuşak blur + geçiş */
		.chat-footer-fog {
			pointer-events: none;
			flex-shrink: 0;
			height: 4.25rem;
			margin-bottom: -1px;
			background: linear-gradient(to top,
				rgba(242, 244, 247, 0.92) 0%,
				rgba(229, 221, 213, 0.5) 42%,
				rgba(229, 221, 213, 0) 100%);
			-webkit-backdrop-filter: blur(16px);
			backdrop-filter: blur(16px);
			-webkit-mask-image: linear-gradient(to top, #000 0%, #000 55%, transparent 100%);
			mask-image: linear-gradient(to top, #000 0%, #000 55%, transparent 100%);
		}
	</style>
</head>
<body class="bg-background font-body text-on-surface antialiased overflow-hidden h-screen flex">

<main class="flex flex-1 h-full w-full min-w-0">
	<!-- Sol panel: WhatsApp Web benzeri -->
	<section id="messenger-sidebar" class="flex flex-col h-full min-h-0 w-full max-w-full md:max-w-[420px] min-w-0 md:min-w-[280px] flex-shrink-0 bg-wp-sidebar border-r border-black/5 max-md:transition-transform max-md:duration-200">
		<!-- Üst çubuk: avatar + ikonlar -->
		<header class="flex items-center justify-between px-2 py-2 pl-3 bg-wp-header border-b border-black/[0.06]">
			<button type="button" id="open-avatar-menu" class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-white shadow-sm flex-shrink-0" title="Profil">
				<img src="<?php echo htmlspecialchars($ben_avatar); ?>" alt="" class="w-full h-full object-cover" width="40" height="40"/>
			</button>
			<div class="flex items-center gap-0.5">
				<button type="button" class="icon-btn" disabled title="Topluluklar (yakında)">
					<span class="material-symbols-outlined text-[22px]">groups</span>
				</button>
				<button type="button" class="icon-btn" disabled title="Durum (yakında)">
					<span class="material-symbols-outlined text-[22px]">adjust</span>
				</button>
				<button type="button" id="btn-open-forum" class="icon-btn" title="Foruma git">
					<span class="material-symbols-outlined text-[22px]">forum</span>
				</button>
				<button type="button" id="btn-more-menu" class="icon-btn" title="Menü">
					<span class="material-symbols-outlined text-[22px]">more_vert</span>
				</button>
			</div>
		</header>

		<!-- Arama + filtre -->
		<div class="px-3 py-2 flex gap-2 items-center bg-wp-sidebar">
			<div class="search-pill flex-1 flex items-center min-h-[2.25rem] px-3 gap-2 border border-black/[0.06]">
				<span class="material-symbols-outlined text-on-surface-variant/70 text-lg">search</span>
				<input id="sidebar-search" type="search" class="flex-1 bg-transparent border-0 text-sm p-0 focus:ring-0 placeholder:text-on-surface-variant/55" placeholder="Aratın veya yeni sohbet başlatın" autocomplete="off"/>
			</div>
			<button type="button" id="btn-filter-chats" class="icon-btn flex-shrink-0 bg-white border border-black/[0.06]" title="Sohbetleri filtrele">
				<span class="material-symbols-outlined text-xl">filter_list</span>
			</button>
		</div>

		<div class="flex flex-col flex-1 min-h-0">
		<!-- Arşiv satırı -->
		<button type="button" id="row-archived" class="<?php echo $arsiv_sayisi > 0 ? '' : 'hidden'; ?> w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-black/[0.04] border-b border-black/[0.04] transition-colors">
			<span class="material-symbols-outlined text-on-surface-variant shrink-0">archive</span>
			<span class="font-medium text-sm text-on-surface flex-1 min-w-0">Arşivlenmiş</span>
			<span id="archived-chats-count" class="shrink-0 min-w-[1.25rem] h-5 px-1.5 rounded-full bg-surface-container-high text-on-surface text-[11px] font-bold flex items-center justify-center"><?php echo (int) $arsiv_sayisi; ?></span>
			<span class="material-symbols-outlined arch-chevron text-on-surface-variant shrink-0 text-xl transition-transform duration-200">expand_more</span>
		</button>

		<div id="thread-list-archived" class="hidden border-b border-black/[0.08] max-h-[38vh] overflow-y-auto custom-scrollbar bg-[#ebebeb]/80">
			<?php if ($genel_arsivde): ?>
			<div id="row-genel-sohbet" class="thread-row flex items-center gap-2 px-2 py-2.5 pl-1 cursor-pointer" data-thread="genel" data-archived="1" data-search="forum genel sohbet tüm üyeler tum uyeler canlı canli bestwp herkes">
				<button type="button" class="btn-row-unarchive shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-primary-container hover:bg-black/[0.06]" title="Arşivden çıkar" data-unarchive-thread="genel">
					<span class="material-symbols-outlined text-[22px]">unarchive</span>
				</button>
				<div class="w-[3.125rem] h-[3.125rem] rounded-full bg-primary-container flex items-center justify-center text-on-primary flex-shrink-0">
					<span class="material-symbols-outlined fill text-2xl">forum</span>
				</div>
				<div class="flex-1 min-w-0 border-b border-black/[0.04] pb-2.5 -mb-px">
					<div class="flex justify-between items-start gap-2">
						<h3 class="font-semibold text-[15px] leading-tight truncate text-on-surface min-w-0">Genel sohbet</h3>
						<div class="flex flex-col items-end shrink-0 gap-1 thread-trail">
							<span class="thread-time text-[11px] text-primary-container whitespace-nowrap"><?php echo htmlspecialchars($genel_time); ?></span>
							<span class="thread-unread-badge<?php echo $okunmadi_genel > 0 ? ' is-on' : ''; ?>"><?php echo $okunmadi_genel > 99 ? '99+' : (int) $okunmadi_genel; ?></span>
						</div>
					</div>
					<p class="thread-snippet text-[13px] text-on-surface-variant truncate mt-0.5"><?php echo htmlspecialchars($genel_snip); ?></p>
				</div>
			</div>
			<?php endif; ?>
			<?php foreach ($sorted_arch_peers as $u):
				$uid = (int) $u['kul_id'];
				$uname = (string) $u['kul_isim'];
				$umail = (string) ($u['kul_mail'] ?? '');
				$search_blob = mb_strtolower($uname . ' ' . $umail . ' üye uye sohbet mesaj özel', 'UTF-8');
				$av = bestwp_avatar_url($uid, $uname);
				$pv = isset($dm_previews[$uid]) ? $dm_previews[$uid] : array('text' => 'Henüz mesaj yok', 'time' => '');
				$sg_txt = bestwp_son_giris_metni($u['kul_son_giris'] ?? null);
				$peer_subtitle = $sg_txt !== '' ? ('Özel mesaj · Son giriş ' . $sg_txt) : 'Özel mesaj · Son giriş bilinmiyor';
				$ok_peer = (int) ($okunmadi_map['peers'][(string) $uid] ?? 0);
				?>
			<div id="row-peer-<?php echo $uid; ?>" class="thread-row flex items-center gap-2 px-2 py-2.5 pl-1 cursor-pointer" data-thread="<?php echo $uid; ?>" data-archived="1" data-search="<?php echo htmlspecialchars($search_blob, ENT_QUOTES, 'UTF-8'); ?>" data-peer-name="<?php echo htmlspecialchars($uname, ENT_QUOTES, 'UTF-8'); ?>" data-peer-avatar="<?php echo htmlspecialchars($av, ENT_QUOTES, 'UTF-8'); ?>" data-peer-subtitle="<?php echo htmlspecialchars($peer_subtitle, ENT_QUOTES, 'UTF-8'); ?>">
				<button type="button" class="btn-row-unarchive shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-primary-container hover:bg-black/[0.06]" title="Arşivden çıkar" data-unarchive-thread="peer:<?php echo $uid; ?>">
					<span class="material-symbols-outlined text-[22px]">unarchive</span>
				</button>
				<div class="w-[3.125rem] h-[3.125rem] rounded-full overflow-hidden flex-shrink-0 ring-2 ring-white shadow-sm bg-slate-100">
					<img src="<?php echo htmlspecialchars($av); ?>" alt="" class="w-full h-full object-cover" width="50" height="50" loading="lazy"/>
				</div>
				<div class="flex-1 min-w-0 border-b border-black/[0.04] pb-2.5 -mb-px">
					<div class="flex justify-between items-start gap-2">
						<h3 class="font-semibold text-[15px] leading-tight truncate text-on-surface min-w-0"><?php echo htmlspecialchars($uname); ?></h3>
						<div class="flex flex-col items-end shrink-0 gap-1 thread-trail">
							<span class="thread-time text-[11px] text-on-surface-variant whitespace-nowrap"><?php echo htmlspecialchars($pv['time']); ?></span>
							<span class="thread-unread-badge<?php echo $ok_peer > 0 ? ' is-on' : ''; ?>"><?php echo $ok_peer > 99 ? '99+' : (int) $ok_peer; ?></span>
						</div>
					</div>
					<p class="thread-snippet text-[13px] text-on-surface-variant truncate mt-0.5"><?php echo htmlspecialchars($pv['text']); ?></p>
				</div>
			</div>
			<?php endforeach; ?>
		</div>

		<div id="thread-list-main" class="flex-1 overflow-y-auto custom-scrollbar min-h-0">
			<?php if (!$genel_arsivde): ?>
			<div id="row-genel-sohbet" class="thread-row flex items-center gap-3 px-3 py-2.5 cursor-pointer" data-thread="genel" data-archived="0" data-search="forum genel sohbet tüm üyeler tum uyeler canlı canli bestwp herkes">
				<div class="w-[3.125rem] h-[3.125rem] rounded-full bg-primary-container flex items-center justify-center text-on-primary flex-shrink-0">
					<span class="material-symbols-outlined fill text-2xl">forum</span>
				</div>
				<div class="flex-1 min-w-0 border-b border-black/[0.04] pb-2.5 -mb-px">
					<div class="flex justify-between items-start gap-2">
						<h3 class="font-semibold text-[15px] leading-tight truncate text-on-surface min-w-0">Genel sohbet</h3>
						<div class="flex flex-col items-end shrink-0 gap-1 thread-trail">
							<span class="thread-time text-[11px] text-primary-container whitespace-nowrap"><?php echo htmlspecialchars($genel_time); ?></span>
							<span class="thread-unread-badge<?php echo $okunmadi_genel > 0 ? ' is-on' : ''; ?>"><?php echo $okunmadi_genel > 99 ? '99+' : (int) $okunmadi_genel; ?></span>
						</div>
					</div>
					<p class="thread-snippet text-[13px] text-on-surface-variant truncate mt-0.5"><?php echo htmlspecialchars($genel_snip); ?></p>
				</div>
			</div>
			<?php endif; ?>
			<?php foreach ($sorted_main_peers as $u):
				$uid = (int) $u['kul_id'];
				$uname = (string) $u['kul_isim'];
				$umail = (string) ($u['kul_mail'] ?? '');
				$search_blob = mb_strtolower($uname . ' ' . $umail . ' üye uye sohbet mesaj özel', 'UTF-8');
				$av = bestwp_avatar_url($uid, $uname);
				$pv = isset($dm_previews[$uid]) ? $dm_previews[$uid] : array('text' => 'Henüz mesaj yok', 'time' => '');
				$sg_txt = bestwp_son_giris_metni($u['kul_son_giris'] ?? null);
				$peer_subtitle = $sg_txt !== '' ? ('Özel mesaj · Son giriş ' . $sg_txt) : 'Özel mesaj · Son giriş bilinmiyor';
				$ok_peer = (int) ($okunmadi_map['peers'][(string) $uid] ?? 0);
				?>
			<div id="row-peer-<?php echo $uid; ?>" class="thread-row flex items-center gap-3 px-3 py-2.5 cursor-pointer" data-thread="<?php echo $uid; ?>" data-archived="0" data-search="<?php echo htmlspecialchars($search_blob, ENT_QUOTES, 'UTF-8'); ?>" data-peer-name="<?php echo htmlspecialchars($uname, ENT_QUOTES, 'UTF-8'); ?>" data-peer-avatar="<?php echo htmlspecialchars($av, ENT_QUOTES, 'UTF-8'); ?>" data-peer-subtitle="<?php echo htmlspecialchars($peer_subtitle, ENT_QUOTES, 'UTF-8'); ?>">
				<div class="w-[3.125rem] h-[3.125rem] rounded-full overflow-hidden flex-shrink-0 ring-2 ring-white shadow-sm bg-slate-100">
					<img src="<?php echo htmlspecialchars($av); ?>" alt="" class="w-full h-full object-cover" width="50" height="50" loading="lazy"/>
				</div>
				<div class="flex-1 min-w-0 border-b border-black/[0.04] pb-2.5 -mb-px">
					<div class="flex justify-between items-start gap-2">
						<h3 class="font-semibold text-[15px] leading-tight truncate text-on-surface min-w-0"><?php echo htmlspecialchars($uname); ?></h3>
						<div class="flex flex-col items-end shrink-0 gap-1 thread-trail">
							<span class="thread-time text-[11px] text-on-surface-variant whitespace-nowrap"><?php echo htmlspecialchars($pv['time']); ?></span>
							<span class="thread-unread-badge<?php echo $ok_peer > 0 ? ' is-on' : ''; ?>"><?php echo $ok_peer > 99 ? '99+' : (int) $ok_peer; ?></span>
						</div>
					</div>
					<p class="thread-snippet text-[13px] text-on-surface-variant truncate mt-0.5"><?php echo htmlspecialchars($pv['text']); ?></p>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		</div>
		<?php
		$credit_variant = 'messenger';
		include __DIR__ . '/partials/credit_sidebar.php';
		?>
	</section>

	<!-- Sağ: karşılama veya sohbet -->
	<section class="flex-1 flex flex-col min-w-0 relative bg-surface-container-low/50">
		<div id="pane-welcome" class="absolute inset-0 flex flex-col items-center justify-center px-4 sm:px-8 py-8 sm:py-12 border-l border-black/[0.04] bg-[#f8fafb]">
			<div class="welcome-illus mb-8 text-primary-container/90">
				<svg viewBox="0 0 280 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<rect x="120" y="40" width="140" height="100" rx="8" fill="#e8f5f2" stroke="#075e54" stroke-width="2"/>
					<rect x="130" y="52" width="120" height="70" rx="4" fill="#fff"/>
					<path d="M175 87 L195 102 L215 82" stroke="#006d2f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
					<rect x="32" y="72" width="56" height="92" rx="10" fill="#f2f4f7" stroke="#00453d" stroke-width="2"/>
					<rect x="40" y="84" width="40" height="58" rx="4" fill="#fff"/>
					<circle cx="60" cy="152" r="4" fill="#075e54"/>
					<path d="M88 118 Q108 108 128 118" stroke="#bec9c5" stroke-width="2" fill="none" stroke-dasharray="4 4"/>
					<path d="M128 118 Q148 128 168 118" stroke="#bec9c5" stroke-width="2" fill="none" stroke-dasharray="4 4"/>
					<circle cx="108" cy="100" r="6" fill="#a8f0e3"/>
					<circle cx="148" cy="95" r="5" fill="#a8f0e3"/>
				</svg>
			</div>
			<h1 class="text-[1.75rem] font-light text-on-surface/85 tracking-tight text-center mb-4"><?php echo htmlspecialchars($site_baslik); ?> Web</h1>
			<p class="text-center text-on-surface-variant text-[14px] leading-relaxed max-w-md">
				Soldan bir sohbet seçin. Telefonunuzu sürekli açık tutmadan mesaj gönderebilir ve alabilirsiniz. Forum ve özel sohbetler tek yerde.
			</p>
			<div class="mt-14 flex items-center gap-2 text-on-surface-variant/70 text-[13px]">
				<span class="material-symbols-outlined text-base">lock</span>
				<span>Uçtan uca şifrelenmiş değildir; mesajlar sunucuda saklanır.</span>
			</div>
			<p class="mt-8 text-center text-[11px] text-on-surface-variant/80 max-w-sm">
				<span class="font-semibold text-on-surface/90">Umut Yılmaz</span> · BestWp Chat Scripti · 2026 ·
				<a href="https://github.com/umtylmzl" target="_blank" rel="noopener noreferrer" class="text-primary font-medium hover:underline">GitHub</a>
			</p>
		</div>

		<div id="pane-chat" class="hidden absolute inset-0 flex flex-col min-w-0 border-l border-black/[0.04]">
			<header class="flex justify-between items-center w-full px-3 md:px-4 py-2 bg-surface-container-lowest/95 backdrop-blur-md border-b border-black/[0.06] z-20">
				<div class="flex items-center gap-3 min-w-0">
					<button type="button" id="btn-back-welcome" class="icon-btn md:hidden flex-shrink-0" title="Geri">
						<span class="material-symbols-outlined">arrow_back</span>
					</button>
					<div id="thread-header-icon" class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-teal-100/80 shadow-sm bg-primary-container flex items-center justify-center text-on-primary">
						<span class="material-symbols-outlined fill text-xl">forum</span>
					</div>
					<div class="min-w-0">
						<h2 id="thread-header-title" class="text-[16px] font-semibold text-on-surface leading-tight truncate">Genel sohbet</h2>
						<p id="thread-header-sub" class="text-[12px] text-on-surface-variant truncate">Forum · Tüm üyeler</p>
					</div>
				</div>
				<div class="flex items-center gap-0.5 text-on-surface-variant">
					<button type="button" id="btn-thread-archive" class="hidden icon-btn" title="Arşivle">
						<span class="material-symbols-outlined text-[22px] btn-thread-archive-icon">archive</span>
					</button>
					<button type="button" id="toggle-mesaj-limit" class="icon-btn" title="Mesaj geçmişi">
						<span class="material-symbols-outlined text-[22px]">history</span>
					</button>
				</div>
			</header>

			<div id="mesaj-limit-bar" class="hidden px-4 py-2 bg-white/80 border-b border-black/[0.06] text-xs flex flex-wrap gap-4 items-center">
				<label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="radio" name="mesaj_sayisi" class="mesaj-sayisi rounded-full" value="son50"/> Son 50</label>
				<label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="radio" name="mesaj_sayisi" class="mesaj-sayisi rounded-full" value="son100" checked/> Son 100</label>
				<label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="radio" name="mesaj_sayisi" class="mesaj-sayisi rounded-full" value="hepsi"/> Tümü</label>
			</div>

			<div id="mesaj-scroll" class="flex-1 overflow-y-auto px-2.5 pt-3 pb-32 md:px-4 md:pt-4 flex flex-col gap-0.5 custom-scrollbar chat-pattern min-h-0">
				<div id="mesaj-alani"></div>
			</div>

			<footer class="absolute bottom-0 left-0 right-0 z-20 flex flex-col">
				<div class="chat-footer-fog" aria-hidden="true"></div>
				<div class="border-t border-black/[0.07] bg-surface-container-low/93 backdrop-blur-xl px-3 py-3 shadow-[0_-4px_24px_rgba(0,0,0,.04)]">
				<div class="flex items-end gap-2 max-w-4xl mx-auto w-full">
					<div class="flex-1 bg-surface-container-lowest rounded-xl px-3 py-2 shadow-sm border border-black/[0.06] flex items-end gap-2 focus-within:ring-2 focus-within:ring-primary/20">
						<textarea id="mesaj-giris" rows="1" class="flex-1 bg-transparent border-0 p-1.5 text-sm focus:ring-0 resize-none max-h-32 placeholder:text-on-surface-variant/50" placeholder="Bir mesaj yazın"></textarea>
						<button type="button" id="btn-send" class="text-primary-container p-2 rounded-full hover:bg-surface-container-low transition-colors flex-shrink-0" title="Gönder">
							<span class="material-symbols-outlined">send</span>
						</button>
					</div>
				</div>
				</div>
			</footer>
		</div>
	</section>
</main>

<div id="sheet-settings" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:justify-end bg-black/40" aria-hidden="true">
	<div class="bg-white w-full sm:w-full sm:max-w-md h-[85vh] sm:h-full sm:max-h-screen rounded-t-3xl sm:rounded-none shadow-2xl flex flex-col sm:ml-auto sm:mr-0">
		<div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
			<h3 class="font-bold text-lg text-teal-950">Menü</h3>
			<button type="button" class="sheet-close p-2 rounded-full hover:bg-slate-100 material-symbols-outlined text-slate-600">close</button>
		</div>
		<div id="sheet-menu" class="flex-1 overflow-y-auto py-2">
			<button type="button" class="menu-item w-full text-left px-4 py-3 hover:bg-slate-50 flex items-center gap-3 border-b border-slate-50" data-href="profil.php?modal=1">
				<span class="material-symbols-outlined text-primary">person</span>
				<span class="font-medium">Profil</span>
			</button>
			<?php if ($admin): ?>
			<p class="px-4 pt-3 pb-1 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">Yönetim</p>
			<a href="index.php" class="flex items-center gap-3 px-4 py-3 hover:bg-teal-50/90 border-b border-slate-100 text-teal-950 no-underline transition-colors">
				<span class="material-symbols-outlined text-primary-container text-[22px]">dashboard</span>
				<span class="font-semibold text-[15px]">Admin paneli</span>
			</a>
			<button type="button" class="menu-item w-full text-left px-4 py-3 hover:bg-slate-50 flex items-center gap-3 border-b border-slate-50" data-href="ayarlar.php?modal=1">
				<span class="material-symbols-outlined text-primary">tune</span>
				<span class="font-medium">Site ayarları</span>
			</button>
			<button type="button" class="menu-item w-full text-left px-4 py-3 hover:bg-slate-50 flex items-center gap-3 border-b border-slate-50" data-href="kullanicilar.php?modal=1">
				<span class="material-symbols-outlined text-primary">group</span>
				<span class="font-medium">Kullanıcılar</span>
			</button>
			<button type="button" class="menu-item w-full text-left px-4 py-3 hover:bg-slate-50 flex items-center gap-3 border-b border-slate-50" data-href="kullaniciekle.php?modal=1">
				<span class="material-symbols-outlined text-primary">person_add</span>
				<span class="font-medium">Kullanıcı ekle</span>
			</button>
			<button type="button" class="menu-item w-full text-left px-4 py-3 hover:bg-slate-50 flex items-center gap-3 border-b border-slate-50" data-href="onay-bekleyenler.php?modal=1">
				<span class="material-symbols-outlined text-primary">verified_user</span>
				<span class="font-medium">Onay bekleyenler</span>
			</button>
			<?php endif; ?>
			<a href="islemler/cikis.php" class="flex items-center gap-3 px-4 py-3 hover:bg-red-50 text-error font-medium">
				<span class="material-symbols-outlined">logout</span>
				Çıkış yap
			</a>
			<div class="px-4 py-3 border-t border-slate-100 text-[10px] text-slate-500 leading-relaxed">
				<p class="font-semibold text-slate-700">Umut Yılmaz</p>
				<p>BestWp Chat Scripti · 2026</p>
				<a href="https://github.com/umtylmzl" target="_blank" rel="noopener noreferrer" class="text-primary font-semibold hover:underline">GitHub</a>
			</div>
		</div>
		<div id="sheet-frame-wrap" class="hidden flex-1 flex flex-col min-h-0 bg-white">
			<div class="flex items-center gap-2 px-2 py-2 border-b border-slate-100">
				<button type="button" id="sheet-back" class="p-2 rounded-full hover:bg-slate-100 material-symbols-outlined">arrow_back</button>
				<span id="sheet-frame-title" class="font-semibold text-sm truncate"></span>
			</div>
			<iframe id="modal-iframe" class="flex-1 w-full min-h-[320px] h-[65vh] bg-slate-50" title="İçerik"></iframe>
		</div>
	</div>
</div>

<?php if ($toast_metin !== ''): ?>
<div id="toast-bar" class="fixed top-4 left-1/2 -translate-x-1/2 z-[60] px-4 py-2 rounded-full bg-teal-900 text-white text-sm shadow-lg"><?php echo htmlspecialchars($toast_metin); ?></div>
<script>
	setTimeout(function () {
		var t = document.getElementById('toast-bar');
		if (t) t.remove();
	}, 4000);
</script>
<?php endif; ?>

<script src="vendor/jquery/jquery.min.js"></script>
<script>
(function () {
	var $scroll = $('#mesaj-scroll');
	var $alani = $('#mesaj-alani');
	var $welcome = $('#pane-welcome');
	var $chat = $('#pane-chat');
	var $sidebar = $('#messenger-sidebar');
	var activeThread = null;
	var pollTimer = null;
	var unreadPollTimer = null;

	function badgeLabel(n) {
		n = parseInt(n, 10) || 0;
		if (n > 99) {
			return '99+';
		}
		return String(n);
	}

	function applyUnreadCounts(data) {
		if (!data || !data.counts) {
			return;
		}
		var c = data.counts;
		var g = parseInt(c.genel, 10) || 0;
		var $bg = $('#row-genel-sohbet .thread-unread-badge');
		if ($bg.length) {
			$bg.toggleClass('is-on', g > 0).text(badgeLabel(g));
		}
		if (c.peers) {
			Object.keys(c.peers).forEach(function (id) {
				var n = parseInt(c.peers[id], 10) || 0;
				var $b = $('#row-peer-' + id + ' .thread-unread-badge');
				if ($b.length) {
					$b.toggleClass('is-on', n > 0).text(badgeLabel(n));
				}
			});
		}
	}

	function fetchUnread() {
		$.getJSON('islemler/ajax.php', { okunmadi_json: '1' }).done(applyUnreadCounts);
	}

	function markThreadRead(t) {
		var threadParam = t === 'genel' ? 'genel' : ('peer:' + t);
		$.post('islemler/ajax.php', { sohbet_okundu: '1', thread: threadParam }, function () {
			fetchUnread();
		});
	}

	function startUnreadPoll() {
		if (unreadPollTimer) {
			return;
		}
		fetchUnread();
		unreadPollTimer = setInterval(fetchUnread, 2000);
	}

	function scrollBottom() {
		var el = $scroll[0];
		if (el) el.scrollTop = el.scrollHeight;
	}

	function yukle() {
		if (activeThread === null) return;
		var sayi = $('.mesaj-sayisi:checked').val() || 'son100';
		var url = 'liste_tailwind.php?sayi=' + encodeURIComponent(sayi) + '&thread=' + encodeURIComponent(activeThread);
		$alani.load(url, function () {
			scrollBottom();
		});
	}

	function startPoll() {
		if (pollTimer) return;
		pollTimer = setInterval(yukle, 2000);
	}
	function stopPoll() {
		if (pollTimer) {
			clearInterval(pollTimer);
			pollTimer = null;
		}
	}

	function showWelcome() {
		stopPoll();
		activeThread = null;
		$('.thread-row').removeClass('is-active');
		$welcome.removeClass('hidden');
		$chat.addClass('hidden');
		$alani.empty();
		$sidebar.removeClass('max-md:hidden');
		$('#btn-thread-archive').addClass('hidden');
	}

	function showChat() {
		$welcome.addClass('hidden');
		$chat.removeClass('hidden');
		if (window.matchMedia('(max-width: 767px)').matches) {
			$sidebar.addClass('max-md:hidden');
		}
		$('#btn-thread-archive').removeClass('hidden');
		startPoll();
		yukle();
	}

	function gonder() {
		if (activeThread === null) return;
		var t = $('#mesaj-giris').val();
		if (!t || !t.trim()) {
			alert('Mesaj boş olamaz');
			return;
		}
		var data = { mesajekle: 'mesajekle', mesaj_detay: t };
		if (activeThread !== 'genel') {
			data.mesaj_alici = activeThread;
		}
		$.post('islemler/ajax.php', data);
		$('#mesaj-giris').val('').css('height', 'auto');
		var snip = t.trim();
		if (snip.length > 40) snip = snip.substring(0, 40) + '…';
		if (activeThread === 'genel') {
			$('#row-genel-sohbet .thread-snippet').text(snip);
			$('#row-genel-sohbet .thread-time').text(nowTime());
		} else {
			var $peerRow = $('#row-peer-' + activeThread);
			if ($peerRow.length) {
				$peerRow.find('.thread-snippet').text('Sen: ' + snip);
				$peerRow.find('.thread-time').text(nowTime());
				var $anchor = $('#thread-list-main #row-genel-sohbet').first();
				if (!$anchor.length) {
					$anchor = $('#thread-list-main .thread-row').first();
				}
				if ($anchor.length) {
					$peerRow.insertAfter($anchor);
				}
			}
		}
		yukle();
	}

	function nowTime() {
		var d = new Date();
		return (d.getHours() < 10 ? '0' : '') + d.getHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes();
	}

	function syncArchiveHeaderBtn(t) {
		var $row = t === 'genel' ? $('#row-genel-sohbet') : $('#row-peer-' + t);
		var archived = ($row.attr('data-archived') || '0') === '1';
		var $ic = $('#btn-thread-archive .btn-thread-archive-icon');
		if (archived) {
			$ic.text('unarchive');
			$('#btn-thread-archive').attr('title', 'Arşivden çıkar');
		} else {
			$ic.text('archive');
			$('#btn-thread-archive').attr('title', 'Arşivle');
		}
	}

	function setThread(t) {
		activeThread = t;
		markThreadRead(t);
		$('.thread-row').removeClass('is-active');
		var $row = t === 'genel' ? $('#row-genel-sohbet') : $('#row-peer-' + t);
		$row.addClass('is-active');
		syncArchiveHeaderBtn(t);

		var $icon = $('#thread-header-icon');
		if (t === 'genel') {
			$icon.removeClass('overflow-hidden bg-white').addClass('flex items-center justify-center bg-primary-container text-on-primary');
			$icon.html('<span class="material-symbols-outlined fill text-xl">forum</span>');
			$('#thread-header-title').text('Genel sohbet');
			$('#thread-header-sub').text('Forum · Tüm üyeler');
			$('#mesaj-giris').attr('placeholder', 'Forumda herkese yazın…');
		} else {
			var name = $row.attr('data-peer-name') || 'Sohbet';
			var av = $row.attr('data-peer-avatar') || '';
			$icon.removeClass('flex items-center justify-center bg-primary-container text-on-primary').addClass('overflow-hidden bg-white');
			$icon.empty();
			$('<img>', { src: av, class: 'w-full h-full object-cover', alt: '' }).appendTo($icon);
			$('#thread-header-title').text(name);
			$('#thread-header-sub').text($row.attr('data-peer-subtitle') || 'Özel mesaj · Son giriş bilinmiyor');
			$('#mesaj-giris').attr('placeholder', name + ' kişisine yazın…');
		}
		showChat();
	}

	$(document).on('click', '.thread-row', function (e) {
		if ($(e.target).closest('.btn-row-unarchive').length) {
			return;
		}
		var t = $(this).data('thread');
		setThread(t === 'genel' ? 'genel' : String(t));
	});

	$('#row-archived').on('click', function () {
		$('#thread-list-archived').toggleClass('hidden');
		$(this).toggleClass('is-open');
	});

	$(document).on('click', '.btn-row-unarchive', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var th = $(this).attr('data-unarchive-thread');
		if (!th) {
			return;
		}
		$.post('islemler/ajax.php', { sohbet_arsiv: '1', thread: th, arsiv: '0' }, function (r) {
			if (r && r.ok) {
				window.location.reload();
			}
		}, 'json');
	});

	$('#btn-thread-archive').on('click', function () {
		if (activeThread === null) {
			return;
		}
		var $row = activeThread === 'genel' ? $('#row-genel-sohbet') : $('#row-peer-' + activeThread);
		var archived = ($row.attr('data-archived') || '0') === '1';
		var threadParam = activeThread === 'genel' ? 'genel' : ('peer:' + activeThread);
		$.post('islemler/ajax.php', { sohbet_arsiv: '1', thread: threadParam, arsiv: archived ? '0' : '1' }, function (r) {
			if (r && r.ok) {
				window.location.reload();
			}
		}, 'json');
	});

	$('#btn-open-forum').on('click', function () {
		setThread('genel');
		$('#row-genel-sohbet')[0].scrollIntoView({ block: 'nearest' });
	});

	$('#btn-back-welcome').on('click', showWelcome);

	$('#btn-send').on('click', gonder);
	$('#mesaj-giris').on('keydown', function (e) {
		if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
			gonder();
		}
	});

	$('#toggle-mesaj-limit').on('click', function () {
		$('#mesaj-limit-bar').toggleClass('hidden');
	});

	$('#sidebar-search').on('input', function () {
		var q = $(this).val().toLowerCase().trim();
		$('.thread-row').each(function () {
			var blob = ($(this).data('search') || '').toString().toLowerCase();
			if (!q || blob.indexOf(q) !== -1) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});

	var filterOn = false;
	$('#btn-filter-chats').on('click', function () {
		filterOn = !filterOn;
		$(this).toggleClass('bg-primary/10 text-primary-container', filterOn);
		if (!filterOn) {
			$('.thread-row').show();
			return;
		}
		$('.thread-row').each(function () {
			var sn = ($(this).find('.thread-snippet').text() || '').toLowerCase();
			var isEmpty = sn.indexOf('henüz mesaj') !== -1 || sn.indexOf('yok') !== -1;
			$(this).toggle(!isEmpty);
		});
	});

	var $sheet = $('#sheet-settings');
	var $menu = $('#sheet-menu');
	var $wrap = $('#sheet-frame-wrap');
	var $iframe = $('#modal-iframe');

	function sheetKapat() {
		$sheet.addClass('hidden').attr('aria-hidden', 'true');
		$menu.removeClass('hidden');
		$wrap.addClass('hidden');
		$iframe.attr('src', 'about:blank');
	}

	function openMenu() {
		$sheet.removeClass('hidden').attr('aria-hidden', 'false');
		$menu.removeClass('hidden');
		$wrap.addClass('hidden');
		$iframe.attr('src', 'about:blank');
	}

	$('#open-avatar-menu').on('click', openMenu);
	$('#btn-more-menu').on('click', openMenu);

	$sheet.on('click', function (e) {
		if (e.target === this) sheetKapat();
	});
	$('.sheet-close').on('click', sheetKapat);

	$('.menu-item').on('click', function () {
		var href = $(this).data('href');
		var title = $(this).find('span.font-medium').text();
		$menu.addClass('hidden');
		$wrap.removeClass('hidden');
		$('#sheet-frame-title').text(title);
		$iframe.attr('src', href);
	});

	$('#sheet-back').on('click', function () {
		$iframe.attr('src', 'about:blank');
		$wrap.addClass('hidden');
		$menu.removeClass('hidden');
	});

	$(document).on('keydown', function (e) {
		if (e.key === 'Escape' && activeThread !== null) {
			showWelcome();
		}
	});

	startUnreadPoll();

	(function handleOpenParam() {
		var params = new URLSearchParams(window.location.search);
		var open = params.get('open');
		var withId = params.get('with');
		if (open === 'genel') {
			setThread('genel');
			var rg = document.getElementById('row-genel-sohbet');
			if (rg) {
				rg.scrollIntoView({ block: 'nearest' });
			}
			return;
		}
		var peer = open && /^\d+$/.test(open) ? open : (withId && /^\d+$/.test(withId) ? withId : null);
		if (peer && document.getElementById('row-peer-' + peer)) {
			setThread(peer);
			var pr = document.getElementById('row-peer-' + peer);
			if (pr) {
				pr.scrollIntoView({ block: 'nearest' });
			}
		}
	})();
})();
</script>
</body>
</html>
