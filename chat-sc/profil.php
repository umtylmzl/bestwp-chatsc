<?php
require_once 'islemler/baglan.php';
require_once 'fonksiyonlar.php';
oturumkontrol();

$sorgu = $db->prepare('SELECT * FROM kullanicilar WHERE kul_id = :kul_id LIMIT 1');
$sorgu->execute(array('kul_id' => (int) $_SESSION['kul_id']));
$kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);
if (!$kullanici) {
	header('location:login.php');
	exit;
}

if (isset($_GET['totp_iptal']) && $_GET['totp_iptal'] === '1') {
	unset($_SESSION['totp_setup_secret']);
	header('Location: profil.php');
	exit;
}

$totp_setup_view = isset($_GET['totp_setup']) && $_GET['totp_setup'] === '1';
$totp_enabled = isset($kullanici['kul_totp_enabled']) && (int) $kullanici['kul_totp_enabled'] === 1;
if ($totp_setup_view && $totp_enabled) {
	header('Location: profil.php');
	exit;
}

$totp_pending_secret = '';
$totp_otpauth = '';
$totp_qr_url = '';
if ($totp_setup_view) {
	require_once __DIR__ . '/islemler/totp.php';
	if (empty($_SESSION['totp_setup_secret'])) {
		$_SESSION['totp_setup_secret'] = BestWpTotp::randomSecret();
	}
	$totp_pending_secret = (string) $_SESSION['totp_setup_secret'];
	$totp_otpauth = BestWpTotp::otpauthUri($totp_pending_secret, (string) $kullanici['kul_mail'], 'BestWp');
	$totp_qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&ecc=M&data=' . rawurlencode($totp_otpauth);
}

$profil_toast = isset($_GET['toast']) ? (string) $_GET['toast'] : '';
$profil_durum = isset($_GET['durum']) ? (string) $_GET['durum'] : '';
$profil_banner = null;
if ($profil_toast === 'totp_ok') {
	$profil_banner = array('tip' => 'ok', 'metin' => 'İki adımlı doğrulama etkinleştirildi.');
} elseif ($profil_toast === 'totp_db_hata') {
	$profil_banner = array('tip' => 'error', 'metin' => 'Kayıt sırasında hata oluştu. Tekrar deneyin.');
} elseif ($profil_toast === 'totp_kapat_ok') {
	$profil_banner = array('tip' => 'ok', 'metin' => 'İki adımlı doğrulama kapatıldı.');
} elseif ($profil_toast === 'profil_ok') {
	$profil_banner = array('tip' => 'ok', 'metin' => 'Profil güncellendi.');
} elseif ($profil_toast === 'profil_hata') {
	$profil_banner = array('tip' => 'error', 'metin' => 'Profil kaydedilemedi.');
}
if ($profil_durum === 'totp_hata') {
	$profil_banner = array('tip' => 'error', 'metin' => 'Doğrulama kodu hatalı.');
} elseif ($profil_durum === 'totp_sifre_hata') {
	$profil_banner = array('tip' => 'error', 'metin' => 'Şifre yanlış.');
} elseif ($profil_durum === 'totp_kapat_yok') {
	$profil_banner = array('tip' => 'error', 'metin' => 'İki adımlı doğrulama zaten kapalı.');
}

$modal = isset($_GET['modal']) && $_GET['modal'] === '1';
if ($modal && $totp_setup_view) {
	header('Location: profil.php?totp_setup=1');
	exit;
}

