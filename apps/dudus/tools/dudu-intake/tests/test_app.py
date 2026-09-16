"""Unit tests for the pure helper functions in app.py.

Fast, no Streamlit widget/network round-trip involved — complements (not replaces) the
`streamlit.testing.v1.AppTest`/real end-to-end publish verification this tool has relied on so
far (#31/#34/#40). Run: pytest (from tools/dudu-intake/, or via CI's unit-dudu-intake job).
"""

import io

import fitz
import pytest
from PIL import Image

from app import (
    extract_embedded_photo,
    find_technical_pdf_path,
    find_technical_ref,
    guess_order_from_technical,
    normalize_photo,
    parse_report_pdf,
    resolve_order_default_index,
    resolve_species_directory,
    scan_species_directory,
    serialize_cards,
    slugify,
    technical_filename_for,
)


def _pdf_bytes(spans):
    """Build a minimal in-memory PDF: `spans` is a list of (text, size, bold) lines, each
    rendered on its own line, in the same top-to-bottom reading order parse_report_pdf expects."""
    doc = fitz.open()
    page = doc.new_page()
    y = 72
    for text, size, bold in spans:
        page.insert_text((72, y), text, fontsize=size, fontname="hebo" if bold else "helv")
        y += size + 6
    return doc.tobytes()


def _lay_report_pdf(title, sections, title_lines=None):
    """sections: list of (heading, body) tuples. `title_lines`, if given, overrides `title` with
    several consecutive title-sized spans — simulating a wrapped H1 that renders across more
    than one visual line."""
    spans = [(line, 20, True) for line in (title_lines or [title])]
    for heading, body in sections:
        spans.append((heading, 15, True))
        spans.append((body, 11, False))
    return _pdf_bytes(spans)


def _technical_pdf(text):
    doc = fitz.open()
    page = doc.new_page()
    page.insert_text((72, 72), text, fontsize=11, fontname="helv")
    return doc.tobytes()


def _pdf_with_images(sizes, fmt="JPEG"):
    """Build a minimal in-memory PDF embedding one raster image per (width, height) in `sizes`,
    for testing extract_embedded_photo. Each image is placed in its own small on-page rect —
    the rect size is independent of the embedded image's actual pixel dimensions, which is what
    extract_embedded_photo inspects."""
    doc = fitz.open()
    page = doc.new_page()
    y = 0
    for w, h in sizes:
        img = Image.new("RGB", (w, h), color=(180, 90, 40))
        buf = io.BytesIO()
        img.save(buf, format=fmt)
        page.insert_image(fitz.Rect(0, y, 100, y + 100), stream=buf.getvalue())
        y += 110
    return doc.tobytes()


# ── slugify ──────────────────────────────────────────────────────────────────

@pytest.mark.parametrize(
    "text,expected",
    [
        ("White-Ringed Atlas Moth", "white-ringed-atlas-moth"),
        ("  Tropical Bont Tick  ", "tropical-bont-tick"),
        ("Aedes Mosquitoes!", "aedes-mosquitoes"),
        ("Multiple   Spaces", "multiple-spaces"),
        ("Already-Hyphenated", "already-hyphenated"),
    ],
)
def test_slugify(text, expected):
    assert slugify(text) == expected


# ── serialize_cards ──────────────────────────────────────────────────────────

def test_serialize_cards_round_trips_and_is_newline_terminated():
    import json

    cards = [{"id": "aedes-mosquitoes", "common_name": "Aedes Mosquitoes"}]
    raw = serialize_cards(cards)
    assert raw.endswith(b"\n")
    assert json.loads(raw) == cards


def test_serialize_cards_pretty_prints():
    cards = [{"id": "a", "common_name": "A"}]
    raw = serialize_cards(cards)
    assert b"\n  " in raw  # indent=2, not compact


# ── parse_report_pdf ─────────────────────────────────────────────────────────

def test_parse_report_pdf_extracts_title_and_sections():
    pdf = _lay_report_pdf(
        "White-Ringed Atlas Moth",
        [
            ("What Is It?", "A large moth with white rings on its wings."),
            ("Where Does It Live?", "Forests across East Africa."),
        ],
    )
    title, sections, err = parse_report_pdf(pdf)
    assert err is None
    assert title == "White-Ringed Atlas Moth"
    assert sections == [
        {"heading": "What Is It?", "body": "A large moth with white rings on its wings."},
        {"heading": "Where Does It Live?", "body": "Forests across East Africa."},
    ]


@pytest.mark.parametrize(
    "raw_title,expected_title",
    [
        ("The White-Ringed Atlas Moth", "White-Ringed Atlas Moth"),
        ("A Tropical Bont Tick", "Tropical Bont Tick"),
        ("An Aedes Mosquito", "Aedes Mosquito"),
    ],
)
def test_parse_report_pdf_strips_leading_article_from_title(raw_title, expected_title):
    pdf = _lay_report_pdf(raw_title, [("What Is It?", "Body text.")])
    title, _sections, err = parse_report_pdf(pdf)
    assert err is None
    assert title == expected_title


