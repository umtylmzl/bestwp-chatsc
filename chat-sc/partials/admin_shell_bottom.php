	</div>
	<p class="flex-shrink-0 px-6 py-2 text-center text-[10px] text-slate-500 border-t border-black/[0.04] bg-white/80">
		Umut Yılmaz · BestWp Chat Scripti · 2026 ·
		<a href="https://github.com/umtylmzl" target="_blank" rel="noopener noreferrer" class="text-primary font-semibold hover:underline">GitHub</a>
	</p>
</main>
<?php if (!isset($admin_show_messenger_fab) || $admin_show_messenger_fab): ?>
<a href="messenger.php" class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 bg-secondary text-on-secondary w-12 h-12 sm:w-14 sm:h-14 rounded-full shadow-xl flex items-center justify-center hover:scale-105 active:scale-95 transition-all z-50" title="Sohbet">
	<span class="material-symbols-outlined text-2xl fill">chat</span>
</a>
<?php endif; ?>
<?php
if (!empty($admin_footer_scripts)) {
	echo $admin_footer_scripts;
}
?>
<script>
(function () {
	var side = document.getElementById('admin-sidebar');
	var overlay = document.getElementById('admin-nav-overlay');
	if (!side || !overlay) return;
	function openNav() {
		side.classList.remove('max-md:-translate-x-full');
		side.classList.add('max-md:translate-x-0');
		overlay.classList.remove('hidden');
		overlay.setAttribute('aria-hidden', 'false');
	}
	function closeNav() {
		side.classList.add('max-md:-translate-x-full');
		side.classList.remove('max-md:translate-x-0');
		overlay.classList.add('hidden');
		overlay.setAttribute('aria-hidden', 'true');
	}
	document.querySelectorAll('.admin-nav-open').forEach(function (b) {
		b.addEventListener('click', openNav);
	});
	document.querySelectorAll('.admin-nav-close').forEach(function (b) {
		b.addEventListener('click', closeNav);
	});
	overlay.addEventListener('click', closeNav);
	side.querySelectorAll('a[href]').forEach(function (a) {
		a.addEventListener('click', function () {
			if (window.matchMedia('(max-width: 767px)').matches) {
				closeNav();
			}
		});
	});
})();
</script>
</body>
</html>
