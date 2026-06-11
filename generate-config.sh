#!/bin/sh
# generate-config.sh
# Generates /var/www/html/includes/config.php from environment variables.
# Falls back to the placeholder values in config.example.php if a variable is not set.

set -e

CONFIG_EXAMPLE="/var/www/html/includes/config.example.php"
CONFIG_OUT="/var/www/html/includes/config.php"

if [ ! -f "$CONFIG_EXAMPLE" ]; then
    echo "ERROR: $CONFIG_EXAMPLE not found. Cannot generate config.php." >&2
    exit 1
fi

# ── Resolve values (env var → default) ────────────────────────────────────────
SUPABASE_URL="${SUPABASE_URL:-https://YOUR_SUPABASE_URL.supabase.co}"
SUPABASE_KEY="${SUPABASE_KEY:-YOUR_SUPABASE_PUBLISHABLE_KEY}"
SUPABASE_SERVICE_KEY="${SUPABASE_SERVICE_KEY:-YOUR_SUPABASE_SERVICE_ROLE_KEY}"

CLOUDINARY_CLOUD_NAME="${CLOUDINARY_CLOUD_NAME:-your_cloud_name}"
CLOUDINARY_API_KEY="${CLOUDINARY_API_KEY:-your_api_key}"
CLOUDINARY_API_SECRET="${CLOUDINARY_API_SECRET:-your_api_secret}"
CLOUDINARY_FOLDER="${CLOUDINARY_FOLDER:-folder_name}"

SUPABASE_STORAGE_BUCKET="${SUPABASE_STORAGE_BUCKET:-bucket_name}"

ADMIN_USERNAME="${ADMIN_USERNAME:-admin}"
ADMIN_PASSWORD_SALT="${ADMIN_PASSWORD_SALT:-your_unique_salt_here}"
ADMIN_PASSWORD_HASH="${ADMIN_PASSWORD_HASH:-your_hashed_password_here}"

SITE_NAME="${SITE_NAME:-SLB-C YPSLB Gemolong}"
BASE_URL="${BASE_URL:-${BASE_PATH:-/}}"

# Normalize BASE_URL for root deployment and remove trailing slash
normalize_base_url() {
    url="$1"
    if [ "$url" = "/web_sekolah" ] || [ "$url" = "/web_sekolah/" ]; then
        echo "/"
        return
    fi
    url="${url%/}"
    if [ -z "$url" ]; then
        echo "/"
    else
        echo "$url"
    fi
}
BASE_URL="$(normalize_base_url "$BASE_URL")"

# ── Escape values for use as sed replacement strings ──────────────────────────
# Escapes: backslash, forward slash (delimiter), and & (sed back-reference)
esc() {
    printf '%s' "$1" | sed "s/[\\\\&/]/\\\\&/g"
}

E_SUPABASE_URL=$(esc "$SUPABASE_URL")
E_SUPABASE_KEY=$(esc "$SUPABASE_KEY")
E_SUPABASE_SERVICE_KEY=$(esc "$SUPABASE_SERVICE_KEY")
E_CLOUDINARY_CLOUD_NAME=$(esc "$CLOUDINARY_CLOUD_NAME")
E_CLOUDINARY_API_KEY=$(esc "$CLOUDINARY_API_KEY")
E_CLOUDINARY_API_SECRET=$(esc "$CLOUDINARY_API_SECRET")
E_CLOUDINARY_FOLDER=$(esc "$CLOUDINARY_FOLDER")
E_SUPABASE_STORAGE_BUCKET=$(esc "$SUPABASE_STORAGE_BUCKET")
E_ADMIN_USERNAME=$(esc "$ADMIN_USERNAME")
E_ADMIN_PASSWORD_SALT=$(esc "$ADMIN_PASSWORD_SALT")
E_ADMIN_PASSWORD_HASH=$(esc "$ADMIN_PASSWORD_HASH")
E_SITE_NAME=$(esc "$SITE_NAME")
E_BASE_URL=$(esc "$BASE_URL")

# ── Generate config.php by replacing placeholder values ───────────────────────
sed \
    -e "s/define('SUPABASE_URL', '[^']*')/define('SUPABASE_URL', '${E_SUPABASE_URL}')/" \
    -e "s/define('SUPABASE_KEY', '[^']*')/define('SUPABASE_KEY', '${E_SUPABASE_KEY}')/" \
    -e "s/define('SUPABASE_SERVICE_KEY', '[^']*')/define('SUPABASE_SERVICE_KEY', '${E_SUPABASE_SERVICE_KEY}')/" \
    -e "s/define('CLOUDINARY_CLOUD_NAME', '[^']*')/define('CLOUDINARY_CLOUD_NAME', '${E_CLOUDINARY_CLOUD_NAME}')/" \
    -e "s/define('CLOUDINARY_API_KEY', '[^']*')/define('CLOUDINARY_API_KEY', '${E_CLOUDINARY_API_KEY}')/" \
    -e "s/define('CLOUDINARY_API_SECRET', '[^']*')/define('CLOUDINARY_API_SECRET', '${E_CLOUDINARY_API_SECRET}')/" \
    -e "s/define('CLOUDINARY_FOLDER', '[^']*')/define('CLOUDINARY_FOLDER', '${E_CLOUDINARY_FOLDER}')/" \
    -e "s/define('SUPABASE_STORAGE_BUCKET', '[^']*')/define('SUPABASE_STORAGE_BUCKET', '${E_SUPABASE_STORAGE_BUCKET}')/" \
    -e "s/define('ADMIN_USERNAME', '[^']*')/define('ADMIN_USERNAME', '${E_ADMIN_USERNAME}')/" \
    -e "s/define('ADMIN_PASSWORD_SALT', '[^']*')/define('ADMIN_PASSWORD_SALT', '${E_ADMIN_PASSWORD_SALT}')/" \
    -e "s/define('ADMIN_PASSWORD_HASH', '[^']*')/define('ADMIN_PASSWORD_HASH', '${E_ADMIN_PASSWORD_HASH}')/" \
    -e "s/define('SITE_NAME', '[^']*')/define('SITE_NAME', '${E_SITE_NAME}')/" \
    -e "s|define('BASE_URL', '[^']*')|define('BASE_URL', '${E_BASE_URL}')|" \
    "$CONFIG_EXAMPLE" > "$CONFIG_OUT"

# ── Permissions ───────────────────────────────────────────────────────────────
chown www-data:www-data "$CONFIG_OUT"
chmod 640 "$CONFIG_OUT"

echo "config.php generated successfully at $CONFIG_OUT"
