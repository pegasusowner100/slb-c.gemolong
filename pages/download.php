<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Download — " . SITE_NAME;

// Get all published downloads
$downloads = [];
$categories = [];
if ($supabaseConnected) {
    $result = supabaseSelect('download', ['status' => 'eq.published', 'order' => 'urutan.asc, created_at.desc']);
    if ($result['success']) {
        $downloads = $result['data'] ?? [];
        // Extract unique categories
        foreach ($downloads as $download) {
            if (!empty($download['kategori']) && !in_array($download['kategori'], $categories)) {
                $categories[] = $download['kategori'];
            }
        }
    }
}

include '../components/head.php';
?>
<body class="text-brand-dark font-sans glass-body">
  <?php include '../components/navbar.php'; ?>
  <div class="glass-content-wrapper">

  <!-- Hero Section -->
  <section class="page-hero bg-brand-dark">
    <div class="max-w-7xl mx-auto px-6">
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">File & Dokumen</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100"><em>Download</em></h1>
    </div>
  </section>

  <!-- Download Section -->
  <section class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section space-y-8">
        <!-- Intro Text -->
        <div class="fade-in-up delay-200">
          <p class="text-brand-muted text-sm leading-relaxed">
            Akses berbagai file dan dokumen penting dari sekolah. Termasuk formulir pendaftaran, kurikulum, pedoman, laporan, dan berbagai dokumen penting lainnya.
          </p>
        </div>

        <!-- Filter Section -->
        <div class="fade-in-up delay-300">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <label class="block text-xs font-bold text-brand-dark mb-2 uppercase tracking-wide">Cari</label>
              <input type="text" id="searchInput" placeholder="Cari judul atau deskripsi..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm">
            </div>
            <div class="flex-1">
              <label class="block text-xs font-bold text-brand-dark mb-2 uppercase tracking-wide">Kategori</label>
              <select id="categoryFilter" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Downloads Table -->
        <?php if (empty($downloads)): ?>
          <div class="text-center py-12 fade-in-up delay-400">
            <iconify-icon icon="lucide:download-cloud" class="text-6xl text-brand-muted/30 mb-4 block"></iconify-icon>
            <p class="text-brand-muted">Belum ada file yang dapat diunduh.</p>
          </div>
        <?php else: ?>
          <div class="bg-white rounded-2xl border border-brand-border shadow-md overflow-hidden fade-in-up delay-400">
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="bg-slate-50">
                  <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="downloadsTable">
                  <?php foreach ($downloads as $download): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors download-item" data-category="<?php echo htmlspecialchars($download['kategori'] ?? ''); ?>" data-title="<?php echo htmlspecialchars(strtolower($download['judul'] ?? '')); ?>" data-desc="<?php echo htmlspecialchars(strtolower($download['deskripsi'] ?? '')); ?>">
                      <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($download['judul']); ?></div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-sm text-slate-600"><?php echo htmlspecialchars(substr($download['deskripsi'] ?? '', 0, 60)) . (strlen($download['deskripsi'] ?? '') > 60 ? '...' : ''); ?></div>
                      </td>
                      <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-brand-accent/10 text-brand-accent text-xs font-bold rounded-md uppercase tracking-wide">
                          <?php echo htmlspecialchars($download['kategori'] ?? 'Umum'); ?>
                        </span>
                      </td>
                      <td class="px-6 py-4">
                        <a href="<?php echo htmlspecialchars($download['file_url']); ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-xs uppercase tracking-widest rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                          <iconify-icon icon="lucide:download" class="text-sm"></iconify-icon>
                          Download
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <div id="noResults" class="hidden text-center py-12">
            <iconify-icon icon="lucide:search-x" class="text-6xl text-brand-muted/30 mb-4 block"></iconify-icon>
            <p class="text-brand-muted">Tidak ada file yang sesuai dengan pencarian Anda.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Filter Script -->
  <script>
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const downloadItems = document.querySelectorAll('.download-item');
    const noResults = document.getElementById('noResults');
    const downloadsTable = document.getElementById('downloadsTable');

    function filterDownloads() {
      const searchTerm = searchInput.value.toLowerCase();
      const selectedCategory = categoryFilter.value;
      let visibleCount = 0;

      downloadItems.forEach(item => {
        const category = item.dataset.category;
        const title = item.dataset.title;
        const description = item.dataset.desc;

        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        const matchesCategory = !selectedCategory || category === selectedCategory;

        if (matchesSearch && matchesCategory) {
          item.style.display = '';
          visibleCount++;
        } else {
          item.style.display = 'none';
        }
      });

      if (visibleCount === 0 && downloadItems.length > 0) {
        noResults.classList.remove('hidden');
        downloadsTable.classList.add('hidden');
      } else {
        noResults.classList.add('hidden');
        downloadsTable.classList.remove('hidden');
      }
    }

    searchInput.addEventListener('input', filterDownloads);
    categoryFilter.addEventListener('change', filterDownloads);
  </script>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
