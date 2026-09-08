<?php
session_start();

if (!isset($_SESSION['hewan'])) {
    $_SESSION['hewan'] = [];
}

$edit_index = $_GET['edit'] ?? '';
$edit_data = isset($_SESSION['hewan'][$edit_index]) ? $_SESSION['hewan'][$edit_index] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['atur_penerima'])) {
        $_SESSION['jumlah_mustahik'] = intval($_POST['jumlah_mustahik']);
        $_SESSION['jumlah_panitia'] = intval($_POST['jumlah_panitia']);
    }

    if (isset($_POST['simpan_hewan'])) {
        $jenis = $_POST['jenis'];
        $berat = floatval($_POST['berat']);
        $jumlah_pekurban = intval($_POST['jumlah_pekurban']);
        $edit_index = $_POST['edit_index'];

        if ($berat > 0 && $jumlah_pekurban > 0) {
            $data = [
                'jenis' => $jenis,
                'berat' => $berat,
                'jumlah_pekurban' => $jumlah_pekurban
            ];
            if ($edit_index !== "") {
                $_SESSION['hewan'][$edit_index] = $data;
                header("Location: index.php");
                exit;
            } else {
                $_SESSION['hewan'][] = $data;
            }
        }
    }

    // Menghitung
    if (isset($_POST['hitung'])) {
        $total_daging = 0;
        $rincian = [];

        foreach ($_SESSION['hewan'] as $hwn) {
            $total_daging += $hwn['berat'];
            $sepertiga = $hwn['berat'] / 3;
            $rincian[] = [
                'jenis' => $hwn['jenis'],
                'berat' => $hwn['berat'],
                'jumlah_pekurban' => $hwn['jumlah_pekurban'],
                'pekurban_per_orang' => round($sepertiga / $hwn['jumlah_pekurban'], 2)
            ];
        }

        $mustahik = $_SESSION['jumlah_mustahik'] ?? 0;
        $panitia  = $_SESSION['jumlah_panitia'] ?? 0;

        $total_sepertiga = $total_daging / 3;
        $mustahik_per_orang = $mustahik > 0 ? round($total_sepertiga / $mustahik, 2) : 0;
        $panitia_per_orang  = $panitia > 0 ? round($total_sepertiga / $panitia, 2) : 0;

        $_SESSION['hasil'] = [
            'total_daging' => $total_daging,
            'rincian_pekurban' => $rincian,
            'mustahik_per_orang' => $mustahik_per_orang,
            'panitia_per_orang' => $panitia_per_orang
        ];
    }

    // Reset
    if (isset($_POST['reset'])) {
        session_unset();
        header("Location: index.php");
        exit;
    }

    // Hapus
    if (isset($_GET['hapus'])) {
        $i = $_GET['hapus'];
        unset($_SESSION['hewan'][$i]);
        $_SESSION['hewan'] = array_values($_SESSION['hewan']); // reindex

        // Lakukan hitung ulang jika masih ada hewan
        if (!empty($_SESSION['hewan'])) {
            $total_daging = 0;
            $rincian = [];

            foreach ($_SESSION['hewan'] as $hwn) {
                $total_daging += $hwn['berat'];
                $sepertiga = $hwn['berat'] / 3;
                $rincian[] = [
                    'jenis' => $hwn['jenis'],
                    'berat' => $hwn['berat'],
                    'jumlah_pekurban' => $hwn['jumlah_pekurban'],
                    'pekurban_per_orang' => round($sepertiga / $hwn['jumlah_pekurban'], 2)
                ];
            }

            $mustahik = $_SESSION['jumlah_mustahik'] ?? 0;
            $panitia  = $_SESSION['jumlah_panitia'] ?? 0;

            $total_sepertiga = $total_daging / 3;
            $mustahik_per_orang = $mustahik > 0 ? round($total_sepertiga / $mustahik, 2) : 0;
            $panitia_per_orang  = $panitia > 0 ? round($total_sepertiga / $panitia, 2) : 0;

            $_SESSION['hasil'] = [
                'total_daging' => $total_daging,
                'rincian_pekurban' => $rincian,
                'mustahik_per_orang' => $mustahik_per_orang,
                'panitia_per_orang' => $panitia_per_orang
            ];
        } else {
            unset($_SESSION['hasil']);
        }

        header("Location: index.php");
        exit;
    }
}

