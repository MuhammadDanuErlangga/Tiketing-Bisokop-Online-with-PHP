<?php
    /** Dokumentasi  variabel nya 
     * $jenisTiket -> Jenis tiket yang dipilih
     * $jumlahTiket -> Berapa tiket yang dibeli
     * $hariPesan -> Hari pemesanan dilakukan
     * return array -> Hasil perhitungan detail
     */

class PengelolaTiketBioskop {
    // harga tiket
    protected $daftarHarga = [
        'dewasa' => 50000,   
        'anak'   => 30000    
    ];
    
    // klo weekend bayar lagi 
    protected $hariWeekend = ['sabtu', 'minggu'];
    protected $biayaTambahan = [
        'weekend' => 10000    
    ];
    
    // diskon kalo bayar total nya 150k
    protected $aturanDiskon = [
        'minimal_pembelian' => 150000,
        'besar_diskon' => 0.10  // 10% diskon nya klo => 150k
    ];
    
    public function hitungHargaTiket($jenisTiket, $jumlahTiket, $hariPesan) {
        // input valid sesuai dengan ketentuan jenis,jumlah serta hari.
        if (!$this->cekInputValid($jenisTiket, $jumlahTiket, $hariPesan)) {
            return [
                'status' => 'error',
            ];
        }
        
        $hargaDasar = $this->daftarHarga[$jenisTiket];
        $totalAwal = $hargaDasar * $jumlahTiket;
        
        // Cek apakah hari weekend, kalo iya ditambah harganya 
        $biayaWeekend = 0;
        if ($this->isHariWeekend($hariPesan)) {
            $biayaWeekend = $this->biayaTambahan['weekend'] * $jumlahTiket;
        }
        
        $totalSebelumDiskon = $totalAwal + $biayaWeekend;
        
        $diskon = $this->hitungDiskon($totalSebelumDiskon);
        
        $totalAkhir = $totalSebelumDiskon - $diskon;
        
        // detail perhitungan
        return [
            'status' => 'sukses',
            'detail' => [
                'jenis_tiket' => $jenisTiket,
                'jumlah_tiket' => $jumlahTiket,
                'harga_dasar' => $hargaDasar,
                'total_harga_dasar' => $totalAwal,
                'biaya_weekend' => $biayaWeekend,
                'diskon' => $diskon,
                'total_akhir' => $totalAkhir
            ]
        ];
    }
    
    private function cekInputValid($jenisTiket, $jumlahTiket, $hariPesan) {
        return (
            isset($this->daftarHarga[$jenisTiket]) &&
            is_numeric($jumlahTiket) &&
            $jumlahTiket > 0 &&
            in_array(strtolower($hariPesan), array_merge($this->hariWeekend, ['senin', 'selasa', 'rabu', 'kamis', 'jumat']))
        );
    }
    // setelah merge maka akan di cek harinya 
    private function isHariWeekend($hari) {
        return in_array(strtolower($hari), $this->hariWeekend);
    }
    
    // kalo lebih dari total 150k dapat diskon
    private function hitungDiskon($total) {
        if ($total >= $this->aturanDiskon['minimal_pembelian']) {
            return $total * $this->aturanDiskon['besar_diskon'];
        }
        return 0;
    }
}

// Inisialisasi sistem
$sistemTiket = new PengelolaTiketBioskop();

// Proses form jika ada request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hasil = $sistemTiket->hitungHargaTiket(
        $_POST['jenis_tiket'] ?? '',
        $_POST['jumlah_tiket'] ?? 0,
        $_POST['hari_pesan'] ?? ''
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Tiket Bioskop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1 class="judul-form">Tiket Bioskop Netflix Daring</h1>
        
        <form method="POST" action="">
            <div class="form-kelompok">
                <label class="form-label">Jenis Tiket:</label>
                <select name="jenis_tiket" class="form-input" required>
                    <option value="dewasa">Tiket Dewasa - Rp50.000</option>
                    <option value="anak">Tiket Anak-anak - Rp30.000</option>
                </select>
            </div>

            <div class="form-kelompok">
                <label class="form-label">Jumlah Tiket:</label>
                <input type="number" name="jumlah_tiket" class="form-input" min="1" required>
            </div>

            <div class="form-kelompok">
                <label class="form-label">Hari Pemesanan:</label>
                <select name="hari_pesan" class="form-input" required>
                    <option value="senin">Senin</option>
                    <option value="selasa">Selasa</option>
                    <option value="rabu">Rabu</option>
                    <option value="kamis">Kamis</option>
                    <option value="jumat">Jumat</option>
                    <option value="sabtu">Sabtu</option>
                    <option value="minggu">Minggu</option>
                </select>
            </div>

            <button type="submit" class="tombol-pesan">Hitung Total Harga</button>
        </form>

        <?php if (isset($hasil) && $hasil['status'] === 'sukses'): ?>
            <div class="hasil">
                <h3>Detail Pemesanan:</h3>
                <div class="detail-harga">
                    <p>Jenis Tiket: <?= ucfirst($hasil['detail']['jenis_tiket']) ?></p>
                    <p>Jumlah Tiket: <?= $hasil['detail']['jumlah_tiket'] ?></p>
                    <p>Harga Dasar: Rp <?= number_format($hasil['detail']['harga_dasar'], 0, ',', '.') ?></p>
                    <p>Total Harga Dasar: Rp <?= number_format($hasil['detail']['total_harga_dasar'], 0, ',', '.') ?></p>
                    
                    <?php if ($hasil['detail']['biaya_weekend'] > 0): ?>
                        <p>Biaya Weekend: Rp <?= number_format($hasil['detail']['biaya_weekend'], 0, ',', '.') ?></p>
                    <?php endif; ?>
                    
                    <?php if ($hasil['detail']['diskon'] > 0): ?>
                        <p>Diskon: Rp <?= number_format($hasil['detail']['diskon'], 0, ',', '.') ?></p>
                    <?php endif; ?>
                    
                    <p><strong>Total Akhir: Rp <?= number_format($hasil['detail']['total_akhir'], 0, ',', '.') ?></strong></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>