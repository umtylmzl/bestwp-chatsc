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
	$modal_title = 'Kullanıcılar';
	include __DIR__ . '/partials/embed_tailwind_top.php';
	?>
	<h1 class="text-lg font-bold text-primary mb-4">Kullanıcılar</h1>
	<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
		<table class="min-w-full text-sm" id="kullanicitablosu">
			<thead class="bg-slate-100 text-left text-xs font-bold uppercase text-slate-600">
				<tr>
					<th class="px-3 py-2">No</th>
					<th class="px-3 py-2">İsim</th>
					<th class="px-3 py-2">Mail</th>
					<th class="px-3 py-2">Telefon</th>
					<th class="px-3 py-2">Onay</th>
					<th class="px-3 py-2">İşlem</th>
				</tr>
			</thead>
			<tbody class="divide-y divide-slate-100">
				<?php
				$sayi = 0;
				$sorgu = $db->prepare('SELECT * FROM kullanicilar ORDER BY kul_id ASC');
				$sorgu->execute();
				while ($k = $sorgu->fetch(PDO::FETCH_ASSOC)) {
					$sayi++;
					$onay = (int) ($k['kul_onay'] ?? 1) === 1;
					?>
				<tr>
					<td class="px-3 py-2"><?php echo $sayi; ?></td>
					<td class="px-3 py-2 font-medium"><?php echo htmlspecialchars($k['kul_isim']); ?></td>
					<td class="px-3 py-2"><?php echo htmlspecialchars($k['kul_mail']); ?></td>
					<td class="px-3 py-2"><?php echo htmlspecialchars($k['kul_telefon'] ?? ''); ?></td>
					<td class="px-3 py-2"><?php echo $onay ? '<span class="text-green-700 font-semibold">Onaylı</span>' : '<span class="text-amber-700 font-semibold">Beklemede</span>'; ?></td>
					<td class="px-3 py-2 flex flex-wrap gap-1">
						<form action="kullaniciduzenle.php" method="POST" target="_blank" class="inline">
							<input type="hidden" name="kul_id" value="<?php echo (int) $k['kul_id']; ?>"/>
							<button type="submit" name="duzenleme" class="rounded-lg bg-primary px-2 py-1 text-xs font-bold text-white hover:brightness-110">Düzenle</button>
						</form>
						<form action="islemler/ajax.php" method="POST" class="inline" target="_top" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
							<input type="hidden" name="kul_id" value="<?php echo (int) $k['kul_id']; ?>"/>
							<button type="submit" name="kulsilme" class="rounded-lg bg-red-600 px-2 py-1 text-xs font-bold text-white hover:brightness-110">Sil</button>
						</form>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<p class="text-xs text-slate-500 mt-3">Düzenleme yeni sekmede açılır.</p>
	<?php
	include __DIR__ . '/partials/embed_tailwind_bottom.php';
	exit;
}

$admin_nav = 'kullanicilar';
$admin_page_title = (!empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp') . ' | Kullanıcılar';
$admin_heading = 'Tüm kullanıcılar';
include __DIR__ . '/partials/admin_shell_top.php';

$kul_list = $db->query('SELECT * FROM kullanicilar ORDER BY kul_id ASC')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bg-white rounded-3xl border border-outline-variant/10 shadow-sm p-6 md:p-8">
	<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
		<p class="text-on-surface-variant text-sm"><?php echo count($kul_list); ?> kayıt</p>
		<input type="search" id="kul-ara" placeholder="Tabloda ara…" class="w-full sm:max-w-xs rounded-full border border-outline-variant/50 bg-surface-container-low px-4 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
	</div>
	<div class="overflow-x-auto rounded-2xl border border-outline-variant/15">
		<table class="min-w-full text-sm text-left" id="kul-tablo">
			<thead class="bg-surface-container-low text-xs font-bold uppercase text-on-surface-variant">
				<tr>
					<th class="px-4 py-3">#</th>
					<th class="px-4 py-3">İsim</th>
					<th class="px-4 py-3">E-posta</th>
					<th class="px-4 py-3">Telefon</th>
					<th class="px-4 py-3">Onay</th>
					<th class="px-4 py-3 text-right">İşlemler</th>
				</tr>
			</thead>
			<tbody class="divide-y divide-outline-variant/10">
				<?php foreach ($kul_list as $i => $k): ?>
				<tr class="kul-row hover:bg-surface-container-low/50">
					<td class="px-4 py-3 text-on-surface-variant"><?php echo $i + 1; ?></td>
					<td class="px-4 py-3 font-semibold text-on-surface"><?php echo htmlspecialchars($k['kul_isim']); ?></td>
					<td class="px-4 py-3"><?php echo htmlspecialchars($k['kul_mail']); ?></td>
					<td class="px-4 py-3"><?php echo htmlspecialchars($k['kul_telefon'] ?? ''); ?></td>
					<td class="px-4 py-3"><?php echo (int) ($k['kul_onay'] ?? 1) === 1
						? '<span class="inline-flex rounded-full bg-secondary-container/40 px-2 py-0.5 text-xs font-bold text-on-secondary-container">Onaylı</span>'
						: '<span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-900">Beklemede</span>'; ?></td>
					<td class="px-4 py-3 text-right">
						<div class="flex flex-wrap justify-end gap-2">
							<form action="kullaniciduzenle.php" method="POST" class="inline">
								<input type="hidden" name="kul_id" value="<?php echo (int) $k['kul_id']; ?>"/>
								<button type="submit" name="duzenleme" class="rounded-full bg-primary px-3 py-1.5 text-xs font-bold text-white hover:brightness-110">Düzenle</button>
							</form>
							<form action="kullanici.php" method="POST" class="inline">
								<input type="hidden" name="kul_id" value="<?php echo (int) $k['kul_id']; ?>"/>
								<button type="submit" name="duzenleme" class="rounded-full border border-primary/30 bg-white px-3 py-1.5 text-xs font-bold text-primary hover:bg-surface-container-low">Görüntüle</button>
							</form>
							<form action="islemler/ajax.php" method="POST" class="inline" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');">
								<input type="hidden" name="kul_id" value="<?php echo (int) $k['kul_id']; ?>"/>
								<button type="submit" name="kulsilme" class="rounded-full bg-error px-3 py-1.5 text-xs font-bold text-white hover:brightness-110">Sil</button>
							</form>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<?php
$admin_footer_scripts = '<script>
(function () {
	var inp = document.getElementById("kul-ara");
	var rows = document.querySelectorAll("#kul-tablo tbody tr.kul-row");
	if (!inp) return;
	inp.addEventListener("input", function () {
		var q = (inp.value || "").toLowerCase().trim();
		rows.forEach(function (tr) {
			tr.style.display = (!q || (tr.textContent || "").toLowerCase().indexOf(q) !== -1) ? "" : "none";
		});
	});
})();
</script>';
include __DIR__ . '/partials/admin_shell_bottom.php';
