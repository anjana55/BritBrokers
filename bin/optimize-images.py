"""
Shrink site images without changing their size on screen.

  python3 bin/optimize-images.py img            # optimise everything under img/
  python3 bin/optimize-images.py uploads --dry  # report only

JPEG: re-encoded at quality 82, progressive, metadata removed (same pixel dimensions).
PNG:  lossless re-compression.
A file is only replaced when the result is at least 15% smaller, and JPEGs only when the
re-encoded image stays visually the same (PSNR >= 38 dB against the original).
Needs Pillow:  pip install Pillow
"""
import math
import os
import sys
from io import BytesIO

from PIL import Image, ImageChops, ImageOps, ImageStat

JPEG_QUALITY = 82
MIN_SAVING = 0.15
MIN_PSNR = 38.0


def psnr(a, b):
    diff = ImageChops.difference(a, b)
    mse = sum(v * v for v in ImageStat.Stat(diff).rms) / 3
    return float("inf") if mse == 0 else 20 * math.log10(255 / math.sqrt(mse))


def optimise(path, dry):
    before = os.path.getsize(path)
    with Image.open(path) as im:
        fmt = im.format
        if fmt == "JPEG":
            src = ImageOps.exif_transpose(im).convert("RGB")
            buf = BytesIO()
            src.save(buf, "JPEG", quality=JPEG_QUALITY, optimize=True, progressive=True)
            quality = psnr(src, Image.open(BytesIO(buf.getvalue())).convert("RGB"))
            if quality < MIN_PSNR:
                return before, before, f"kept (PSNR {quality:.1f} dB)"
        elif fmt == "PNG":
            buf = BytesIO()
            im.save(buf, "PNG", optimize=True)
            quality = float("inf")
        else:
            return before, before, "skipped"
    after = len(buf.getvalue())
    if after > before * (1 - MIN_SAVING):
        return before, before, "kept (little to gain)"
    if not dry:
        with open(path, "wb") as f:
            f.write(buf.getvalue())
    return before, after, f"PSNR {quality:.1f} dB" if quality != float("inf") else "lossless"


def main():
    root = sys.argv[1] if len(sys.argv) > 1 else "img"
    dry = "--dry" in sys.argv
    total_before = total_after = changed = 0
    for d, _, files in os.walk(root):
        for f in sorted(files):
            if not f.lower().endswith((".jpg", ".jpeg", ".png")):
                continue
            p = os.path.join(d, f)
            try:
                b, a, note = optimise(p, dry)
            except Exception as e:  # unreadable image: leave it alone
                b = a = os.path.getsize(p)
                note = f"skipped ({e})"
            total_before += b
            total_after += a
            if a < b:
                changed += 1
                print(f"{b // 1024:6} KB -> {a // 1024:5} KB  {note:15} {p}")
    print(f"\n{changed} files {'would be ' if dry else ''}optimised: {total_before / 1048576:.1f} MB -> {total_after / 1048576:.1f} MB")


if __name__ == "__main__":
    main()