def test_parse_report_pdf_joins_a_title_wrapped_across_lines():
    pdf = _lay_report_pdf(
        None,
        [("What Are They?", "Body text.")],
        title_lines=["The Paper Wasp - An Insect That Builds Its", "Nursery Out of Chewed Wood"],
    )
    title, sections, err = parse_report_pdf(pdf)
    assert err is None
    assert title == "Paper Wasp - An Insect That Builds Its Nursery Out of Chewed Wood"
    assert sections == [{"heading": "What Are They?", "body": "Body text."}]


def test_parse_report_pdf_errors_when_no_title_found():
    # No bold text at all, so no candidate title.
    pdf = _pdf_bytes([("Just some regular text.", 11, False)])
    title, sections, err = parse_report_pdf(pdf)
    assert title is None
    assert sections == []
    assert err is not None


def test_parse_report_pdf_errors_when_no_sections_found():
    # A title-sized bold heading, but nothing bold after it.
    pdf = _pdf_bytes([("Title Only", 20, True), ("No headings follow.", 11, False)])
    title, sections, err = parse_report_pdf(pdf)
    assert title == "Title Only"
    assert sections == []
    assert err is not None


# ── guess_order_from_technical ────────────────────────────────────────────────

def test_guess_order_from_technical_returns_most_common_match(tmp_path):
    path = tmp_path / "technical.pdf"
    path.write_bytes(
        _technical_pdf(
            "This species belongs to Order Araneae. Other members of Order Araneae share traits."
        )
    )
    assert guess_order_from_technical(path) == "Araneae"


def test_guess_order_from_technical_returns_none_when_no_match(tmp_path):
    path = tmp_path / "technical.pdf"
    path.write_bytes(_technical_pdf("No taxonomic order stated here at all."))
    assert guess_order_from_technical(path) is None


def test_guess_order_from_technical_returns_none_for_missing_path():
    assert guess_order_from_technical(None) is None


# ── find_technical_pdf_path / find_technical_ref ──────────────────────────────

def test_find_technical_pdf_path_finds_sibling_file(monkeypatch, tmp_path):
    import app

    monkeypatch.setattr(app, "DUDUS_ROOT", tmp_path)
    species_dir = tmp_path / "White-Ringed Atlas Moth"
    species_dir.mkdir()
    (species_dir / "atlas-moth-web.pdf").write_bytes(b"%PDF-fake")

    result = find_technical_pdf_path("White-Ringed Atlas Moth", "atlas-moth-web-la.pdf")
    assert result == species_dir / "atlas-moth-web.pdf"


def test_find_technical_pdf_path_returns_none_when_file_missing(monkeypatch, tmp_path):
    import app

    monkeypatch.setattr(app, "DUDUS_ROOT", tmp_path)
    result = find_technical_pdf_path("Nonexistent Species", "report-la.pdf")
    assert result is None


def test_find_technical_pdf_path_returns_none_for_non_lay_filename():
    # Filename doesn't end in "-la.pdf", so there's no technical sibling to derive.
    assert find_technical_pdf_path("Some Species", "report.pdf") is None


def test_find_technical_ref_empty_string_when_no_match(monkeypatch, tmp_path):
    import app

    monkeypatch.setattr(app, "DUDUS_ROOT", tmp_path)
    assert find_technical_ref("Nonexistent Species", "report-la.pdf") == ""


def test_find_technical_ref_returns_relative_path_string(monkeypatch, tmp_path):
    import app

    monkeypatch.setattr(app, "DUDUS_ROOT", tmp_path)
    species_dir = tmp_path / "Tropical Bont Tick"
    species_dir.mkdir()
    (species_dir / "tbt-web.pdf").write_bytes(b"%PDF-fake")

    assert find_technical_ref("Tropical Bont Tick", "tbt-web-la.pdf") == "Tropical Bont Tick/tbt-web.pdf"


# ── technical_filename_for ────────────────────────────────────────────────────

def test_technical_filename_for_strips_la_suffix():
    assert technical_filename_for("atlas-moth-web-la.pdf") == "atlas-moth-web.pdf"


def test_technical_filename_for_returns_none_for_non_lay_filename():
    assert technical_filename_for("report.pdf") is None


# ── scan_species_directory ────────────────────────────────────────────────────

def test_scan_species_directory_pairs_lay_with_its_technical_sibling(tmp_path):
    (tmp_path / "bm-la.pdf").write_bytes(b"%PDF-fake")
    (tmp_path / "bm.pdf").write_bytes(b"%PDF-fake")
    (tmp_path / "photo.jpg").write_bytes(b"fake-jpg")

    result = scan_species_directory(tmp_path)
    assert result["pairs"] == [(tmp_path / "bm-la.pdf", tmp_path / "bm.pdf")]
    assert result["photos"] == [tmp_path / "photo.jpg"]


