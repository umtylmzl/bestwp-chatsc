<?php
/**
 * Ortak imza: Umut Yılmaz · BestWp Chat Scripti · 2026
 * $credit_variant: admin | messenger | user | login | embed
 */
if (!isset($credit_variant)) {
	$credit_variant = 'admin';
}
$gh = 'https://github.com/umtylmzl';

if ($credit_variant === 'messenger') {
	?>
<div class="px-3 py-2.5 border-t border-black/[0.08] bg-wp-sidebar flex-shrink-0">
	<p class="font-semibold text-[11px] text-[#111b21] leading-tight">Umut Yılmaz</p>
	<p class="text-[10px] text-[#667781] leading-snug mt-0.5">BestWp Chat Scripti · 2026</p>
	<a href="<?php echo htmlspecialchars($gh, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="text-[10px] font-semibold text-primary hover:underline mt-1 inline-block">GitHub — @umtylmzl</a>
</div>
	<?php
} elseif ($credit_variant === 'user') {
	?>
<div class="mt-8 pt-4 border-t border-slate-200 text-center text-[10px] text-slate-500 leading-relaxed">
	<p class="font-semibold text-slate-700">Umut Yılmaz</p>
	<p>BestWp Chat Scripti · 2026</p>
	<a href="<?php echo htmlspecialchars($gh, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="text-primary font-semibold hover:underline mt-1 inline-block">github.com/umtylmzl</a>
</div>
	<?php
} elseif ($credit_variant === 'login') {
	?>
<div class="mt-8 text-center text-[11px] text-on-surface-variant leading-relaxed max-w-md mx-auto px-4">
	<p class="font-bold text-on-surface">Umut Yılmaz</p>
	<p class="mt-0.5">BestWp Chat Scripti · 2026</p>
	<a href="<?php echo htmlspecialchars($gh, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="text-primary font-semibold hover:underline mt-2 inline-block">GitHub — umtylmzl</a>
</div>
	<?php
} elseif ($credit_variant === 'embed') {
	?>
<div class="mt-6 pt-4 border-t border-slate-200 text-[10px] text-slate-500">
	<p class="font-semibold text-slate-700">Umut Yılmaz · BestWp Chat Scripti · 2026</p>
	<a href="<?php echo htmlspecialchars($gh, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="text-primary font-semibold hover:underline mt-1 inline-block">github.com/umtylmzl</a>
</div>
	<?php
} else {
	?>
<div class="px-3 py-2.5 border-t border-slate-200/80 flex-shrink-0 bg-slate-100/50">
	<p class="font-semibold text-[10px] text-slate-700 leading-tight">Umut Yılmaz</p>
	<p class="text-[9px] text-slate-500 leading-snug mt-0.5">BestWp Chat Scripti · 2026</p>
	<a href="<?php echo htmlspecialchars($gh, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="text-[9px] font-semibold text-primary hover:underline mt-1 inline-block">GitHub — @umtylmzl</a>
</div>
	<?php
}
