"""Golden path for the home page's order buttons (issue #23): one button per taxonomic order
present in the data, linking to that order's own page of grid cards. Every assertion is derived
from the actual bundled card index at test time — orders and counts are never hardcoded (see
_common.py).
"""

from _common import BASE_URL, CARDS, browser_page

UNCLASSIFIED_LABEL = "Order not yet identified"


def _order_slug(order):
    return "unclassified" if order is None else order.lower()


def _orders_present():
    return {c["order"] if c["order"] else UNCLASSIFIED_LABEL for c in CARDS}


def test_only_orders_present_in_data_are_listed():
    with browser_page() as page:
        page.goto("/")
        page.wait_for_selector('[data-testid="order-button"]')
        rendered = set(
            page.locator('[data-testid="order-button"]').evaluate_all(
                "els => els.map(e => e.getAttribute('data-order'))"
            )
        )
        assert rendered == _orders_present()


def test_order_button_navigates_to_its_own_page_with_only_its_dudus():
    order = next(c["order"] for c in CARDS if c["order"])
    expected = {c["id"] for c in CARDS if c["order"] == order}
    not_expected = {c["id"] for c in CARDS if c["order"] != order}
    with browser_page() as page:
        page.goto("/")
        page.click(f'[data-testid="order-button"][data-order="{order}"]')
        page.wait_for_selector('[data-testid="order-page"]')
        assert page.url.rstrip("/").endswith(f"/orders/{_order_slug(order)}")
        ids = set(
            page.locator('[data-testid="grid-card"]').evaluate_all(
                "els => els.map(e => e.getAttribute('data-dudu-id'))"
            )
        )
        assert ids == expected
        assert not (ids & not_expected)


def test_unclassified_dudus_grouped_under_fallback_button():
    unclassified = {c["id"] for c in CARDS if not c["order"]}
    if not unclassified:
        print("SKIP test_unclassified_dudus_grouped_under_fallback_button: every card has an order")
        return
    with browser_page() as page:
        page.goto("/")
        page.click(f'[data-testid="order-button"][data-order="{UNCLASSIFIED_LABEL}"]')
        page.wait_for_selector('[data-testid="order-page"]')
        ids = set(
            page.locator('[data-testid="grid-card"]').evaluate_all(
                "els => els.map(e => e.getAttribute('data-dudu-id'))"
            )
        )
        assert ids == unclassified


def test_grid_card_on_order_page_links_to_its_detail_page():
    card = CARDS[0]
    order_slug = _order_slug(card["order"])
    with browser_page() as page:
        page.goto(f"/orders/{order_slug}")
        page.click(f'[data-testid="grid-card"][data-dudu-id="{card["id"]}"]')
        page.wait_for_selector('[data-testid="dudu-detail"]')
        assert page.url.rstrip("/").endswith(f"/dudus/{card['id']}")


def test_grid_card_shows_only_the_name_not_a_tagline():
    # A few common_name values are "Name — descriptive tagline" (issue #93) — the tagline
    # belongs to the report, not the thumbnail.
    card = next(c for c in CARDS if " — " in c["common_name"])
    short_name = card["common_name"].split(" — ")[0]
    order_slug = _order_slug(card["order"])
    with browser_page() as page:
        page.goto(f"/orders/{order_slug}")
        card_el = page.locator(f'[data-testid="grid-card"][data-dudu-id="{card["id"]}"]')
        assert card_el.text_content().strip() == short_name


def test_order_page_breadcrumb_shows_trail_and_links_home():
    order = next(c["order"] for c in CARDS if c["order"])
    with browser_page() as page:
        page.goto(f"/orders/{_order_slug(order)}")
        text = page.locator('[data-testid="order-breadcrumb"]').text_content()
        assert "Dudus" in text
        assert order in text
        page.click('[data-testid="order-breadcrumb-item"]')
        page.wait_for_selector('[data-testid="browse-page"]')
        assert page.url.rstrip("/") == BASE_URL


TESTS = [
    test_only_orders_present_in_data_are_listed,
    test_order_button_navigates_to_its_own_page_with_only_its_dudus,
    test_unclassified_dudus_grouped_under_fallback_button,
    test_grid_card_on_order_page_links_to_its_detail_page,
    test_grid_card_shows_only_the_name_not_a_tagline,
    test_order_page_breadcrumb_shows_trail_and_links_home,
]

if __name__ == "__main__":
    for t in TESTS:
        t()
        print(f"PASS {t.__name__}")
