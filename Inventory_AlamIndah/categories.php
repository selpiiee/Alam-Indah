<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$errors = [];
$modal = null;
$form = ['id' => 0, 'name' => '', 'code' => '', 'description' => ''];
if (is_post()) {
    require_can('categories.manage');
    csrf_check();
    if (($_POST['do'] ?? '') === 'delete') {
        $id = (int) $_POST['id'];
        $n = (int) val("SELECT COUNT(*) FROM items WHERE category_id=?", [$id]);
        if ($n)
            flash('Kategori masih memiliki barang dan tidak dapat dihapus.', 'err');
        else {
            q("DELETE FROM categories WHERE id=?", [$id]);
            flash('Kategori berhasil dihapus.');
        }
        redirect('categories.php');
    }
    $form = ['id' => (int) ($_POST['id'] ?? 0), 'name' => trim($_POST['name'] ?? ''), 'code' => strtoupper(trim($_POST['code'] ?? '')), 'description' => trim($_POST['description'] ?? '')];
    $modal = $form['id'] ? 'edit' : 'add';
    if ($form['name'] === '')
        $errors['name'] = 'Nama kategori wajib diisi';
    elseif (val("SELECT COUNT(*) FROM categories WHERE LOWER(name)=LOWER(?) AND id<>?", [$form['name'], $form['id']]))
        $errors['name'] = 'Nama kategori sudah digunakan';
    if (!preg_match('/^[A-Z]{3}$/', $form['code']))
        $errors['code'] = 'Kode harus 3 huruf, contoh: AND';
    elseif (val("SELECT COUNT(*) FROM categories WHERE code=? AND id<>?", [$form['code'], $form['id']]))
        $errors['code'] = 'Kode sudah digunakan';
    if (!$errors) {
        if ($form['id'])
            q("UPDATE categories SET name=?,code=?,description=? WHERE id=?", [$form['name'], $form['code'], $form['description'] ?: null, $form['id']]);
        else
            q("INSERT INTO categories(name,code,description) VALUES (?,?,?)", [$form['name'], $form['code'], $form['description'] ?: null]);
        flash('Kategori “' . e($form['name']) . '” berhasil disimpan');
        redirect('categories.php');
    }
}
$qq = trim($_GET['q'] ?? '');
$o = $_GET['o'] ?? 'name-asc';
$page = max(1, (int) ($_GET['page'] ?? 1));
$ORD = ['name' => 'c.name', 'n' => 'n', 'stock' => 'stock', 'value' => 'value'];
$w = $qq !== '' ? 'WHERE c.name ILIKE ? OR c.code ILIKE ?' : '';
$p = $qq !== '' ? ["%$qq%", "%$qq%"] : [];
$base = "SELECT c.*, COUNT(i.id) n, COALESCE(SUM(i.stock),0) stock, COALESCE(SUM(i.stock*i.price_per_ton),0) value,
  COUNT(i.id) FILTER (WHERE i.stock<=0) habis, COUNT(i.id) FILTER (WHERE i.stock>0 AND i.stock<i.min_stock) menipis
  FROM categories c LEFT JOIN items i ON i.category_id=c.id $w GROUP BY c.id";
if (($_GET['export'] ?? '') === 'csv')
    csv_download('kategori.csv', ['Kategori', 'Kode', 'Jumlah Barang', 'Total Stok', 'Nilai'], array_map(fn($r) => [$r['name'], $r['code'], $r['n'], $r['stock'], $r['value']], rows("$base ORDER BY " . order_by($ORD, 'name-asc'), $p)));
$total = count(rows($base, $p));
$data = rows("$base ORDER BY " . order_by($ORD, 'name-asc') . ' LIMIT 10 OFFSET ' . (($page - 1) * 10), $p);
$exp = '?' . http_build_query(array_merge($_GET, ['export' => 'csv']));

