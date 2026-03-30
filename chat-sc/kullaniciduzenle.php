<?php
require_once 'islemler/baglan.php';
require_once 'fonksiyonlar.php';
oturumkontrol();

if (!yetkikontrol()) {
	header('location:messenger.php');
	exit;
}

if (!isset($_POST['kul_id'])) {
	header('location:kullanicilar.php');
	exit;
}

$kul_id = (int) $_POST['kul_id'];
$sorgu = $db->prepare('SELECT * FROM kullanicilar WHERE kul_id = :kul_id LIMIT 1');
$sorgu->execute(array('kul_id' => $kul_id));
$kullanicibilgisi = $sorgu->fetch(PDO::FETCH_ASSOC);
if (!$kullanicibilgisi) {
	header('location:kullanicilar.php');
	exit;
}

$admin_nav = 'kullanicilar';
$admin_page_title = (!empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp') . ' | Kullanıcı düzenle';
$admin_heading = 'Kullanıcı düzenle';
include __DIR__ . '/partials/admin_shell_top.php';
?>
<div class="max-w-2xl bg-white rounded-3xl border border-outline-variant/10 shadow-sm p-6 md:p-8">
	<form action="islemler/ajax.php" method="POST" accept-charset="utf-8" class="space-y-5">
		<input type="hidden" name="kul_id" value="<?php echo (int) $kul_id; ?>"/>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">İsim soyisim</label>
			<input type="text" name="kul_isim" value="<?php echo htmlspecialchars($kullanicibilgisi['kul_isim']); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">E-posta</label>
			<input type="email" name="kul_mail" value="<?php echo htmlspecialchars($kullanicibilgisi['kul_mail']); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Telefon</label>
			<input type="text" name="kul_telefon" value="<?php echo htmlspecialchars($kullanicibilgisi['kul_telefon'] ?? ''); ?>" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
		</div>
		<div>
			<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Şifre <span class="font-normal text-on-surface-variant/80">(boş = değişmez)</span></label>
			<input type="password" name="kul_sifre" autocomplete="new-password" placeholder="Yeni şifre" class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
		</div>
		<div class="flex flex-wrap gap-3 pt-2">
			<button type="submit" name="kulduzenle" class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-2.5 text-sm font-bold text-white hover:brightness-110">Kaydet</button>
			<a href="kullanicilar.php" class="inline-flex items-center justify-center rounded-full border border-outline-variant px-6 py-2.5 text-sm font-semibold text-on-surface hover:bg-surface-container-low">İptal</a>
		</div>
	</form>
</div>
<?php include __DIR__ . '/partials/admin_shell_bottom.php'; ?>
