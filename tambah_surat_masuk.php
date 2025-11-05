<?php
require 'vendor/autoload.php'; // Load library untuk membaca file PDF dan DOCX
require_once __DIR__ . '/vendor/autoload.php';

use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordReader;

// Daftar kategori surat dengan kata kunci terkait
// $categories = [
//     'Surat Undangan' => ['undangan', 'mengundang', 'mengikuti kegiatan', 'analisis', 'diundang', 'undang'],
//     'Surat Jalan' => ['pengiriman', 'barang', 'jalan', 'ekspedisi', 'logistik'],
//     'Surat Keputusan' => ['putusan', 'keputusan', 'dikeluarkan', 'berlaku'],
//     'Surat Pemberitahuan' => ['pemberitahuan', 'informasi', 'diberitahukan'],
//     'Surat Permohonan' => ['permohonan', 'mohon', 'mengajukan'],
//     'Surat Tugas' => ['tugas', '', 'pertugasan']
// ];
$categories = [];
$kategori_query = mysqli_query($config, "SELECT * FROM kategori_surat");

while ($kat = mysqli_fetch_assoc($kategori_query)) {
    $id_kat = $kat['id'];
    $nama_kategori = $kat['nama_kategori'];

    $keywords = [];
    $keyword_query = mysqli_query($config, "SELECT keyword FROM kata_kunci_kategori WHERE kategori_id = $id_kat");
    while ($k = mysqli_fetch_assoc($keyword_query)) {
        $keywords[] = $k['keyword'];
    }

    $categories[$nama_kategori] = $keywords;
}


// Fungsi klasifikasi surat berdasarkan kata kunci
function classify_surat($text, $categories)
{
    foreach ($categories as $category => $keywords) {
        foreach ($keywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return $category;
            }
        }
    }
    return 'Kategori Tidak Diketahui';
}

// Fungsi untuk ekstrak teks dari file (PDF & DOCX)
function extract_text_from_file($filePath)
{
    if (!file_exists($filePath)) {
        return "File tidak ditemukan.";
    }

    $ext = pathinfo($filePath, PATHINFO_EXTENSION);

    try {
        if ($ext === 'pdf') {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        } elseif ($ext === 'docx') {
            $phpWord = WordReader::load($filePath);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . " ";
                    }
                }
            }
            return trim($text);
        }
    } catch (Exception $e) {
        return "Error membaca file: " . $e->getMessage();
    }

    return 'Format tidak didukung';
}

if (empty($_SESSION['admin'])) {
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
    header("Location: ./");
    die();
}

if (isset($_POST['submit'])) {

    // // Validasi form kosong
    // if (
    //     empty($_POST['no_agenda']) || empty($_POST['no_surat']) || empty($_POST['asal_surat']) ||
    //     empty($_POST['isi']) || empty($_POST['kode']) || empty($_POST['indeks']) ||
    //     empty($_POST['tgl_surat']) || empty($_POST['keterangan'])
    // ) {

    //     $_SESSION['errEmpty'] = 'ERROR! Semua form wajib diisi';
    //     echo '<script language="javascript">window.history.back();</script>';
    // } else {
    $no_agenda = $_POST['no_agenda'];
    $no_surat = $_POST['no_surat'];
    $asal_surat = $_POST['asal_surat'];
    $isi = $_POST['isi'];
    $kode = substr($_POST['kode'], 0, 30);
    $nkode = trim($kode);
    $indeks = $_POST['indeks'];
    $tgl_surat = $_POST['tgl_surat'];
    $keterangan = $_POST['keterangan'];
    $id_user = $_SESSION['id_user'];

    $kategori_surat = classify_surat($isi, $categories); // Klasifikasi berdasarkan input isi

    // Cek duplikasi nomor surat
    $cek = mysqli_query($config, "SELECT * FROM tbl_surat_masuk WHERE no_surat='$no_surat'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['errDup'] = 'Nomor Surat sudah terpakai, gunakan yang lain!';
        echo '<script language="javascript">window.history.back();</script>';
        exit();
    }

    // Proses unggah file
    $allowed_ext = ['jpg', 'png', 'jpeg', 'doc', 'docx', 'pdf'];
    $file = $_FILES['file']['name'];
    $file_temp = $_FILES['file']['tmp_name'];
    $file_size = $_FILES['file']['size'];
    $target_dir = "upload/surat_masuk/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    if ($file != "") {
        $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext) && $file_size < 2500000) {
            $new_filename = rand(1, 10000) . "-" . $file;
            move_uploaded_file($file_temp, $target_dir . $new_filename);

            // 🔥 **EKSTRAKSI TEKS OTOMATIS & KLASIFIKASI**
            if ($file_ext === 'pdf' || $file_ext === 'docx') {
                $text_from_file = extract_text_from_file($target_dir . $new_filename);
                $kategori_surat = classify_surat($text_from_file, $categories);
            }

            // Simpan ke database
            $query = mysqli_query($config, "INSERT INTO tbl_surat_masuk 
                        (no_agenda, no_surat, asal_surat, isi, kode, indeks, tgl_surat, tgl_diterima, file, keterangan, kategori_surat, id_user) 
                        VALUES 
                        ('$no_agenda', '$no_surat', '$asal_surat', '$isi', '$nkode', '$indeks', '$tgl_surat', NOW(), '$new_filename', '$keterangan', '$kategori_surat', '$id_user')");

            if ($query) {
                $_SESSION['succAdd'] = 'SUKSES! Data berhasil ditambahkan';
                header("Location: ./admin.php?page=tsm");
                exit();
            } else {
                $_SESSION['errQ'] = 'ERROR! Ada masalah dengan query';
                echo '<script language="javascript">window.history.back();</script>';
                exit();
            }
        } else {
            $_SESSION['errFormat'] = 'Format file tidak didukung atau ukuran terlalu besar!';
            echo '<script language="javascript">window.history.back();</script>';
            exit();
        }
    } else {
        // Jika tidak ada file diunggah, simpan tanpa file
        $query = mysqli_query($config, "INSERT INTO tbl_surat_masuk 
                    (no_agenda, no_surat, asal_surat, isi, kode, indeks, tgl_surat, tgl_diterima, file, keterangan, kategori_surat, id_user) 
                    VALUES 
                    ('$no_agenda', '$no_surat', '$asal_surat', '$isi', '$nkode', '$indeks', '$tgl_surat', NOW(), '', '$keterangan', '$kategori_surat', '$id_user')");

        if ($query) {
            $_SESSION['succAdd'] = 'SUKSES! Data berhasil ditambahkan';
            header("Location: ./admin.php?page=tsm");
            exit();
        } else {
            $_SESSION['errQ'] = 'ERROR! Ada masalah dengan query';
            echo '<script language="javascript">window.history.back();</script>';
            exit();
        }
    }
}

