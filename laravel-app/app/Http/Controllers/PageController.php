&lt;?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PageController extends Controller
{
    private function fetchData($table, $filters = [])
    {
        $supabaseUrl = 'https://cmmzoykcoiystbgndaif.supabase.co/rest/v1';
        $supabaseKey = 'sb_publishable_t8dDSxNlTmiUtu1_hR16-w_goO6xAes';

        try {
            $response = Http::withHeaders([
                'apikey' =&gt; $supabaseKey,
                'Authorization' =&gt; 'Bearer ' . $supabaseKey,
            ])-&gt;get($supabaseUrl . '/' . $table, $filters);
            return $response-&gt;json() ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getProfil()
    {
        $data = $this-&gt;fetchData('profil_sekolah', ['id' =&gt; 'eq.1', 'limit' =&gt; 1]);
        return $data[0] ?? [];
    }

    public function profil()
    {
        $profil = $this-&gt;getProfil();
        return view('pages.profil', compact('profil'));
    }

    public function program()
    {
        $programs = $this-&gt;fetchData('program', ['order' =&gt; 'urutan.asc']);
        return view('pages.program', compact('programs'));
    }

    public function fasilitas()
    {
        $fasilitas = $this-&gt;fetchData('fasilitas', ['order' =&gt; 'urutan.asc']);
        return view('pages.fasilitas', compact('fasilitas'));
    }

    public function prestasi()
    {
        $prestasi = $this-&gt;fetchData('prestasi', ['order' =&gt; 'tanggal.desc']);
        return view('pages.prestasi', compact('prestasi'));
    }

    public function berita()
    {
        $berita = array_filter($this-&gt;fetchData('berita', ['order' =&gt; 'tanggal.desc']), function($item) {
            return ($item['status'] ?? 'published') === 'published';
        });
        return view('pages.berita', compact('berita'));
    }

    public function guru()
    {
        $guru = $this-&gt;fetchData('guru', []);
        return view('pages.guru', compact('guru'));
    }

    public function siswa()
    {
        $siswa = $this-&gt;fetchData('siswa', ['status' =&gt; 'eq.Aktif', 'order' =&gt; 'no_induk.asc']);
        return view('pages.siswa', compact('siswa'));
    }

    public function galeri()
    {
        $galeriData = array_filter($this-&gt;fetchData('galeri', ['order' =&gt; 'tanggal_upload.desc']), function($item) {
            return ($item['status'] ?? 'published') === 'published';
        });
        $galeri = ['Photo' =&gt; [], 'Video' =&gt; []];
        foreach ($galeriData as $item) {
            $jenis = $item['jenis_galeri'] ?? 'Photo';
            if (!isset($galeri[$jenis])) $galeri[$jenis] = [];
            $galeri[$jenis][] = $item;
        }
        return view('pages.galeri', compact('galeri'));
    }

    public function statistik()
    {
        $profil = $this-&gt;getProfil();
        $siswaCount = count($this-&gt;fetchData('siswa', ['status' =&gt; 'eq.Aktif']));
        $guruCount = count($this-&gt;fetchData('guru', []));
        $beritaCount = count($this-&gt;fetchData('berita', []));
        $galeriCount = count($this-&gt;fetchData('galeri', []));
        $ppdbCount = count($this-&gt;fetchData('ppdb', []));
        $faqCount = count($this-&gt;fetchData('faq', []));
        $prestasiCount = count($this-&gt;fetchData('prestasi', []));
        return view('pages.statistik', compact('profil', 'siswaCount', 'guruCount', 'beritaCount', 'galeriCount', 'ppdbCount', 'faqCount', 'prestasiCount'));
    }

    public function anggaran()
    {
        $anggaran = $this-&gt;fetchData('anggaran_bosn', ['order' =&gt; 'tahun.desc']);
        $realisasi = $this-&gt;fetchData('realisasi_bulanan', ['order' =&gt; 'tahun.desc']);
        $rencana = $this-&gt;fetchData('rencana_anggaran', ['order' =&gt; 'created_at.desc']);
        return view('pages.anggaran', compact('anggaran', 'realisasi', 'rencana'));
    }

    public function layanan()
    {
        return view('pages.layanan');
    }

    public function faq()
    {
        $faqs = array_filter($this-&gt;fetchData('faq', ['order' =&gt; 'urutan.asc, created_at.asc']), function($item) {
            return ($item['status'] ?? 'published') === 'published';
        });
        return view('pages.faq', compact('faqs'));
    }
}
