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
	$modal_title = 'Onay bekleyenler';
	include __DIR__ . '/partials/embed_tailwind_top.php';
	if (isset($_GET['durum'])) {
		if ($_GET['durum'] === 'ok') {
			echo '<div class="mb-3 rounded-lg bg-green-100 px-3 py-2 text-sm text-green-900">Kullanıcı onaylandı.</div>';
		} elseif ($_GET['durum'] === 'ok_red') {
			echo '<div class="mb-3 rounded-lg bg-amber-100 px-3 py-2 text-sm text-amber-900">Kayıt reddedildi.</div>';
		} elseif ($_GET['durum'] === 'no') {
			echo '<div class="mb-3 rounded-lg bg-red-100 px-3 py-2 text-sm text-red-900">İşlem başarısız.</div>';
		}
	}
	?>
	<h1 class="text-lg font-bold text-primary mb-4">Onay bekleyen kayıtlar</h1>
	<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
		<table class="min-w-full text-sm">
			<thead class="bg-slate-100 text-left text-xs font-bold uppercase text-slate-600">
				<tr>
					<th class="px-3 py-2">İsim</th>
					<th class="px-3 py-2">E-posta</th>
					<th class="px-3 py-2">Telefon</th>
					<th class="px-3 py-2">İşlem</th>
				</tr>
			</thead>
			<tbody class="divide-y divide-slate-100">
				<?php
				$sorgu = $db->prepare('SELECT * FROM kullanicilar WHERE COALESCE(kul_onay,1) = 0 ORDER BY kul_id DESC');
				$sorgu->execute();
				$bos = true;
				while ($row = $sorgu->fetch(PDO::FETCH_ASSOC)) {
					$bos = false;
					?>
				<tr>
					<td class="px-3 py-2 font-medium"><?php echo htmlspecialchars($row['kul_isim']); ?></td>
					<td class="px-3 py-2"><?php echo htmlspecialchars($row['kul_mail']); ?></td>
					<td class="px-3 py-2"><?php echo htmlspecialchars($row['kul_telefon'] ?? ''); ?></td>
					<td class="px-3 py-2 flex flex-wrap gap-1">
						<form action="islemler/ajax.php" method="post" target="_top" class="inline">
							<input type="hidden" name="kul_id" value="<?php echo (int) $row['kul_id']; ?>"/>
							<button type="submit" name="kulonayla" class="rounded-lg bg-green-700 px-2 py-1 text-xs font-bold text-white">Onayla</button>
						</form>
						<form action="islemler/ajax.php" method="post" target="_top" class="inline" onsubmit="return confirm('Reddetmek istediğinize emin misiniz?');">
							<input type="hidden" name="kul_id" value="<?php echo (int) $row['kul_id']; ?>"/>
							<button type="submit" name="kulreddet" class="rounded-lg border border-red-300 px-2 py-1 text-xs font-bold text-red-700">Reddet</button>
						</form>
					</td>
				</tr>
				<?php } ?>
				<?php if ($bos): ?>
				<tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">Bekleyen kayıt yok.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
	include __DIR__ . '/partials/embed_tailwind_bottom.php';
	exit;
}

$admin_nav = 'onay';
$admin_page_title = (!empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp') . ' | Onay bekleyenler';
$admin_heading = 'Onay bekleyen kayıtlar';
include __DIR__ . '/partials/admin_shell_top.php';

if (isset($_GET['durum'])) {
	if ($_GET['durum'] === 'ok') {
		echo '<div class="rounded-2xl bg-green-50 border border-green-200 px-4 py-3 text-sm font-medium text-green-900 mb-6">Kullanıcı onaylandı.</div>';
	} elseif ($_GET['durum'] === 'ok_red') {
		echo '<div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm font-medium text-amber-900 mb-6">Kayıt reddedildi ve silindi.</div>';
	} elseif ($_GET['durum'] === 'no') {
		echo '<div class="rounded-2xl bg-error-container/50 border border-error/20 px-4 py-3 text-sm font-medium text-error mb-6">İşlem gerçekleştirilemedi.</div>';
	}
}

$sorgu = $db->prepare('SELECT * FROM kullanicilar WHERE COALESCE(kul_onay,1) = 0 ORDER BY kul_id DESC');
$sorgu->execute();
$bekleyen_list = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bg-white rounded-3xl border border-outline-variant/10 shadow-sm p-6 md:p-8">
	<?php if (empty($bekleyen_list)): ?>
	<p class="text-on-surface-variant text-center py-12">Bekleyen kayıt yok.</p>
	<?php else: ?>
	<div class="overflow-x-auto rounded-2xl border border-outline-variant/15">
		<table class="min-w-full text-sm text-left">
			<thead class="bg-surface-container-low text-xs font-bold uppercase text-on-surface-variant">
				<tr>
					<th class="px-4 py-3">İsim</th>
					<th class="px-4 py-3">E-posta</th>
					<th class="px-4 py-3">Telefon</th>
					<th class="px-4 py-3 text-right">İşlem</th>
				</tr>
			</thead>
			<tbody class="divide-y divide-outline-variant/10">
				<?php foreach ($bekleyen_list as $row): ?>
				<tr class="hover:bg-surface-container-low/40">
					<td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($row['kul_isim']); ?></td>
					<td class="px-4 py-3"><?php echo htmlspecialchars($row['kul_mail']); ?></td>
					<td class="px-4 py-3"><?php echo htmlspecialchars($row['kul_telefon'] ?? ''); ?></td>
					<td class="px-4 py-3 text-right">
						<div class="flex flex-wrap justify-end gap-2">
							<form action="islemler/ajax.php" method="post" class="inline">
								<input type="hidden" name="kul_id" value="<?php echo (int) $row['kul_id']; ?>"/>
								<button type="submit" name="kulonayla" class="rounded-full bg-secondary px-4 py-1.5 text-xs font-bold text-on-secondary hover:brightness-110">Onayla</button>
							</form>
							<form action="islemler/ajax.php" method="post" class="inline" onsubmit="return confirm('Bu kaydı reddetmek istediğinize emin misiniz?');">
								<input type="hidden" name="kul_id" value="<?php echo (int) $row['kul_id']; ?>"/>
								<button type="submit" name="kulreddet" class="rounded-full border-2 border-error/40 bg-white px-4 py-1.5 text-xs font-bold text-error hover:bg-error-container/20">Reddet</button>
							</form>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</div>
<?php include __DIR__ . '/partials/admin_shell_bottom.php'; ?>