if ($modal) {
	$modal_title = 'Profil';
	include __DIR__ . '/partials/embed_tailwind_top.php';
	?>
	<h1 class="text-lg font-bold text-primary mb-2">Profil</h1>
	<p class="text-xs text-slate-500 mb-4">Ad ve e-posta kayıt sonrası değiştirilemez.</p>
	<div class="space-y-3 mb-4">
		<div>
			<p class="text-xs font-bold text-slate-600">Ad soyad</p>
			<p class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"><?php echo htmlspecialchars($kullanici['kul_isim']); ?></p>
		</div>
		<div>
			<p class="text-xs font-bold text-slate-600">E-posta</p>
			<p class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"><?php echo htmlspecialchars($kullanici['kul_mail']); ?></p>
		</div>
	</div>
	<form action="islemler/ajax.php" method="POST" accept-charset="utf-8" target="_top" class="space-y-4">
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">Telefon</label>
			<input type="text" name="kul_telefon" value="<?php echo htmlspecialchars($kullanici['kul_telefon'] ?? ''); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Telefon"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-slate-600 mb-1">Şifre <span class="font-normal">(boş = değişmez)</span></label>
			<input type="password" name="kul_sifre" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Yeni şifre"/>
		</div>
		<button type="submit" name="profilkaydet" value="1" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:brightness-110">Kaydet</button>
	</form>
	<p class="text-xs text-slate-500 mt-4">İki adımlı doğrulama (Google Authenticator) için tam ekran <a href="profil.php" class="text-primary font-semibold hover:underline" target="_top">profil sayfasını</a> açın.</p>
	<?php
	include __DIR__ . '/partials/embed_tailwind_bottom.php';
	exit;
}

$is_admin = yetkikontrol();