function cat_alert(array $r): string
{
    if ($r['habis'] && $r['menipis'])
        return '<span class="badge b-err">' . $r['habis'] . ' habis · ' . $r['menipis'] . ' menipis ' . ico('x-circle', true) . '</span>';
    if ($r['habis'])
        return '<span class="badge b-err">' . $r['habis'] . ' habis ' . ico('x-circle', true) . '</span>';
    if ($r['menipis'])
        return '<span class="badge b-warn">' . $r['menipis'] . ' menipis ' . ico('warning', true) . '</span>';
    return '<span class="muted">Aman</span>';
}
render_header(['title' => 'Kategori', 'active' => 'categories', 'export' => $exp]); ?>
<div class="page-head">
    <div>
        <h1 class="page-title">Kategori</h1>
        <p class="page-sub">Kelompokkan barang batu alam berdasarkan jenis</p>
    </div>
    <div class="head-actions"><?php if (can('categories.manage')): ?><button class="btn btn-primary"
                data-cat-add><?= ico('plus') ?>Tambah Kategori</button><?php endif; ?></div>
</div>
<div class="card">
    <form class="toolbar" method="get"><label class="search"><?= ico('magnifying-glass') ?><input name="q"
                value="<?= e($qq) ?>" placeholder="Cari kategori..." aria-label="Cari kategori"></label>
        <label class="chip"><select name="o"
                data-auto-submit><?php foreach (['name-asc' => 'Nama A–Z', 'name-desc' => 'Nama Z–A', 'stock-desc' => 'Stok tertinggi', 'value-desc' => 'Nilai tertinggi'] as $k => $l): ?>
                    <option value="<?= $k ?>" <?= $o === $k ? 'selected' : '' ?>>Urutkan: <?= $l ?></option>
                <?php endforeach; ?>
            </select><?= ico('caret-down', false, 'caret') ?></label>
    </form>
    <div class="table-wrap">
        <table class="t">
            <thead>
                <tr>
                    <th><?= sort_link('Kategori', 'name') ?></th>
                    <th class="r"><?= sort_link('Jumlah Barang', 'n') ?></th>
                    <th class="r"><?= sort_link('Total Stok', 'stock') ?></th>
                    <th class="r"><?= sort_link('Nilai Persediaan', 'value') ?></th>
                    <th>Peringatan Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $r): ?>
                    <tr class="click" data-href="<?= url('category_view.php?id=' . $r['id']) ?>">
                        <td><?= e($r['name']) ?></td>
                        <td class="r"><?= $r['n'] ?> barang</td>
                        <td class="r"><?= ton($r['stock']) ?></td>
                        <td class="r"><?= rp($r['value']) ?></td>
                        <td><?= cat_alert($r) ?></td>
                        <td><span style="display:flex;gap:10px"><a class="icon-act"
                                    href="<?= url('category_view.php?id=' . $r['id']) ?>"
                                    aria-label="Lihat"><?= ico('eye') ?></a>
                                <?php if (can('categories.manage')): ?><button class="icon-act" aria-label="Edit"
                                        data-cat-edit='<?= e(json_encode(['id' => $r['id'], 'name' => $r['name'], 'code' => trim($r['code']), 'description' => $r['description']])) ?>'><?= ico('pencil-simple') ?></button>
                                    <?php if ($r['n']): ?><button class="icon-act del" aria-label="Hapus"
                                            data-cat-block='<?= e(json_encode(['id' => $r['id'], 'name' => $r['name'], 'n' => $r['n']])) ?>'><?= ico('trash') ?></button>
                                    <?php else: ?>
                                        <form method="post" data-confirm data-title="Hapus kategori <?= e($r['name']) ?>?"
                                            data-text="Kategori akan dihapus permanen." data-ok="Hapus Kategori">
                                            <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden"
                                                name="id" value="<?= $r['id'] ?>"><button class="icon-act del"
                                                aria-label="Hapus"><?= ico('trash') ?></button></form><?php endif; endif; ?>
                            </span></td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $data ? pagination($total, $page, 10, 'kategori') : empty_state('magnifying-glass', 'Kategori tidak ditemukan', 'Tidak ada kategori yang cocok dengan pencarian.', 'Hapus Filter', url('categories.php')) ?>
</div>

