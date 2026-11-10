<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$id = (int) ($_GET['id'] ?? 0);
$c = row("SELECT * FROM categories WHERE id=?", [$id]);
if (!$c) {
    flash('Kategori tidak ditemukan.', 'err');
    redirect('categories.php');
}
if (is_post() && ($_POST['do'] ?? '') === 'delete') {
    require_can('categories.manage');
    csrf_check();
    if (!val("SELECT COUNT(*) FROM items WHERE category_id=?", [$id])) {
        q("DELETE FROM categories WHERE id=?", [$id]);
        flash('Kategori berhasil dihapus.');
        redirect('categories.php');
    }
}
$items = rows("SELECT i.*, s.name sup FROM items i JOIN suppliers s ON s.id=i.supplier_id WHERE i.category_id=? ORDER BY i.name", [$id]);
$n = count($items);
$stock = array_sum(array_column($items, 'stock'));
$val = array_sum(array_map(fn($r) => $r['stock'] * $r['price_per_ton'], $items));
$attn = count(array_filter($items, fn($r) => $r['stock'] < $r['min_stock']));
[$vv, $vu] = rp_short($val);
render_header(['title' => $c['name'], 'crumb' => 'Detail Kategori', 'active' => 'categories']); ?>
<div class="page-head">
    <div>
        <h1 class="page-title"><?= e($c['name']) ?></h1>
        <p class="page-sub">Kode <?= e(trim($c['code'])) ?> · <?= e($c['description'] ?? '') ?></p>
    </div>
    <?php if (can('categories.manage')): ?>
        <div class="head-actions"><a class="btn btn-secondary"
                href="<?= url('categories.php') ?>"><?= ico('pencil-simple') ?>Edit</a>
            <?php if ($n): ?><button class="btn btn-danger"
                    onclick="openModal('cat-block')"><?= ico('trash') ?>Hapus</button>
            <?php else: ?>
                <form method="post" data-confirm data-title="Hapus kategori <?= e($c['name']) ?>?"
                    data-text="Kategori akan dihapus permanen." data-ok="Hapus Kategori"><?= csrf_field() ?><input type="hidden"
                        name="do" value="delete"><button class="btn btn-danger"><?= ico('trash') ?>Hapus</button></form>
            <?php endif; ?>
        </div><?php endif; ?>
</div>
<div class="grid g4">
    <div class="card metric">
        <div class="card-head">Jumlah Barang</div>
        <div class="m-val">
            <div class="m-row"><?= $n ?><small>SKU</small></div>
        </div>
    </div>
    <div class="card metric">
        <div class="card-head">Total Stok</div>
        <div class="m-val">
            <div class="m-row"><?= qty($stock) ?><small>Ton</small></div>
        </div>
    </div>
    <div class="card metric">
        <div class="card-head">Nilai Persediaan</div>
        <div class="m-val">
            <div class="m-row">Rp <?= $vv ?><small><?= $vu ?></small></div>
        </div>
    </div>
    <div class="card metric">
        <div class="card-head">Perlu Perhatian <?= $attn ? ico('warning', true, 'neg') : '' ?></div>
        <div class="m-val">
            <div class="m-row <?= $attn ? 'err' : '' ?>"><?= $attn ?><small>barang</small></div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-head">Barang dalam Kategori Ini<?php if (can('items.create')): ?><a
                class="btn btn-secondary btn-sm" href="<?= url('item_form.php?cat=' . $id) ?>"><?= ico('plus') ?>Tambah
                Barang</a><?php endif; ?></div>
    <?php if (!$items):
        echo empty_state('cube', 'Belum ada barang', 'Kategori ini belum memiliki barang.'); else: ?>
        <div class="table-wrap">
            <table class="t">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th class="r">Stok</th>
                        <th class="r">Minimum</th>
                        <th>Pemasok</th>
                        <th class="r">Harga / Ton</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $r):
                        $s = stock_status($r['stock'], $r['min_stock']); ?>
                        <tr class="click" data-href="<?= url('item_view.php?id=' . $r['id']) ?>">
                            <td><?= item_cell($r) ?></td>
                            <td class="r <?= $s === 'habis' ? 'neg' : ($s === 'menipis' ? 'warn' : '') ?>">
                                <?= ton($r['stock']) ?></td>
                            <td class="r"><?= ton($r['min_stock']) ?></td>
                            <td><?= e($r['sup']) ?></td>
                            <td class="r"><?= rp($r['price_per_ton']) ?></td>
                            <td><?= status_badge($s) ?></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="height:8px"></div><?php endif; ?>
</div>
<div class="overlay" id="cat-block" hidden>
    <div class="modal">
        <div class="m-ico m-blue"><?= ico('info', true) ?></div>
        <h3>Kategori tidak dapat dihapus</h3>
        <p class="d">Kategori <?= e($c['name']) ?> masih memiliki <?= $n ?> barang. Pindahkan barang ke kategori lain
            terlebih dahulu sebelum menghapus.</p>
        <div class="acts"><a class="btn btn-secondary" href="<?= url('items.php?cat=' . $id) ?>">Lihat Barang</a><a
                class="btn btn-primary" href="<?= url('items.php?cat=' . $id) ?>">Pindahkan Barang</a></div>
    </div>
</div>
<?php render_footer();