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
	$modal_title = 'Site ayarları';
	include __DIR__ . '/partials/embed_tailwind_top.php';
	?>
	<h1 class="text-lg font-bold text-primary mb-4">Site ayarları</h1>
	<form action="islemler/ajax.php" method="POST" accept-charset="utf-8" enctype="multipart/form-data" target="_top" class="space-y-4 max-w-lg">
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">Site logosu</label>
			<input type="file" name="site_logo" class="block w-full text-sm text-slate-600"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">Site başlık</label>
			<input type="text" name="site_baslik" value="<?php echo htmlspecialchars($ayarcek['site_baslik'] ?? ''); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">Site açıklama</label>
			<input type="text" name="site_aciklama" value="<?php echo htmlspecialchars($ayarcek['site_aciklama'] ?? ''); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">Site link</label>
			<input type="text" name="site_link" value="<?php echo htmlspecialchars($ayarcek['site_link'] ?? ''); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">Site sahibi e-posta</label>
			<input type="text" name="site_sahip_mail" value="<?php echo htmlspecialchars($ayarcek['site_sahip_mail'] ?? ''); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"/>
		</div>
		<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
			<div>
				<label class="block text-xs font-bold text-slate-600 mb-1">Mail host</label>
				<input type="text" name="site_mail_host" value="<?php echo htmlspecialchars($ayarcek['site_mail_host'] ?? ''); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"/>
			</div>
			<div>
				<label class="block text-xs font-bold text-slate-600 mb-1">Mail adresi</label>
				<input type="text" name="site_mail_mail" value="<?php echo htmlspecialchars($ayarcek['site_mail_mail'] ?? ''); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"/>
			</div>
			<div>
				<label class="block text-xs font-bold text-slate-600 mb-1">Mail port</label>
				<input type="text" name="site_mail_port" value="<?php echo htmlspecialchars($ayarcek['site_mail_port'] ?? ''); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"/>
			</div>
			<div>
				<label class="block text-xs font-bold text-slate-600 mb-1">Mail şifresi</label>
				<input type="text" name="site_mail_sifre" value="<?php echo htmlspecialchars($ayarcek['site_mail_sifre'] ?? ''); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"/>
			</div>
		</div>
		<button type="submit" name="ayarkaydet" value="1" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:brightness-110">Kaydet</button>
	</form>
	<?php
	include __DIR__ . '/partials/embed_tailwind_bottom.php';
	exit;
}

$admin_nav = 'ayarlar';
$admin_page_title = (!empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp') . ' | Site ayarları';
$admin_heading = 'Site ayarları';
include __DIR__ . '/partials/admin_shell_top.php';

$logo_fn = $ayarcek['site_logo'] ?? '';
$logo_ok = $logo_fn !== '' && is_file(__DIR__ . '/dosyalar/' . $logo_fn);
?>
<div class="max-w-3xl bg-white rounded-3xl border border-outline-variant/10 shadow-sm p-6 md:p-8">
	<?php if ($logo_ok): ?>
	<div class="mb-6 flex items-center gap-4">
		<img src="dosyalar/<?php echo htmlspecialchars($logo_fn); ?>" alt="Logo" class="h-14 w-auto rounded-lg border border-outline-variant/20 object-contain bg-white p-1"/>
		<p class="text-xs text-on-surface-variant">Mevcut logo. Yeni dosya seçerseniz güncellenir.</p>
	</div>
	<?php endif; ?>
	<form action="islemler/ajax.php" method="POST" accept-charset="utf-8" enctype="multipart/form-data" class="space-y-5">
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Site logosu</label>
			<input type="file" name="site_logo" class="block w-full text-sm text-on-surface-variant file:mr-3 file:rounded-full file:border-0 file:bg-primary file:px-4 file:py-2 file:text-xs file:font-bold file:text-white"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Site başlık</label>
			<input type="text" name="site_baslik" value="<?php echo htmlspecialchars($ayarcek['site_baslik'] ?? ''); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Site açıklama</label>
			<input type="text" name="site_aciklama" value="<?php echo htmlspecialchars($ayarcek['site_aciklama'] ?? ''); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Site link</label>
			<input type="text" name="site_link" value="<?php echo htmlspecialchars($ayarcek['site_link'] ?? ''); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Site sahibi e-posta</label>
			<input type="text" name="site_sahip_mail" value="<?php echo htmlspecialchars($ayarcek['site_sahip_mail'] ?? ''); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
		</div>
		<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<div>
				<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Mail host</label>
				<input type="text" name="site_mail_host" value="<?php echo htmlspecialchars($ayarcek['site_mail_host'] ?? ''); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
			</div>
			<div>
				<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Mail adresi</label>
				<input type="text" name="site_mail_mail" value="<?php echo htmlspecialchars($ayarcek['site_mail_mail'] ?? ''); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
			</div>
			<div>
				<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Mail port</label>
				<input type="text" name="site_mail_port" value="<?php echo htmlspecialchars($ayarcek['site_mail_port'] ?? ''); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
			</div>
			<div>
				<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Mail şifresi</label>
				<input type="text" name="site_mail_sifre" value="<?php echo htmlspecialchars($ayarcek['site_mail_sifre'] ?? ''); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
			</div>
		</div>
		<button type="submit" name="ayarkaydet" class="inline-flex rounded-full bg-primary px-6 py-2.5 text-sm font-bold text-white hover:brightness-110">Kaydet</button>
	</form>
</div>
<?php include __DIR__ . '/partials/admin_shell_bottom.php'; ?>
