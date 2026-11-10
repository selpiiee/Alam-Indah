<?php

function nav_def(): array
{
    return [
        'MENU' => [
            ['overview', 'Overview', 'overview.php', 'grid-four'],
            ['items', 'Data Barang', 'items.php', 'cube'],
            ['categories', 'Kategori', 'categories.php', 'tag'],
            ['suppliers', 'Supplier', 'suppliers.php', 'buildings'],
        ],
        'TRANSAKSI' => [
            ['incoming', 'Barang Masuk', 'incoming.php', 'arrow-circle-down', 'txn.create'],
            ['outgoing', 'Barang Keluar', 'outgoing.php', 'arrow-circle-up', 'txn.create'],
            ['history', 'Riwayat Stok', 'history.php', 'arrows-clockwise'],
            ['lowstock', 'Stok Menipis', 'low_stock.php', 'warning', null, true],
        ],
        'ANALISIS' => [
            ['reports', 'Laporan', 'reports.php', 'book-open-text'],
        ],
        'ADMIN' => [
            ['users', 'Pengguna', 'users.php', 'users', 'users.manage'],
            ['warehouse', 'Pengaturan Gudang', 'warehouse.php', 'warehouse', 'warehouse.manage'],
        ],
    ];
}

function render_header(array $o): void
{
    $title = $o['title'] ?? 'Alam Indah';
    $active = $o['active'] ?? '';
    $crumb = $o['crumb'] ?? $title;
    $u = user();
    $ws = val("SELECT value FROM settings WHERE key='warehouse'") ?: 'Kota Malang';
    $company = val("SELECT value FROM settings WHERE key='company'") ?: 'Alam Indah';
    $low = low_stock_count();
    $export = $o['export'] ?? null;
    ?><!doctype html>
    <html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= e($title) ?> · Alam Indah</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Geist:wght@400;500;600&display=swap">
        <link rel="stylesheet" href="<?= url('assets/vendor/phosphor/regular/style.css') ?>">
        <link rel="stylesheet" href="<?= url('assets/vendor/phosphor/fill/style.css') ?>">
        <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
    </head>

    <body data-base="<?= e(BASE) ?>">
        <aside class="sidebar" id="sidebar">
            <div>
                <div class="ws"><span class="ws-logo"><?= ico('mountains', true) ?></span>
                    <span
                        class="ws-text"><small><?= e($company) ?></small><b><?= e($ws) ?></b></span><?= ico('caret-up-down', false, 'muted') ?>
                </div>
                <form class="side-search" action="<?= url('items.php') ?>" role="search">
                    <?= ico('magnifying-glass') ?><input name="q" placeholder="Search" aria-label="Cari barang"
                        id="gsearch"><kbd><?= ico('command', true) ?>F</kbd>
                </form>
                <nav class="nav">
                    <?php foreach (nav_def() as $label => $items):
                        $vis = array_filter($items, fn($i) => empty($i[4]) || can($i[4]));
                        if (!$vis)
                            continue; ?>
                        <div class="nav-group">
                            <div class="nav-label"><?= $label ?></div>
                            <?php foreach ($vis as $i): ?>
                                <a class="nav-item<?= $active === $i[0] ? ' active' : '' ?>" href="<?= url($i[2]) ?>">
                                    <?= ico($i[3], true) ?><span><?= $i[1] ?></span>
                                    <?php if (!empty($i[5]) && $low > 0): ?><em class="nav-count"
                                            style="font-style:normal"><?= $low ?></em><?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="side-foot">
                <a class="nav-item<?= $active === 'profile' ? ' active' : '' ?>"
                    href="<?= url('profile.php') ?>"><?= ico('gear', true) ?><span>Pengaturan</span></a>
                <a class="nav-item" href="<?= url('logout.php') ?>"><?= ico('sign-out') ?><span>Log Out</span></a>
            </div>
        </aside>
        <div class="main">
            <header class="topbar">
                <div class="crumb">
                    <button class="btn btn-icon burger" id="burger" aria-label="Menu"><?= ico('list') ?></button>
                    <span>Main Menu</span><?= ico('caret-right') ?>
                    <span class="crumb-cur"><?= ico('squares-four', true) ?><?= e($crumb) ?></span>
                </div>
                <div class="top-actions">
                    <?php if ($export): ?>
                        <a class="btn btn-secondary btn-sm" href="<?= e($export) ?>"><?= ico('export') ?>Export</a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-sm" onclick="window.print()"><?= ico('export') ?>Export</button>
                    <?php endif; ?>
                    <button class="btn btn-icon" data-menu="topmenu" aria-label="Lainnya"
                        style="width:36px;height:36px"><?= ico('dots-three') ?></button>
                    <div class="menu-pop" id="topmenu" hidden>
                        <div style="padding:8px 10px;border-bottom:1px solid var(--divider);margin-bottom:4px"><b
                                style="color:var(--heading);font-weight:590"><?= e($u['name']) ?></b>
                            <div class="small muted"><?= e(ROLE_LABEL[$u['role']]) ?></div>
                        </div>
                        <button onclick="window.print()"><?= ico('printer') ?>Cetak halaman</button>
                        <a href="<?= url('profile.php') ?>"><?= ico('user-circle') ?>Profil saya</a>
                        <a href="<?= url('logout.php') ?>"><?= ico('sign-out') ?>Keluar</a>
                    </div>
                </div>
            </header>
            <main class="content">
                <?php
}

