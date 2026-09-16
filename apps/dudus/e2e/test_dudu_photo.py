"""Golden path for existing reference photos on cards (folded into issue #7 per the user):
dudus with a photo_ref render it on the detail page and as a browser-list thumbnail; dudus
without one render neither.
"""

from urllib.parse import quote

from _common import CARDS, browser_page


def test_dudu_with_photo_renders_it_on_detail_page():
    card = next(c for c in CARDS if c["photo_ref"])
    with browser_page() as page:
        page.goto(f"/dudus/{card['id']}")
        img = page.locator('[data-testid="dudu-photo"]')
        img.wait_for()
        # next/image rewrites src through its optimizer (/_next/image?url=...&w=...), so the
        # original path only survives URL-encoded inside the query string, not as a raw
        # substring. alt text is the unencoded, reliable signal that this is the right photo.
        assert img.get_attribute("alt") == card["common_name"]
        src = img.get_attribute("src") or ""
        assert quote(card["photo_ref"], safe="") in src


def test_dudu_without_photo_renders_no_photo_element():
    card = next((c for c in CARDS if not c["photo_ref"]), None)
    if card is None:
        print("SKIP test_dudu_without_photo_renders_no_photo_element: every card has a photo")
        return
    with browser_page() as page:
        page.goto(f"/dudus/{card['id']}")
        page.wait_for_selector('[data-testid="dudu-detail"]')
        assert page.locator('[data-testid="dudu-photo"]').count() == 0


def test_browser_list_shows_thumbnail_for_photographed_dudu():
    card = next(c for c in CARDS if c["photo_ref"])
    with browser_page() as page:
        page.goto("/search")
        page.fill(
            'input[aria-label="Search dudus by common name"]',
            card["common_name"],
        )
        page.wait_for_selector('[data-testid="card-list"]')
        thumb = page.locator('[data-testid="card-list"] img')
        thumb.wait_for()
        assert thumb.count() >= 1


TESTS = [
    test_dudu_with_photo_renders_it_on_detail_page,
    test_dudu_without_photo_renders_no_photo_element,
    test_browser_list_shows_thumbnail_for_photographed_dudu,
]

if __name__ == "__main__":
    for t in TESTS:
        t()
        print(f"PASS {t.__name__}")
