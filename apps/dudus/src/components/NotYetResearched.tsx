"use client";

import Link from "next/link";

/**
 * The "not yet researched" empty state (issue #6) — distinct on purpose from a generic
 * "not found." Two triggers share this copy: a manual search with no match now, and (later,
 * once #7-#9 land) a photo-based guess that doesn't land on anything in the card index. Neither
 * means "we don't know what this is" — it means the library hasn't covered it yet, which is
 * solvable later, not a dead end.
 *
 * Pass `onClear` when embedded inline next to the thing that produced the empty query (e.g. the
 * search box) — renders a button that resets it. Omit it when this is the only thing on the
 * page (e.g. a future standalone photo-result view) — renders a link back to the browser
 * instead.
 */
export default function NotYetResearched({
  query,
  onClear,
}: {
  query?: string;
  onClear?: () => void;
}) {
  return (
    <div data-testid="not-yet-researched" className="mt-6">
      <p className="text-sm text-foreground">
        {query ? (
          <>&quot;{query}&quot; isn&apos;t in the library yet.</>
        ) : (
          <>This one isn&apos;t in the library yet.</>
        )}
      </p>
      <p className="mt-1 text-sm text-muted">
        That doesn&apos;t mean it isn&apos;t a real dudu — the library is still growing, one
        researched dudu at a time.
      </p>
      {onClear ? (
        <button
          type="button"
          onClick={onClear}
          className="mt-3 text-sm font-medium text-accent underline"
        >
          ← Browse all dudus
        </button>
      ) : (
        <Link href="/" className="mt-3 inline-block text-sm font-medium text-accent underline">
          ← Browse all dudus
        </Link>
      )}
    </div>
  );
}
