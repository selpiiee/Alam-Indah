<?php
require __DIR__ . '/includes/bootstrap.php'; require_login();
const PER = 10;
$st = $_GET['status'] ?? 'all'; $q = trim($_GET['q'] ?? ''); $cat = (int)($_GET['cat'] ?? 0); $sup = (int)($_GET['sup'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1)); $state = $_GET['state'] ?? '';
$smin = ($_GET['smin'] ?? '') !== '' ? parse_num($_GET['smin']) : null; $smax = ($_GET['smax'] ?? '') !== '' ? parse_num($_GET['smax']) : null;
$ORD = ['name' => 'i.name', 'cat' => 'c.name', 'stock' => 'i.stock', 'min' => 'i.min_stock', 'sup' => 's.name', 'price' => 'i.price_per_ton'];
$err = false; $counts = ['all' => 0, 'tersedia' => 0, 'menipis' => 0, 'habis' => 0]; $data = []; $total = 0;
try {
    $c = row("SELECT COUNT(*) a, COUNT(*) FILTER (WHERE stock>=min_stock AND stock>0) t, COUNT(*) FILTER (WHERE stock<min_stock AND stock>0) m, COUNT(*) FILTER (WHERE stock<=0) h FROM items");
    $counts = ['all' => $c['a'], 'tersedia' => $c['t'], 'menipis' => $c['m'], 'habis' => $c['h']];
    $w = ['TRUE']; $p = [];
    if ($q !== '') { $w[] = '(i.name ILIKE ? OR i.sku ILIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; }
    if ($cat) { $w[] = 'i.category_id=?'; $p[] = $cat; } if ($sup) { $w[] = 'i.supplier_id=?'; $p[] = $sup; }
    if ($smin !== null) { $w[] = 'i.stock>=?'; $p[] = $smin; } if ($smax !== null) { $w[] = 'i.stock<=?'; $p[] = $smax; }
    if ($st === 'tersedia') $w[] = 'i.stock>=i.min_stock AND i.stock>0'; elseif ($st === 'menipis') $w[] = 'i.stock<i.min_stock AND i.stock>0'; elseif ($st === 'habis') $w[] = 'i.stock<=0';
    $W = implode(' AND ', $w); $from = "FROM items i JOIN categories c ON c.id=i.category_id JOIN suppliers s ON s.id=i.supplier_id WHERE $W";
    $total = (int)val("SELECT COUNT(*) $from", $p); $ob = order_by($ORD, 'name-asc');
    if (($_GET['export'] ?? '') === 'csv') {
        $all = rows("SELECT i.sku,i.name,c.name cat,i.stock,i.min_stock,s.name sup,i.price_per_ton $from ORDER BY $ob", $p);
        csv_download('data-barang.csv', ['SKU', 'Nama', 'Kategori', 'Stok (Ton)', 'Minimum (Ton)', 'Pemasok', 'Harga/Ton'], array_map(fn($r) => array_values($r), $all));
    }
    $data = rows("SELECT i.*, c.name cat, s.name sup $from ORDER BY $ob LIMIT " . PER . ' OFFSET ' . (($page - 1) * PER), $p);
    $cats = rows("SELECT id,name FROM categories ORDER BY name"); $sups = rows("SELECT id,name FROM suppliers ORDER BY name");
} catch (PDOException $ex) { $err = true; $cats = $sups = []; }
if ($state === 'error') $err = true;
$filtered = $q !== '' || $cat || $sup || $smin !== null || $smax !== null;
$qs = fn(array $o) => '?' . http_build_query(array_filter(array_merge($_GET, $o, ['page' => null]), fn($v) => $v !== null && $v !== ''));
$tab = fn($k, $l) => '<a class="tab' . ($st === $k ? ' on' : '') . '" href="' . e($qs(['status' => $k === 'all' ? null : $k])) . '">' . $l . ' (' . $counts[$k] . ')</a>';
$o = $_GET['o'] ?? 'name-asc';

render_header(['title' => 'Data Barang', 'active' => 'items', 'export' => $qs(['export' => 'csv'])]); ?>
<div class="page-head"><div><h1 class="page-title">Data Barang</h1><p class="page-sub">Kelola daftar barang batu alam, stok, dan pemasok</p></div>
  <div class="head-actions"><a class="btn btn-secondary" href="<?= e($qs(['export' => 'csv'])) ?>"><?= ico('export') ?>Export</a>
  <?php if (can('items.create')): ?><a class="btn btn-primary" href="<?= url('item_form.php') ?>"><?= ico('plus') ?>Tambah Barang</a><?php endif; ?></div></div>
<div class="tabs"><?= $tab('all', 'Semua') . $tab('tersedia', 'Tersedia') . $tab('menipis', 'Menipis') . $tab('habis', 'Habis') ?></div>

<div class="card">
  <form class="toolbar" method="get"><input type="hidden" name="status" value="<?= e($st === 'all' ? '' : $st) ?>">
    <label class="search"><?= ico('magnifying-glass') ?><input name="q" value="<?= e($q) ?>" placeholder="Cari nama atau SKU barang..." aria-label="Cari barang"></label>
    <label class="chip"><select name="cat" data-auto-submit><option value="0">Semua Kategori</option><?php foreach ($cats as $r): ?><option value="<?= $r['id'] ?>" <?= $cat == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select><?= ico('caret-down', false, 'caret') ?></label>
    <label class="chip"><select name="sup" data-auto-submit><option value="0">Semua Pemasok</option><?php foreach ($sups as $r): ?><option value="<?= $r['id'] ?>" <?= $sup == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select><?= ico('caret-down', false, 'caret') ?></label>
    <label class="chip"><select name="o" data-auto-submit><?php foreach (['name-asc' => 'Nama A–Z', 'name-desc' => 'Nama Z–A', 'stock-asc' => 'Stok terendah', 'stock-desc' => 'Stok tertinggi', 'price-desc' => 'Harga tertinggi'] as $k => $l): ?><option value="<?= $k ?>" <?= $o === $k ? 'selected' : '' ?>>Urutkan: <?= $l ?></option><?php endforeach; ?></select><?= ico('caret-down', false, 'caret') ?></label>
    <span class="grow"></span><button type="button" class="btn btn-secondary btn-sm" data-menu="adv"><?= ico('sliders-horizontal') ?>Filter Lanjutan</button>
    <div class="menu-pop" id="adv" hidden style="right:16px;top:64px;padding:14px;width:260px"><b style="color:var(--heading)">Rentang stok (Ton)</b>
      <div style="display:flex;gap:8px;margin:10px 0"><input class="input" name="smin" placeholder="Min" value="<?= e($_GET['smin'] ?? '') ?>"><input class="input" name="smax" placeholder="Maks" value="<?= e($_GET['smax'] ?? '') ?>"></div>
      <button class="btn btn-primary btn-sm" style="width:100%">Terapkan</button></div>
  </form>
  <div class="table-wrap"><table class="t"><thead><tr>
    <th><?= sort_link('Barang', 'name') ?></th><th><?= sort_link('Kategori', 'cat') ?></th><th class="r"><?= sort_link('Stok', 'stock') ?></th><th class="r"><?= sort_link('Minimum', 'min') ?></th><th><?= sort_link('Pemasok', 'sup') ?></th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if ($state === 'loading'): echo skeleton_rows(8, 7); elseif (!$err): foreach ($data as $r): $s = stock_status($r['stock'], $r['min_stock']); ?>
      <tr class="click" data-href="<?= url('item_view.php?id=' . $r['id']) ?>"><td><?= item_cell($r) ?></td><td><?= e($r['cat']) ?></td>
        <td class="r <?= $s !== 'tersedia' ? ($s === 'habis' ? 'neg' : 'warn') : '' ?>"><?= ton($r['stock']) ?></td><td class="r"><?= ton($r['min_stock']) ?></td><td><?= e($r['sup']) ?></td><td><?= status_badge($s) ?></td>
        <td><span style="display:flex;gap:10px"><a class="icon-act" href="<?= url('item_view.php?id=' . $r['id']) ?>" title="Lihat" aria-label="Lihat"><?= ico('eye') ?></a>
          <?php if (can('items.edit')): ?><a class="icon-act" href="<?= url('item_form.php?id=' . $r['id']) ?>" title="Edit" aria-label="Edit"><?= ico('pencil-simple') ?></a><?php endif; ?>
          <?php if (can('items.delete')): ?><form method="post" action="<?= url('item_delete.php') ?>" data-confirm data-title="Hapus <?= e($r['name']) ?>?" data-text="Barang beserta riwayat stoknya akan dihapus permanen dan tidak dapat dikembalikan. Stok saat ini <?= ton($r['stock']) ?>." data-ok="Hapus Barang"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="icon-act del" title="Hapus" aria-label="Hapus"><?= ico('trash') ?></button></form><?php endif; ?></span></td></tr>
    <?php endforeach; endif; ?></tbody></table></div>
  <?php if ($state === 'loading'): ?><div class="t-foot"><span>Memuat data barang...</span></div>
  <?php elseif ($err): echo empty_state('warning-circle', 'Gagal memuat data', 'Daftar barang tidak dapat dimuat saat ini. Periksa koneksi Anda lalu coba lagi.', 'Coba Lagi', url('items.php'), true);
  elseif (!$data): echo empty_state('magnifying-glass', 'Barang tidak ditemukan', $filtered ? 'Tidak ada barang yang cocok dengan ' . ($q !== '' ? '“' . e($q) . '”. Periksa ejaan atau hapus filter.' : 'filter yang dipilih. Hapus filter untuk melihat semua barang.') : 'Belum ada barang terdaftar.', $filtered ? 'Hapus Filter' : '', url('items.php'));
  else: echo pagination($total, $page, PER, 'barang'); endif; ?>
</div>
<?php render_footer();
