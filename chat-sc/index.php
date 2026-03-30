<?php
require_once 'islemler/baglan.php';
require_once 'fonksiyonlar.php';
oturumkontrol();

bestwp_touch_son_giris($db, (int) $_SESSION['kul_id']);

if (!yetkikontrol()) {
	header('Location: messenger.php');
	exit;
}

$site_baslik = !empty($ayarcek['site_baslik']) ? $ayarcek['site_baslik'] : 'BestWp';
$me_id = (int) $_SESSION['kul_id'];

$toplam_kul = (int) $db->query('SELECT COUNT(*) FROM kullanicilar')->fetchColumn();
$toplam_admin = (int) $db->query('SELECT COUNT(*) FROM kullanicilar WHERE kul_yetki = 1')->fetchColumn();
$bekleyen = 0;
try {
	$bekleyen = (int) $db->query('SELECT COUNT(*) FROM kullanicilar WHERE COALESCE(kul_onay,1) = 0')->fetchColumn();
} catch (Exception $e) {
}
$onayli_kul = max(0, $toplam_kul - $bekleyen);
$toplam_mesaj = (int) $db->query('SELECT COUNT(*) FROM mesajlar')->fetchColumn();
$forum_mesaj = 0;
$ozel_mesaj = 0;
try {
	$forum_mesaj = (int) $db->query('SELECT COUNT(*) FROM mesajlar WHERE mesaj_alici IS NULL')->fetchColumn();
	$ozel_mesaj = (int) $db->query('SELECT COUNT(*) FROM mesajlar WHERE mesaj_alici IS NOT NULL')->fetchColumn();
} catch (Exception $e) {
}

$yuk_oran = $toplam_mesaj > 0 ? min(96, (int) round(15 + ($forum_mesaj / max(1, $toplam_mesaj)) * 70)) : 12;

