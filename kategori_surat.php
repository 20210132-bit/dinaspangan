<!-- Materialize JS (WAJIB agar M.Modal berfungsi) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>


<?php
// Pastikan session admin sudah login
if (empty($_SESSION['admin'])) {
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
    header("Location: ./");
    die();
}

// Tambah kategori
if (isset($_POST['add_kategori'])) {
    $nama = mysqli_real_escape_string($config, $_POST['nama_kategori']);
    $keywords = explode(',', mysqli_real_escape_string($config, $_POST['keywords']));

    $insert_kategori = mysqli_query($config, "INSERT INTO kategori_surat (nama_kategori) VALUES ('$nama')");
    $kategori_id = mysqli_insert_id($config);

    foreach ($keywords as $keyword) {
        $keyword = trim($keyword);
        if (!empty($keyword)) {
            mysqli_query($config, "INSERT INTO kata_kunci_kategori (kategori_id, keyword) VALUES ($kategori_id, '$keyword')");
        }
    }
    header("Location: ?page=kategori_surat");
    exit();
}

// Hapus kategori
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($config, "DELETE FROM kategori_surat WHERE id=$id");
    header("Location: ?page=kategori_surat");
    exit();
}

// Edit kategori
if (isset($_POST['edit_kategori'])) {
    $id = intval($_POST['id_kategori']);
    $nama = mysqli_real_escape_string($config, $_POST['nama_kategori']);
    $keywords = explode(',', mysqli_real_escape_string($config, $_POST['keywords']));

    mysqli_query($config, "UPDATE kategori_surat SET nama_kategori='$nama' WHERE id=$id");
    mysqli_query($config, "DELETE FROM kata_kunci_kategori WHERE kategori_id=$id");
    foreach ($keywords as $keyword) {
        $keyword = trim($keyword);
        if (!empty($keyword)) {
            mysqli_query($config, "INSERT INTO kata_kunci_kategori (kategori_id, keyword) VALUES ($id, '$keyword')");
        }
    }
    header("Location: ?page=kategori_surat");
    exit();
}

// Ambil data kategori dan keyword
$query = mysqli_query($config, "SELECT * FROM kategori_surat");
?>

<h4>Manajemen Kategori Surat</h4>

<!-- Tombol Tambah -->
<a class="btn green modal-trigger" href="#addModal">
    <i class="material-icons left">add</i>Tambah Kategori
</a>


<div class="table-responsive">
    <table class="striped highlight responsive-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kategori</th>
                <th>Kata Kunci</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            while ($row = mysqli_fetch_assoc($query)):
                $keywords_query = mysqli_query($config, "SELECT keyword FROM kata_kunci_kategori WHERE kategori_id = {$row['id']}");
                $keywords = [];
                while ($k = mysqli_fetch_assoc($keywords_query)) {
                    $keywords[] = $k['keyword'];
                }
                $all_keywords = implode(', ', $keywords);
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                <td><?= htmlspecialchars($all_keywords) ?></td>
                <td>
                    <?php
                        $safe_nama = json_encode($row['nama_kategori']);
                        $safe_keywords = json_encode($all_keywords);
                        ?>
                    <button class="btn btn-small blue waves-effect waves-light" style="margin-right:5px;"
                        onclick='editKategori(<?= $row['id'] ?>, <?= $safe_nama ?>, <?= $safe_keywords ?>)'>
                        <i class="material-icons left">edit</i> Edit
                    </button>

                    <a href="?page=kategori_surat&delete=<?= $row['id'] ?>"
                        onclick="return confirm('Hapus kategori ini?')"
                        class="btn btn-small red waves-effect waves-light">
                        <i class="material-icons left">delete</i> Hapus
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <!-- Tampilan mobile - versi kartu -->
<div class="table-card-mobile">
    <?php
    mysqli_data_seek($query, 0); // reset pointer hasil query
    $no = 1;
    while ($row = mysqli_fetch_assoc($query)):
        $keywords_query = mysqli_query($config, "SELECT keyword FROM kata_kunci_kategori WHERE kategori_id = {$row['id']}");
        $keywords = [];
        while ($k = mysqli_fetch_assoc($keywords_query)) {
            $keywords[] = $k['keyword'];
        }
        $all_keywords = implode(', ', $keywords);
        $safe_nama = json_encode($row['nama_kategori']);
        $safe_keywords = json_encode($all_keywords);
    ?>
    <div class="table-card">
        <h6><?= $no++ ?>. <?= htmlspecialchars($row['nama_kategori']) ?></h6>
        <p><strong>Kata Kunci:</strong> <?= htmlspecialchars($all_keywords) ?></p>
        <div class="actions">
            <button class="btn blue waves-effect"
                onclick='editKategori(<?= $row['id'] ?>, <?= $safe_nama ?>, <?= $safe_keywords ?>)'>
                <i class="material-icons left">edit</i>Edit
            </button>
            <a href="?page=kategori_surat&delete=<?= $row['id'] ?>"
                onclick="return confirm('Hapus kategori ini?')"
                class="btn red waves-effect">
                <i class="material-icons left">delete</i>Hapus
            </a>
        </div>
    </div>
    <?php endwhile; ?>
</div>
</div>

<!-- Modal Tambah -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <h5>Tambah Kategori</h5>
        <form method="POST">
            <div class="input-field">
                <input type="text" name="nama_kategori" id="nama_kategori" required>
                <label for="nama_kategori">Nama Kategori</label>
            </div>
            <div class="input-field">
                <textarea name="keywords" id="keywords" class="materialize-textarea" required></textarea>
                <label for="keywords">Kata Kunci (pisahkan dengan koma)</label>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_kategori" class="btn green waves-effect">Simpan</button>
                <a href="#!" class="modal-close btn grey lighten-1 waves-effect">Batal</a>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h5>Edit Kategori</h5>
        <form method="POST">
            <input type="hidden" id="edit_id" name="id_kategori">
            <div class="input-field">
                <input type="text" id="edit_nama" name="nama_kategori" required>
                <label for="edit_nama">Nama Kategori</label>
            </div>
            <div class="input-field">
                <textarea id="edit_keywords" name="keywords" class="materialize-textarea" required></textarea>
                <label for="edit_keywords">Kata Kunci (pisahkan dengan koma)</label>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_kategori" class="btn blue waves-effect">Update</button>
                <a href="#!" class="modal-close btn grey lighten-1 waves-effect">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.modal');
    M.Modal.init(elems);
});

function editKategori(id, nama, keywords) {
    console.log('Opening modal for ID:', id); // DEBUG

    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_keywords').value = keywords;

    M.updateTextFields(); // agar label tetap di atas

    const instance = M.Modal.getInstance(document.getElementById('editModal'));
    instance.open();
}
</script>