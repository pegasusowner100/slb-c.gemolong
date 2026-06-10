@extends('layouts.app')

@section('title', 'Profil Sekolah - SLB-C YPSLB Gemolong')

@section('content')
    &lt;div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"&gt;
        &lt;div class="text-center mb-12"&gt;
            &lt;h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4"&gt;Profil Sekolah&lt;/h1&gt;
            &lt;div class="w-24 h-1 bg-green-600 mx-auto"&gt;&lt;/div&gt;
        &lt;/div&gt;

        &lt;div class="bg-white rounded-2xl shadow-xl p-8 mb-8"&gt;
            &lt;h2 class="text-2xl font-bold text-gray-800 mb-6"&gt;{{ $profil['nama_sekolah'] ?? 'SLB-C YPSLB Gemolong' }}&lt;/h2&gt;
            &lt;div class="grid md:grid-cols-2 gap-8"&gt;
                &lt;div&gt;
                    @if(!empty($profil['gambar_gedung']))
                        &lt;img src="{{ $profil['gambar_gedung'] }}" alt="Gedung Sekolah" class="w-full rounded-xl shadow-lg"&gt;
                    @else
                        &lt;img src="https://picsum.photos/seed/gedung-sekolah/600/400.jpg" alt="Gedung Sekolah" class="w-full rounded-xl shadow-lg"&gt;
                    @endif
                &lt;/div&gt;
                &lt;div class="space-y-4"&gt;
                    &lt;div&gt;
                        &lt;h3 class="text-lg font-semibold text-gray-700"&gt;Akreditasi&lt;/h3&gt;
                        &lt;p class="text-gray-600"&gt;{{ $profil['akreditasi'] ?? 'A' }}&lt;/p&gt;
                    &lt;/div&gt;
                    &lt;div&gt;
                        &lt;h3 class="text-lg font-semibold text-gray-700"&gt;Visi&lt;/h3&gt;
                        &lt;p class="text-gray-600"&gt;{{ $profil['visi'] ?? 'Menjadikan SLB-C YPSLB Gemolong sebagai lembaga pendidikan luar biasa yang unggul.' }}&lt;/p&gt;
                    &lt;/div&gt;
                    &lt;div&gt;
                        &lt;h3 class="text-lg font-semibold text-gray-700"&gt;Misi&lt;/h3&gt;
                        &lt;p class="text-gray-600"&gt;{{ $profil['misi'] ?? 'Menyelenggarakan pendidikan berkualitas untuk anak berkebutuhan khusus.' }}&lt;/p&gt;
                    &lt;/div&gt;
                    &lt;div&gt;
                        &lt;h3 class="text-lg font-semibold text-gray-700"&gt;Alamat&lt;/h3&gt;
                        &lt;p class="text-gray-600"&gt;{{ $profil['alamat'] ?? 'Jl. Pendidikan No. 1, Gemolong, Kabupaten Sragen' }}&lt;/p&gt;
                    &lt;/div&gt;
                &lt;/div&gt;
            &lt;/div&gt;
        &lt;/div&gt;

        @if(!empty($profil['sejarah']))
        &lt;div class="bg-white rounded-2xl shadow-xl p-8 mb-8"&gt;
            &lt;h2 class="text-2xl font-bold text-gray-800 mb-4"&gt;Sejarah&lt;/h2&gt;
            &lt;p class="text-gray-600 leading-relaxed"&gt;{{ $profil['sejarah'] }}&lt;/p&gt;
        &lt;/div&gt;
        @endif

        @if(!empty($profil['nama_kepala_sekolah']))
        &lt;div class="bg-white rounded-2xl shadow-xl p-8"&gt;
            &lt;h2 class="text-2xl font-bold text-gray-800 mb-4"&gt;Kepala Sekolah&lt;/h2&gt;
            &lt;div class="flex items-center gap-6"&gt;
                &lt;img src="{{ $profil['foto_kepala_sekolah'] ?? 'https://picsum.photos/seed/kepsek/200/200.jpg' }}" alt="Kepala Sekolah" class="w-32 h-32 rounded-full object-cover border-4 border-green-600"&gt;
                &lt;div&gt;
                    &lt;h3 class="text-xl font-bold text-gray-800"&gt;{{ $profil['nama_kepala_sekolah'] }}&lt;/h3&gt;
                    &lt;p class="text-gray-600"&gt;{{ $profil['profil_kepala_sekolah'] ?? 'Kepala sekolah yang inovatif dan berdedikasi.' }}&lt;/p&gt;
                &lt;/div&gt;
            &lt;/div&gt;
        &lt;/div&gt;
        @endif
    &lt;/div&gt;
@endsection
