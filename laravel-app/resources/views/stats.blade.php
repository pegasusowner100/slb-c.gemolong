&lt;!DOCTYPE html&gt;
&lt;html lang="id"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Statistik Sekolah - SLB-C YPSLB Gemolong&lt;/title&gt;
    &lt;script src="https://cdn.tailwindcss.com"&gt;&lt;/script&gt;
    &lt;script src="https://cdn.jsdelivr.net/npm/iconify-icon@2.1.0/dist/iconify-icon.min.js"&gt;&lt;/script&gt;
    &lt;style&gt;
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .counter {
            font-feature-settings: "tnum", "tnum";
            font-variant-numeric: tabular-nums;
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body class="bg-gray-50 min-h-screen"&gt;
    &lt;div class="container mx-auto px-6 py-16"&gt;
        &lt;div class="text-center mb-16"&gt;
            &lt;h1 class="text-5xl font-bold text-gray-900 mb-4"&gt;Statistik Sekolah&lt;/h1&gt;
            &lt;p class="text-xl text-gray-600"&gt;SLB-C YPSLB Gemolong&lt;/p&gt;
        &lt;/div&gt;

        &lt;div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16"&gt;
            &lt;div class="animate-fade-in-up [animation-delay:0.1s] bg-gradient-to-br from-blue-500 to-blue-600 p-8 rounded-3xl shadow-2xl hover:shadow-blue-500/30 hover:-translate-y-2 transition-all duration-300"&gt;
                &lt;div class="flex items-center justify-between mb-6"&gt;
                    &lt;div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center"&gt;
                        &lt;iconify-icon icon="lucide:users" class="text-white text-3xl"&gt;&lt;/iconify-icon&gt;
                    &lt;/div&gt;
                    &lt;span class="text-white/60 text-sm font-semibold uppercase tracking-widest"&gt;Siswa&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class="counter text-6xl font-extrabold text-white mb-2" data-target="{{ $siswaCount }}"&gt;0&lt;/div&gt;
                &lt;div class="text-blue-100 text-lg"&gt;Total Siswa Aktif&lt;/div&gt;
            &lt;/div&gt;

            &lt;div class="animate-fade-in-up [animation-delay:0.2s] bg-gradient-to-br from-emerald-500 to-emerald-600 p-8 rounded-3xl shadow-2xl hover:shadow-emerald-500/30 hover:-translate-y-2 transition-all duration-300"&gt;
                &lt;div class="flex items-center justify-between mb-6"&gt;
                    &lt;div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center"&gt;
                        &lt;iconify-icon icon="lucide:user-check" class="text-white text-3xl"&gt;&lt;/iconify-icon&gt;
                    &lt;/div&gt;
                    &lt;span class="text-white/60 text-sm font-semibold uppercase tracking-widest"&gt;Guru&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class="counter text-6xl font-extrabold text-white mb-2" data-target="{{ $guruCount }}"&gt;0&lt;/div&gt;
                &lt;div class="text-emerald-100 text-lg"&gt;Tenaga Pendidik&lt;/div&gt;
            &lt;/div&gt;

            &lt;div class="animate-fade-in-up [animation-delay:0.3s] bg-gradient-to-br from-purple-500 to-purple-600 p-8 rounded-3xl shadow-2xl hover:shadow-purple-500/30 hover:-translate-y-2 transition-all duration-300"&gt;
                &lt;div class="flex items-center justify-between mb-6"&gt;
                    &lt;div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center"&gt;
                        &lt;iconify-icon icon="lucide:file-text" class="text-white text-3xl"&gt;&lt;/iconify-icon&gt;
                    &lt;/div&gt;
                    &lt;span class="text-white/60 text-sm font-semibold uppercase tracking-widest"&gt;Berita&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class="counter text-6xl font-extrabold text-white mb-2" data-target="{{ $beritaCount }}"&gt;0&lt;/div&gt;
                &lt;div class="text-purple-100 text-lg"&gt;Artikel Berita&lt;/div&gt;
            &lt;/div&gt;

            &lt;div class="animate-fade-in-up [animation-delay:0.4s] bg-gradient-to-br from-orange-500 to-orange-600 p-8 rounded-3xl shadow-2xl hover:shadow-orange-500/30 hover:-translate-y-2 transition-all duration-300"&gt;
                &lt;div class="flex items-center justify-between mb-6"&gt;
                    &lt;div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center"&gt;
                        &lt;iconify-icon icon="lucide:images" class="text-white text-3xl"&gt;&lt;/iconify-icon&gt;
                    &lt;/div&gt;
                    &lt;span class="text-white/60 text-sm font-semibold uppercase tracking-widest"&gt;Galeri&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class="counter text-6xl font-extrabold text-white mb-2" data-target="{{ $galeriCount }}"&gt;0&lt;/div&gt;
                &lt;div class="text-orange-100 text-lg"&gt;Foto &amp; Video&lt;/div&gt;
            &lt;/div&gt;
        &lt;/div&gt;

        &lt;div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8"&gt;
            &lt;div class="animate-fade-in-up [animation-delay:0.5s] bg-gradient-to-br from-pink-500 to-pink-600 p-8 rounded-3xl shadow-2xl hover:shadow-pink-500/30 hover:-translate-y-2 transition-all duration-300"&gt;
                &lt;div class="flex items-center justify-between mb-6"&gt;
                    &lt;div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center"&gt;
                        &lt;iconify-icon icon="lucide:clipboard-list" class="text-white text-3xl"&gt;&lt;/iconify-icon&gt;
                    &lt;/div&gt;
                    &lt;span class="text-white/60 text-sm font-semibold uppercase tracking-widest"&gt;PPDB&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class="counter text-6xl font-extrabold text-white mb-2" data-target="{{ $ppdbCount }}"&gt;0&lt;/div&gt;
                &lt;div class="text-pink-100 text-lg"&gt;Pendaftar PPDB&lt;/div&gt;
            &lt;/div&gt;

            &lt;div class="animate-fade-in-up [animation-delay:0.6s] bg-gradient-to-br from-indigo-500 to-indigo-600 p-8 rounded-3xl shadow-2xl hover:shadow-indigo-500/30 hover:-translate-y-2 transition-all duration-300"&gt;
                &lt;div class="flex items-center justify-between mb-6"&gt;
                    &lt;div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center"&gt;
                        &lt;iconify-icon icon="lucide:help-circle" class="text-white text-3xl"&gt;&lt;/iconify-icon&gt;
                    &lt;/div&gt;
                    &lt;span class="text-white/60 text-sm font-semibold uppercase tracking-widest"&gt;FAQ&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class="counter text-6xl font-extrabold text-white mb-2" data-target="{{ $faqCount }}"&gt;0&lt;/div&gt;
                &lt;div class="text-indigo-100 text-lg"&gt;Pertanyaan &amp; Jawaban&lt;/div&gt;
            &lt;/div&gt;

            &lt;div class="animate-fade-in-up [animation-delay:0.7s] bg-gradient-to-br from-amber-500 to-amber-600 p-8 rounded-3xl shadow-2xl hover:shadow-amber-500/30 hover:-translate-y-2 transition-all duration-300"&gt;
                &lt;div class="flex items-center justify-between mb-6"&gt;
                    &lt;div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center"&gt;
                        &lt;iconify-icon icon="lucide:trophy" class="text-white text-3xl"&gt;&lt;/iconify-icon&gt;
                    &lt;/div&gt;
                    &lt;span class="text-white/60 text-sm font-semibold uppercase tracking-widest"&gt;Prestasi&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class="counter text-6xl font-extrabold text-white mb-2" data-target="{{ $prestasiCount }}"&gt;0&lt;/div&gt;
                &lt;div class="text-amber-100 text-lg"&gt;Prestasi Nasional&lt;/div&gt;
            &lt;/div&gt;

            &lt;div class="animate-fade-in-up [animation-delay:0.8s] bg-gradient-to-br from-gray-700 to-gray-800 p-8 rounded-3xl shadow-2xl hover:shadow-gray-500/30 hover:-translate-y-2 transition-all duration-300"&gt;
                &lt;div class="flex items-center justify-between mb-6"&gt;
                    &lt;div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center"&gt;
                        &lt;iconify-icon icon="lucide:calendar" class="text-white text-3xl"&gt;&lt;/iconify-icon&gt;
                    &lt;/div&gt;
                    &lt;span class="text-white/60 text-sm font-semibold uppercase tracking-widest"&gt;Tahun&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class="counter text-6xl font-extrabold text-white mb-2" data-target="{{ $profil['tahun_berdiri'] ?? 1990 }}"&gt;0&lt;/div&gt;
                &lt;div class="text-gray-300 text-lg"&gt;Tahun Berdiri&lt;/div&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/div&gt;

    &lt;script&gt;
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.counter');
            
            counters.forEach(counter =&gt; {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // 2 seconds
                const step = target / (duration / 16); // 60fps
                let current = 0;

                const updateCounter = () =&gt; {
                    if (current &lt; target) {
                        current += step;
                        counter.textContent = Math.floor(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };

                updateCounter();
            });
        });
    &lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
