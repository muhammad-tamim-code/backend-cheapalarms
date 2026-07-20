"""Convert Safeguard Physical Security photos to WebP for site-blocks."""
from __future__ import annotations

from pathlib import Path

from PIL import Image

ROOT = Path(r"C:\ByteBlazeIT\Safeguard\photos")
OUT = Path(
    r"c:\ByteBlazeIT\Headless\headless-cheapalarms\wordpress\wp-content\plugins\site-blocks\assets\images\physical-security"
)

MAPPING: list[tuple[Path, str, int | None]] = [
    # Hub
    (ROOT / "physical hub" / "ChatGPT Image Jul 6, 2026, 02_04_56 PM (1).png", "hub-hero.webp", 1920),
    (ROOT / "physical hub" / "ChatGPT Image Jul 6, 2026, 02_04_57 PM (2).png", "hub-covers.webp", 1200),
    (ROOT / "physical hub" / "ChatGPT Image Jul 6, 2026, 02_04_57 PM (3).png", "hub-integration.webp", 1200),
    (ROOT / "physical hub" / "ChatGPT Image Jul 6, 2026, 02_04_58 PM (4).png", "hub-sites.webp", 1400),
    (ROOT / "physical hub" / "ChatGPT Image Jul 6, 2026, 02_04_59 PM (6).png", "hub-why.webp", 1200),
    # Static
    (ROOT / "static" / "ChatGPT Image Jul 6, 2026, 01_58_45 PM (1).png", "static-hero.webp", 1920),
    (ROOT / "static" / "ChatGPT Image Jul 6, 2026, 01_58_46 PM (2).png", "static-intro.webp", 1200),
    (ROOT / "static" / "ChatGPT Image Jul 6, 2026, 01_58_47 PM (3).png", "static-duties.webp", 1200),
    (ROOT / "static" / "ChatGPT Image Jul 6, 2026, 01_58_47 PM (4).png", "static-integration.webp", 1200),
    (ROOT / "static" / "ChatGPT Image Jul 6, 2026, 01_58_49 PM (5).png", "static-why.webp", 1200),
    # Mobile (no dedicated GPS dashboard asset — reuse hub control room for tracked section)
    (ROOT / "mobile" / "ChatGPT Image Jul 6, 2026, 01_02_55 PM (1).png", "mobile-hero.webp", 1920),
    (ROOT / "mobile" / "ChatGPT Image Jul 6, 2026, 01_02_55 PM (2).png", "mobile-intro.webp", 1200),
    (ROOT / "mobile" / "ChatGPT Image Jul 6, 2026, 01_02_56 PM (3).png", "mobile-duties.webp", 1200),
    (ROOT / "physical hub" / "ChatGPT Image Jul 6, 2026, 02_04_57 PM (3).png", "mobile-tracked.webp", 1400),
    (ROOT / "mobile" / "ChatGPT Image Jul 6, 2026, 01_02_56 PM (5).png", "mobile-why.webp", 1200),
]


def convert(src: Path, dest: Path, max_width: int | None) -> None:
    img = Image.open(src)
    if img.mode not in ("RGB", "RGBA"):
        img = img.convert("RGB")
    elif img.mode == "RGBA":
        background = Image.new("RGB", img.size, (255, 255, 255))
        background.paste(img, mask=img.split()[3])
        img = background

    if max_width and img.width > max_width:
        ratio = max_width / img.width
        new_size = (max_width, int(img.height * ratio))
        img = img.resize(new_size, Image.Resampling.LANCZOS)

    dest.parent.mkdir(parents=True, exist_ok=True)
    img.save(dest, "WEBP", quality=85, method=6)
    size_kb = dest.stat().st_size / 1024
    print(f"OK  {dest.name:24} {size_kb:6.0f} KB  <- {src.name}")


def main() -> None:
    for src, name, max_w in MAPPING:
        if not src.is_file():
            raise FileNotFoundError(src)
        convert(src, OUT / name, max_w)
    print(f"\nDone: {len(MAPPING)} files -> {OUT}")


if __name__ == "__main__":
    main()