if ($is_admin) {
	$admin_nav = 'profil';
	$admin_page_title = (!empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp') . ' | Profil';
	$admin_heading = 'Profilim';
	include __DIR__ . '/partials/admin_shell_top.php';
} else {
	$user_page_title = 'Profil';
	include __DIR__ . '/partials/user_page_shell_top.php';
}
?>
<?php if ($profil_banner):
	if ($is_admin) {
		$bc = $profil_banner['tip'] === 'ok'
			? 'bg-secondary-container/30 text-on-secondary-container border-outline-variant/40'
			: 'bg-error-container/80 text-on-error-container border-error/20';
	} else {
		$bc = $profil_banner['tip'] === 'ok'
			? 'bg-emerald-50 text-emerald-900 border-emerald-200'
			: 'bg-red-50 text-red-900 border-red-200';
	}
	?>
<div class="max-w-2xl mx-auto mb-4 rounded-2xl border px-4 py-3 text-sm font-medium <?php echo $bc; ?>">
	<?php echo htmlspecialchars($profil_banner['metin']); ?>
</div>
<?php endif; ?>

<?php if ($totp_setup_view):
	$card = $is_admin ? 'max-w-2xl bg-white rounded-3xl border border-outline-variant/10 shadow-sm p-6 md:p-8' : 'max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8';
	$t = $is_admin ? 'text-on-surface-variant' : 'text-slate-600';
	?>
<div class="<?php echo $card; ?>">
	<a href="profil.php" class="inline-flex items-center gap-1 text-sm font-semibold <?php echo $is_admin ? 'text-primary' : 'text-[#00453d]'; ?> hover:underline mb-4">&larr; Profil sayfasına dön</a>
	<h2 class="text-lg font-bold <?php echo $is_admin ? 'text-primary' : 'text-slate-900'; ?> mb-2">İki adımlı doğrulama</h2>
	<p class="text-sm <?php echo $t; ?> mb-6">Google Authenticator veya uyumlu bir uygulama ile aşağıdaki QR kodunu tarayın. Ardından uygulamadaki 6 haneli kodu girerek kurulumu tamamlayın.</p>
	<div class="flex flex-col sm:flex-row gap-6 items-start mb-6">
		<div class="rounded-2xl border <?php echo $is_admin ? 'border-outline-variant/30 bg-surface-container-low p-3' : 'border-slate-200 bg-slate-50 p-3'; ?> shrink-0 mx-auto sm:mx-0">
			<img src="<?php echo htmlspecialchars($totp_qr_url); ?>" width="220" height="220" alt="QR kod" class="w-[200px] h-[200px] sm:w-[220px] sm:h-[220px]"/>
		</div>
		<div class="min-w-0 flex-1 space-y-3">
			<p class="text-xs font-bold <?php echo $t; ?>">Manuel anahtar</p>
			<code class="block text-sm break-all rounded-xl px-3 py-2 <?php echo $is_admin ? 'bg-surface-container-low border border-outline-variant/30' : 'bg-slate-100 border border-slate-200'; ?>"><?php echo htmlspecialchars($totp_pending_secret); ?></code>
		</div>
	</div>
	<form action="islemler/ajax.php" method="POST" class="space-y-4 max-w-sm">
		<div>
			<label class="block text-xs font-bold <?php echo $t; ?> mb-1.5">Doğrulama kodu</label>
			<input type="text" name="totp_code" inputmode="numeric" pattern="[0-9]*" maxlength="6" class="w-full rounded-xl border <?php echo $is_admin ? 'border-outline-variant/50 bg-surface-container-low' : 'border-slate-300'; ?> px-4 py-2.5 text-center text-xl tracking-widest font-mono" placeholder="000000" required autocomplete="one-time-code"/>
		</div>
		<div class="flex flex-wrap gap-3">
			<button type="submit" name="totp_kurulum_onay" value="1" class="inline-flex rounded-full bg-primary px-6 py-2.5 text-sm font-bold text-white hover:brightness-110">Etkinleştir</button>
			<a href="profil.php?totp_iptal=1" class="inline-flex items-center rounded-full border <?php echo $is_admin ? 'border-outline-variant/40 text-on-surface' : 'border-slate-300 text-slate-700'; ?> px-6 py-2.5 text-sm font-semibold hover:bg-slate-50">İptal</a>
		</div>
	</form>
</div>
<?php else: ?>
<div class="<?php echo $is_admin ? 'max-w-2xl bg-white rounded-3xl border border-outline-variant/10 shadow-sm p-6 md:p-8' : 'bg-white rounded-2xl border border-slate-200 shadow-sm p-6'; ?>">
	<p class="text-sm <?php echo $is_admin ? 'text-on-surface-variant' : 'text-slate-600'; ?> mb-6">Ad ve e-posta kayıt sonrası değiştirilemez.</p>
	<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
		<div>
			<p class="text-xs font-bold <?php echo $is_admin ? 'text-on-surface-variant' : 'text-slate-600'; ?> mb-1.5">Ad soyad</p>
			<p class="rounded-xl border <?php echo $is_admin ? 'border-outline-variant/30 bg-surface-container-low' : 'border-slate-200 bg-slate-50'; ?> px-4 py-2.5 text-sm"><?php echo htmlspecialchars($kullanici['kul_isim']); ?></p>
		</div>
		<div>
			<p class="text-xs font-bold <?php echo $is_admin ? 'text-on-surface-variant' : 'text-slate-600'; ?> mb-1.5">E-posta</p>
			<p class="rounded-xl border <?php echo $is_admin ? 'border-outline-variant/30 bg-surface-container-low' : 'border-slate-200 bg-slate-50'; ?> px-4 py-2.5 text-sm"><?php echo htmlspecialchars($kullanici['kul_mail']); ?></p>
		</div>
	</div>
	<form action="islemler/ajax.php" method="POST" accept-charset="utf-8" class="space-y-5">
		<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<div>
				<label class="block text-xs font-bold <?php echo $is_admin ? 'text-on-surface-variant' : 'text-slate-600'; ?> mb-1.5">Telefon</label>
				<input type="text" name="kul_telefon" value="<?php echo htmlspecialchars($kullanici['kul_telefon'] ?? ''); ?>" class="w-full rounded-xl border <?php echo $is_admin ? 'border-outline-variant/50 bg-surface-container-low focus:ring-primary/20 focus:border-primary' : 'border-slate-300 bg-white focus:ring-[#00453d]/20 focus:border-[#00453d]'; ?> px-4 py-2.5 text-sm focus:ring-2" placeholder="Telefon"/>
			</div>
			<div>
				<label class="block text-xs font-bold <?php echo $is_admin ? 'text-on-surface-variant' : 'text-slate-600'; ?> mb-1.5">Şifre <span class="font-normal opacity-80">(boş = değişmez)</span></label>
				<input type="password" name="kul_sifre" autocomplete="new-password" class="w-full rounded-xl border <?php echo $is_admin ? 'border-outline-variant/50 bg-surface-container-low focus:ring-primary/20 focus:border-primary' : 'border-slate-300 bg-white focus:ring-[#00453d]/20 focus:border-[#00453d]'; ?> px-4 py-2.5 text-sm focus:ring-2" placeholder="Yeni şifre"/>
			</div>
		</div>
		<button type="submit" name="profilkaydet" class="inline-flex rounded-full bg-primary px-6 py-2.5 text-sm font-bold text-white hover:brightness-110">Kaydet</button>
	</form>
</div>

<div class="<?php echo $is_admin ? 'max-w-2xl mt-6 bg-white rounded-3xl border border-outline-variant/10 shadow-sm p-6 md:p-8' : 'max-w-2xl mx-auto mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8'; ?>">
	<h2 class="text-base font-bold <?php echo $is_admin ? 'text-primary' : 'text-slate-900'; ?> mb-2 flex items-center gap-2">
		<span class="material-symbols-outlined text-xl <?php echo $is_admin ? 'text-primary' : 'text-[#00453d]'; ?>">shield_lock</span>
		İki adımlı doğrulama
	</h2>
	<p class="text-sm <?php echo $is_admin ? 'text-on-surface-variant' : 'text-slate-600'; ?> mb-4">Google Authenticator ile hesabınıza ek güvenlik katmanı ekleyebilirsiniz.</p>
	<?php if ($totp_enabled): ?>
	<p class="text-sm font-semibold <?php echo $is_admin ? 'text-secondary' : 'text-emerald-700'; ?> mb-4">Durum: <span class="uppercase tracking-wide">açık</span></p>
	<form action="islemler/ajax.php" method="POST" class="space-y-4 max-w-md">
		<div>
			<label class="block text-xs font-bold <?php echo $is_admin ? 'text-on-surface-variant' : 'text-slate-600'; ?> mb-1.5">Mevcut şifreniz</label>
			<input type="password" name="kul_sifre_totp" autocomplete="current-password" required class="w-full rounded-xl border <?php echo $is_admin ? 'border-outline-variant/50 bg-surface-container-low' : 'border-slate-300'; ?> px-4 py-2.5 text-sm"/>
		</div>
		<div>
			<label class="block text-xs font-bold <?php echo $is_admin ? 'text-on-surface-variant' : 'text-slate-600'; ?> mb-1.5">Authenticator kodu</label>
			<input type="text" name="totp_code_kapat" inputmode="numeric" pattern="[0-9]*" maxlength="6" required class="w-full rounded-xl border <?php echo $is_admin ? 'border-outline-variant/50 bg-surface-container-low' : 'border-slate-300'; ?> px-4 py-2.5 text-sm text-center tracking-widest font-mono" placeholder="000000" autocomplete="one-time-code"/>
		</div>
		<button type="submit" name="totp_kapat" value="1" class="inline-flex rounded-full border-2 border-error/40 text-error px-6 py-2.5 text-sm font-bold hover:bg-error-container/30">İki adımlı doğrulamayı kapat</button>
	</form>
	<?php else: ?>
	<p class="text-sm <?php echo $is_admin ? 'text-on-surface-variant' : 'text-slate-600'; ?> mb-4">Durum: kapalı</p>
	<a href="profil.php?totp_setup=1" class="inline-flex rounded-full bg-primary px-6 py-2.5 text-sm font-bold text-white hover:brightness-110">Kuruluma başla</a>
	<?php endif; ?>
</div>
<?php endif; ?>
<?php
if ($is_admin) {
	include __DIR__ . '/partials/admin_shell_bottom.php';
} else {
	include __DIR__ . '/partials/user_page_shell_bottom.php';
}
