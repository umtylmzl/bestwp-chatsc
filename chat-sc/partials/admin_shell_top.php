<?php
if (!isset($db) || !isset($_SESSION['kul_id'])) {
	exit;
}
$admin_nav = $admin_nav ?? '';
$admin_shell_mode = $admin_shell_mode ?? 'page';
$admin_page_title = $admin_page_title ?? (!empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp');
$admin_heading = $admin_heading ?? $admin_page_title;
$site_baslik_shell = !empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp';

$me_id_shell = (int) $_SESSION['kul_id'];
$kul_sorgu_shell = $db->prepare('SELECT * FROM kullanicilar WHERE kul_id = :id LIMIT 1');
$kul_sorgu_shell->execute(array('id' => $me_id_shell));
$me_shell = $kul_sorgu_shell->fetch(PDO::FETCH_ASSOC) ?: array();
$ben_avatar_shell = bestwp_avatar_url($me_id_shell, (string) ($me_shell['kul_isim'] ?? 'Admin'));
$bekleyen_shell = bestwp_pending_registrations_count($db);

$nav_panel = $admin_nav === 'panel'
	? 'flex items-center gap-3 px-3 py-2.5 rounded-xl bg-teal-100/90 text-teal-950 font-semibold text-sm shadow-sm border border-teal-200/50'
	: 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-700 hover:bg-slate-200/80 font-medium text-sm transition-colors';
$nav_sohbet = $admin_nav === 'sohbet'
	? 'flex items-center gap-3 px-3 py-2.5 rounded-xl bg-teal-100/90 text-teal-950 font-semibold text-sm shadow-sm border border-teal-200/50'
	: 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-700 hover:bg-slate-200/80 font-medium text-sm transition-colors';
$nav_ayar = $admin_nav === 'ayarlar'
	? 'flex items-center gap-3 px-3 py-2.5 rounded-xl bg-teal-100/90 text-teal-950 font-semibold text-sm shadow-sm border border-teal-200/50'
	: 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-700 hover:bg-slate-200/80 font-medium text-sm transition-colors';
$nav_profil = $admin_nav === 'profil'
	? 'flex items-center gap-3 px-3 py-2.5 rounded-xl bg-teal-100/90 text-teal-950 font-semibold text-sm shadow-sm border border-teal-200/50'
	: 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-700 hover:bg-slate-200/80 font-medium text-sm transition-colors';

$sub_kul_list = $admin_nav === 'kullanicilar' ? 'block py-2 px-2.5 rounded-lg text-sm bg-white text-primary font-semibold shadow-sm' : 'block py-2 px-2.5 rounded-lg text-sm text-slate-600 hover:bg-white hover:text-primary font-medium transition-colors';
$sub_kul_ekle = $admin_nav === 'kullaniciekle' ? 'block py-2 px-2.5 rounded-lg text-sm bg-white text-primary font-semibold shadow-sm' : 'block py-2 px-2.5 rounded-lg text-sm text-slate-600 hover:bg-white hover:text-primary font-medium transition-colors';
$sub_onay = $admin_nav === 'onay' ? 'flex items-center justify-between gap-2 py-2 px-2.5 rounded-lg text-sm bg-white text-primary font-semibold shadow-sm' : 'flex items-center justify-between gap-2 py-2 px-2.5 rounded-lg text-sm text-slate-600 hover:bg-white hover:text-primary font-medium transition-colors';
?>
<!DOCTYPE html>
<html class="light" lang="tr">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title><?php echo htmlspecialchars($admin_page_title); ?></title>
	<?php include __DIR__ . '/head_favicons.php'; ?>
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
						'on-primary-fixed-variant': '#005047',
						'surface-container-high': '#e6e8eb',
						'primary-container': '#075e54',
						'on-surface-variant': '#3f4946',
						'outline-variant': '#bec9c5',
						'on-surface': '#191c1e',
						'secondary-container': '#5dfd8a',
						primary: '#00453d',
						'on-primary': '#ffffff',
						secondary: '#006d2f',
						'on-secondary': '#ffffff',
						'on-secondary-container': '#007232',
						'surface-container-lowest': '#ffffff',
						'surface-container-low': '#f2f4f7',
						'secondary-fixed': '#66ff8e',
						error: '#ba1a1a',
						'error-container': '#ffdad6',
						'on-error': '#ffffff',
						'on-error-container': '#93000a'
					},
					fontFamily: { body: ['Inter', 'sans-serif'] }
				}
			}
		};
	</script>
	<style>
		.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
		.material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
		::-webkit-scrollbar { width: 6px; }
		::-webkit-scrollbar-track { background: transparent; }
		::-webkit-scrollbar-thumb { background: #bec9c5; border-radius: 10px; }
		.glass-search { background: rgba(242, 244, 247, 0.85); backdrop-filter: blur(20px); }
		.tonal-shift { transition: background-color 0.2s ease; }
		.admin-rail details > summary { list-style: none; }
		.admin-rail details > summary::-webkit-details-marker { display: none; }
		.admin-rail details[open] .admin-chevron { transform: rotate(180deg); }
		.admin-chevron { transition: transform 0.2s ease; }
	</style>
</head>
<body class="bg-background font-body text-on-surface antialiased flex h-screen overflow-hidden">

<div id="admin-nav-overlay" class="fixed inset-0 z-30 bg-slate-900/45 md:hidden hidden" aria-hidden="true"></div>

<aside id="admin-sidebar" class="admin-rail fixed left-0 top-0 z-40 bg-slate-100 h-screen w-56 flex flex-col border-r border-slate-200/80 shadow-sm transition-transform duration-200 ease-out max-md:-translate-x-full md:translate-x-0">
	<button type="button" class="admin-nav-close md:hidden absolute right-2 top-3 z-10 p-2 rounded-xl text-slate-600 hover:bg-white/90 border border-slate-200/60" aria-label="Menüyü kapat">
		<span class="material-symbols-outlined text-2xl">close</span>
	</button>
	<div class="px-3 pt-4 pb-3 border-b border-slate-200/60">
		<a href="index.php" class="flex items-center gap-3 min-w-0 rounded-xl p-2 hover:bg-white/80 transition-colors" title="Panel">
			<span class="w-11 h-11 rounded-full bg-primary-container overflow-hidden shadow-sm ring-2 ring-white flex-shrink-0">
				<img src="<?php echo htmlspecialchars($ben_avatar_shell); ?>" alt="" class="w-full h-full object-cover"/>
			</span>
			<div class="min-w-0 text-left">
				<p class="text-xs font-bold text-teal-950 truncate"><?php echo htmlspecialchars($me_shell['kul_isim'] ?? 'Admin'); ?></p>
				<p class="text-[10px] text-slate-500 font-medium uppercase tracking-wide">Yönetici</p>
			</div>
		</a>
	</div>
	<nav class="flex-1 overflow-y-auto py-3 px-2 space-y-1">
		<a href="index.php" class="<?php echo $nav_panel; ?>">
			<span class="material-symbols-outlined fill text-xl">dashboard</span>
			Panel
		</a>
		<a href="messenger.php" class="<?php echo $nav_sohbet; ?>">
			<span class="material-symbols-outlined text-xl">chat</span>
			Sohbet
		</a>
		<p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Yönetim</p>
		<details class="group" open>
			<summary class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-700 hover:bg-slate-200/80 font-semibold text-sm cursor-pointer select-none transition-colors">
				<span class="material-symbols-outlined text-xl">group</span>
				<span class="flex-1 text-left">Kullanıcılar</span>
				<span class="material-symbols-outlined text-lg text-slate-500 admin-chevron">expand_more</span>
			</summary>
			<div class="mt-1 mb-2 ml-3 pl-3 border-l-2 border-slate-300/80 space-y-0.5">
				<a href="kullanicilar.php" class="<?php echo $sub_kul_list; ?>">Tüm kullanıcılar</a>
				<a href="kullaniciekle.php" class="<?php echo $sub_kul_ekle; ?>">Kullanıcı ekle</a>
				<a href="onay-bekleyenler.php" class="<?php echo $sub_onay; ?>">
					<span>Onay bekleyenler</span>
					<?php if ($bekleyen_shell > 0): ?>
					<span class="min-w-[1.25rem] h-5 px-1 bg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center"><?php echo (int) $bekleyen_shell; ?></span>
					<?php endif; ?>
				</a>
			</div>
		</details>
		<a href="ayarlar.php" class="<?php echo $nav_ayar; ?>">
			<span class="material-symbols-outlined text-xl">tune</span>
			Site ayarları
		</a>
		<a href="profil.php" class="<?php echo $nav_profil; ?>">
			<span class="material-symbols-outlined text-xl">person</span>
			Profilim
		</a>
	</nav>
	<?php
	$credit_variant = 'admin';
	include __DIR__ . '/credit_sidebar.php';
	?>
	<div class="p-2 border-t border-slate-200/60 space-y-1">
		<a href="islemler/cikis.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-error hover:bg-red-50 font-medium text-sm transition-colors">
			<span class="material-symbols-outlined text-xl">logout</span>
			Çıkış
		</a>
	</div>
</aside>

<main class="flex-1 ml-0 md:ml-56 flex flex-col overflow-hidden min-w-0 w-full">
	<?php if ($admin_shell_mode === 'dashboard'): ?>
	<header class="sticky top-0 z-30 bg-white/90 backdrop-blur-xl flex flex-wrap justify-between items-center gap-4 w-full px-4 sm:px-6 py-3.5 shadow-sm border-b border-black/[0.06]">
		<div class="flex flex-wrap items-center gap-3 sm:gap-6 min-w-0">
			<button type="button" class="admin-nav-open md:hidden flex-shrink-0 p-2.5 rounded-xl text-slate-700 hover:bg-slate-100 border border-slate-200/80" aria-label="Menüyü aç">
				<span class="material-symbols-outlined text-2xl">menu</span>
			</button>
			<h1 class="text-base sm:text-lg font-bold text-teal-900 tracking-tight truncate min-w-0">
				<?php echo htmlspecialchars($site_baslik_shell); ?>
				<span class="text-secondary ml-2 font-normal text-sm opacity-75">Yönetim terminali</span>
			</h1>
			<div class="glass-search hidden md:flex items-center gap-2 px-4 py-2 rounded-full w-72 lg:w-96 border border-black/[0.06] focus-within:ring-2 focus-within:ring-primary/15">
				<span class="material-symbols-outlined text-outline text-lg">search</span>
				<input id="dash-filter" type="search" class="bg-transparent border-none focus:ring-0 text-sm w-full p-0 text-on-surface" placeholder="Kart veya kayıt ara…" autocomplete="off"/>
			</div>
		</div>
		<div class="flex items-center gap-4 flex-wrap">
			<div class="flex gap-2 text-slate-600">
				<a href="messenger.php?open=genel" class="p-2 rounded-full hover:bg-slate-100 hover:text-primary transition-colors" title="Forum"><span class="material-symbols-outlined">forum</span></a>
				<a href="onay-bekleyenler.php" class="p-2 rounded-full hover:bg-slate-100 hover:text-primary transition-colors relative" title="Onay bekleyenler">
					<span class="material-symbols-outlined">verified_user</span>
					<?php if ($bekleyen_shell > 0): ?>
					<span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center"><?php echo (int) $bekleyen_shell; ?></span>
					<?php endif; ?>
				</a>
				<a href="islemler/cikis.php" class="p-2 rounded-full hover:bg-red-50 text-error transition-colors" title="Çıkış"><span class="material-symbols-outlined">logout</span></a>
			</div>
			<div class="h-8 w-px bg-outline-variant/40 hidden sm:block"></div>
			<div class="flex items-center gap-2">
				<div class="text-right hidden sm:block">
					<p class="text-xs font-bold text-on-surface">Sistem</p>
					<p class="text-[10px] text-secondary font-semibold uppercase tracking-tight">Çalışıyor</p>
				</div>
				<div class="w-2 h-2 rounded-full bg-secondary animate-pulse flex-shrink-0"></div>
			</div>
		</div>
	</header>
	<?php else: ?>
	<header class="sticky top-0 z-30 bg-white/90 backdrop-blur-xl flex flex-wrap justify-between items-center gap-3 w-full px-4 sm:px-6 py-3.5 shadow-sm border-b border-black/[0.06]">
		<div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
			<button type="button" class="admin-nav-open md:hidden flex-shrink-0 p-2 rounded-full text-slate-600 hover:bg-slate-100 hover:text-primary transition-colors" aria-label="Menüyü aç">
				<span class="material-symbols-outlined">menu</span>
			</button>
			<a href="index.php" class="p-2 rounded-full text-slate-600 hover:bg-slate-100 hover:text-primary transition-colors shrink-0" title="Panele dön"><span class="material-symbols-outlined">arrow_back</span></a>
			<h1 class="text-base sm:text-lg font-bold text-teal-900 tracking-tight truncate min-w-0"><?php echo htmlspecialchars($admin_heading); ?></h1>
		</div>
		<div class="flex items-center gap-2">
			<a href="messenger.php" class="text-sm font-semibold text-primary hover:underline">Sohbet</a>
			<a href="islemler/cikis.php" class="p-2 rounded-full hover:bg-red-50 text-error transition-colors" title="Çıkış"><span class="material-symbols-outlined">logout</span></a>
		</div>
	</header>
	<?php endif; ?>

	<div class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 <?php echo $admin_shell_mode === 'dashboard' ? 'space-y-8' : 'space-y-6'; ?>">
