<?php
include 'islemler/baglan.php';
require_once __DIR__ . '/fonksiyonlar.php';

$step_totp = isset($_GET['step']) && $_GET['step'] === 'totp';
$t_get = isset($_GET['t']) ? trim((string) $_GET['t']) : '';
$tok_ok = ($t_get !== '') ? bestwp_totp_login_token_read($t_get) : null;
$sess_ok = !empty($_SESSION['totp_pending_kul_id']);

if ($step_totp && !$tok_ok && !$sess_ok) {
	header('Location: login.php');
	exit;
}

$totp_form_token = '';
if ($t_get !== '' && $tok_ok) {
	$totp_form_token = $t_get;
} elseif (!empty($_SESSION['totp_pending_signed'])) {
	$totp_form_token = (string) $_SESSION['totp_pending_signed'];
} elseif ($sess_ok) {
	$totp_form_token = bestwp_totp_login_token_create((int) $_SESSION['totp_pending_kul_id']);
	$_SESSION['totp_pending_signed'] = $totp_form_token;
}

$show_totp_step = $step_totp && ($tok_ok || $sess_ok);

$totp_pending_mail = isset($_SESSION['totp_pending_mail']) ? (string) $_SESSION['totp_pending_mail'] : '';
if ($show_totp_step && $totp_pending_mail === '' && $tok_ok && isset($db) && $db instanceof PDO) {
	$mq = $db->prepare('SELECT kul_mail FROM kullanicilar WHERE kul_id = :id LIMIT 1');
	$mq->execute(array('id' => (int) $tok_ok['kid']));
	$mrow = $mq->fetch(PDO::FETCH_ASSOC);
	if ($mrow && !empty($mrow['kul_mail'])) {
		$totp_pending_mail = (string) $mrow['kul_mail'];
	}
}

$durum = isset($_GET['durum']) ? $_GET['durum'] : '';
$kayit_sekmeler = array('kayit_ok', 'kayit_hata', 'kayit_eksik', 'kayit_mail', 'kayit_sifre_kisa', 'kayit_sifre_uyusmaz', 'mail_var');
$tab_kayit = !$show_totp_step && ((isset($_GET['tab']) && $_GET['tab'] === 'register') || in_array($durum, $kayit_sekmeler, true));

