<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title><?php echo isset($modal_title) ? htmlspecialchars($modal_title) : 'BestWp'; ?></title>
	<?php include __DIR__ . '/head_favicons.php'; ?>
	<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
	<script>
		tailwind.config = {
			theme: {
				extend: {
					colors: {
						primary: '#00453d',
						'on-primary': '#ffffff',
						secondary: '#006d2f',
						background: '#f7f9fc'
					},
					fontFamily: { sans: ['Inter', 'sans-serif'] }
				}
			}
		};
	</script>
</head>
<body class="bg-slate-100 p-4 font-sans text-slate-800 antialiased text-sm">
