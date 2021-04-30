<?php
namespace App\Helpers;

class Terbilang
{
    /**
     * Versi dari pustaka
     *
     * @var string
     */
    const VERSION = '1.0';

    /**
     * Pemisah angka desimal dibelakang koma.
     *
     * @var string
     */
    protected $pemisahDesimal;

    /**
     * Array bilangan dari mulai satu hingga sebelas. Nol tidak
     * dimasukkan karena perlu perlakuan special.
     *
     * @var array
     */
    protected $bilangan = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas'
    ];

    /**
     * Class constructor.
     *
     * @param string $pemisahDesimal
     * @return void
     */
    public function __construct($pemisahDesimal='.')
    {
        $this->pemisahDesimal = $pemisahDesimal;
    }

    /**
     * Static constructor untuk membuat object tanpa new keyword.
     *
     * @param string $pemisahDesimal
     * @return RioAstamal\AngkaTerbilang\Terbilang
     */
    public static function create($pemisahDesimal='.')
    {
        return new static($pemisahDesimal);
    }

    /**
     * Method wrapper yang mengkombinasikan terjemahan angka bulat
     * dan desimal.
     *
     * @param float $angka Angka yang akan diterjemahkan
     * @return string
     */
    public function terbilang($angka)
    {
        $angka = (string)$angka;
        if (! is_string($angka)) {
            throw new TerbilangException('ERROR: Angka harus berupa string');
        }

        // Hapus semua karakter yang bukan angka dan yang digunakan
        // oleh scientific notation seperti e,E,+ contoh 2.5E+2
        $angka = preg_replace('/([^0-9eE\+' . $this->pemisahDesimal . '])/', '', $angka);
        $angka = str_replace($this->pemisahDesimal, '.', $angka);

        if (! is_numeric($angka)) {
            throw new TerbilangException('ERROR: Angka tidak dapat dikenali');
        }

        if (strpos((string)$angka, '.') === false) {
            if ($angka + 0 === 0) {
                return 'nol';
            }
            return $this->terjemahkanAngka($angka);
        }

        list($angka, $desimal) = explode('.', $angka, 2);

        if ($angka + 0 === 0) {
            return 'nol koma ' . $this->terjemahkanPerAngka($desimal);
        }

        return rtrim($this->terjemahkanAngka($angka) . ' koma ' . $this->terjemahkanPerAngka($desimal) . ' rupiah ');
    }

    /**
     * @param float $angka Angka yang akan diterjemahkan
     * @param string
     */
    protected function terjemahkanAngka($angka)
    {
        // Angka dibawah 12 dapat langsung dimapping ke index array $bilangan
        if ($this->lebihKecilDari($angka, '12')) {
            return $this->bilangan[$angka];
        }

        // Angka belasan didapat dengan cara pengurangan angka tersebut
        // dengan 10. Hasilnya langsung dapat dimapping ke index array $this->bilangan
        // dan ditambahkan suffix ' belas'.
        //
        // Contoh:
        // 1. 18 - 10 = 8 -> index ke-8 + 'belas'
        // 2. 17 - 10 = 7 -> index ke-7 + 'belas'
        if ($this->lebihKecilDari($angka, '20')) {
            return $this->bilangan[$angka - 10] . ' belas';
        }

        // Angka puluhan didapat dengan dua operasi yaitu pembagian dan
        // modulus dengan angka 10. Hasil bagi dan modulus masing-masing
        // akan dimapping ke index array $this->bilangan
        //
        // Contoh angka 48:
        // 48 / 10 = 4.8 => dibulatkan -> 4 -> index ke-4 + 'puluh'
        // 48 % 10 = 8 -> index ke-8
        if ($this->lebihKecilDari($angka, '100')) {
            $hasilBagi = $this->bulatkanKebawah($angka / 10);
            $hasilMod = $angka % 10;

            return rtrim(sprintf('%s puluh %s',
                $this->bilangan[$hasilBagi],
                $this->bilangan[$hasilMod]
            ));
        }

        // Angka seratusan didapat dengan mengurangkan angka tersebut
        // dengan 100. Hasil dari pengurangan tersebut dapat berupa
        // satuan dan puluhan oleh karenanya kita gunakan rekursif
        // untuk mendapat bilangan tersebut.
        //
        // Contoh 100:
        // 100 - 100 = 0 -> 'seratus ' + index ke-0
        //
        // Contoh 125:
        // 125 - 100 = 25 -> 'seratus ' + terjemahkanAngka(25)
        if ($this->lebihKecilDari($angka, '200')) {
            return rtrim(sprintf('seratus %s', $this->terjemahkanAngka($angka - 100)));
        }

        // Angka ratusan didapat mirip dengan cara mendapatkan angka puluhan.
        // Perbedaannya pada ratusan kita menggunakan rekursif untuk sisa
        // modulusnya
        //
        // Contoh 205:
        // 205 / 100 = 2.05 => dibulatkan -> 2 -> index ke-2 + ' ratus'
        // 205 % 100 = 5 -> terjemahkanAngka(5)
        //
        // Contoh 499:
        // 499 / 100 = 4.99 => dibulatkan -> 4 -> index ke-4 + ' ratus'
        // 499 % 100 = 99 -> terjemahkanAngka(99)
        if ($this->lebihKecilDari($angka, '1000')) {
            $hasilBagi = $this->bulatkanKebawah($angka / 100);
            $hasilMod = $angka % 100;

            return rtrim(sprintf('%s ratus %s',
                $this->bilangan[$hasilBagi],
                $this->terjemahkanAngka($hasilMod)
            ));
        }

        // Angka seribuan.
        //
        // Contoh 1011:
        // 1011 - 1000 = 11 => 'seribu ' + terjemahkanAngka(11)
        if ($this->lebihKecilDari($angka, '2000')) {
            return rtrim(sprintf('seribu %s', $this->terjemahkanAngka($angka - 1000)));
        }

        // Angka ribuan sampai ratusan ribu
        if ($this->lebihKecilDari($angka, '1000000')) {
            $hasilBagi = $this->bulatkanKebawah($angka / 1000);
            $hasilMod = $angka % 1000;

            return rtrim(sprintf('%s ribu %s',
                $this->terjemahkanAngka($hasilBagi),
                $this->terjemahkanAngka($hasilMod)
            ));
        }

        // Angka jutaan sampai ratusan juta (dibawah 1 Milyar)
        if ($this->lebihKecilDari($angka, '1000000000')) {
            $hasilBagi = $this->bulatkanKebawah($angka / 1000000);
            $hasilMod = $angka % 1000000;

            return rtrim(sprintf('%s juta %s',
                $this->terjemahkanAngka($hasilBagi),
                $this->terjemahkanAngka($hasilMod)
            ));
        }

        // Angka milyaran sampai ratusan milyar (dibawah 1 Triliun)
        // Karena angka cukup besar dan sistem 32 bit hanya sampai pada
        // kisaran 2 Milyar, maka digunakan extension BC Math.
        if ($this->lebihKecilDari($angka, '1000000000000')) {
            $hasilBagi = $this->bulatkanKebawah(bcdiv($angka, '1000000000'));
            $hasilMod = bcmod($angka, '1000000000');

            return rtrim(sprintf('%s milyar %s',
                $this->terjemahkanAngka($hasilBagi),
                $this->terjemahkanAngka($hasilMod)
            ));
        }

        // Angka triliunan. Angka diatas 1000 triliun tidak diubah
        // ke bentuk satuan lain seperti kuadriliun, dan seterusnya.
        if ($this->lebihKecilDari($angka, '1000000000000000000000000')) {
            $hasilBagi = $this->bulatkanKebawah(bcdiv($angka, '1000000000000'));
            $hasilMod = bcmod($angka, '1000000000000');

            return rtrim(sprintf('%s triliun %s',
                $this->terjemahkanAngka($hasilBagi),
                $this->terjemahkanAngka($hasilMod)
            ));
        }

        $hasilBagi = $this->bulatkanKebawah(bcdiv($angka, '1000000000000000000000000'));
        $hasilMod = bcmod($angka, '1000000000000000000000000');

        return rtrim(sprintf('%s septiliun %s',
            $this->terjemahkanAngka($hasilBagi),
            $this->terjemahkanAngka($hasilMod)
        ));
    }

    /**
     * Terjemahkan setiap angka menjadi bilangan dalam
     * Bahasa Indonesia tanpa perlu mengindahkan satuan.
     *
     * @param float $angka
     * @return string
     */
    public function terjemahkanPerAngka($angka)
    {
        $bilangan = $this->bilangan;
        $bilangan[0] = 'nol';

        $terbilang = [];
        $length = strlen($angka);

        for ($i=0; $i<$length; $i++) {
            $index = (int)$angka[$i];
            $terbilang[] = $bilangan[$index];
        }

        return implode(' ', $terbilang);
    }

    /**
     * Perbandingan angka menggunakan bcmath untuk angka
     * yang sangat besar.
     *
     * @param string $x Angka yang dibandingkan
     * @param string $y Angka pembanding
     * @return bool
     */
    protected function lebihKecilDari($x, $y)
    {
        return bccomp($x, $y) === -1 ? true : false;
    }

    /**
     * Pembulatan kebawah menggunakan bcmath.
     *
     * @param string $angka
     * @return string
     */
    protected function bulatkanKebawah($angka)
    {
        // Tambahkan dengan 0 maka otomatis bcmath akan mengkonversi
        // kenilai pembulatan kebawah
        return bcadd($angka, 0);
    }

    /**
     * Shortcut untuk method terbilang()
     *
     * @param float $angka Angka yang akan diterjemahkan
     * @return string
     */
    public function t($angka)
    {
        return $this->terbilang($angka);
    }

    /**
     * Magic setter untuk attribute private/protected
     *
     * @param string $attr
     * @param mixed $nilai
     */
    public function __set($attr, $nilai)
    {
        if (property_exists($this, $attr)) {
            $this->{$attr} = $nilai;
        }
    }
}
class TerbilangException extends \Exception {}
