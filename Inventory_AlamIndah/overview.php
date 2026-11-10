<?php

require __DIR__ . '/includes/bootstrap.php';
require_login();
[$from, $to] = range_from_request();
$range = (int) ($_GET['range'] ?? 0);
if (in_array($range, [7, 30, 90], true)) {
  $from = date('Y-m-d', strtotime("$to -" . ($range - 1) . ' days'));
} else {
  $range = (int) round((strtotime($to) - strtotime($from)) / 86400) + 1;
}
$cat = (int) ($_GET['cat'] ?? 0);
$cw = $cat ? ' AND it.category_id = ' . $cat : '';
$nDays = max(1, $range);

$tot = row("SELECT COALESCE(SUM(stock),0) s, COALESCE(SUM(stock*price_per_ton),0) v FROM items it WHERE TRUE $cw");
$mv = row("SELECT COALESCE(SUM(CASE WHEN t.type='in' THEN i.qty_change END),0) masuk, COALESCE(-SUM(CASE WHEN t.type='out' THEN i.qty_change END),0) keluar,
           COALESCE(SUM(i.qty_change),0) net, COUNT(DISTINCT t.id) FILTER (WHERE t.type='in') n_in, COUNT(DISTINCT t.id) FILTER (WHERE t.type='out') n_out
           FROM stock_transaction_items i JOIN stock_transactions t ON t.id=i.transaction_id JOIN items it ON it.id=i.item_id
           WHERE t.trans_date BETWEEN ? AND ? $cw", [$from, $to]);
$start = (float) $tot['s'] - (float) $mv['net'];
$pct = $start > 0 ? ((float) $mv['net'] / $start * 100) : 0;
$daily = rows("SELECT t.trans_date d, COALESCE(SUM(CASE WHEN t.type='in' THEN i.qty_change END),0) masuk, COALESCE(-SUM(CASE WHEN t.type='out' THEN i.qty_change END),0) keluar
           FROM stock_transaction_items i JOIN stock_transactions t ON t.id=i.transaction_id JOIN items it ON it.id=i.item_id
           WHERE t.trans_date BETWEEN ? AND ? $cw GROUP BY 1", [$from, $to]);
$byDay = array_column($daily, null, 'd');
$days = [];
for ($d = strtotime($from); $d <= strtotime($to); $d += 86400) {
  $k = date('Y-m-d', $d);
  $days[] = [$k, (float) ($byDay[$k]['masuk'] ?? 0), (float) ($byDay[$k]['keluar'] ?? 0)];
}
$maxv = max(10, ...array_map(fn($x) => max($x[1], $x[2]), $days));
$step = max(5, ceil($maxv / 4 / 5) * 5);
$top = $step * 4;
$cats = rows("SELECT c.name, SUM(it.stock) s FROM categories c JOIN items it ON it.category_id=c.id WHERE TRUE $cw GROUP BY c.id,c.name HAVING SUM(it.stock)>0 ORDER BY s DESC");
$lowAll = rows("SELECT it.* FROM items it WHERE it.stock < it.min_stock $cw ORDER BY it.stock/NULLIF(it.min_stock,0) ASC");
$empties = array_values(array_filter($lowAll, fn($r) => $r['stock'] <= 0));
$low = array_slice($lowAll, 0, 5);
$recent = rows("SELECT t.type,t.reference,t.created_at,i.qty_change,it.name FROM stock_transactions t JOIN stock_transaction_items i ON i.transaction_id=t.id JOIN items it ON it.id=i.item_id WHERE TRUE $cw ORDER BY t.created_at DESC LIMIT 5");
$catOpts = rows("SELECT id,name FROM categories ORDER BY name");
[$vv, $vu] = rp_short((float) $tot['v']);
$colors = ['#ff4f00', '#334155', '#0b5a80', '#ffb36d', '#5d789a', '#0a8fd8', '#a8b9cf', '#fde9bb'];
$sum = max(1, array_sum(array_column($cats, 's')));
$acc = 0;
$stops = [];
foreach ($cats as $i => $c) {
  $a = $acc / $sum * 100;
  $acc += $c['s'];
  $stops[] = $colors[$i % 8] . " {$a}% " . ($acc / $sum * 100) . '%';
}
$nLow = count($lowAll);
$nMenipis = $nLow - count($empties);
$emptyNames = implode(', ', array_map(fn($r) => $r['name'], $empties));

render_header(['title' => 'Overview', 'active' => 'overview']); ?>
<div class="page-head">
  <div>
    <h1 class="page-title">Overview Inventory Management</h1>
    <p class="page-sub">Ringkasan pergerakan stok batu alam dan status pengiriman</p>
  </div>
  <form class="head-actions" method="get"><?= date_picker($from, $to) ?>
    <label class="chip"><select name="cat" data-auto-submit>
        <option value="0">Semua Kategori</option><?php foreach ($catOpts as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $cat === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select><?= ico('caret-down', false, 'caret') ?></label>
    <input type="hidden" name="from" value="<?= e($from) ?>"><input type="hidden" name="to" value="<?= e($to) ?>">
  </form>
</div>

<?php if ($nLow): ?>
  <div class="alert al-warn" role="alert"><?= ico('warning', true, 'lead') ?>
    <div><b><?= $nLow ?> barang membutuhkan perhatian</b>
      <p><?= $nMenipis ?> barang di bawah batas
        minimum<?= $empties ? ' dan ' . count($empties) . ' barang kosong (' . e($emptyNames) . ')' : '' ?>. Lakukan
        penerimaan stok agar pengiriman tidak tertunda.</p>
    </div>
    <button class="x" data-dismiss aria-label="Tutup"><?= ico('x') ?></button>
  </div><?php endif; ?>

<div class="quick"><span class="muted">Aksi cepat</span>
  <?php if (can('txn.create')): ?><a class="btn btn-primary"
      href="<?= url('incoming.php') ?>"><?= ico('arrow-circle-down') ?>Catat Barang Masuk</a>
    <a class="btn btn-secondary" href="<?= url('outgoing.php') ?>"><?= ico('arrow-circle-up') ?>Catat Barang
      Keluar</a><?php endif; ?>
  <?php if (can('items.create')): ?><a class="btn btn-secondary"
      href="<?= url('item_form.php') ?>"><?= ico('plus') ?>Tambah Barang</a><?php endif; ?>
  <a class="btn btn-secondary" href="<?= url('reports.php') ?>"><?= ico('book-open-text') ?>Lihat Laporan</a>
</div>

<div class="grid g4">
  <div class="card metric">
    <div class="card-head">Total Stok Tersedia</div>
    <div class="m-val">
      <div class="m-row"><?= qty($tot['s']) ?><small>Ton</small></div>
      <div class="m-trend <?= $mv['net'] >= 0 ? 'pos' : 'neg' ?>">
        <?= ico($mv['net'] >= 0 ? 'trend-up' : 'trend-down') ?><?= number_format(abs($pct), 1, ',', '.') ?>%
        (<?= e(signed_ton($mv['net'])) ?>) dari <?= $nDays ?> hari lalu</div>
    </div>
  </div>
  <div class="card metric">
    <div class="card-head">Total Barang Masuk</div>
    <div class="m-val">
      <div class="m-row"><?= qty($mv['masuk']) ?><small>Ton</small></div>
      <div class="m-trend pos"><?= ico('trend-up') ?><?= (int) $mv['n_in'] ?> penerimaan dalam <?= $nDays ?> hari</div>
    </div>
  </div>
  <div class="card metric">
    <div class="card-head">Total Barang Keluar</div>
    <div class="m-val">
      <div class="m-row"><?= qty($mv['keluar']) ?><small>Ton</small></div>
      <div class="m-trend neg"><?= ico('trend-down') ?><?= (int) $mv['n_out'] ?> pengeluaran dalam <?= $nDays ?> hari
      </div>
    </div>
  </div>
  <div class="card metric">
    <div class="card-head">Nilai Inventori</div>
    <div class="m-val">
      <div class="m-row">Rp <?= $vv ?><small><?= $vu ?></small></div>
    </div>
  </div>
</div>

<div class="grid g-chart">
  <div class="card">
    <div class="card-head">Pergerakan Stok <?= $nDays ?> Hari</div>
    <div class="card-body">
      <div class="head-actions" style="justify-content:space-between;margin-bottom:12px">
        <div class="legend"><span><i class="dot" style="background:var(--brand)"></i>Barang Masuk
            <b><?= qty($mv['masuk']) ?> Ton</b></span><span><i class="dot" style="background:#334155"></i>Barang Keluar
            <b><?= qty($mv['keluar']) ?> Ton</b></span></div>
        <div class="seg"><?php foreach ([7, 30, 90] as $r): ?><a
              href="?<?= e(http_build_query(['range' => $r, 'to' => $to, 'cat' => $cat])) ?>"
              class="<?= $range === $r ? 'on' : '' ?>"><?= $r ?> Hari</a><?php endforeach; ?></div>
      </div>
      <div class="chart" style="margin-bottom:28px">
        <?php for ($g = 0; $g <= 4; $g++):
          $y = 8 + 196 * (1 - $g / 4); ?><span class="grid-l"
            style="top:<?= $y ?>px"></span><span class="y"
            style="top:<?= $y ?>px"><?= $g * $step ?></span><?php endfor; ?>
        <div class="plot"><?php foreach ($days as $n => [$d, $in, $out]): ?>
            <div class="col" title="<?= dt($d, 'j M') ?>: +<?= qty($in) ?> / −<?= qty($out) ?> Ton">
              <span class="bar o sm" style="height:<?= $in / $top * 100 ?>%"></span><span class="bar s sm"
                style="height:<?= $out / $top * 100 ?>%"></span>
              <?php if (count($days) <= 10 || $n % ceil(count($days) / 10) === 0): ?><span
                  class="x"><?= dt($d, 'j M') ?></span><?php endif; ?>
            </div><?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-head">Distribusi Stok per Kategori</div>
    <div class="card-body" style="display:flex;gap:20px;align-items:center;flex-wrap:wrap;justify-content:center">
      <div class="donut" style="background:conic-gradient(<?= implode(',', $stops) ?: '#ebeef3 0 100%' ?>)">
        <div class="donut-c"><b><?= qty($tot['s']) ?></b><small>Ton</small></div>
      </div>
      <div class="leg-list"><?php foreach ($cats as $i => $c): ?><span><i class="dot"
              style="background:<?= $colors[$i % 8] ?>"></i><?= e($c['name']) ?> <b><?= qty($c['s']) ?>
              Ton</b></span><?php endforeach; ?></div>
    </div>
  </div>
</div>

<div class="grid g-chart">
  <div class="card">
    <div class="card-head">Perlu Restock<a class="link-more" href="<?= url('low_stock.php') ?>">Lihat semua
        <?= ico('caret-right') ?></a></div>
    <?php if (!$low): ?>  <?= empty_state('tray', 'Semua stok aman', 'Tidak ada barang di bawah batas minimum.') ?><?php else: ?>
      <div class="table-wrap">
        <table class="t">
          <thead>
            <tr>
              <th>Barang</th>
              <th>Stok / Minimum</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($low as $r): ?>
              <tr class="click" data-href="<?= url('item_view.php?id=' . $r['id']) ?>">
                <td><?= item_cell($r) ?></td>
                <td style="padding-top:8px;padding-bottom:8px"><?= stock_cell($r['stock'], $r['min_stock']) ?></td>
                <td><?= status_badge(stock_status($r['stock'], $r['min_stock'])) ?></td>
              </tr><?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div style="height:12px"></div><?php endif; ?>
  </div>
  <div class="card">
    <div class="card-head">Aktivitas Terbaru<a class="link-more" href="<?= url('history.php') ?>">Riwayat
        <?= ico('caret-right') ?></a></div>
    <div class="card-body">
      <?php foreach ($recent as $r):
        $in = $r['qty_change'] > 0; ?>
        <div class="act-row"><span class="act-ico"
            style="background:<?= $in ? 'var(--ok-bg);color:var(--ok-fg)' : 'var(--brand-soft);color:var(--brand-text)' ?>"><?= ico($in ? 'arrow-circle-down' : 'arrow-circle-up', true) ?></span>
          <div class="t"><b><?= e($r['name']) ?></b><span class="small muted"><?= e($r['reference']) ?> ·
              <?= dt($r['created_at'], 'j M Y H:i') ?></span></div>
          <b class="<?= $in ? 'pos' : '' ?>" style="font-weight:510"><?= signed_ton($r['qty_change']) ?></b>
        </div><?php endforeach; ?>
    </div>
  </div>
</div>

<?php render_footer();