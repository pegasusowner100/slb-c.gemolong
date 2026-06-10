<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($title) ? $title : 'Admin Dashboard — SLB-C YPSLB Gemolong'; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');
    
    body {
      font-family: 'Segoe UI', 'Arial', Helvetica, sans-serif;
    }
    
    .font-serif {
      font-family: 'Georgia', serif;
    }
    
    /* Table header (thead) warna krem untuk halaman admin */
    table thead th {
      background-color: #F5ECD8 !important;
      color: #1f2937 !important;
      border-bottom: 1px solid rgba(0,0,0,0.06) !important;
    }
  </style>
  <script>
    function filterAdminTable(input) {
      const query = (input.value || '').trim().toLowerCase();
      const selector = input.dataset.filterSelector;
      if (!selector) return;

      const elements = document.querySelectorAll(selector);
      let visibleCount = 0;

      elements.forEach(element => {
        const text = (element.dataset.search || element.textContent || '').toLowerCase();
        const show = query === '' || text.includes(query);
        element.style.display = show ? '' : 'none';
        if (show) {
          visibleCount++;
        }
      });

      const noResultsSelector = input.dataset.noResultsSelector;
      if (noResultsSelector) {
        const noResults = document.querySelector(noResultsSelector);
        if (noResults) {
          noResults.style.display = visibleCount === 0 ? '' : 'none';
        }
      }
    }
  </script>
</head>
<body class="bg-[#F9F8F4] flex min-h-screen">