$mesajlar = array(
	'no' => array('tip' => 'error', 'metin' => 'E-posta veya şifre hatalı.'),
	'totp_hata' => array('tip' => 'error', 'metin' => 'Doğrulama kodu geçersiz veya süresi doldu. Tekrar deneyin.'),
	'totp_session' => array('tip' => 'error', 'metin' => 'Oturum süresi doldu. Lütfen yeniden giriş yapın.'),
	'beklemede' => array('tip' => 'warn', 'metin' => 'Hesabınız henüz onaylanmadı. Yönetici onayından sonra giriş yapabilirsiniz.'),
	'kayit_ok' => array('tip' => 'ok', 'metin' => 'Kayıt alındı. Yönetici onayından sonra giriş yapabilirsiniz.'),
	'kayit_hata' => array('tip' => 'error', 'metin' => 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.'),
	'kayit_eksik' => array('tip' => 'error', 'metin' => 'Ad, e-posta ve şifre zorunludur.'),
	'kayit_mail' => array('tip' => 'error', 'metin' => 'Geçerli bir e-posta adresi girin.'),
	'kayit_sifre_kisa' => array('tip' => 'error', 'metin' => 'Şifre en az 6 karakter olmalıdır.'),
	'kayit_sifre_uyusmaz' => array('tip' => 'error', 'metin' => 'Şifreler eşleşmiyor.'),
	'mail_var' => array('tip' => 'error', 'metin' => 'Bu e-posta ile zaten kayıt var.'),
);
$banner = isset($mesajlar[$durum]) ? $mesajlar[$durum] : null;
$login_site_title = !empty($ayarcek['site_baslik']) ? (string) $ayarcek['site_baslik'] : 'BestWp';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title><?php echo htmlspecialchars($login_site_title); ?> | Erişim</title>
  <?php include __DIR__ . '/partials/head_favicons.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&amp;display=swap" rel="stylesheet"/>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "on-tertiary": "#ffffff",
            "on-secondary": "#ffffff",
            "outline": "#6f7976",
            "surface-bright": "#f7f9fc",
            "surface-container-highest": "#e0e3e6",
            "error-container": "#ffdad6",
            "surface-container": "#eceef1",
            "background": "#f7f9fc",
            "primary-fixed": "#a8f0e3",
            "on-primary-fixed": "#00201c",
            "on-primary-container": "#8dd5c8",
            "primary-fixed-dim": "#8cd4c7",
            "on-secondary-fixed-variant": "#005322",
            "on-secondary-container": "#007232",
            "on-tertiary-fixed": "#0c2003",
            "surface-container-high": "#e6e8eb",
            "primary-container": "#075e54",
            "on-surface-variant": "#3f4946",
            "tertiary-fixed": "#cfebba",
            "outline-variant": "#bec9c5",
            "on-surface": "#191c1e",
            "surface-tint": "#1c695f",
            "secondary-container": "#5dfd8a",
            "surface-variant": "#e0e3e6",
            "surface-dim": "#d8dadd",
            "inverse-primary": "#8cd4c7",
            "secondary-fixed": "#66ff8e",
            "on-tertiary-fixed-variant": "#364d29",
            "surface": "#f7f9fc",
            "inverse-on-surface": "#eff1f4",
            "primary": "#00453d",
            "on-tertiary-container": "#b5d0a1",
            "on-primary": "#ffffff",
            "tertiary-fixed-dim": "#b4cf9f",
            "inverse-surface": "#2d3133",
            "tertiary-container": "#435a34",
            "on-primary-fixed-variant": "#005047",
            "surface-container-lowest": "#ffffff",
            "tertiary": "#2c421f",
            "on-secondary-fixed": "#002109",
            "on-error": "#ffffff",
            "secondary": "#006d2f",
            "on-background": "#191c1e",
            "error": "#ba1a1a",
            "on-error-container": "#93000a",
            "secondary-fixed-dim": "#3de273",
            "surface-container-low": "#f2f4f7"
          },
          fontFamily: {
            headline: ["Inter", "sans-serif"],
            body: ["Inter", "sans-serif"],
            label: ["Inter", "sans-serif"]
          },
          borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
        },
      },
    };
  </script>
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    .material-symbols-outlined.fill {
      font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
    }
    body {
      background-color: #f7f9fc;
      background-image: radial-gradient(circle at 2px 2px, #eceef1 1px, transparent 0);
      background-size: 40px 40px;
    }
  </style>
</head>
<body class="font-body text-on-surface min-h-screen flex items-center justify-center p-4 sm:p-6">
  <div class="w-full max-w-[440px] flex flex-col items-center min-w-0">
    <?php if ($banner): ?>
    <?php
      $bc = $banner['tip'] === 'ok' ? 'bg-secondary-container/30 text-on-secondary-container border-outline-variant/40'
        : ($banner['tip'] === 'warn' ? 'bg-primary-fixed/40 text-on-primary-fixed border-primary-container/30'
        : 'bg-error-container/80 text-on-error-container border-error/20');
    ?>
    <div class="w-full mb-6 rounded-2xl border px-4 py-3 text-sm font-medium <?php echo $bc; ?>">
      <?php echo htmlspecialchars($banner['metin']); ?>
    </div>
    <?php endif; ?>

    <div class="mb-8 sm:mb-10 text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-container text-on-primary mb-4 shadow-xl">
        <span class="material-symbols-outlined fill text-4xl"><?php echo $show_totp_step ? 'shield_lock' : 'chat'; ?></span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-primary font-headline">BestWp</h1>
      <p class="text-on-surface-variant mt-2 text-sm font-medium"><?php echo $show_totp_step ? 'İki adımlı doğrulama' : 'Dijital iletişiminizi güvende tutun.'; ?></p>
    </div>

    <main class="w-full glass-card border-none rounded-[2rem] shadow-[0_32px_64px_-12px_rgba(0,69,61,0.08)] overflow-hidden">
      <?php if ($show_totp_step): ?>
      <div class="p-6 sm:p-10">
        <p class="text-sm text-on-surface-variant mb-6 text-center break-all"><?php echo htmlspecialchars($totp_pending_mail); ?> hesabı için kimlik doğrulayıcı uygulamanızdaki 6 haneli kodu girin.</p>
        <form action="islemler/ajax.php" method="POST" class="space-y-6" autocomplete="one-time-code">
          <input type="hidden" name="totp_state" value="<?php echo htmlspecialchars($totp_form_token, ENT_QUOTES, 'UTF-8'); ?>"/>
          <input type="hidden" name="totpdogrula" value="1"/>
          <div class="space-y-2">
            <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant px-1" for="totp_code">Doğrulama kodu</label>
            <div class="relative group">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline group-focus-within:text-primary transition-colors">pin</span>
              <input id="totp_code" name="totp_code" inputmode="numeric" pattern="[0-9]*" maxlength="6" class="w-full pl-12 pr-4 py-4 bg-surface-container-low border-none rounded-2xl focus:ring-2 focus:ring-primary-fixed focus:bg-surface-container-lowest transition-all placeholder:text-outline-variant text-on-surface text-center text-2xl tracking-[0.35em] font-mono" placeholder="000000" type="text" required autofocus/>
            </div>
          </div>
          <button class="w-full py-4 bg-primary-container text-white rounded-full font-bold text-lg shadow-lg shadow-primary-container/20 hover:bg-primary transition-all active:scale-[0.98] flex items-center justify-center gap-2" type="submit">
            Doğrula ve giriş yap
            <span class="material-symbols-outlined">verified_user</span>
          </button>
        </form>
        <p class="mt-6 text-center">
          <a href="islemler/cikis.php" class="text-sm font-semibold text-primary hover:underline">Farklı hesapla giriş</a>
        </p>
      </div>
      <?php else: ?>
      <div class="flex p-2 bg-surface-container-low/50" role="tablist">
        <button type="button" id="tab-login" class="flex-1 py-3 text-sm font-semibold rounded-2xl transition-all duration-200 <?php echo $tab_kayit ? 'text-on-surface-variant hover:bg-surface-container-high/50 font-medium' : 'bg-surface-container-lowest text-primary shadow-sm'; ?>">
          Giriş
        </button>
        <button type="button" id="tab-register" class="flex-1 py-3 text-sm rounded-2xl transition-all duration-200 <?php echo $tab_kayit ? 'bg-surface-container-lowest text-primary shadow-sm font-semibold' : 'text-on-surface-variant hover:bg-surface-container-high/50 font-medium'; ?>">
          Kayıt ol
        </button>
      </div>

      <div class="p-6 sm:p-10">
        <div id="panel-login" class="<?php echo $tab_kayit ? 'hidden' : ''; ?>">
          <form action="islemler/ajax.php" method="POST" class="space-y-6">
            <div class="space-y-2">
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant px-1" for="kul_mail">E-posta</label>
              <div class="relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline group-focus-within:text-primary transition-colors">alternate_email</span>
                <input id="kul_mail" name="kul_mail" class="w-full pl-12 pr-4 py-4 bg-surface-container-low border-none rounded-2xl focus:ring-2 focus:ring-primary-fixed focus:bg-surface-container-lowest transition-all placeholder:text-outline-variant text-on-surface" placeholder="ornek@eposta.com" type="email" autocomplete="username" required/>
              </div>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between items-end px-1">
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="kul_sifre">Şifre</label>
                <span class="text-xs font-semibold text-outline-variant cursor-not-allowed" title="Yakında">Unuttum?</span>
              </div>
              <div class="relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline group-focus-within:text-primary transition-colors">lock</span>
                <input id="kul_sifre" name="kul_sifre" class="w-full pl-12 pr-12 py-4 bg-surface-container-low border-none rounded-2xl focus:ring-2 focus:ring-primary-fixed focus:bg-surface-container-lowest transition-all placeholder:text-outline-variant text-on-surface login-pass" placeholder="••••••••" type="password" autocomplete="current-password" required/>
                <button type="button" class="toggle-pass absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary" data-target="kul_sifre" aria-label="Şifreyi göster">
                  <span class="material-symbols-outlined text-xl">visibility</span>
                </button>
              </div>
            </div>
            <div class="pt-4">
              <button class="w-full py-4 bg-primary-container text-white rounded-full font-bold text-lg shadow-lg shadow-primary-container/20 hover:bg-primary transition-all active:scale-[0.98] flex items-center justify-center gap-2" type="submit" name="oturumacma" value="1">
                Giriş yap
                <span class="material-symbols-outlined">arrow_forward</span>
              </button>
            </div>
          </form>
        </div>

        <div id="panel-register" class="<?php echo $tab_kayit ? '' : 'hidden'; ?>">
          <form action="islemler/ajax.php" method="POST" class="space-y-5">
            <div class="space-y-2">
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant px-1" for="reg_isim">Ad Soyad</label>
              <div class="relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline group-focus-within:text-primary transition-colors">person</span>
                <input id="reg_isim" name="kul_isim" class="w-full pl-12 pr-4 py-3.5 bg-surface-container-low border-none rounded-2xl focus:ring-2 focus:ring-primary-fixed focus:bg-surface-container-lowest transition-all placeholder:text-outline-variant text-on-surface" placeholder="Adınız Soyadınız" type="text" required/>
              </div>
            </div>
            <div class="space-y-2">
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant px-1" for="reg_mail">E-posta</label>
              <div class="relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline group-focus-within:text-primary transition-colors">alternate_email</span>
                <input id="reg_mail" name="kul_mail" class="w-full pl-12 pr-4 py-3.5 bg-surface-container-low border-none rounded-2xl focus:ring-2 focus:ring-primary-fixed focus:bg-surface-container-lowest transition-all placeholder:text-outline-variant text-on-surface" placeholder="ornek@eposta.com" type="email" autocomplete="email" required/>
              </div>
            </div>
            <div class="space-y-2">
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant px-1" for="reg_tel">Telefon <span class="font-normal normal-case text-outline-variant">(isteğe bağlı)</span></label>
              <div class="relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline group-focus-within:text-primary transition-colors">call</span>
                <input id="reg_tel" name="kul_telefon" class="w-full pl-12 pr-4 py-3.5 bg-surface-container-low border-none rounded-2xl focus:ring-2 focus:ring-primary-fixed focus:bg-surface-container-lowest transition-all placeholder:text-outline-variant text-on-surface" placeholder="05xx xxx xx xx" type="text"/>
              </div>
            </div>
            <div class="space-y-2">
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant px-1" for="reg_sifre">Şifre</label>
              <div class="relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline group-focus-within:text-primary transition-colors">lock</span>
                <input id="reg_sifre" name="kul_sifre" class="w-full pl-12 pr-12 py-3.5 bg-surface-container-low border-none rounded-2xl focus:ring-2 focus:ring-primary-fixed focus:bg-surface-container-lowest transition-all placeholder:text-outline-variant text-on-surface reg-pass" placeholder="En az 6 karakter" type="password" autocomplete="new-password" required minlength="6"/>
                <button type="button" class="toggle-pass absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary" data-target="reg_sifre" aria-label="Şifreyi göster">
                  <span class="material-symbols-outlined text-xl">visibility</span>
                </button>
              </div>
            </div>
            <div class="space-y-2">
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant px-1" for="reg_sifre2">Şifre tekrar</label>
              <div class="relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline group-focus-within:text-primary transition-colors">lock_reset</span>
                <input id="reg_sifre2" name="kul_sifre_tekrar" class="w-full pl-12 pr-12 py-3.5 bg-surface-container-low border-none rounded-2xl focus:ring-2 focus:ring-primary-fixed focus:bg-surface-container-lowest transition-all placeholder:text-outline-variant text-on-surface reg-pass2" placeholder="Şifreyi tekrarlayın" type="password" autocomplete="new-password" required/>
                <button type="button" class="toggle-pass absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary" data-target="reg_sifre2" aria-label="Şifreyi göster">
                  <span class="material-symbols-outlined text-xl">visibility</span>
                </button>
              </div>
            </div>
            <p class="text-xs text-on-surface-variant leading-relaxed px-1">Kayıt sonrası hesabınız yönetici onayına düşer; onaylanana kadar giriş yapamazsınız.</p>
            <div class="pt-2">
              <button class="w-full py-4 bg-primary-container text-white rounded-full font-bold text-lg shadow-lg shadow-primary-container/20 hover:bg-primary transition-all active:scale-[0.98] flex items-center justify-center gap-2" type="submit" name="kayitol" value="1">
                Hesap oluştur
                <span class="material-symbols-outlined">person_add</span>
              </button>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <div class="px-4 sm:px-10 py-5 sm:py-6 bg-surface-container-low/30 text-center border-t border-outline-variant/10">
        <p class="text-[10px] text-on-surface-variant font-medium leading-relaxed">
          BestWp Chat Scripti — Umut Yılmaz · 2026 ·
          <a class="font-semibold text-primary hover:underline" href="https://github.com/umtylmzl" target="_blank" rel="noopener noreferrer">GitHub</a>
        </p>
        <p class="text-[10px] text-on-surface-variant/80 mt-2 leading-relaxed">
          Giriş veya kayıt ile site kurallarına uyduğunuzu kabul etmiş olursunuz.
        </p>
      </div>
    </main>

    <?php
    $credit_variant = 'login';
    include __DIR__ . '/partials/credit_sidebar.php';
    ?>
  </div>

  <script>
    (function () {
      var tabLogin = document.getElementById('tab-login');
      var tabRegister = document.getElementById('tab-register');
      var panelLogin = document.getElementById('panel-login');
      var panelRegister = document.getElementById('panel-register');

      function showLogin() {
        if (!panelLogin || !panelRegister || !tabLogin || !tabRegister) return;
        panelLogin.classList.remove('hidden');
        panelRegister.classList.add('hidden');
        tabLogin.className = 'flex-1 py-3 text-sm font-semibold rounded-2xl bg-surface-container-lowest text-primary shadow-sm transition-all duration-200';
        tabRegister.className = 'flex-1 py-3 text-sm font-medium rounded-2xl text-on-surface-variant hover:bg-surface-container-high/50 transition-all duration-200';
      }
      function showRegister() {
        if (!panelLogin || !panelRegister || !tabLogin || !tabRegister) return;
        panelLogin.classList.add('hidden');
        panelRegister.classList.remove('hidden');
        tabRegister.className = 'flex-1 py-3 text-sm font-semibold rounded-2xl bg-surface-container-lowest text-primary shadow-sm transition-all duration-200';
        tabLogin.className = 'flex-1 py-3 text-sm font-medium rounded-2xl text-on-surface-variant hover:bg-surface-container-high/50 transition-all duration-200';
      }

      if (tabLogin && tabRegister) {
        tabLogin.addEventListener('click', showLogin);
        tabRegister.addEventListener('click', showRegister);
      }

      document.querySelectorAll('.toggle-pass').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-target');
          var input = document.getElementById(id);
          if (!input) return;
          var icon = btn.querySelector('.material-symbols-outlined');
          if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.textContent = 'visibility_off';
          } else {
            input.type = 'password';
            if (icon) icon.textContent = 'visibility';
          }
        });
      });
    })();
  </script>
</body>
</html>
