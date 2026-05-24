#!/usr/bin/env bash
# Extrait logo-256.png et govgenz-logo-qr.png depuis govgenz-logo.svg (nécessite PHP + GD).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
if command -v docker >/dev/null 2>&1 && [[ -f ../govgenz-local/docker-compose.yml ]]; then
  (cd ../govgenz-local && docker compose exec -T web php -r "
\$svg = file_get_contents('/var/www/html/public/assets/img/govgenz-logo.svg');
preg_match('/data:image\\/png;base64,([^\"]+)/', \$svg, \$m);
\$raw = base64_decode(\$m[1], true);
\$src = imagecreatefromstring(\$raw);
\$sw = imagesx(\$src); \$sh = imagesy(\$src);
foreach ([['/var/www/html/public/assets/logo-256.png', 256], ['/var/www/html/public/assets/img/govgenz-logo-qr.png', 128]] as [\$path, \$size]) {
  \$dst = imagecreatetruecolor(\$size, \$size);
  imagealphablending(\$dst, false);
  imagesavealpha(\$dst, true);
  imagefill(\$dst, 0, 0, imagecolorallocatealpha(\$dst, 0, 0, 0, 127));
  imagecopyresampled(\$dst, \$src, 0, 0, 0, 0, \$size, \$size, \$sw, \$sh);
  imagepng(\$dst, \$path, 6);
  imagedestroy(\$dst);
  echo \"wrote \$path\n\";
}
imagedestroy(\$src);
")
else
  php -r "
\$svg = file_get_contents('public/assets/img/govgenz-logo.svg');
preg_match('/data:image\\/png;base64,([^\"]+)/', \$svg, \$m);
\$raw = base64_decode(\$m[1], true);
\$src = imagecreatefromstring(\$raw);
\$sw = imagesx(\$src); \$sh = imagesy(\$src);
foreach ([['public/assets/logo-256.png', 256], ['public/assets/img/govgenz-logo-qr.png', 128]] as [\$path, \$size]) {
  \$dst = imagecreatetruecolor(\$size, \$size);
  imagealphablending(\$dst, false);
  imagesavealpha(\$dst, true);
  imagefill(\$dst, 0, 0, imagecolorallocatealpha(\$dst, 0, 0, 0, 127));
  imagecopyresampled(\$dst, \$src, 0, 0, 0, 0, \$size, \$size, \$sw, \$sh);
  imagepng(\$dst, \$path, 6);
  imagedestroy(\$dst);
  echo \"wrote \$path\n\";
}
imagedestroy(\$src);
"
fi
