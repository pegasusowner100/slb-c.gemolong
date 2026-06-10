&lt;?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StatsController extends Controller
{
    public function index()
    {
        $supabaseUrl = 'https://cmmzoykcoiystbgndaif.supabase.co/rest/v1';
        $supabaseKey = 'sb_publishable_t8dDSxNlTmiUtu1_hR16-w_goO6xAes';

        try {
            // Fetch profile sekolah
            $profilResponse = Http::withHeaders([
                'apikey' =&gt; $supabaseKey,
                'Authorization' =&gt; 'Bearer ' . $supabaseKey,
            ])-&gt;get($supabaseUrl . '/profil_sekolah', [
                'limit' =&gt; 1,
            ]);
            
            $profil = $profilResponse-&gt;json()[0] ?? [];

            // Fetch counts for all entities
            $siswaCount = $this-&gt;getCount($supabaseUrl, $supabaseKey, 'siswa');
            $guruCount = $this-&gt;getCount($supabaseUrl, $supabaseKey, 'guru');
            $beritaCount = $this-&gt;getCount($supabaseUrl, $supabaseKey, 'berita');
            $galeriCount = $this-&gt;getCount($supabaseUrl, $supabaseKey, 'galeri');
            $ppdbCount = $this-&gt;getCount($supabaseUrl, $supabaseKey, 'ppdb');
            $faqCount = $this-&gt;getCount($supabaseUrl, $supabaseKey, 'faq');
            $prestasiCount = $this-&gt;getCount($supabaseUrl, $supabaseKey, 'prestasi');

            return view('stats', compact(
                'profil',
                'siswaCount',
                'guruCount',
                'beritaCount',
                'galeriCount',
                'ppdbCount',
                'faqCount',
                'prestasiCount'
            ));
        } catch (\Exception $e) {
            return view('stats', [
                'profil' =&gt; [],
                'siswaCount' =&gt; 0,
                'guruCount' =&gt; 0,
                'beritaCount' =&gt; 0,
                'galeriCount' =&gt; 0,
                'ppdbCount' =&gt; 0,
                'faqCount' =&gt; 0,
                'prestasiCount' =&gt; 0,
            ]);
        }
    }

    private function getCount($url, $key, $table)
    {
        try {
            $response = Http::withHeaders([
                'apikey' =&gt; $key,
                'Authorization' =&gt; 'Bearer ' . $key,
                'Prefer' =&gt; 'count=exact',
            ])-&gt;get($url . '/' . $table, ['limit' =&gt; 1]);
            
            // Get count from header
            $countHeader = $response-&gt;header('content-range');
            if ($countHeader) {
                preg_match('/\/(\d+)/', $countHeader, $matches);
                return isset($matches[1]) ? (int)$matches[1] : 0;
            }
            
            // Fallback to counting items
            return count($response-&gt;json() ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
