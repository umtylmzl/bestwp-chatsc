<?php
/**
 * Eski SB Admin üst şablonu kaldırıldı.
 * BestWp Chat Scripti — Umut Yılmaz · 2026 · https://github.com/umtylmzl
 */
require_once __DIR__ . '/islemler/baglan.php';
require_once __DIR__ . '/fonksiyonlar.php';
oturumkontrol('login.php');
header('Location: ' . (yetkikontrol() ? 'index.php' : 'messenger.php'));
exit;
