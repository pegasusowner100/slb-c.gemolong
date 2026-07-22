<?php
// components/share.php
// Usage:
// set $share_url and $share_title before include
// optional: set $compact = true for small inline icons

if (!isset($share_url) || empty($share_url)) {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $proto . '://' . ($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ''));
    $share_url = $host . rtrim(defined('BASE_URL') ? BASE_URL : '/', '/') . '/' . ltrim(($_SERVER['REQUEST_URI'] ?? ''), '/');
}

if (!isset($share_title) || empty($share_title)) {
    $share_title = defined('SITE_NAME') ? SITE_NAME : 'Website';
}

$compact = $compact ?? false;

if (!function_exists('esc')) {
    function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

$encUrl = rawurlencode($share_url);
$encTitle = rawurlencode($share_title);

if ($compact) {
    ?>
    <div class="share-buttons-compact flex items-center gap-2">
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-2xl bg-[#1877F2] shadow-sm flex items-center justify-center text-white hover:shadow-md" title="Bagikan ke Facebook"><iconify-icon icon="mdi:facebook" class="w-6 h-6"></iconify-icon></a>
      <a href="https://twitter.com/intent/tweet?text=<?php echo $encTitle; ?>&url=<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-2xl bg-[#1DA1F2] shadow-sm flex items-center justify-center text-white hover:shadow-md" title="Bagikan ke X/Twitter"><iconify-icon icon="mdi:twitter" class="w-6 h-6"></iconify-icon></a>
      <a href="https://api.whatsapp.com/send?text=<?php echo $encTitle; ?>%20<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-2xl bg-[#25D366] shadow-sm flex items-center justify-center text-white hover:shadow-md" title="Bagikan ke WhatsApp"><iconify-icon icon="mdi:whatsapp" class="w-6 h-6"></iconify-icon></a>
      <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-2xl bg-[#0A66C2] shadow-sm flex items-center justify-center text-white hover:shadow-md" title="Bagikan ke LinkedIn"><iconify-icon icon="mdi:linkedin" class="w-6 h-6"></iconify-icon></a>
      <button type="button" onclick="copyShareLink('<?php echo esc($share_url); ?>', this)" class="w-11 h-11 rounded-2xl bg-slate-900 shadow-sm flex items-center justify-center text-white hover:shadow-md" title="Salin tautan"><iconify-icon icon="mdi:link-variant" class="w-6 h-6"></iconify-icon></button>
    </div>
    <?php
} else {
    ?>
    <div class="share-buttons mt-4">
      <div class="flex flex-wrap justify-start items-center gap-1">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center p-1 text-[#1877F2] hover:opacity-80" title="Bagikan ke Facebook"><iconify-icon icon="mdi:facebook" style="font-size:2.6rem;width:2.6rem;height:2.6rem"></iconify-icon></a>
        <a href="https://twitter.com/intent/tweet?text=<?php echo $encTitle; ?>&url=<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center p-1 text-[#1DA1F2] hover:opacity-80" title="Bagikan ke X/Twitter"><iconify-icon icon="mdi:twitter" style="font-size:2.6rem;width:2.6rem;height:2.6rem"></iconify-icon></a>
        <a href="https://api.whatsapp.com/send?text=<?php echo $encTitle; ?>%20<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center p-1 text-[#25D366] hover:opacity-80" title="Bagikan ke WhatsApp"><iconify-icon icon="mdi:whatsapp" style="font-size:2.6rem;width:2.6rem;height:2.6rem"></iconify-icon></a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center p-1 text-[#0A66C2] hover:opacity-80" title="Bagikan ke LinkedIn"><iconify-icon icon="mdi:linkedin" style="font-size:2.6rem;width:2.6rem;height:2.6rem"></iconify-icon></a>
        <a href="https://t.me/share/url?url=<?php echo $encUrl; ?>&text=<?php echo $encTitle; ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center p-1 text-[#0088CC] hover:opacity-80" title="Bagikan ke Telegram"><iconify-icon icon="mdi:telegram" style="font-size:2.6rem;width:2.6rem;height:2.6rem"></iconify-icon></a>
        <a href="mailto:?subject=<?php echo $encTitle; ?>&body=<?php echo $encUrl; ?>" class="flex items-center justify-center p-1 text-[#DD4B39] hover:opacity-80" title="Bagikan lewat Email"><iconify-icon icon="mdi:email" style="font-size:2.6rem;width:2.6rem;height:2.6rem"></iconify-icon></a>
        <button type="button" onclick="copyShareLink('<?php echo esc($share_url); ?>', this)" class="flex items-center justify-center p-1 text-[#475569] hover:opacity-80" title="Salin tautan"><iconify-icon icon="mdi:link-variant" style="font-size:2.6rem;width:2.6rem;height:2.6rem"></iconify-icon></button>
      </div>
    </div>
    <?php
}

// JS helper untuk menyalin link (define once)
?>
<script>
if (typeof copyShareLink === 'undefined') {
  function copyShareLink(url, btn) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(function() {
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="text-xs font-semibold">Tersalin</span>';
        setTimeout(function(){ btn.innerHTML = orig; }, 1500);
      }).catch(function(){ alert('Salin tautan gagal.'); });
    } else {
      try {
        const ta = document.createElement('textarea');
        ta.value = url;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        alert('Tautan disalin.');
      } catch(e) { alert('Salin tautan gagal.'); }
    }
  }
}
</script>

<?php