def test_scan_species_directory_pair_has_none_technical_when_sibling_missing(tmp_path):
    (tmp_path / "bm-la.pdf").write_bytes(b"%PDF-fake")

    result = scan_species_directory(tmp_path)
    assert result["pairs"] == [(tmp_path / "bm-la.pdf", None)]


def test_scan_species_directory_finds_multiple_pairs(tmp_path):
    (tmp_path / "ak-la.pdf").write_bytes(b"%PDF-fake")
    (tmp_path / "ak.pdf").write_bytes(b"%PDF-fake")
    (tmp_path / "ak-web-la.pdf").write_bytes(b"%PDF-fake")
    (tmp_path / "ak-web.pdf").write_bytes(b"%PDF-fake")

    result = scan_species_directory(tmp_path)
    assert result["pairs"] == [
        (tmp_path / "ak-la.pdf", tmp_path / "ak.pdf"),
        (tmp_path / "ak-web-la.pdf", tmp_path / "ak-web.pdf"),
    ]


def test_scan_species_directory_empty_when_nothing_found(tmp_path):
    result = scan_species_directory(tmp_path)
    assert result == {"pairs": [], "photos": []}


# ── resolve_species_directory ─────────────────────────────────────────────────

def test_resolve_species_directory_resolves_relative_to_dudus_root(monkeypatch, tmp_path):
    import app

    monkeypatch.setattr(app, "DUDUS_ROOT", tmp_path)
    species_dir = tmp_path / "Biting Midges"
    species_dir.mkdir()

    resolved, err = resolve_species_directory("Biting Midges")
    assert err is None
    assert resolved == species_dir


def test_resolve_species_directory_accepts_absolute_path(tmp_path):
    resolved, err = resolve_species_directory(str(tmp_path))
    assert err is None
    assert resolved == tmp_path


def test_resolve_species_directory_errors_on_empty_input():
    resolved, err = resolve_species_directory("   ")
    assert resolved is None
    assert err is not None


def test_resolve_species_directory_errors_when_not_a_directory(monkeypatch, tmp_path):
    import app

    monkeypatch.setattr(app, "DUDUS_ROOT", tmp_path)
    resolved, err = resolve_species_directory("Nonexistent Species")
    assert resolved is None
    assert err is not None


# ── resolve_order_default_index ───────────────────────────────────────────────

def test_resolve_order_default_index_for_known_order():
    known = ["Araneae", "Coleoptera"]
    assert resolve_order_default_index(known, "Coleoptera") == 1


def test_resolve_order_default_index_for_detected_but_new_order():
    known = ["Araneae", "Coleoptera"]
    # "Other (type manually)" sits right after "Unknown", i.e. len(known) + 1.
    assert resolve_order_default_index(known, "Phasmatodea") == len(known) + 1


def test_resolve_order_default_index_for_no_detected_order():
    known = ["Araneae", "Coleoptera"]
    # "Unknown (fix later)" sits right after the known orders, i.e. len(known).
    assert resolve_order_default_index(known, None) == len(known)


# ── normalize_photo ───────────────────────────────────────────────────────────

def test_normalize_photo_pads_narrow_image_to_3_2():
    img = Image.new("RGB", (100, 100))  # 1:1 — narrower than 3:2
    normalized, warning = normalize_photo(img)
    assert warning is None
    assert normalized.size == (150, 100)


def test_normalize_photo_warns_on_wide_image_without_cropping():
    img = Image.new("RGB", (300, 100))  # 3:1 — wider than 3:2
    normalized, warning = normalize_photo(img)
    assert normalized.size == (300, 100)  # left untouched, never cropped
    assert warning is not None


def test_normalize_photo_leaves_3_2_image_untouched():
    img = Image.new("RGB", (300, 200))  # exactly 3:2
    normalized, warning = normalize_photo(img)
    assert normalized.size == (300, 200)
    assert warning is None


# ── extract_embedded_photo ────────────────────────────────────────────────────

def test_extract_embedded_photo_returns_none_when_no_image():
    pdf = _pdf_bytes([("Just some text, no embedded photo.", 11, False)])
    img, ext = extract_embedded_photo(pdf)
    assert img is None
    assert ext is None


def test_extract_embedded_photo_finds_a_single_embedded_image():
    pdf = _pdf_with_images([(300, 200)])
    img, ext = extract_embedded_photo(pdf)
    assert img is not None
    assert img.size == (300, 200)
    assert ext == "jpg"


def test_extract_embedded_photo_picks_the_largest_by_pixel_area():
    # A small incidental image plus the real, much larger specimen photo — the larger one wins.
    pdf = _pdf_with_images([(40, 40), (800, 600)])
    img, ext = extract_embedded_photo(pdf)
    assert img.size == (800, 600)


def test_extract_embedded_photo_detects_png_extension():
    pdf = _pdf_with_images([(300, 200)], fmt="PNG")
    _img, ext = extract_embedded_photo(pdf)
    assert ext == "png"
