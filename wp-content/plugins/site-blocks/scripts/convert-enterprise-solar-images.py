"""Convert Enterprise, Safeguard Solutions, and Solar Monitoring photos to WebP."""
from __future__ import annotations

from pathlib import Path

from PIL import Image

PHOTOS = Path(r"C:\ByteBlazeIT\Safeguard\photos")
ASSETS = Path(
    r"C:\ByteBlazeIT\Headless\headless-cheapalarms\wordpress\wp-content\plugins\site-blocks\assets\images"
)

MAPPING: list[tuple[Path, Path, int | None]] = [
    # Enterprise Solutions hub
    (
        PHOTOS / "enterprise solutions" / "ChatGPT Image Jul 14, 2026, 03_23_10 PM (1).png",
        ASSETS / "enterprise" / "hub-hero.webp",
        1920,
    ),
    (
        PHOTOS / "enterprise solutions" / "ChatGPT Image Jul 14, 2026, 03_23_10 PM (2).png",
        ASSETS / "enterprise" / "hub-intro.webp",
        1400,
    ),
    (
        PHOTOS / "enterprise solutions" / "ChatGPT Image Jul 14, 2026, 03_23_11 PM (3).png",
        ASSETS / "enterprise" / "hub-approach.webp",
        1400,
    ),
    (
        PHOTOS / "enterprise solutions" / "ChatGPT Image Jul 14, 2026, 03_23_12 PM (4).png",
        ASSETS / "enterprise" / "hub-integration.webp",
        1400,
    ),
    (
        PHOTOS / "enterprise solutions" / "ChatGPT Image Jul 14, 2026, 03_23_12 PM (5).png",
        ASSETS / "enterprise" / "hub-promo.webp",
        1400,
    ),
    (
        PHOTOS / "enterprise solutions" / "ChatGPT Image Jul 14, 2026, 03_23_13 PM (6).png",
        ASSETS / "enterprise" / "hub-commercial.webp",
        1600,
    ),
    # Safeguard Solutions
    (
        PHOTOS / "safeguard solutions" / "ChatGPT Image Jul 14, 2026, 03_27_37 PM (1).png",
        ASSETS / "enterprise" / "safeguard-solutions-hero.webp",
        1920,
    ),
    (
        PHOTOS / "safeguard solutions" / "ChatGPT Image Jul 14, 2026, 03_27_39 PM (2).png",
        ASSETS / "enterprise" / "ss-cloud-video.webp",
        1400,
    ),
    (
        PHOTOS / "safeguard solutions" / "ChatGPT Image Jul 14, 2026, 03_27_40 PM (3).png",
        ASSETS / "enterprise" / "ss-ai-search.webp",
        1400,
    ),
    (
        PHOTOS / "safeguard solutions" / "ChatGPT Image Jul 14, 2026, 03_27_41 PM (4).png",
        ASSETS / "enterprise" / "ss-access-events.webp",
        1400,
    ),
    (
        PHOTOS / "safeguard solutions" / "ChatGPT Image Jul 14, 2026, 03_27_41 PM (5).png",
        ASSETS / "enterprise" / "ss-multi-site-map.webp",
        1400,
    ),
    # Solar monitoring
    (
        PHOTOS / "solar monitoring rural" / "ChatGPT Image Jul 14, 2026, 03_31_48 PM (1).png",
        ASSETS / "monitoring" / "solar-monitoring-hero.webp",
        1920,
    ),
    (
        PHOTOS / "solar monitoring rural" / "ChatGPT Image Jul 14, 2026, 03_31_48 PM (2).png",
        ASSETS / "monitoring" / "solar-monitoring-install.webp",
        1400,
    ),
    (
        PHOTOS / "solar monitoring rural" / "ChatGPT Image Jul 14, 2026, 03_31_49 PM (3).png",
        ASSETS / "monitoring" / "solar-monitoring-night.webp",
        1400,
    ),
    (
        PHOTOS / "solar monitoring rural" / "ChatGPT Image Jul 14, 2026, 03_31_49 PM (4).png",
        ASSETS / "monitoring" / "solar-monitoring-rural.webp",
        1400,
    ),
    (
        PHOTOS / "solar monitoring rural" / "ChatGPT Image Jul 14, 2026, 03_31_50 PM (5).png",
        ASSETS / "monitoring" / "solar-monitoring-4g.webp",
        1400,
    ),
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
        img = img.resize((max_width, int(img.height * ratio)), Image.Resampling.LANCZOS)

    dest.parent.mkdir(parents=True, exist_ok=True)
    img.save(dest, "WEBP", quality=85, method=6)
    print(f"OK  {dest.parent.name}/{dest.name:36} {dest.stat().st_size / 1024:6.0f} KB  <- {src.name}")


def main() -> None:
    for src, dest, max_w in MAPPING:
        if not src.is_file():
            raise FileNotFoundError(src)
        convert(src, dest, max_w)
    print(f"\nDone: {len(MAPPING)} WebP files")


if __name__ == "__main__":
    main()
