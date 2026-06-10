<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title ?? 'SLB-C YPSLB Gemolong — Mandiri, Berkarakter, Berprestasi'; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Segoe UI', 'Arial', 'Helvetica', 'sans-serif'],
            serif: ['Georgia', 'serif'],
          },
          colors: {
            brand: {
              bg: '#F9F8F4',
              dark: '#1F2D26',
              accent: '#3E6B4E',
              'accent-hover': '#2F5B41',
              muted: '#5F6F65',
              label: '#9FB5A5',
              border: '#E8E4D9',
              footer: '#1F2D26',
              'footer-card': '#2B3A33',
            }
          }
        }
      }
    }
  </script>
  <style>
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #F9F8F4; }
    ::-webkit-scrollbar-thumb { background: #3E6B4E; border-radius: 3px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
    @keyframes slideInLeft { from{opacity:0;transform:translateX(-40px)} to{opacity:1;transform:translateX(0)} }
    @keyframes pulse-ring { 0%{transform:scale(1);opacity:1} 100%{transform:scale(1.4);opacity:0} }
    @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }

    .animate-float { animation: float 3s ease-in-out infinite; }
    .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; }
    .animate-slide-left { animation: slideInLeft 0.8s ease-out forwards; }

    .reveal-card {
      opacity: 0;
      transform: translateX(-40px);
      animation: revealFromLeft 0.85s ease-out forwards;
    }
    @keyframes revealFromLeft {
      from { opacity: 0; transform: translateX(-40px); }
      to { opacity: 1; transform: translateX(0); }
    }

    .gallery-item:hover .gallery-overlay { opacity:1; }
    .news-card:hover .news-img { transform:scale(1.05); }
    .program-card:hover { transform:translateY(-8px); box-shadow:0 25px 50px -12px rgba(0,0,0,0.15); }
    .teacher-card:hover .teacher-img { transform:scale(1.08); }
    .facility-card:hover .facility-img { transform:scale(1.05); }
    .facility-card:hover .facility-overlay { opacity:1; }

    .faq-answer { max-height:0; overflow:hidden; transition:max-height 0.5s ease, padding 0.5s ease; }
    .faq-answer.open { max-height:2000px; }
    .faq-icon { transition:transform 0.3s ease; }
    .faq-item.active .faq-icon { transform:rotate(45deg); }

    .timeline-item::before {
      content:''; position:absolute; left:50%; top:0; bottom:0; width:2px; background:#E8E4D9; transform:translateX(-50%);
    }
    @media(max-width:768px) {
      .timeline-item::before { left:20px; }
    }

    .calendar-day:hover { background:#3E6B4E; color:white; }
    .calendar-day.active { background:#3E6B4E; color:white; }
    .calendar-day.has-event { position:relative; }
    .calendar-day.has-event::after {
      content:''; position:absolute; bottom:4px; left:50%; transform:translateX(-50%);
      width:5px; height:5px; background:#3E6B4E; border-radius:50%;
    }

    .partner-logo { filter:grayscale(100%) opacity(0.5); transition:all 0.3s; }
    .partner-logo:hover { filter:grayscale(0%) opacity(1); }

    .ppdb-step.completed .step-circle { background:#3E6B4E; color:white; border-color:#3E6B4E; }
    .ppdb-step.current .step-circle { background:#3E6B4E; color:white; border-color:#3E6B4E; box-shadow:0 0 0 4px rgba(62,107,78,0.2); }
    .ppdb-step.pending .step-circle { background:transparent; color:#5F6F65; border-color:#E8E4D9; }

    .toast {
      position:fixed; bottom:2rem; right:2rem; z-index:200; padding:1rem 1.5rem;
      background:#3E6B4E; color:white; border-radius:0.5rem; font-size:0.875rem;
      box-shadow:0 10px 30px rgba(0,0,0,0.2); transform:translateY(100px); opacity:0;
      transition:all 0.4s ease;
    }
    .toast.show { transform:translateY(0); opacity:1; }

    .hero-sweep-text {
      position: relative;
      display: inline-block;
      color: #FFD700;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.15));
      background-size: 200% 200%;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      animation: heroSweep 8s ease infinite;
      text-shadow: 0 0 12px rgba(255, 215, 0, 0.65);
    }
    .hero-sweep-text::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,0.18) 45%, rgba(255,255,255,0.32) 55%, transparent 100%);
      transform: translateX(-100%);
      animation: heroSweepGlow 3.5s ease-in-out infinite;
      pointer-events: none;
    }
    @keyframes heroSweep {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    @keyframes heroSweepGlow {
      0% { transform: translateX(-120%); opacity: 0; }
      40% { opacity: 1; }
      60% { opacity: 1; }
      100% { transform: translateX(220%); opacity: 0; }
    }
    @keyframes marquee {
      0% { transform: translateX(0); }
      100% { transform: translateX(-100%); }
    }
    .animate-marquee {
      animation: marquee 30s linear infinite;
      display: flex;
    }
    .animate-marquee:hover {
      animation-play-state: paused;
    }
    .shimmer {
      background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.08) 50%, transparent 100%);
      background-size: 200% 100%;
      animation: shimmer 2s infinite;
    }
    /* Compact cards: reduce padding/height/font-size for dashboard palettes */
    .compact-cards .p-6 { padding: 0.75rem !important; }
    .compact-cards .p-4 { padding: 0.5rem !important; }
    .compact-cards .text-4xl { font-size: 1.25rem !important; }
    .compact-cards .text-2xl { font-size: 1rem !important; }
    .compact-cards .text-xs { font-size: 0.65rem !important; }
    .compact-cards .w-12.h-12 { width: 36px !important; height: 36px !important; }
    .compact-cards img { height: 48px !important; }
    .compact-cards table th, .compact-cards table td { padding: 0.4rem 0.6rem !important; }
    .compact-cards .rounded-2xl { border-radius: 0.75rem !important; }
    .compact-cards .inline-block.px-3.py-2 { padding: 0.35rem 0.6rem !important; font-size:0.8rem !important; }
    /* Compact card adjustments (no vertical scaling) */
    .compact-cards .gap-6 { gap: 0.5rem !important; }
    .compact-cards .mb-8 { margin-bottom: 0.5rem !important; }

    /* Aggressive 50% height reduction for compact mode */
    .compact-cards .bg-white.rounded-2xl,
    .compact-cards .bg-white.rounded-xl,
    .compact-cards .bg-gradient-to-r {
      transform: scaleY(0.5);
      transform-origin: top;
    }

    /* Make title and date blue for the four latest-content cards */
    .bg-gradient-to-r.from-purple-50 h3,
    .bg-gradient-to-r.from-orange-50 h3,
    .bg-gradient-to-r.from-emerald-50 h3,
    .bg-gradient-to-r.from-sky-50 h3 {
      color: #1e40af !important; /* blue-800 */
    }
    .bg-gradient-to-r.from-purple-50 ~ .p-4 .text-xs,
    .bg-gradient-to-r.from-orange-50 ~ .p-4 .text-xs,
    .bg-gradient-to-r.from-emerald-50 ~ .p-4 .text-xs,
    .bg-gradient-to-r.from-sky-50 ~ .p-4 .text-xs {
      color: #1e40af !important;
    }
    .compact-cards .bg-gradient-to-r.from-purple-50 h3,
    .compact-cards .bg-gradient-to-r.from-orange-50 h3,
    .compact-cards .bg-gradient-to-r.from-emerald-50 h3,
    .compact-cards .bg-gradient-to-r.from-sky-50 h3 {
      color: #1e40af !important; /* blue-800 */
    }
    .compact-cards .bg-gradient-to-r.from-purple-50 ~ .p-4 .text-xs,
    .compact-cards .bg-gradient-to-r.from-orange-50 ~ .p-4 .text-xs,
    .compact-cards .bg-gradient-to-r.from-emerald-50 ~ .p-4 .text-xs,
    .compact-cards .bg-gradient-to-r.from-sky-50 ~ .p-4 .text-xs {
      color: #1e40af !important;
    }
    /* Uniform font, size, and blue color for palette titles, section headers, and dates */
    .max-w-7xl .text-xs.font-bold.uppercase,
    .max-w-7xl .p-4 .text-xs,
    .max-w-7xl .p-6 h3.font-semibold {
      font-family: 'Segoe UI', 'Arial', Helvetica, sans-serif !important;
      font-size: 0.8rem !important;
      color: #1e40af !important; /* blue-800 */
      line-height: 1.1 !important;
    }
    /* Keep the numeric counts larger but use the same font family */
    .max-w-7xl .text-4xl {
      font-family: 'Segoe UI', 'Arial', Helvetica, sans-serif !important;
      font-size: 1.75rem !important;
      line-height: 1 !important;
      color: #1e40af !important;
    }
    
    /* Page Hero Style */
    .page-hero {
      padding-top: 5rem;
      padding-bottom: 0.5rem;
      background-color: #4c3900 !important;
    }
    .page-hero span {
      margin-bottom: -0.3rem;
      display: block;
      color: #f3e1a3 !important;
    }
    .page-hero h1,
    .page-hero h2,
    .page-hero h3 {
      color: #ffffff !important;
    }
    
  </style>
</head>
<body class="bg-brand-bg text-brand-dark font-sans">
