"""Golden path for the card browser (issue #4), now at /search: full list renders, search
filters correctly, breadcrumb goes back home.

Nothing here hardcodes a total count or a specific dudu — the card set changes over time (dudus
get added or withdrawn, as in issue #14), so every expectation is derived from CARDS (the actual
bundled data, loaded in _common.py) at test time.

Locators are scoped to specific data-testid elements, never page.text_content("body") — the
body's textContent includes Next.js's inline RSC hydration payload (a <script> tag serializing
the full card list for the client), which contains every dudu's name regardless of what's
actually rendered/filtered on screen. An unscoped body-text assertion here would always find
every dudu, whether or not it's actually shown.
"""

from _common import BASE_URL, CARDS, browser_page

TOTAL = len(CARDS)


def test_full_list_renders():
    with browser_page() as page:
        page.goto("/search")
        page.wait_for_selector('[data-testid="result-count"]')
        assert f"{TOTAL} of {TOTAL} dudus" in page.text_content(
            '[data-testid="result-count"]'
        )
        list_text = page.text_content('[data-testid="card-list"]')
        # Every card in the index should appear in the rendered list exactly once.
        for card in CARDS:
            assert list_text.count(card["common_name"]) == 1, card["common_name"]


def test_search_filters_to_matching_subset():
    # Use the first word of some card's name as the query, and compute which cards should
    # match from the actual data — never assume a fixed expected count.
    query = CARDS[0]["common_name"].split()[0]
    expected = [c for c in CARDS if query.lower() in c["common_name"].lower()]
    not_expected = [c for c in CARDS if c not in expected]

    with browser_page() as page:
        page.goto("/search")
        page.fill('input[aria-label="Search dudus by common name"]', query)
        page.wait_for_selector(f"text={len(expected)} of {TOTAL} dudus")
        list_text = page.text_content('[data-testid="card-list"]')
        for card in expected:
            assert card["common_name"] in list_text
        for card in not_expected:
            assert card["common_name"] not in list_text


def test_search_no_match_shows_not_yet_researched_state():
    with browser_page() as page:
        page.goto("/search")
        page.fill(
            'input[aria-label="Search dudus by common name"]', "zzzznomatchxyz"
        )
        empty_state = page.locator('[data-testid="not-yet-researched"]')
        empty_state.wait_for()
        text = empty_state.text_content()
        # Distinct from "we don't know" / a dead end — issue #6's actual requirement, not just
        # "some empty state exists."
        assert "zzzznomatchxyz" in text
        assert "isn't in the library yet" in text
        assert "library is still growing" in text


def test_no_match_clear_button_returns_to_full_list():
    with browser_page() as page:
        page.goto("/search")
        page.fill(
            'input[aria-label="Search dudus by common name"]', "zzzznomatchxyz"
        )
        page.locator('[data-testid="not-yet-researched"]').wait_for()
        page.click('[data-testid="not-yet-researched"] button')
        page.wait_for_selector('[data-testid="card-list"]')
        assert f"{TOTAL} of {TOTAL} dudus" in page.text_content(
            '[data-testid="result-count"]'
        )


def test_breadcrumb_links_back_home():
    with browser_page() as page:
        page.goto("/search")
        page.click('[data-testid="breadcrumb-home"]')
        page.wait_for_selector('[data-testid="browse-page"]')
        assert page.url.rstrip("/") == BASE_URL


TESTS = [
    test_full_list_renders,
    test_search_filters_to_matching_subset,
    test_search_no_match_shows_not_yet_researched_state,
    test_no_match_clear_button_returns_to_full_list,
    test_breadcrumb_links_back_home,
]

if __name__ == "__main__":
    for t in TESTS:
        t()
        print(f"PASS {t.__name__}")
