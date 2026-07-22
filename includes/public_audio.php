<?php

function getPublicAudioConfigPath() {
    return __DIR__ . '/public_audio_config.php';
}

function loadPublicAudioConfig() {
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $configPath = getPublicAudioConfigPath();
    if (file_exists($configPath)) {
        $loaded = include $configPath;
        if (is_array($loaded)) {
            $config = array_merge([
                'url' => '',
                'enabled' => false,
                'updated_at' => null,
            ], $loaded);
            return $config;
        }
    }

    $config = [
        'url' => '',
        'enabled' => false,
        'updated_at' => null,
    ];

    return $config;
}

function savePublicAudioConfig($url, $enabled = true) {
    $url = trim((string) $url);
    $config = [
        'url' => $url,
        'enabled' => $enabled && !empty($url),
        'updated_at' => date('c'),
    ];

    $configPath = getPublicAudioConfigPath();
    $configDir = dirname($configPath);
    
    // Ensure config directory exists
    if (!is_dir($configDir)) {
        mkdir($configDir, 0755, true);
    }

    $content = "<?php\nreturn " . var_export($config, true) . ";\n";
    $result = file_put_contents($configPath, $content);
    
    return $result !== false;
}

function getDefaultPublicAudioUrl() {
    if (defined('DEFAULT_PUBLIC_AUDIO_URL') && trim((string) DEFAULT_PUBLIC_AUDIO_URL) !== '') {
        return trim((string) DEFAULT_PUBLIC_AUDIO_URL);
    }

    return '';
}

function getPublicAudioUrl() {
    $config = loadPublicAudioConfig();
    $url = trim((string) ($config['url'] ?? ''));

    // If explicitly disabled in config, return empty
    if (isset($config['enabled']) && !$config['enabled']) {
        return '';
    }

    if (!empty($url) && !empty($config['enabled'])) {
        return $url;
    }

    return getDefaultPublicAudioUrl();
}

function isPublicAudioEnabled() {
    return !empty(getPublicAudioUrl());
}

function encodePublicAudioUrl($url) {
    if (empty($url)) {
        return '';
    }

    $parts = parse_url($url);
    if ($parts === false) {
        return rawurlencode($url);
    }

    $path = $parts['path'] ?? '';
    $path = implode('/', array_map('rawurlencode', explode('/', $path)));

    $encodedUrl = '';
    if (isset($parts['scheme'])) {
        $encodedUrl .= $parts['scheme'] . '://';
    }
    if (isset($parts['user'])) {
        $encodedUrl .= $parts['user'];
        if (isset($parts['pass'])) {
            $encodedUrl .= ':' . $parts['pass'];
        }
        $encodedUrl .= '@';
    }
    if (isset($parts['host'])) {
        $encodedUrl .= $parts['host'];
    }
    if (isset($parts['port'])) {
        $encodedUrl .= ':' . $parts['port'];
    }
    $encodedUrl .= $path;
    if (isset($parts['query'])) {
        $encodedUrl .= '?' . $parts['query'];
    }
    if (isset($parts['fragment'])) {
        $encodedUrl .= '#' . $parts['fragment'];
    }

    return $encodedUrl;
}

function normalizePublicAudioUrl($url) {
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    if (strpos($url, '/uploads/public/') === 0 && defined('BASE_URL') && BASE_URL !== '') {
        $url = rtrim(BASE_URL, '/') . $url;
    } elseif (strpos($url, 'uploads/public/') === 0 && defined('BASE_URL') && BASE_URL !== '') {
        $url = rtrim(BASE_URL, '/') . '/' . $url;
    }

    return encodePublicAudioUrl($url);
}

function renderPublicAudioPlayer($extraAttrs = []) {
    $audioUrl = getPublicAudioUrl();
    if (empty($audioUrl)) {
        return '';
    }

    $audioUrl = normalizePublicAudioUrl($audioUrl);

    $attrs = array_merge([
        'autoplay' => true,
        'loop' => true,
        'playsinline' => true,
        'preload' => 'auto',
        'id' => 'bgPublicAudio',
        'style' => 'display: none;',
    ], $extraAttrs);

    $parts = [];
    foreach ($attrs as $name => $value) {
        if ($value === true) {
            $parts[] = $name;
        } else {
            $parts[] = $name . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }
    }

    $audioHtml = '<audio ' . implode(' ', $parts) . ' src="' . htmlspecialchars($audioUrl, ENT_QUOTES, 'UTF-8') . '"></audio>';

    $widgetHtml = '
    <!-- Floating Audio Toggle Button -->
    <button id="publicAudioToggle" class="fixed bottom-24 right-8 z-50 flex items-center justify-center gap-2 bg-[#3E6B4E] hover:bg-[#2F5B41] text-white w-12 h-12 md:w-auto md:px-4 rounded-full shadow-xl border border-white/20 transition-all duration-300 transform hover:scale-105" title="Musik Latar">
      <span id="publicAudioIconContainer" class="flex items-center justify-center">
        <iconify-icon id="publicAudioIcon" icon="lucide:volume-2" class="w-5 h-5"></iconify-icon>
      </span>
      <span id="publicAudioText" class="hidden md:inline text-xs font-bold tracking-wider uppercase">Sound On</span>
    </button>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
      const audio = document.getElementById("bgPublicAudio");
      const toggleBtn = document.getElementById("publicAudioToggle");
      const icon = document.getElementById("publicAudioIcon");
      const label = document.getElementById("publicAudioText");

      if (!audio || !toggleBtn) return;

      const isMutedPref = localStorage.getItem("public_audio_muted") === "true";
      const savedTime = localStorage.getItem("public_audio_time");
      
      if (savedTime) {
        audio.currentTime = parseFloat(savedTime);
      }
      
      function updateUI(isMuted) {
        if (isMuted) {
          icon.setAttribute("icon", "lucide:volume-x");
          if (label) label.textContent = "Sound Off";
          toggleBtn.classList.remove("bg-[#3E6B4E]", "hover:bg-[#2F5B41]");
          toggleBtn.classList.add("bg-gray-500", "hover:bg-gray-600");
        } else {
          icon.setAttribute("icon", "lucide:volume-2");
          if (label) label.textContent = "Sound On";
          toggleBtn.classList.remove("bg-gray-500", "hover:bg-gray-600");
          toggleBtn.classList.add("bg-[#3E6B4E]", "hover:bg-[#2F5B41]");
        }
      }

      if (isMutedPref) {
        audio.muted = true;
        audio.pause();
        updateUI(true);
      } else {
        audio.muted = false;
        const playPromise = audio.play();
        if (playPromise !== undefined) {
          playPromise.then(() => {
            updateUI(false);
          }).catch(error => {
            console.log("Autoplay blocked, waiting for interaction");
            audio.muted = true;
            updateUI(true);
          });
        }
      }

      // Continuously save current time
      audio.addEventListener("timeupdate", function() {
        if (!audio.paused && !audio.muted) {
          localStorage.setItem("public_audio_time", audio.currentTime);
        }
      });

      toggleBtn.addEventListener("click", function() {
        if (audio.paused || audio.muted) {
          audio.muted = false;
          audio.play().then(() => {
            localStorage.setItem("public_audio_muted", "false");
            updateUI(false);
          }).catch(err => {
            console.error("Playback failed", err);
          });
        } else {
          audio.pause();
          audio.muted = true;
          localStorage.setItem("public_audio_muted", "true");
          updateUI(true);
        }
      });
    });
    </script>
    ';

    return $audioHtml . $widgetHtml;
}
?>