$forum_son = array();
try {
	$fs = $db->prepare(
		'SELECT m.mesaj_detay, m.mesaj_gonderen, k.kul_isim FROM mesajlar m
		LEFT JOIN kullanicilar k ON k.kul_id = m.mesaj_gonderen
		WHERE m.mesaj_alici IS NULL ORDER BY m.mesaj_id DESC LIMIT 4'
	);
	$fs->execute();
	$forum_son = $fs->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$dm_kartlar = array();
try {
	$dmq = $db->prepare(
		'SELECT m.mesaj_detay, m.mesaj_gonderen, m.mesaj_alici, g.kul_isim AS g_isim, a.kul_isim AS a_isim
		FROM mesajlar m
		LEFT JOIN kullanicilar g ON g.kul_id = m.mesaj_gonderen
		LEFT JOIN kullanicilar a ON a.kul_id = m.mesaj_alici
		WHERE m.mesaj_alici IS NOT NULL
		ORDER BY m.mesaj_id DESC LIMIT 80'
	);
	$dmq->execute();
	$seen = array();
	while ($row = $dmq->fetch(PDO::FETCH_ASSOC)) {
		$x = (int) $row['mesaj_gonderen'];
		$y = (int) $row['mesaj_alici'];
		$key = $x < $y ? $x . '_' . $y : $y . '_' . $x;
		if (isset($seen[$key])) {
			continue;
		}
		$seen[$key] = true;
		$dm_kartlar[] = $row;
		if (count($dm_kartlar) >= 3) {
			break;
		}
	}
} catch (Exception $e) {
}

$dm_slotlar = array_slice($dm_kartlar, 0, 3);
while (count($dm_slotlar) < 3) {
	$dm_slotlar[] = null;
}

$yeni_uyeler = array();
$yu = $db->prepare('SELECT kul_id, kul_isim, kul_mail FROM kullanicilar ORDER BY kul_id DESC LIMIT 4');
$yu->execute();
$yeni_uyeler = $yu->fetchAll(PDO::FETCH_ASSOC);

$toast = isset($_GET['toast']) ? $_GET['toast'] : '';
$toast_metin = '';
if ($toast === 'ayar_ok') {
	$toast_metin = 'Ayarlar kaydedildi.';
} elseif ($toast === 'ayar_hata') {
	$toast_metin = 'Ayarlar kaydedilemedi.';
} elseif ($toast === 'admin_ok') {
	$toast_metin = 'İşlem tamamlandı.';
} elseif ($toast === 'kullanici_ok') {
	$toast_metin = 'Kullanıcı eklendi.';
} elseif ($toast === 'admin_hata') {
	$toast_metin = 'İşlem başarısız.';
} elseif ($toast === 'onay_ok') {
	$toast_metin = 'Kullanıcı onaylandı.';
} elseif ($toast === 'red_ok') {
	$toast_metin = 'Kayıt reddedildi.';
} elseif ($toast === 'ok') {
	$toast_metin = 'Giriş başarılı.';
}

function admin_snip($t, $max = 80)
{
	$t = trim(preg_replace('/\s+/', ' ', (string) $t));
	if (function_exists('mb_strlen') && mb_strlen($t) > $max) {
		return mb_substr($t, 0, $max) . '…';
	}
	return strlen($t) > $max ? substr($t, 0, $max) . '…' : $t;
}
?>
<?php
$admin_nav = 'panel';
$admin_shell_mode = 'dashboard';
$admin_page_title = $site_baslik . ' | Yönetim';
include __DIR__ . '/partials/admin_shell_top.php';
?>
		<?php if ($toast_metin !== ''): ?>
		<div class="rounded-2xl bg-primary-container/15 border border-primary-container/25 px-4 py-3 text-sm font-medium text-primary"><?php echo htmlspecialchars($toast_metin); ?></div>
		<?php endif; ?>

		<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 dash-card">
			<div class="bg-surface-container-lowest p-6 rounded-3xl shadow-sm border border-outline-variant/10 flex flex-col justify-between">
				<div>
					<p class="text-on-surface-variant text-sm font-medium">Toplam kullanıcı</p>
					<h3 class="text-3xl font-extrabold text-primary mt-1"><?php echo number_format($toplam_kul, 0, ',', '.'); ?></h3>
				</div>
				<div class="mt-4 flex items-center gap-2 text-secondary text-xs font-bold">
					<span class="material-symbols-outlined text-sm">group</span>
					<span><?php echo number_format($onayli_kul, 0, ',', '.'); ?> onaylı</span>
				</div>
			</div>
			<div class="bg-surface-container-lowest p-6 rounded-3xl shadow-sm border border-outline-variant/10 flex flex-col justify-between">
				<div>
					<p class="text-on-surface-variant text-sm font-medium">Yönetici</p>
					<h3 class="text-3xl font-extrabold text-primary mt-1"><?php echo (int) $toplam_admin; ?></h3>
				</div>
				<div class="mt-4 flex items-center gap-2 text-secondary text-xs font-bold">
					<span class="material-symbols-outlined text-sm">admin_panel_settings</span>
					<span>Yetki seviyesi 1</span>
				</div>
			</div>
			<div class="bg-surface-container-lowest p-6 rounded-3xl shadow-sm border border-outline-variant/10 flex flex-col justify-between">
				<div>
					<p class="text-on-surface-variant text-sm font-medium">Onay bekleyen</p>
					<h3 class="text-3xl font-extrabold <?php echo $bekleyen > 0 ? 'text-error' : 'text-primary'; ?> mt-1"><?php echo (int) $bekleyen; ?></h3>
				</div>
				<a href="onay-bekleyenler.php" class="mt-4 inline-flex items-center gap-2 text-primary text-xs font-bold hover:underline">
					<span class="material-symbols-outlined text-sm">open_in_new</span>
					Onay listesine git
				</a>
			</div>
			<div class="bg-primary p-6 rounded-3xl shadow-lg flex flex-col justify-between relative overflow-hidden text-white">
				<div class="relative z-10">
					<p class="text-sm font-medium opacity-90">Toplam mesaj</p>
					<h3 class="text-3xl font-extrabold mt-1"><?php echo number_format($toplam_mesaj, 0, ',', '.'); ?></h3>
					<p class="text-xs opacity-80 mt-2">Forum: <?php echo number_format($forum_mesaj, 0, ',', '.'); ?> · Özel: <?php echo number_format($ozel_mesaj, 0, ',', '.'); ?></p>
				</div>
				<div class="absolute bottom-0 right-0 p-4 opacity-15">
					<span class="material-symbols-outlined text-6xl">chat</span>
				</div>
				<div class="mt-4 relative z-10 w-full bg-white/15 h-1.5 rounded-full overflow-hidden">
					<div class="bg-secondary-fixed h-full transition-all" style="width: <?php echo (int) $yuk_oran; ?>%"></div>
				</div>
			</div>
		</section>

		<section>
			<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-5">
				<div>
					<h2 class="text-2xl font-extrabold tracking-tight text-on-surface">Canlı özet</h2>
					<p class="text-on-surface-variant text-sm">Forum ve son özel konuşma önizlemeleri</p>
				</div>
				<div class="flex flex-wrap gap-2">
					<a href="messenger.php?open=genel" class="bg-primary text-white px-4 py-2 rounded-full text-xs font-bold inline-flex items-center gap-2 shadow-md hover:brightness-105 transition-all">
						<span class="material-symbols-outlined text-sm">forum</span>
						Foruma git
					</a>
					<a href="kullanicilar.php" class="bg-surface-container-high px-4 py-2 rounded-full text-xs font-bold hover:bg-surface-container-highest transition-colors">Tüm kullanıcılar</a>
				</div>
			</div>

			<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
				<div class="bg-surface-container-lowest rounded-[2rem] p-6 shadow-sm border border-outline-variant/10 hover:shadow-md transition-shadow dash-card">
					<div class="flex items-center justify-between mb-4">
						<div class="flex items-center gap-2">
							<span class="material-symbols-outlined text-primary-container text-3xl">forum</span>
							<span class="font-bold text-on-surface">Genel forum</span>
						</div>
						<span class="bg-secondary-container/40 text-on-secondary-container text-[10px] font-bold px-3 py-1 rounded-full uppercase">Herkese açık</span>
					</div>
					<div class="space-y-2 mb-5 min-h-[5rem]">
						<?php if (empty($forum_son)): ?>
						<p class="text-sm text-on-surface-variant">Henüz forum mesajı yok.</p>
						<?php else: ?>
							<?php foreach (array_slice($forum_son, 0, 2) as $i => $fm): ?>
						<div class="bg-surface-container-low p-3 rounded-2xl <?php echo $i === 0 ? 'rounded-tl-sm' : 'rounded-tr-sm ml-6'; ?> text-xs">
							<p class="text-on-surface-variant font-bold mb-0.5"><?php echo htmlspecialchars($fm['kul_isim'] ?? 'Kullanıcı'); ?></p>
							<p class="text-on-surface"><?php echo htmlspecialchars(admin_snip($fm['mesaj_detay'] ?? '', 120)); ?></p>
						</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<div class="flex items-center justify-between pt-4 border-t border-outline-variant/10">
						<p class="text-[10px] text-on-surface-variant"><?php echo (int) $forum_mesaj; ?> forum mesajı</p>
						<a href="messenger.php?open=genel" class="bg-primary px-4 py-2 rounded-full text-white text-xs font-bold hover:brightness-110 inline-flex items-center gap-1.5 transition-all">
							<span class="material-symbols-outlined text-sm">login</span>
							Aç
						</a>
					</div>
				</div>

				<?php
				foreach ($dm_slotlar as $idx => $dm):
					if ($dm === null):
				?>
				<div class="bg-surface-container-low p-6 rounded-[2rem] border border-dashed border-outline-variant flex flex-col items-center justify-center text-center min-h-[220px] dash-card">
					<span class="material-symbols-outlined text-4xl text-outline-variant/50 mb-2">chat_bubble</span>
					<p class="text-sm font-bold text-on-surface-variant">Özel konuşma yok</p>
					<p class="text-xs text-on-surface-variant/80 mt-1">Üyeler mesajlaştıkça burada özet görünür.</p>
				</div>
				<?php
					else:
					$g_isim = $dm['g_isim'] ?? 'Kullanıcı';
					$a_isim = $dm['a_isim'] ?? 'Kullanıcı';
					$gid = (int) $dm['mesaj_gonderen'];
					$aid = (int) $dm['mesaj_alici'];
					$av1 = bestwp_avatar_url($gid, $g_isim);
					$av2 = bestwp_avatar_url($aid, $a_isim);
					$badge = $idx === 1 ? 'error' : ($idx === 2 ? 'surface' : 'secondary');
					$badge_text = $idx === 1 ? 'Son aktivite' : ($idx === 2 ? 'Kuyruk' : 'Özel');
					$peer_link = null;
					if ($me_id === $gid) {
						$peer_link = $aid;
					} elseif ($me_id === $aid) {
						$peer_link = $gid;
					}
					$sohbet_href = $peer_link ? 'messenger.php?open=' . (int) $peer_link : 'messenger.php';
					?>
				<div class="bg-surface-container-lowest rounded-[2rem] p-6 shadow-sm border border-outline-variant/10 hover:shadow-md transition-shadow dash-card">
					<div class="flex items-center justify-between mb-4">
						<div class="flex -space-x-2">
							<img src="<?php echo htmlspecialchars($av1); ?>" class="w-10 h-10 rounded-full border-2 border-white object-cover" alt=""/>
							<img src="<?php echo htmlspecialchars($av2); ?>" class="w-10 h-10 rounded-full border-2 border-white object-cover" alt=""/>
						</div>
						<?php if ($badge === 'error'): ?>
						<span class="bg-error-container text-error text-[10px] font-bold px-3 py-1 rounded-full uppercase"><?php echo $badge_text; ?></span>
						<?php elseif ($badge === 'surface'): ?>
						<span class="bg-surface-container-highest text-on-surface-variant text-[10px] font-bold px-3 py-1 rounded-full uppercase"><?php echo $badge_text; ?></span>
						<?php else: ?>
						<span class="bg-secondary-container/35 text-on-secondary-container text-[10px] font-bold px-3 py-1 rounded-full uppercase"><?php echo $badge_text; ?></span>
						<?php endif; ?>
					</div>
					<div class="space-y-2 mb-5">
						<div class="bg-surface-container-low p-3 rounded-2xl rounded-tl-sm text-xs">
							<p class="text-on-surface-variant font-bold mb-0.5"><?php echo htmlspecialchars($g_isim); ?></p>
							<p class="text-on-surface"><?php echo htmlspecialchars(admin_snip($dm['mesaj_detay'] ?? '', 100)); ?></p>
						</div>
						<p class="text-[10px] text-on-surface-variant">↔ <?php echo htmlspecialchars($a_isim); ?></p>
					</div>
					<div class="flex items-center justify-between pt-4 border-t border-outline-variant/10">
						<p class="text-[10px] text-on-surface-variant">Özel mesaj özeti</p>
						<a href="<?php echo htmlspecialchars($sohbet_href); ?>" class="bg-primary px-4 py-2 rounded-full text-white text-xs font-bold hover:brightness-110 inline-flex items-center gap-1.5 transition-all">
							<span class="material-symbols-outlined text-sm">chat</span>
							Sohbete git
						</a>
					</div>
				</div>
				<?php
					endif;
				endforeach;
				?>
			</div>
		</section>

		<section class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-10">
			<div class="lg:col-span-2 bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-outline-variant/10 dash-card">
				<div class="flex items-center justify-between mb-5">
					<h3 class="text-lg font-bold">Son kayıtlar</h3>
					<a href="kullanicilar.php" class="text-primary text-xs font-bold hover:underline">Tümünü gör</a>
				</div>
				<div class="space-y-3">
					<?php foreach ($yeni_uyeler as $yu): ?>
					<div class="flex items-center justify-between p-4 bg-surface-container-low rounded-2xl tonal-shift hover:bg-surface-container transition-colors">
						<div class="flex items-center gap-3 min-w-0">
							<div class="w-10 h-10 rounded-xl bg-primary-fixed/50 flex items-center justify-center flex-shrink-0">
								<span class="material-symbols-outlined text-primary text-lg">person</span>
							</div>
							<div class="min-w-0">
								<p class="text-sm font-bold truncate"><?php echo htmlspecialchars($yu['kul_isim']); ?></p>
								<p class="text-xs text-on-surface-variant truncate"><?php echo htmlspecialchars($yu['kul_mail'] ?? ''); ?></p>
							</div>
						</div>
						<span class="text-[10px] font-medium text-outline flex-shrink-0 ml-2">#<?php echo (int) $yu['kul_id']; ?></span>
					</div>
					<?php endforeach; ?>
					<?php if ($bekleyen > 0): ?>
					<div class="flex items-center justify-between p-4 bg-error-container/30 rounded-2xl border border-error/20">
						<div class="flex items-center gap-3">
							<div class="w-10 h-10 rounded-xl bg-error-container flex items-center justify-center">
								<span class="material-symbols-outlined text-error">hourglass_top</span>
							</div>
							<div>
								<p class="text-sm font-bold">Onay bekleyen kayıt</p>
								<p class="text-xs text-on-surface-variant"><?php echo (int) $bekleyen; ?> kullanıcı onayınızı bekliyor.</p>
							</div>
						</div>
						<a href="onay-bekleyenler.php" class="text-xs font-bold text-error hover:underline">İncele</a>
					</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="bg-surface-container-lowest rounded-3xl p-6 md:p-8 shadow-sm border border-outline-variant/10 dash-card">
				<h3 class="text-lg font-bold mb-5">Özet</h3>
				<div class="space-y-4">
					<div class="flex items-center justify-between">
						<div class="flex items-center gap-2">
							<div class="w-2 h-2 rounded-full bg-secondary"></div>
							<span class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Veritabanı</span>
						</div>
						<span class="text-xs font-medium">Bağlı</span>
					</div>
					<div class="flex items-center justify-between">
						<div class="flex items-center gap-2">
							<div class="w-2 h-2 rounded-full bg-secondary"></div>
							<span class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Mesajlaşma</span>
						</div>
						<span class="text-xs font-medium"><?php echo $toplam_mesaj > 0 ? 'Aktif' : 'Boş'; ?></span>
					</div>
					<div class="flex items-center justify-between">
						<div class="flex items-center gap-2">
							<div class="w-2 h-2 rounded-full <?php echo $bekleyen > 0 ? 'bg-error' : 'bg-secondary'; ?>"></div>
							<span class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Onay kuyruğu</span>
						</div>
						<span class="text-xs font-medium <?php echo $bekleyen > 0 ? 'text-error' : ''; ?>"><?php echo (int) $bekleyen; ?> bekliyor</span>
					</div>
					<div class="pt-4 mt-2 border-t border-outline-variant/10">
						<p class="text-[10px] text-on-surface-variant leading-relaxed">Umut Yılmaz · BestWp Chat Scripti · 2026 · <a href="https://github.com/umtylmzl" target="_blank" rel="noopener noreferrer" class="text-primary font-semibold hover:underline">GitHub</a></p>
					</div>
				</div>
			</div>
		</section>
<?php
$admin_footer_scripts = <<<JS
<script>
(function () {
	var inp = document.getElementById("dash-filter");
	if (!inp) return;
	inp.addEventListener("input", function () {
		var q = (inp.value || "").toLowerCase().trim();
		document.querySelectorAll(".dash-card").forEach(function (el) {
			var t = (el.textContent || "").toLowerCase();
			el.style.display = (!q || t.indexOf(q) !== -1) ? "" : "none";
		});
	});
})();
</script>
JS;
include __DIR__ . "/partials/admin_shell_bottom.php";

