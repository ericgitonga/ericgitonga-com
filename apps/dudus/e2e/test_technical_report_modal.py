"""Golden path for the technical report modal (issue #88, revised #91): the link lives only on
the detail page (the actual report), never on the grid-card thumbnail, which stays a plain
photo + name navigation card. The detail page has two independent trigger instances — one below
the photo, one at the bottom of the report — both opening the same modal content.

Nothing here hardcodes a specific dudu — picks whatever qualifies out of CARDS (the actual
bundled data) at test time, same convention as test_dudu_detail.py.
"""

from _common import CARDS, browser_page


def test_grid_card_has_no_technical_report_link():
    card = next(c for c in CARDS if c["technical_sections"] and c["order"])
    order_slug = card["order"].lower()
    with browser_page() as page:
        page.goto(f"/orders/{order_slug}")
        card_el = page.locator(f'[data-testid="grid-card"][data-dudu-id="{card["id"]}"]')
        assert card_el.locator('[data-testid="technical-report-link"]').count() == 0


def test_detail_page_has_two_technical_report_links_opening_the_same_modal():
    card = next(c for c in CARDS if c["technical_sections"])
    with browser_page() as page:
        page.goto(f"/dudus/{card['id']}")
        links = page.locator('[data-testid="technical-report-link"]')
        assert links.count() == 2

        links.first.click()
        modal = page.locator('[data-testid="technical-report-modal"]')
        modal.wait_for()
        first_section = card["technical_sections"][0]
        assert first_section["heading"] in modal.text_content()
        assert modal.locator("img").count() == 0
        page.click('[data-testid="technical-report-modal-close"]')
        modal.wait_for(state="detached")

        links.last.click()
        modal.wait_for()
        assert first_section["heading"] in modal.text_content()


def test_technical_report_modal_closes_via_close_button():
    card = next(c for c in CARDS if c["technical_sections"])
    with browser_page() as page:
        page.goto(f"/dudus/{card['id']}")
        page.locator('[data-testid="technical-report-link"]').first.click()
        page.locator('[data-testid="technical-report-modal"]').wait_for()
        page.click('[data-testid="technical-report-modal-close"]')
        page.locator('[data-testid="technical-report-modal"]').wait_for(state="detached")


def test_card_without_technical_sections_shows_no_link():
    card = next((c for c in CARDS if not c["technical_sections"]), None)
    if card is None:
        print(
            "SKIP test_card_without_technical_sections_shows_no_link: "
            "every card has technical_sections"
        )
        return
    with browser_page() as page:
        page.goto(f"/dudus/{card['id']}")
        assert page.locator('[data-testid="technical-report-link"]').count() == 0


TESTS = [
    test_grid_card_has_no_technical_report_link,
    test_detail_page_has_two_technical_report_links_opening_the_same_modal,
    test_technical_report_modal_closes_via_close_button,
    test_card_without_technical_sections_shows_no_link,
]

if __name__ == "__main__":
    for t in TESTS:
        t()
        print(f"PASS {t.__name__}")
