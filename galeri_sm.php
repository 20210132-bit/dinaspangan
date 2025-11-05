<?php
// cek session
if (empty($_SESSION['admin'])) {
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
    header("Location: ./");
    die();
} else {

    if (isset($_REQUEST['act'])) {
        $act = $_REQUEST['act'];
        switch ($act) {
            case 'fsm':
                include "file_sm.php";
                break;
        }
    } else {
        // pagging
        $limit = 8;
        $pg = @$_GET['pg'];
        if (empty($pg)) {
            $curr = 0;
            $pg = 1;
        } else {
            $curr = ($pg - 1) * $limit;
        }
?>

<!-- Row Start -->
<div class="row">
    <!-- Secondary Nav START -->
    <div class="col s12">
        <div class="z-depth-1">
            <nav class="secondary-nav">
                <div class="nav-wrapper blue-grey darken-1">
                    <div class="col m12">
                        <ul class="left">
                            <li class="waves-effect waves-light">
                                <a href="?page=gsm" class="judul">
                                    <i class="material-icons">image</i> Galeri File Surat Masuk
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Secondary Nav END -->
</div>
<!-- Row END -->

<!-- Row form Start -->
<div class="row jarak-form">
    <?php
            if (isset($_REQUEST['submit'])) {

                $dari_tanggal = $_REQUEST['dari_tanggal'];
                $sampai_tanggal = $_REQUEST['sampai_tanggal'];

                if ($dari_tanggal == "" || $sampai_tanggal == "") {
                    header("Location: ./admin.php?page=gsm");
                    die();
                } else {
                    $query = mysqli_query($config, "SELECT * FROM tbl_surat_masuk WHERE tgl_diterima BETWEEN '$dari_tanggal' AND '$sampai_tanggal' ORDER By id_surat DESC LIMIT 10");
            ?>
    <!-- Row form Start -->
    <div class="row jarak-form black-text">
        <form class="col s12" method="post" action="">
            <div class="input-field col s3">
                <i class="material-icons prefix md-prefix">date_range</i>
                <input id="dari_tanggal" type="text" name="dari_tanggal" required>
                <label for="dari_tanggal">Dari Tanggal</label>
            </div>
            <div class="input-field col s3">
                <i class="material-icons prefix md-prefix">date_range</i>
                <input id="sampai_tanggal" type="text" name="sampai_tanggal" required>
                <label for="sampai_tanggal">Sampai Tanggal</label>
            </div>
            <div class="col s6">
                <button type="submit" name="submit" class="btn-large blue waves-effect waves-light">
                    FILTER <i class="material-icons">filter_list</i>
                </button>&nbsp;&nbsp;
                <button type="reset" onclick="window.history.back()"
                    class="btn-large deep-orange waves-effect waves-light">
                    RESET <i class="material-icons">refresh</i>
                </button>
            </div>
        </form>
    </div>
    <!-- Row form END -->

    <div class="row agenda">
        <div class="col s12">
            <p class="warna agenda">
                Galeri file surat masuk antara tanggal
                <strong><?php echo indoDate($dari_tanggal); ?></strong> sampai dengan tanggal
                <strong><?php echo indoDate($sampai_tanggal); ?></strong>
            </p>
        </div>
    </div>

    <?php
                    if (mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_array($query)) {
                            if (!empty($row['file'])) {
                                $ekstensi  = array('jpg', 'png', 'jpeg');
                                $ekstensi2 = array('doc', 'docx');
                                $file      = $row['file'];
                                $x         = explode('.', $file);
                                $eks       = strtolower(end($x));
                                if (in_array($eks, $ekstensi)) {
                    ?>
    <div class="col m3">
        <img class="galeri materialboxed" data-caption="<?php echo indoDate($row['tgl_diterima']); ?>"
            src="./upload/surat_masuk/<?php echo $row['file']; ?>" />
        <a class="btn light-green darken-1" href="?page=gsm&act=fsm&id_surat=<?php echo $row['id_surat']; ?>">
            Tampilkan Ukuran Penuh
        </a>
    </div>
    <?php
                                } elseif (in_array($eks, $ekstensi2)) {
                                ?>
    <div class="col m3">
        <img class="galeri materialboxed" data-caption="<?php echo indoDate($row['tgl_diterima']); ?>"
            src="./asset/img/word.png" />
        <a class="btn light-green darken-1" href="?page=gsm&act=fsm&id_surat=<?php echo $row['id_surat']; ?>">
            Lihat Detail File
        </a>
    </div>
    <?php
                                } else {
                                ?>
    <div class="col m3">
        <img class="galeri materialboxed" data-caption="<?php echo indoDate($row['tgl_diterima']); ?>"
            src="./asset/img/pdf.png" />
        <a class="btn light-green darken-1" href="?page=gsm&act=fsm&id_surat=<?php echo $row['id_surat']; ?>">
            Lihat Detail File
        </a>
    </div>
    <?php
                                }
                            }
                        }
                    } else {
                        ?>
    <div class="col m12">
        <div class="card blue lighten-5">
            <div class="card-content notif">
                <span class="card-title lampiran">
                    <center>Tidak ada file lampiran surat masuk yang ditemukan</center>
                </span>
            </div>
        </div>
    </div>
    <?php
                    }
                    ?>
</div>
<?php
                }
            } else {
                // Ambil kategori yang dipilih dari GET
                $kategori_filter = isset($_GET['kategori_surat']) ? $_GET['kategori_surat'] : '';

                // Query dengan filter kategori jika dipilih
                if (!empty($kategori_filter)) {
                    $query = mysqli_query($config, "SELECT * FROM tbl_surat_masuk WHERE kategori_surat='$kategori_filter' ORDER BY id_surat DESC LIMIT $curr, $limit");
                } else {
                    $query = mysqli_query($config, "SELECT * FROM tbl_surat_masuk ORDER BY id_surat DESC LIMIT $curr, $limit");
                }

    ?>
<!-- Row form Start -->
<div class="row jarak-form black-text">
    <form class="col s12" method="GET" action="">
        <input type="hidden" name="page" value="gsm"> <!-- Agar tetap di halaman galeri -->

        <div class="input-field col s3">
            <i class="material-icons prefix md-prefix">date_range</i>
            <input id="dari_tanggal" type="text" name="dari_tanggal" required>
            <label for="dari_tanggal">Dari Tanggal</label>
        </div>
        <div class="input-field col s3">
            <i class="material-icons prefix md-prefix">date_range</i>
            <input id="sampai_tanggal" type="text" name="sampai_tanggal" required>
            <label for="sampai_tanggal">Sampai Tanggal</label>
        </div>

        <!-- Wrapper untuk ikon dan select agar sejajar -->
        <style>
        .row .col.s4 {
            margin-top: 0px !important;
        }
        </style>
        <div class="input-field col s4">
            <div class="d-flex">
                <select name="kategori_surat" id="filter_kategori" onchange="this.form.submit()"
                    class="browser-default">
                    <option value=""
                        <?= (!isset($_GET['kategori_surat']) || $_GET['kategori_surat'] == '') ? 'selected' : '' ?>>
                        Semua Kategori
                    </option>
                    <?php
                        $kategori_q = mysqli_query($config, "SELECT * FROM kategori_surat ORDER BY nama_kategori ASC");
                        while ($kat = mysqli_fetch_assoc($kategori_q)):
                            $selected = (isset($_GET['kategori_surat']) && $_GET['kategori_surat'] == $kat['nama_kategori']) ? 'selected' : '';
                            echo "<option value=\"{$kat['nama_kategori']}\" $selected>{$kat['nama_kategori']}</option>";
                        endwhile;
                        ?>
                </select>

            </div>
        </div>

    </form>
</div>
<!-- Row form END -->


<?php
                if (mysqli_num_rows($query) > 0) {
                    while ($row = mysqli_fetch_array($query)) {
                        if (!empty($row['file'])) {
                            $ekstensi  = array('jpg', 'png', 'jpeg');
                            $ekstensi2 = array('doc', 'docx');
                            $file      = $row['file'];
                            $x         = explode('.', $file);
                            $eks       = strtolower(end($x));
                            if (in_array($eks, $ekstensi)) {
    ?>
<div class="col m3">
    <img class="galeri materialboxed" data-caption="<?php echo indoDate($row['tgl_diterima']); ?>"
        src="./upload/surat_masuk/<?php echo $row['file']; ?>" />
    <a class="btn light-green darken-1" href="?page=gsm&act=fsm&id_surat=<?php echo $row['id_surat']; ?>">
        Tampilkan Ukuran Penuh
    </a>
</div>
<?php
                            } elseif (in_array($eks, $ekstensi2)) {
                ?>
<div class="col m3">
    <img class="galeri materialboxed" data-caption="<?php echo indoDate($row['tgl_diterima']); ?>"
        src="./asset/img/word.png" />
    <a class="btn light-green darken-1" href="?page=gsm&act=fsm&id_surat=<?php echo $row['id_surat']; ?>">
        Lihat Detail File
    </a>
</div>
<?php
                            } else {
                ?>
<div class="col m3">
    <img class="galeri materialboxed" data-caption="<?php echo indoDate($row['tgl_diterima']); ?>"
        src="./asset/img/pdf.png" />
    <a class="btn light-green darken-1" href="?page=gsm&act=fsm&id_surat=<?php echo $row['id_surat']; ?>">
        Lihat Detail File
    </a>
</div>
<?php
                            }
                        }
                    }
                } else {
        ?>
<div class="col m12">
    <div class="card blue lighten-5">
        <div class="card-content notif">
            <span class="card-title lampiran">
                <center>Tidak ada data untuk ditampilkan</center>
            </span>
        </div>
    </div>
</div>
<?php
                }
    ?>
</div>
<?php
                $query = mysqli_query($config, "SELECT * FROM tbl_surat_masuk");
                $cdata = mysqli_num_rows($query);
                $cpg = ceil($cdata / $limit);
    ?>
<!-- Pagination START -->
<ul class="pagination">
    <?php if ($cdata > $limit): ?>
    <?php if ($pg > 1): ?>
    <?php $prev = $pg - 1; ?>
    <li><a href="?page=gsm&pg=1"><i class="material-icons md-48">first_page</i></a></li>
    <li><a href="?page=gsm&pg=<?php echo $prev; ?>"><i class="material-icons md-48">chevron_left</i></a></li>
    <?php else: ?>
    <li class="disabled"><a href=""><i class="material-icons md-48">first_page</i></a></li>
    <li class="disabled"><a href=""><i class="material-icons md-48">chevron_left</i></a></li>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $cpg; $i++): ?>
    <?php if ((($i >= $pg - 3) && ($i <= $pg + 3)) || ($i == 1) || ($i == $cpg)): ?>
    <?php if ($i == $pg): ?>
    <li class="active waves-effect waves-dark"><a href="?page=gsm&pg=<?php echo $i; ?>"> <?php echo $i; ?> </a></li>
    <?php else: ?>
    <li class="waves-effect waves-dark"><a href="?page=gsm&pg=<?php echo $i; ?>"> <?php echo $i; ?> </a></li>
    <?php endif; ?>
    <?php endif; ?>
    <?php endfor; ?>

    <?php if ($pg < $cpg): ?>
    <?php $next = $pg + 1; ?>
    <li><a href="?page=gsm&pg=<?php echo $next; ?>"><i class="material-icons md-48">chevron_right</i></a></li>
    <li><a href="?page=gsm&pg=<?php echo $cpg; ?>"><i class="material-icons md-48">last_page</i></a></li>
    <?php else: ?>
    <li class="disabled"><a href=""><i class="material-icons md-48">chevron_right</i></a></li>
    <li class="disabled"><a href=""><i class="material-icons md-48">last_page</i></a></li>
    <?php endif; ?>
    <?php endif; ?>
</ul>
<!-- Pagination END -->
<?php
            }
        }
    }
?>