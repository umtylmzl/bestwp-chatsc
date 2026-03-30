<?php
require_once 'islemler/baglan.php';
require_once 'fonksiyonlar.php';
oturumkontrol();

if (!yetkikontrol()) {
	header('location:messenger.php');
	exit;
}

$modal = isset($_GET['modal']) && $_GET['modal'] === '1';

if ($modal) {
	$modal_title = 'Kullanıcı ekle';
	include __DIR__ . '/partials/embed_tailwind_top.php';
	?>
	<h1 class="text-lg font-bold text-primary mb-4">Kullanıcı ekle</h1>
	<form action="islemler/ajax.php" method="POST" accept-charset="utf-8" target="_top" class="space-y-4 max-w-md">
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">İsim soyisim</label>
			<input type="text" name="kul_isim" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="İsim soyisim"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">E-posta</label>
			<input type="email" name="kul_mail" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="E-posta"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">Telefon</label>
			<input type="text" name="kul_telefon" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Telefon"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">Şifre</label>
			<input type="password" name="kul_sifre" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Şifre"/>
		</div>
		<button type="submit" name="kulekle" value="1" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:brightness-110">Kaydet</button>
	</form>
	<?php
	include __DIR__ . '/partials/embed_tailwind_bottom.php';
	exit;
}

$admin_nav = 'kullaniciekle';
$admin_page_title = (!empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp') . ' | Kullanıcı ekle';
$admin_heading = 'Kullanıcı ekle';
include __DIR__ . '/partials/admin_shell_top.php';
?>
<div class="max-w-2xl bg-white rounded-3xl border border-outline-variant/10 shadow-sm p-6 md:p-8">
	<form action="islemler/ajax.php" method="POST" accept-charset="utf-8" class="space-y-5">
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">İsim soyisim</label>
			<input type="text" name="kul_isim" required class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="İsim soyisim"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">E-posta</label>
			<input type="email" name="kul_mail" required class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="E-posta"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Telefon</label>
			<input type="text" name="kul_telefon" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="Telefon"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Şifre</label>
			<input type="password" name="kul_sifre" required class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="Şifre"/>
		</div>
		<button type="submit" name="kulekle" class="inline-flex rounded-full bg-primary px-6 py-2.5 text-sm font-bold text-white hover:brightness-110">Kaydet</button>
	</form>
</div>
<?php include __DIR__ . '/partials/admin_shell_bottom.php'; ?>