<?php if (can('categories.manage')): ?>
    <div class="overlay" id="cat-modal" hidden <?= $modal ? 'data-autoopen' : '' ?>>
        <form class="modal" method="post" novalidate style="padding:20px;width:min(500px,100%)"><?= csrf_field() ?><input
                type="hidden" name="id" id="c-id" value="<?= $form['id'] ?>">
            <h3 id="c-title" style="font-size:16px"><?= $modal === 'edit' ? 'Edit Kategori' : 'Tambah Kategori' ?></h3>
            <p class="d" id="c-sub" style="margin-bottom:12px">
                <?= $modal === 'edit' ? 'Perbarui nama atau deskripsi kategori.' : 'Buat kategori baru untuk mengelompokkan barang.' ?>
            </p>
            <div class="stack" style="gap:14px">
                <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>"><label>Nama Kategori<span
                            class="req">*</span></label><input class="input" name="name" id="c-name"
                        value="<?= e($form['name']) ?>"
                        placeholder="Isi di sini"><?= field_error($errors['name'] ?? null) ?></div>
                <div class="field<?= isset($errors['code']) ? ' has-error' : '' ?>"><label>Kode Kategori<span
                            class="req">*</span></label><input class="input" name="code" id="c-code" maxlength="3"
                        value="<?= e($form['code']) ?>" placeholder="Isi di sini"
                        style="text-transform:uppercase"><?= field_error($errors['code'] ?? null) ?: '<span class="hint">3 huruf, contoh: AND</span>' ?>
                </div>
                <div class="field"><label>Deskripsi</label><textarea class="textarea" name="description" id="c-desc"
                        placeholder="Tulis keterangan..."><?= e($form['description']) ?></textarea></div>
            </div>
            <div class="acts"><button type="button" class="btn btn-secondary" data-close>Batal</button><button
                    class="btn btn-primary"><?= ico('check') ?><span
                        id="c-ok"><?= $modal === 'edit' ? 'Simpan Perubahan' : 'Simpan Kategori' ?></span></button></div>
        </form>
    </div>
    <div class="overlay" id="cat-block" hidden>
        <div class="modal">
            <div class="m-ico m-blue"><?= ico('info', true) ?></div>
            <h3>Kategori tidak dapat dihapus</h3>
            <p class="d" id="b-text"></p>
            <div class="acts"><a class="btn btn-secondary" id="b-view" href="#">Lihat Barang</a><a class="btn btn-primary"
                    id="b-move" href="#">Pindahkan Barang</a></div>
        </div>
    </div>
    <script>
        (() => {
            const $ = (s) => document.querySelector(s), set = (t, s, ok, d) => { $('#c-title').textContent = t; $('#c-sub').textContent = s; $('#c-ok').textContent = ok; $('#c-id').value = d.id || 0; $('#c-name').value = d.name || ''; $('#c-code').value = d.code || ''; $('#c-desc').value = d.description || ''; document.querySelectorAll('#cat-modal .has-error').forEach(e => e.classList.remove('has-error')); document.querySelectorAll('#cat-modal .err-msg').forEach(e => e.remove()); openModal('cat-modal'); };
            document.querySelector('[data-cat-add]')?.addEventListener('click', () => set('Tambah Kategori', 'Buat kategori baru untuk mengelompokkan barang.', 'Simpan Kategori', {}));
            document.querySelectorAll('[data-cat-edit]').forEach(b => b.addEventListener('click', () => set('Edit Kategori', 'Perbarui nama atau deskripsi kategori.', 'Simpan Perubahan', JSON.parse(b.dataset.catEdit))));
            document.querySelectorAll('[data-cat-block]').forEach(b => b.addEventListener('click', () => { const d = JSON.parse(b.dataset.catBlock); $('#b-text').textContent = `Kategori ${d.name} masih memiliki ${d.n} barang. Pindahkan barang ke kategori lain terlebih dahulu sebelum menghapus.`; $('#b-view').href = $('#b-move').href = document.body.dataset.base + '/items.php?cat=' + d.id; openModal('cat-block'); }));
        })();
    </script>
<?php endif;
render_footer();