$hewan = $_SESSION['hewan'] ?? [];
$hasil = $_SESSION['hasil'] ?? null;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Kalkulator Kurban</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="card p-4 shadow-sm">
            <h3 class="text-center mb-4">Kalkulator Pembagian Daging Kurban</h3>

            <!-- Form mustahik dan panitia -->
            <form method="post" class="row g-3 mb-4">
                <div class="col-md-5">
                    <input type="number" name="jumlah_mustahik" class="form-control" placeholder="Jumlah Masyarakat"
                        value="<?= $_SESSION['jumlah_mustahik'] ?? '' ?>" required>
                </div>
                <div class="col-md-5">
                    <input type="number" name="jumlah_panitia" class="form-control" placeholder="Jumlah Panitia"
                        value="<?= $_SESSION['jumlah_panitia'] ?? '' ?>" required>
                </div>
                <div class="col-md-2">
                    <button name="atur_penerima" class="btn btn-success w-100">Simpan</button>
                </div>
            </form>

            <!-- Form tambah/edit hewan -->
            <form method="post" class="row g-3">
                <input type="hidden" name="edit_index" value="<?= $edit_index ?>">
                <div class="col-md-3">
                    <select name="jenis" class="form-select" required>
                        <option value="sapi" <?= ($edit_data['jenis'] ?? '') == 'sapi' ? 'selected' : '' ?>>Sapi</option>
                        <option value="kambing" <?= ($edit_data['jenis'] ?? '') == 'kambing' ? 'selected' : '' ?>>Kambing</option>
                        <option value="domba" <?= ($edit_data['jenis'] ?? '') == 'domba' ? 'selected' : '' ?>>Domba</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.1" name="berat" class="form-control" placeholder="Berat (kg)"
                        value="<?= $edit_data['berat'] ?? '' ?>" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="jumlah_pekurban" class="form-control" placeholder="Jumlah pekurban"
                        value="<?= $edit_data['jumlah_pekurban'] ?? '' ?>" required>
                </div>
                <div class="col-md-3">
                    <button name="simpan_hewan" class="btn btn-<?= isset($edit_data) ? 'warning' : 'primary' ?> w-100">
                        <?= isset($edit_data) ? 'Update' : 'Tambah' ?> Hewan
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel hewan -->
        <?php if (!empty($hewan)): ?>
            <div class="card mt-4 p-4 shadow-sm">
                <h5>Daftar Hewan Kurban</h5>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis</th>
                            <th>Berat</th>
                            <th>Shohibul Qurban</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hewan as $i => $h): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= ucfirst($h['jenis']) ?></td>
                                <td><?= $h['berat'] ?> kg</td>
                                <td><?= $h['jumlah_pekurban'] ?></td>
                                <td>
                                    <a href="?edit=<?= $i ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="?hapus=<?= $i ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" class="d-flex gap-2">
                    <button name="hitung" class="btn btn-success">Hitung Pembagian</button>
                    <button name="reset" class="btn btn-danger">Reset Semua</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Hasil -->
        <?php if ($hasil): ?>
            <div class="card mt-4 p-4 shadow-sm bg-white">
                <h4 class="text-center mb-3">Hasil Pembagian Daging</h4>
                <p><strong>Total Daging:</strong> <?= $hasil['total_daging'] ?> kg</p>

                <h5>Shohibul Qurban</h5>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis</th>
                            <th>Berat</th>
                            <th>Shohibul Qurban / Orang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hasil['rincian_pekurban'] as $i => $r): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= ucfirst($r['jenis']) ?></td>
                                <td><?= $r['berat'] ?> kg</td>
                                <td><?= $r['pekurban_per_orang'] ?> kg</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h5>Masyarakat & Panitia</h5>
                <p>Masyarakat: <?= $hasil['mustahik_per_orang'] ?> kg / orang</p>
                <p>Panitia: <?= $hasil['panitia_per_orang'] ?> kg / orang</p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>