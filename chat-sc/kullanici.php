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
$admin_page_title = (!empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp') . ' | Kullanıcı';
$admin_heading = 'Kullanıcı detayı';
include __DIR__ . '/partials/admin_shell_top.php';
?>
<div class="max-w-2xl bg-white rounded-3xl border border-outline-variant/10 shadow-sm p-6 md:p-8 space-y-4">
	<p class="text-sm text-on-surface-variant">Salt okunur özet. Düzenlemek için listeden <strong>Düzenle</strong> kullanın.</p>
	<div>
		<p class="text-xs font-bold text-on-surface-variant mb-1">İsim</p>
		<p class="rounded-xl border border-outline-variant/30 bg-surface-container-low px-4 py-2.5 text-sm"><?php echo htmlspecialchars($kullanicibilgisi['kul_isim']); ?></p>
	</div>
	<div>
		<p class="text-xs font-bold text-on-surface-variant mb-1">E-posta</p>
		<p class="rounded-xl border border-outline-variant/30 bg-surface-container-low px-4 py-2.5 text-sm"><?php echo htmlspecialchars($kullanicibilgisi['kul_mail']); ?></p>
	</div>
	<div>
		<p class="text-xs font-bold text-on-surface-variant mb-1">Telefon</p>
		<p class="rounded-xl border border-outline-variant/30 bg-surface-container-low px-4 py-2.5 text-sm"><?php echo htmlspecialchars($kullanicibilgisi['kul_telefon'] ?? ''); ?></p>
	</div>
	<div class="pt-4">
		<form action="kullaniciduzenle.php" method="POST" class="inline">
			<input type="hidden" name="kul_id" value="<?php echo (int) $kul_id; ?>"/>
			<button type="submit" name="duzenleme" class="rounded-full bg-primary px-6 py-2.5 text-sm font-bold text-white hover:brightness-110">Düzenlemeye git</button>
		</form>
		<a href="kullanicilar.php" class="ml-3 text-sm font-semibold text-primary hover:underline">Listeye dön</a>
	</div>
</div>
<?php include __DIR__ . '/partials/admin_shell_bottom.php'; ?>