?>


<!-- Row Start -->
<div class="row">
    <!-- Secondary Nav START -->
    <div class="col s12">
        <div class="z-depth-1">
            <nav class="secondary-nav">
                <div class="nav-wrapper blue-grey darken-1">
                    <div class="row" style="margin: 0;">
                        <!-- Tombol Tambah Data -->
                        <div class="col s12 m7">
                            <ul class="left">
                                <li class="waves-effect waves-light">
                                    <a href="?page=tsm&act=add" class="judul">
                                        <i class="material-icons">mail</i> Tambah Data Surat Masuk
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Search Bar Responsif -->
                        <div class="col s12 m5">
                            <form method="post" action="?page=tsm">
                                <div class="input-field round-in-box">
                                    <input id="search" type="search" name="cari"
                                        placeholder="Ketik dan tekan enter mencari data..." required>
                                    <label for="search">
                                        <i class="material-icons md-dark">search</i>
                                    </label>
                                    <input type="submit" name="submit" class="hidden">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Secondary Nav END -->
</div>
<!-- Row END -->

<?php
if (isset($_SESSION['errQ'])) {
    $errQ = $_SESSION['errQ'];
    echo '<div id="alert-message" class="row">
                            <div class="col m12">
                                <div class="card red lighten-5">
                                    <div class="card-content notif">
                                        <span class="card-title red-text"><i class="material-icons md-36">clear</i> ' . $errQ . '</span>
                                    </div>
                                </div>
                            </div>
                        </div>';
    unset($_SESSION['errQ']);
}
if (isset($_SESSION['errEmpty'])) {
    $errEmpty = $_SESSION['errEmpty'];
    echo '<div id="alert-message" class="row">
                            <div class="col m12">
                                <div class="card red lighten-5">
                                    <div class="card-content notif">
                                        <span class="card-title red-text"><i class="material-icons md-36">clear</i> ' . $errEmpty . '</span>
                                    </div>
                                </div>
                            </div>
                        </div>';
    unset($_SESSION['errEmpty']);
}
?>

