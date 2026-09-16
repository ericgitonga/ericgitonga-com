"""Shared helpers for the Playwright E2E smoke suite.

Written against the Python `playwright` package (the `ds` conda env already has it installed
with browsers pre-cached), not `@playwright/test` — deliberate, so Python tooling doesn't need
a parallel npm toolchain. Specs are plain scripts (`TESTS = [...]` list of functions,
`assert`-based), run via:

    npm run build && npm start   # in one terminal
    conda run -n ds python e2e/run.py   # in another

BASE_URL overrides the default local server; CI points it at a locally built-and-started server
(see .github/workflows/e2e.yml) rather than a live Vercel Preview URL, to avoid depending on
Vercel's own deployment-webhook timing.
"""

import json
import os
from contextlib import contextmanager
from pathlib import Path

from playwright.sync_api import sync_playwright

BASE_URL = os.environ.get("BASE_URL", "http://localhost:3000").rstrip("/")

# The live card count changes over time (dudus get added/withdrawn) — specs must never
# hardcode a total or a specific dudu's name/count. Load the actual bundled data instead, so
# every assertion is derived from whatever's really in the index at test time.
CARDS = json.loads(
    (Path(__file__).parent.parent / "src" / "data" / "card_index.json").read_text()
)


@contextmanager
def browser_page():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        try:
            page = browser.new_page(base_url=BASE_URL)
            yield page
        finally:
            browser.close()
