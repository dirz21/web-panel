<?php
// Pake folder /tmp biar ga crash di Railway
$db = new PDO('sqlite:/tmp/panel.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS licenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key_code TEXT UNIQUE,
    hwid TEXT,
    status TEXT DEFAULT 'unused',
    duration_days INTEGER DEFAULT 30,
    max_devices INTEGER DEFAULT 1,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

if (isset($_POST['generate'])) {
    $duration = (int)$_POST['duration'];
    $max_dev = (int)$_POST['max_devices'];
    $new_key = "CIPXIT-" . strtoupper(substr(md5(uniqid()), 0, 6)) . "-APK";
    $stmt = $db->prepare("INSERT INTO licenses (key_code, duration_days, max_devices) VALUES (?, ?, ?)");
    $stmt->execute([$new_key, $duration, $max_dev]);
    header("Location: panel.php"); exit;
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM licenses WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: panel.php"); exit;
}

$licenses = $db->query("SELECT * FROM licenses ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$stat_total = count($licenses);
$stat_active = count(array_filter($licenses, fn($l) => $l['status'] === 'active'));
$stat_unused = count(array_filter($licenses, fn($l) => $l['status'] === 'unused'));
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIPXIT // License Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050807; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #1F2E2A; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #00FFC2; }
    </style>
</head>
<body class="text-slate-300 flex h-screen overflow-hidden">

    <aside class="w-64 border-r border-[#1F2E2A] bg-[#070B0A] flex flex-col justify-between hidden md:flex">
        <div>
            <div class="p-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded bg-[#00FFC2] flex items-center justify-center text-black font-bold text-lg">C</div>
                <div>
                    <div class="font-semibold text-sm text-[#00FFC2] tracking-widest uppercase">CipXit</div>
                    <div class="text-[10px] text-slate-500 tracking-wider">NEW CONTROL</div>
                </div>
            </div>
            <div class="px-6 py-2 text-[10px] font-semibold text-slate-600 tracking-widest mb-2">WORKSPACE</div>
            <nav class="px-3 space-y-1">
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#0E1614] text-[#00FFC2] border border-[#1F2E2A] text-sm font-medium">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Overview
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-[#0E1614] transition text-sm">
                    <i data-lucide="key" class="w-4 h-4"></i> Licenses <span class="ml-auto bg-[#1F2E2A] text-xs px-2 py-0.5 rounded-full text-slate-300"><?= $stat_total ?></span>
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-[#0E1614] transition text-sm">
                    <i data-lucide="activity" class="w-4 h-4"></i> Activity
                </a>
            </nav>
        </div>
        <div class="p-4 border-t border-[#1F2E2A] m-3 rounded-lg bg-[#0E1614]">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-2 h-2 rounded-full bg-[#00FFC2] animate-pulse"></div>
                <span class="text-xs font-medium">System online</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-slate-700 flex items-center justify-center text-xs">A</div>
                <span class="text-xs text-slate-400">Admin</span>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-16 border-b border-[#1F2E2A] flex items-center justify-between px-6 bg-[#070B0A]">
            <div class="flex items-center text-xs tracking-widest text-slate-500">
                <span class="text-slate-300">CIPXIT NEW</span> <span class="mx-2">/</span> LICENSE CONTROL
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-[#0E1614] border border-[#1F2E2A] px-3 py-1.5 rounded-full">
                    <div class="w-2 h-2 rounded-full bg-[#00FFC2]"></div>
                    <span class="text-[10px] tracking-widest">LIVE</span>
                </div>
                <button class="text-slate-400 hover:text-white"><i data-lucide="user" class="w-4 h-4"></i></button>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                <div>
                    <div class="text-xs font-semibold text-[#00FFC2] tracking-widest mb-2 uppercase">Overview / <?= date('d M Y') ?></div>
                    <h1 class="text-3xl lg:text-4xl font-bold text-white mb-2 tracking-tight">License overview</h1>
                    <p class="text-slate-400 text-sm">Pantau dan kelola semua akses aplikasi dari satu tempat.</p>
                </div>
                <button onclick="document.getElementById('generateModal').classList.remove('hidden')" class="flex items-center gap-2 bg-[#00FFC2] text-black px-5 py-2.5 rounded-lg font-semibold hover:bg-[#00d6a3] transition shadow-[0_0_15px_rgba(0,255,194,0.2)]">
                    <i data-lucide="plus" class="w-4 h-4"></i> Buat lisensi
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-[#0E1614] border border-[#1F2E2A] p-5 rounded-xl">
                    <div class="flex items-center gap-3 mb-4 text-slate-400">
                        <i data-lucide="key" class="w-5 h-5 text-[#00FFC2]"></i>
                        <span class="text-xs font-medium">Total lisensi</span>
                    </div>
                    <div class="text-3xl font-mono text-white mb-2"><?= str_pad($stat_total, 2, '0', STR_PAD_LEFT) ?></div>
                    <div class="text-[10px] text-[#00FFC2]">+0 bulan ini</div>
                </div>
                <div class="bg-[#0E1614] border border-[#1F2E2A] p-5 rounded-xl">
                    <div class="flex items-center gap-3 mb-4 text-slate-400">
                        <i data-lucide="shield-check" class="w-5 h-5 text-[#00FFC2]"></i>
                        <span class="text-xs font-medium">Lisensi aktif</span>
                    </div>
                    <div class="text-3xl font-mono text-white mb-2"><?= str_pad($stat_active, 2, '0', STR_PAD_LEFT) ?></div>
                    <div class="text-[10px] text-slate-500"><?= $stat_total > 0 ? round(($stat_active / $stat_total) * 100) : 0 ?>% dari total key</div>
                </div>
                <div class="bg-[#0E1614] border border-[#1F2E2A] p-5 rounded-xl">
                    <div class="flex items-center gap-3 mb-4 text-slate-400">
                        <i data-lucide="users" class="w-5 h-5 text-[#00FFC2]"></i>
                        <span class="text-xs font-medium">Device terdaftar</span>
                    </div>
                    <div class="text-3xl font-mono text-white mb-2"><?= str_pad($stat_active, 2, '0', STR_PAD_LEFT) ?></div>
                    <div class="text-[10px] text-slate-500">0% utilisasi kapasitas</div>
                </div>
                <div class="bg-[#0E1614] border border-[#1F2E2A] p-5 rounded-xl flex flex-col justify-between">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-medium text-slate-400">System health</span>
                        <span class="text-sm font-mono text-[#00FFC2]">98.7%</span>
                    </div>
                    <div class="w-full bg-[#1F2E2A] rounded-full h-1.5 mb-4">
                        <div class="bg-[#00FFC2] h-1.5 rounded-full" style="width: 98.7%"></div>
                    </div>
                    <div class="flex items-center gap-2 text-[10px] text-slate-400">
                        <div class="w-1.5 h-1.5 rounded-full bg-[#00FFC2]"></div> All services operational
                    </div>
                </div>
            </div>

            <div class="bg-[#0E1614] border border-[#1F2E2A] rounded-xl overflow-hidden">
                <div class="p-6 border-b border-[#1F2E2A] flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            License inventory 
                            <span class="text-[10px] bg-[#1F2E2A] text-slate-300 px-2 py-0.5 rounded-full"><?= $stat_total ?></span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">Daftar key yang diterbitkan di workspace ini.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-wider text-slate-500 border-b border-[#1F2E2A]">
                                <th class="px-6 py-4 font-medium">License Key</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium">Valid Until</th>
                                <th class="px-6 py-4 font-medium">Device Usage</th>
                                <th class="px-6 py-4 font-medium">Created</th>
                                <th class="px-6 py-4 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#1F2E2A] text-sm">
                            <?php if($stat_total > 0): ?>
                                <?php foreach($licenses as $row): 
                                    $dev_count = empty($row['hwid']) ? 0 : count(json_decode($row['hwid'], true));
                                    $status_color = $row['status'] == 'active' ? 'text-[#00FFC2] border-[#00FFC2]/30 bg-[#00FFC2]/10' : 'text-slate-400 border-slate-600 bg-slate-800';
                                ?>
                                <tr class="hover:bg-[#141C1A] transition group">
                                    <!-- BAGIAN KEY + TOMBOL COPY -->
                                    <td class="px-6 py-4 font-mono text-[#00FFC2] flex items-center gap-3">
                                        <?= htmlspecialchars($row['key_code']) ?>
                                        <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($row['key_code']) ?>'); alert('Key berhasil di-copy: <?= htmlspecialchars($row['key_code']) ?>');" class="text-slate-500 hover:text-[#00FFC2] transition opacity-0 group-hover:opacity-100" title="Copy Key">
                                            <i data-lucide="copy" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-[10px] uppercase tracking-wider border rounded-full <?= $status_color ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-400 text-xs"><?= $row['expires_at'] ?? 'Lifetime' ?></td>
                                    <td class="px-6 py-4 text-slate-400 text-xs"><?= $dev_count ?> / <?= $row['max_devices'] ?> slots</td>
                                    <td class="px-6 py-4 text-slate-500 text-xs"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus key ini?')" class="text-slate-600 hover:text-red-400 opacity-0 group-hover:opacity-100 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">
                                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-3 opacity-50"></i>
                                        Belum ada lisensi pada filter ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-8 flex justify-between items-center text-[10px] text-slate-600">
                <div>&copy; 2026 CIPXIT NEW</div>
                <div class="flex gap-2 items-center">
                    <i data-lucide="shield" class="w-3 h-3"></i> License control panel &middot; v1.0.0
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL -->
    <div id="generateModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-[#0E1614] border border-[#1F2E2A] w-full max-w-md rounded-2xl p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-white flex items-center gap-2"><i data-lucide="key" class="text-[#00FFC2]"></i> Buat Lisensi Baru</h3>
                <button onclick="document.getElementById('generateModal').classList.add('hidden')" class="text-slate-500 hover:text-white"><i data-lucide="x"></i></button>
            </div>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-xs text-slate-400 mb-2">Masa Aktif (Hari)</label>
                    <select name="duration" class="w-full bg-[#070B0A] border border-[#1F2E2A] rounded-lg p-3 text-sm text-white focus:border-[#00FFC2] outline-none">
                        <option value="1">1 Hari</option>
                        <option value="7">7 Hari</option>
                        <option value="30" selected>30 Hari</option>
                        <option value="0">Lifetime</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-xs text-slate-400 mb-2">Max Devices</label>
                    <input type="number" name="max_devices" value="1" min="1" class="w-full bg-[#070B0A] border border-[#1F2E2A] rounded-lg p-3 text-sm text-white focus:border-[#00FFC2] outline-none">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Batal</button>
                    <button type="submit" name="generate" class="bg-[#00FFC2] text-black font-semibold px-6 py-2 rounded-lg text-sm hover:bg-[#00d6a3]">Generate</button>
                </div>
            </form>
        </div>
    </div>
    <script> lucide.createIcons(); </script>
</body>
</html>
