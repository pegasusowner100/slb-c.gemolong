<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Pengumuman — " . SITE_NAME;

include '../components/head.php';
?>
<body class="bg-brand-bg text-brand-dark font-sans">
  <?php include '../components/navbar.php'; ?>
  <!-- React Pengumuman App -->
  <div id="root"></div>

  <!-- React and ReactDOM -->
  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  
  <!-- Babel for JSX -->
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

  <!-- Supabase SDK -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

  <!-- PDF.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

  <script type="text/babel">
    const { useState, useEffect, useMemo, useRef } = React;

    // --- KONFIGURASI SUPABASE ---
    const SUPABASE_URL = "<?php echo SUPABASE_URL; ?>";
    const SUPABASE_ANON_KEY = "<?php echo SUPABASE_KEY; ?>";
    const supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

    // --- MOCK LUCIDE ICONS (SVG) ---
    const Icon = ({ path, size = 24, className = '', viewBox = "0 0 24 24" }) => (
      <svg className={className} width={size} height={size} fill="none" viewBox={viewBox} stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        {path}
      </svg>
    );

    const FileText = (props) => <Icon {...props} path={<><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></>} />;
    const Search = (props) => <Icon {...props} path={<><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></>} />;
    const Clock = (props) => <Icon {...props} path={<><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></>} />;
    const Building2 = (props) => <Icon {...props} path={<><path d="M2 22h20"/><path d="M4 22V4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v18"/><path d="M9 22v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/></>} />;
    const Download = (props) => <Icon {...props} path={<><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></>} />;
    const Sparkles = (props) => <Icon {...props} path={<><path d="m12 3 1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M3 5h4"/><path d="M19 17v4"/><path d="M17 19h4"/></>} />;

    const PDFToPhoto = ({ url }) => {
      const [pages, setPages] = useState([]);
      const [loading, setLoading] = useState(false);

      useEffect(() => {
        if (!url) return;
        const render = async () => {
          setLoading(true);
          try {
            const loadingTask = pdfjsLib.getDocument(url);
            const pdf = await loadingTask.promise;
            const pageImgs = [];
            for (let i = 1; i <= pdf.numPages; i++) {
              const page = await pdf.getPage(i);
              const viewport = page.getViewport({ scale: 1.5 });
              const canvas = document.createElement('canvas');
              const context = canvas.getContext('2d');
              canvas.height = viewport.height;
              canvas.width = viewport.width;
              await page.render({ canvasContext: context, viewport }).promise;
              pageImgs.push(canvas.toDataURL());
            }
            setPages(pageImgs);
          } catch (e) {
            console.error("PDF Render Error:", e);
          } finally {
            setLoading(false);
          }
        };
        render();
      }, [url]);

      if (loading) return (
        <div className="flex flex-col items-center justify-center py-20">
          <div className="animate-spin text-emerald-600 mb-4" style={{ fontSize: 32 }}>
            <Icon path={<><path d="M21 12a9 9 0 1 1-6.219-8.56"/></>} size={32} />
          </div>
          <p className="text-xs font-bold text-gray-400 uppercase tracking-widest text-center">Menyiapkan Tampilan Dokumen...</p>
        </div>
      );

      return (
        <div className="flex flex-col items-center gap-6 w-full">
          {pages.map((img, i) => (
            <div key={i} className="w-full bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
              <img src={img} alt={`Halaman ${i+1}`} className="w-full h-auto select-none" />
              <div className="bg-gray-50 px-4 py-2 text-[9px] font-bold text-gray-400 border-t flex justify-between uppercase tracking-widest italic">
                <span>Halaman {i + 1} dari {pages.length}</span>
                <span>Pratinjau Sistem</span>
              </div>
            </div>
          ))}
        </div>
      );
    };

    function App() {
      const [data, setData] = useState([]);
      const [sources, setSources] = useState(['Gubernur', 'Setda Prov Jateng', 'BKD Prov Jateng', 'Dinas Sosial Prov Jateng', 'PPS Raharjo']);
      const [selected, setSelected] = useState(null);
      const [searchTerm, setSearchTerm] = useState('');
      const [loading, setLoading] = useState(false);

      // Fetch data from Supabase
      const fetchData = async () => {
        setLoading(true);
        const { data: pengumuman, error } = await supabaseClient
          .from('pengumuman')
          .select('*')
          .order('created_at', { ascending: false });
        
        if (error) console.error('Error fetching data:', error);
        else {
          setData(pengumuman || []);
          // Tampilkan otomatis pengumuman terakhir jika belum ada yang dipilih
          if (pengumuman && pengumuman.length > 0 && !selected) {
            setSelected(pengumuman[0]);
          }
        }

        const { data: sourcesData, error: sourcesError } = await supabaseClient
          .from('sumber_info')
          .select('nama');
        
        if (sourcesError) console.error('Error fetching sources:', sourcesError);
        else if (sourcesData) setSources(sourcesData.map(s => s.nama));
        
        setLoading(false);
      };

      useEffect(() => {
        fetchData();
      }, []);

      const filtered = data.filter(d => 
        d.judul?.toLowerCase().includes(searchTerm.toLowerCase()) || 
        d.no?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        d.sumber?.toLowerCase().includes(searchTerm.toLowerCase())
      );

      return (
        <div className="w-full flex flex-col bg-gradient-to-br from-slate-50 via-blue-50/20 to-slate-100 min-h-screen">
          {/* Hero Section */}
          <section className="page-hero bg-brand-dark">
            <div className="max-w-7xl mx-auto px-6">
              <span className="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">Informasi</span>
              <h1 className="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1]">Pengumuman</h1>
            </div>
          </section>

          <div className="flex-1 flex overflow-hidden flex-col md:flex-row gap-4 md:gap-0 p-4 md:p-0">
            {/* Sidebar */}
            <aside className="w-full md:w-96 flex-none bg-white rounded-2xl md:rounded-none md:border-r-2 border-slate-100 flex flex-col overflow-hidden shadow-lg md:shadow-none">
              <div className="p-4 md:p-5 border-b-2 border-slate-100 bg-gradient-to-r from-emerald-50 to-teal-50 flex justify-between items-center">
                <h3 className="text-xs font-black text-emerald-700 uppercase tracking-widest flex items-center gap-2">
                  <Clock size={14} /> Daftar Pengumuman
                </h3>
                {loading && <div className="animate-spin text-emerald-600" style={{ fontSize: 14 }}><Icon path={<><path d="M21 12a9 9 0 1 1-6.219-8.56"/></>} size={14} /></div>}
              </div>
              <div className="flex-1 overflow-y-auto p-4 space-y-2">
                {filtered.map((item) => {
                  const priorityColors = {
                    'Segera': { bg: 'from-red-500 to-red-600', text: 'text-red-100', badge: 'bg-red-100 text-red-700' },
                    'Sangat Penting': { bg: 'from-orange-500 to-orange-600', text: 'text-orange-100', badge: 'bg-orange-100 text-orange-700' },
                    'Penting': { bg: 'from-amber-500 to-amber-600', text: 'text-amber-100', badge: 'bg-amber-100 text-amber-700' },
                    'Normal': { bg: 'from-emerald-500 to-emerald-600', text: 'text-emerald-100', badge: 'bg-emerald-100 text-emerald-700' }
                  };
                  const colors = priorityColors[item.prioritas] || priorityColors['Normal'];
                  const isSelected = selected?.id === item.id;

                  return (
                    <div
                      key={item.id}
                      onClick={() => setSelected(item)}
                      className={`p-4 rounded-xl cursor-pointer transition-all border-2 relative overflow-hidden group ${isSelected ? `bg-gradient-to-br ${colors.bg} ${colors.text} border-transparent shadow-lg` : 'bg-gradient-to-br from-slate-50 to-slate-100 border-slate-200 hover:border-slate-300 hover:shadow-md'}`}
                    >
                      {item.id === data[0]?.id && (
                        <div className={`absolute top-1 right-1 px-2 py-0.5 text-[7px] font-black uppercase tracking-tighter rounded-full flex items-center gap-1 ${isSelected ? 'bg-white/25 text-white' : 'bg-emerald-600 text-white'}`}>
                          <Sparkles size={8} /> NEW
                        </div>
                      )}
                      <div className="flex justify-between items-start mb-2">
                        <div className={`text-[8px] font-black uppercase px-2 py-0.5 rounded-full ${isSelected ? 'bg-white/30' : colors.badge}`}>
                          {item.prioritas}
                        </div>
                        <div className={`text-[8px] font-bold ${isSelected ? 'text-white/70' : 'text-slate-500'}`}>
                          {new Date(item.tgl).toLocaleDateString('id-ID')}
                        </div>
                      </div>
                      <h4 className={`font-black text-sm uppercase tracking-tight leading-tight mb-2 line-clamp-2 ${isSelected ? 'text-white' : 'text-slate-900'}`}>
                        {item.judul}
                      </h4>
                      <div className={`text-[8px] font-bold uppercase flex items-center gap-1 ${isSelected ? 'text-white/70' : 'text-slate-600'}`}>
                        <Building2 size={10} />
                        {item.sumber}
                      </div>
                    </div>
                  );
                })}
                {filtered.length === 0 && (
                  <div className="p-8 text-center text-gray-400">
                    <Search size={32} className="mx-auto mb-2 opacity-20" />
                    <p className="text-xs font-black uppercase">Tidak ada pengumuman</p>
                  </div>
                )}
              </div>
            </aside>

            {/* Content Area */}
            <main className="flex-1 overflow-y-auto p-0 md:p-8">
              {selected ? (
                <div className="h-full flex items-center justify-center md:items-stretch">
                  <div className="w-full bg-white rounded-2xl p-6 md:p-8 shadow-xl border-2 border-slate-200 max-h-full overflow-y-auto">
                    <div className="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-6 pb-6 border-b-2 border-slate-100">
                      <div className="space-y-3 flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                          <span className="text-[9px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase">{selected.no}</span>
                          <span className="text-slate-200">•</span>
                          <span className="text-[9px] font-black text-slate-500 bg-slate-100 px-3 py-1 rounded-full uppercase">{new Date(selected.tgl).toLocaleDateString('id-ID')}</span>
                          <span className="text-slate-200">•</span>
                          <div className="flex items-center gap-2 px-3 py-1 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-full border border-emerald-100">
                            <Building2 size={12} className="text-emerald-600" />
                            <span className="text-[8px] font-black text-emerald-700 uppercase">{selected.sumber}</span>
                          </div>
                          <span className="text-slate-200">•</span>
                          <span className={`text-[9px] font-black uppercase px-3 py-1 rounded-full ${
                            selected.prioritas === 'Segera' ? 'bg-red-100 text-red-700' :
                            selected.prioritas === 'Sangat Penting' ? 'bg-orange-100 text-orange-700' :
                            selected.prioritas === 'Penting' ? 'bg-amber-100 text-amber-700' :
                            'bg-emerald-100 text-emerald-700'
                          }`}>{selected.prioritas}</span>
                        </div>
                        <h2 className="text-2xl md:text-3xl font-black text-slate-900 uppercase tracking-tight leading-tight">
                          {selected.judul}
                        </h2>
                      </div>
                      <a
                        href={selected.pdf}
                        target="_blank"
                        className="w-full md:w-auto bg-gradient-to-r from-slate-800 to-slate-900 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all border border-slate-700 hover:border-slate-600 whitespace-nowrap"
                      >
                        <Download size={16} /> PDF
                      </a>
                    </div>

                    <div className="mb-8 bg-gradient-to-br from-slate-50 to-blue-50/50 p-4 rounded-xl border border-slate-100">
                      <p className="text-slate-700 font-bold leading-relaxed text-sm whitespace-pre-wrap">{selected.konten}</p>
                    </div>

                    <div className="border-t-2 border-slate-100 pt-6">
                      <h3 className="text-xs font-black uppercase tracking-widest text-slate-600 mb-4">📄 Dokumen PDF</h3>
                      <PDFToPhoto url={selected.pdf} />
                    </div>
                  </div>
                </div>
              ) : (
                <div className="h-full flex flex-col items-center justify-center text-gray-300">
                  <Icon path={<><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></>} size={64} className="opacity-20 mb-4" />
                  <p className="text-xs font-black uppercase tracking-[0.3em] text-center">Pilih pengumuman untuk melihat detail</p>
                </div>
              )}
            </main>
          </div>
        </div>
      );
    }

    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);
  </script>
  <?php include '../components/footer.php'; ?>
</body>
</html>