<!-- Row form Start -->
<div class="row jarak-form">
    <!-- Form START -->
    <form class="col s12" method="POST" action="?page=tsm&act=add" enctype="multipart/form-data">
        <!-- Row in form START -->
        <div class="row">
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">looks_one</i>
                <?php
                echo '<input id="no_agenda" type="number" class="validate" name="no_agenda" value="';
                $sql = mysqli_query($config, "SELECT no_agenda FROM tbl_surat_masuk");
                $no_agenda = "1";
                if (mysqli_num_rows($sql) == 0) {
                    echo $no_agenda;
                }

                $result = mysqli_num_rows($sql);
                $counter = 0;
                while (list($no_agenda) = mysqli_fetch_array($sql)) {
                    if (++$counter == $result) {
                        $no_agenda++;
                        echo $no_agenda;
                    }
                }
                echo '" required>';

                if (isset($_SESSION['no_agenda'])) {
                    $no_agenda = $_SESSION['no_agenda'];
                    echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $no_agenda . '</div>';
                    unset($_SESSION['no_agenda']);
                }
                ?>
                <label for="no_agenda">Nomor Agenda</label>
            </div>
            <!-- <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">bookmark</i>
                <input id="kode" type="text" class="validate" name="kode" required>
                <?php
                if (isset($_SESSION['kode'])) {
                    $kode = $_SESSION['kode'];
                    echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $kode . '</div>';
                    unset($_SESSION['kode']);
                }
                ?>
                <label for="kode">Kode Klasifikasi</label>
            </div> -->
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">place</i>
                <input id="asal_surat" type="text" class="validate" name="asal_surat" required>
                <?php
                if (isset($_SESSION['asal_surat'])) {
                    $asal_surat = $_SESSION['asal_surat'];
                    echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $asal_surat . '</div>';
                    unset($_SESSION['asal_surat']);
                }
                ?>
                <label for="asal_surat">Asal Surat</label>
            </div>
            <!-- <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">storage</i>
                <input id="indeks" type="text" class="validate" name="indeks" required>
                <?php
                if (isset($_SESSION['indeks'])) {
                    $indeks = $_SESSION['indeks'];
                    echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $indeks . '</div>';
                    unset($_SESSION['indeks']);
                }
                ?>
                <label for="indeks">Indeks Berkas</label>
            </div> -->
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">looks_two</i>
                <input id="no_surat" type="text" class="validate" name="no_surat" required>
                <?php
                if (isset($_SESSION['no_surat'])) {
                    $no_surat = $_SESSION['no_surat'];
                    echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $no_surat . '</div>';
                    unset($_SESSION['no_surat']);
                }
                if (isset($_SESSION['errDup'])) {
                    $errDup = $_SESSION['errDup'];
                    echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $errDup . '</div>';
                    unset($_SESSION['errDup']);
                }
                ?>
                <label for="no_surat">Nomor Surat</label>
            </div>
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">date_range</i>
                <input id="tgl_surat" type="text" name="tgl_surat" class="datepicker" required>
                <?php
                if (isset($_SESSION['tgl_surat'])) {
                    $tgl_surat = $_SESSION['tgl_surat'];
                    echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $tgl_surat . '</div>';
                    unset($_SESSION['tgl_surat']);
                }
                ?>
                <label for="tgl_surat">Tanggal Surat</label>
            </div>
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">description</i>
                <textarea id="isi" class="materialize-textarea validate" name="isi" required></textarea>
                <?php
                if (isset($_SESSION['isi'])) {
                    $isi = $_SESSION['isi'];
                    echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $isi . '</div>';
                    unset($_SESSION['isi']);
                }
                ?>
                <label for="isi">Isi Ringkas</label>
            </div>
            <!-- <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">featured_play_list</i>
                <input id="keterangan" type="text" class="validate" name="keterangan" required>
                <?php
                if (isset($_SESSION['keterangan'])) {
                    $keterangan = $_SESSION['keterangan'];
                    echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $keterangan . '</div>';
                    unset($_SESSION['keterangan']);
                }
                ?>
                <label for="keterangan">Keterangan</label>
            </div> -->
            <div class="input-field col s6">
                <div class="file-field input-field">
                    <div class="btn light-green darken-1">
                        <span>File</span>
                        <input type="file" id="file" name="file">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" placeholder="Upload file DOCX/hasil scan ke DOCX surat masuk">
                        <?php
                        if (isset($_SESSION['errSize'])) {
                            $errSize = $_SESSION['errSize'];
                            echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $errSize . '</div>';
                            unset($_SESSION['errSize']);
                        }
                        if (isset($_SESSION['errFormat'])) {
                            $errFormat = $_SESSION['errFormat'];
                            echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">' . $errFormat . '</div>';
                            unset($_SESSION['errFormat']);
                        }
                        ?>
                        <small class="red-text">*Format file yang diperbolehkan *.DOCX dan
                            ukuran maksimal file 2 MB!</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row in form END -->

        <div class="row">
            <div class="col 6">
                <button type="submit" name="submit" class="btn-large blue waves-effect waves-light">SIMPAN <i
                        class="material-icons">done</i></button>
            </div>
            <div class="col 6">
                <a href="?page=tsm" class="btn-large deep-orange waves-effect waves-light">BATAL <i
                        class="material-icons">clear</i></a>
            </div>
        </div>

    </form>
    <!-- Form END -->

</div>
<!-- Row form END -->