function render_footer(): void
{
    ?>
            </main>
        </div>
        <div class="toast-wrap" id="toasts">
            <?php foreach (($_SESSION['flash'] ?? []) as $f): ?>
                <div class="toast" role="status">
                    <?= $f['t'] === 'ok' ? ico('check-circle', true, 'ok') : ico('warning-circle', true, 'bad') ?><span><?= $f['m'] /* already escaped by caller */ ?></span><button
                        aria-label="Tutup"><?= ico('x') ?></button></div>
            <?php endforeach;
            unset($_SESSION['flash']); ?>
        </div>
        <script src="<?= url('assets/js/app.js') ?>"></script>
    </body>

    </html>
    <?php
}

function render_forbidden(): void
{
    render_header(['title' => 'Akses ditolak', 'crumb' => 'Akses ditolak']);
    echo '<div class="card"><div class="empty"><span class="e-ico red">' . ico('lock-key', true) . '</span><b>Akses ditolak</b><p>Peran ' . e(ROLE_LABEL[user()['role']] ?? '') . ' tidak memiliki izin untuk membuka halaman atau melakukan aksi ini.</p><a class="btn btn-secondary" href="' . url('overview.php') . '">Kembali ke Overview</a></div></div>';
    render_footer();
}

function empty_state(string $icon, string $title, string $text, string $btn = '', string $href = '', bool $red = false): string
{
    return '<div class="empty"><span class="e-ico' . ($red ? ' red' : '') . '">' . ico($icon, $red) . '</span><b>' . e($title) . '</b><p>' . $text . '</p>'
        . ($btn ? '<a class="btn btn-secondary btn-sm" href="' . e($href) . '">' . e($btn) . '</a>' : '') . '</div>';
}
function alert(string $type, string $title, string $text, string $icon = ''): string
{
    $icons = ['err' => 'x-circle', 'warn' => 'warning', 'info' => 'info', 'ok' => 'check-circle'];
    return '<div class="alert al-' . $type . '" role="alert">' . ico($icons[$type] ?? 'info', true, 'lead') . '<div><b>' . e($title) . '</b><p>' . $text . '</p></div></div>';
}
function field_error(?string $msg): string
{
    return $msg ? '<span class="err-msg">' . ico('warning-circle', true) . e($msg) . '</span>' : '';
}
function skeleton_rows(int $n = 8, int $cols = 7): string
{
    $h = '';
    for ($i = 0; $i < $n; $i++) {
        $h .= '<tr><td><div class="cell-prod"><span class="sk c"></span><span class="sk" style="width:90px"></span></div></td>';
        for ($c = 1; $c < $cols; $c++)
            $h .= '<td><span class="sk" style="width:' . (50 + ($c * 13) % 40) . 'px"></span></td>';
        $h .= '</tr>';
    }
    return $h;
}

function date_picker(string $from, string $to): string
{
    [, $ref] = default_range();
    return '<div class="dp" data-from="' . e($from) . '" data-to="' . e($to) . '" data-ref="' . e($ref) . '"><button type="button" class="chip dpb" data-dp-toggle>'
        . ico('calendar-blank') . e(range_label($from, $to)) . ico('caret-down') . '</button><div class="dp-pop" hidden></div></div>';
}
