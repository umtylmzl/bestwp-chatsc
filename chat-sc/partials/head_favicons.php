<?php
/**
 * Favicon / PWA ikonları — dosyalar assets/icons/ altında.
 */
$__sn = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '/index.php';
$__dir = dirname($__sn);
$__bestwp_root = ($__dir === '/' || $__dir === '.') ? '' : rtrim($__dir, '/');
$__bestwp_url = function ($file) use ($__bestwp_root) {
	$path = ($__bestwp_root === '' ? '/' : $__bestwp_root . '/') . ltrim($file, '/');
	return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
};
?>
<link rel="icon" href="<?php echo $__bestwp_url('assets/icons/favicon.ico'); ?>" sizes="any">
<link rel="apple-touch-icon" href="<?php echo $__bestwp_url('assets/icons/apple-touch-icon.png'); ?>">
<link rel="manifest" href="<?php echo $__bestwp_url('site.webmanifest'); ?>">
<meta name="theme-color" content="#00453d">
