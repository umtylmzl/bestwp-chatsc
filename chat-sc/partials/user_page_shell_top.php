<?php
if (!isset($_SESSION['kul_id'])) {
	exit;
}
$user_page_title = $user_page_title ?? 'BestWp';
?>
<!DOCTYPE html>
<html class="light" lang="tr">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title><?php echo htmlspecialchars($user_page_title); ?></title>
	<?php include __DIR__ . '/head_favicons.php'; ?>
	<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&amp;display=swap" rel="stylesheet"/>
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&amp;display=swap" rel="stylesheet"/>
	<script>
		tailwind.config = {
			theme: {
				extend: {
					colors: { primary: '#00453d', secondary: '#006d2f', background: '#f7f9fc' },
					fontFamily: { sans: ['Inter', 'sans-serif'] }
				}
			}
		};
	</script>
	<style>.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }</style>
</head>
<body class="bg-[#f7f9fc] min-h-screen font-sans text-slate-800 antialiased">
	<header class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-slate-200 px-3 sm:px-4 py-3 flex items-center justify-between gap-2 min-w-0">
		<a href="messenger.php" class="inline-flex items-center gap-2 text-sm font-semibold text-[#00453d] hover:underline">
			<span class="material-symbols-outlined text-xl">arrow_back</span>
			Sohbete dön
		</a>
		<span class="text-sm font-bold text-teal-900 truncate"><?php echo htmlspecialchars($user_page_title); ?></span>
		<a href="islemler/cikis.php" class="text-sm text-red-600 font-medium hover:underline">Çıkış</a>
	</header>
	<div class="max-w-2xl mx-auto p-4 md:p-6">
