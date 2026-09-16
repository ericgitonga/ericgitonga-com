"""Geotag/EXIF stripping on captured photos (issue #10): a photo carrying GPS EXIF must never
be recorded, stored, or transmitted with that location data attached — verified here by
checking the actual bytes the app holds after capture, not just reading the source code.
"""

import base64
import io

from PIL import Image
from PIL.TiffImagePlugin import IFDRational

from _common import browser_page

GPS_IFD_TAG = 34853


def _jpeg_with_gps_exif() -> bytes:
    """Build a small in-memory JPEG carrying GPS EXIF (Nairobi-ish coordinates), so the test
    doesn't depend on a checked-in fixture file that could drift or go stale."""
    img = Image.new("RGB", (64, 64), color="green")
    exif = Image.Exif()
    exif[GPS_IFD_TAG] = {
        1: "S",
        2: (IFDRational(1, 1), IFDRational(17, 1), IFDRational(0, 1)),
        3: "E",
        4: (IFDRational(36, 1), IFDRational(49, 1), IFDRational(0, 1)),
    }
    buf = io.BytesIO()
    img.save(buf, format="jpeg", exif=exif)
    return buf.getvalue()


def _has_gps_exif(jpeg_bytes: bytes) -> bool:
    exif = Image.open(io.BytesIO(jpeg_bytes)).getexif()
    return GPS_IFD_TAG in exif and bool(exif.get_ifd(GPS_IFD_TAG))


def test_captured_photo_with_gps_exif_is_stripped_before_preview():
    source_bytes = _jpeg_with_gps_exif()
    # Sanity check: if this ever failed, the test below would pass for the wrong reason
    # (nothing to strip in the first place).
    assert _has_gps_exif(source_bytes), "test fixture itself must carry GPS EXIF"

    with browser_page() as page:
        page.goto("/identify")
        page.set_input_files(
            'input[aria-label="Capture a photo of a specimen"]',
            {
                "name": "specimen-with-location.jpg",
                "mimeType": "image/jpeg",
                "buffer": source_bytes,
            },
        )
        preview = page.locator('[data-testid="photo-preview"]')
        preview.wait_for()
        blob_url = preview.get_attribute("src")
        assert blob_url and blob_url.startswith("blob:")

        rendered_b64 = page.evaluate(
            """async (url) => {
                const res = await fetch(url);
                const buf = await res.arrayBuffer();
                let binary = "";
                for (const byte of new Uint8Array(buf)) {
                    binary += String.fromCharCode(byte);
                }
                return btoa(binary);
            }""",
            blob_url,
        )
        rendered_bytes = base64.b64decode(rendered_b64)

        assert not _has_gps_exif(rendered_bytes), (
            "captured photo still carries GPS EXIF after capture — issue #10 regression"
        )


TESTS = [
    test_captured_photo_with_gps_exif_is_stripped_before_preview,
]

if __name__ == "__main__":
    for t in TESTS:
        t()
        print(f"PASS {t.__name__}")
