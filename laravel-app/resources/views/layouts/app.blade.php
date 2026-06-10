&lt;!DOCTYPE html&gt;
&lt;html lang="id"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;@yield('title', 'SLB-C YPSLB Gemolong')&lt;/title&gt;
    &lt;script src="https://cdn.tailwindcss.com"&gt;&lt;/script&gt;
    &lt;script src="https://cdn.jsdelivr.net/npm/iconify-icon@2.1.0/dist/iconify-icon.min.js"&gt;&lt;/script&gt;
    &lt;style&gt;
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body class="bg-gray-50"&gt;
    &lt;nav class="bg-gradient-to-r from-green-800 to-green-700 shadow-lg sticky top-0 z-50"&gt;
        &lt;div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"&gt;
            &lt;div class="flex justify-between items-center h-16"&gt;
                &lt;div class="flex items-center"&gt;
                    &lt;a href="/" class="text-white font-bold text-xl"&gt;SLB-C YPSLB Gemolong&lt;/a&gt;
                &lt;/div&gt;
                &lt;div class="hidden md:flex items-center space-x-6"&gt;
                    &lt;a href="{{ route('profil') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Profil&lt;/a&gt;
                    &lt;a href="{{ route('program') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Program&lt;/a&gt;
                    &lt;a href="{{ route('fasilitas') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Fasilitas&lt;/a&gt;
                    &lt;a href="{{ route('prestasi') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Prestasi&lt;/a&gt;
                    &lt;a href="{{ route('berita') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Berita&lt;/a&gt;
                    &lt;a href="{{ route('guru') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Guru&lt;/a&gt;
                    &lt;a href="{{ route('siswa') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Siswa&lt;/a&gt;
                    &lt;a href="{{ route('galeri') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Galeri&lt;/a&gt;
                    &lt;a href="{{ route('statistik') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Statistik&lt;/a&gt;
                    &lt;a href="{{ route('anggaran') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Anggaran&lt;/a&gt;
                    &lt;a href="{{ route('layanan') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;Layanan&lt;/a&gt;
                    &lt;a href="{{ route('faq') }}" class="text-white hover:text-yellow-300 transition-colors font-medium"&gt;FAQ&lt;/a&gt;
                &lt;/div&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/nav&gt;

    &lt;main class="py-8"&gt;
        @yield('content')
    &lt;/main&gt;

    &lt;footer class="bg-gray-800 text-white py-8 mt-12"&gt;
        &lt;div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center"&gt;
            &lt;p class="text-gray-300"&gt;&amp;copy; {{ date('Y') }} SLB-C YPSLB Gemolong. All rights reserved.&lt;/p&gt;
        &lt;/div&gt;
    &lt;/footer&gt;
&lt;/body&gt;
&lt;/html&gt;
