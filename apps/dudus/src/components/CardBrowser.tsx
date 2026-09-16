"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import Image from "next/image";
import type { Card } from "@/types/card";
import { filterCardsByQuery, sortCardsByCommonName } from "@/lib/cardIndex";
import NotYetResearched from "@/components/NotYetResearched";

export default function CardBrowser({ cards }: { cards: Card[] }) {
  const [query, setQuery] = useState("");

  const sorted = useMemo(() => sortCardsByCommonName(cards), [cards]);

  const filtered = useMemo(
    () => filterCardsByQuery(sorted, query),
    [sorted, query],
  );

  return (
    <div className="w-full">
      <input
        type="search"
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Search by common name…"
        aria-label="Search dudus by common name"
        className="w-full rounded-lg border border-line bg-transparent px-4 py-2 text-base outline-none focus:ring-2 focus:ring-accent/40"
      />

      <p
        data-testid="result-count"
        className="mt-3 text-sm font-mono text-muted"
      >
        {filtered.length} of {cards.length} dudus
      </p>

      {filtered.length === 0 ? (
        <NotYetResearched query={query} onClear={() => setQuery("")} />
      ) : (
        <ul
          data-testid="card-list"
          className="mt-4 divide-y divide-line"
        >
          {filtered.map((card) => {
            const taxonomyLine = [card.family, card.order]
              .filter(Boolean)
              .join(" · ");
            return (
              <li key={card.id} className="py-3">
                <Link
                  href={`/dudus/${card.id}`}
                  className="flex items-center gap-3 hover:opacity-70"
                >
                  {card.photo_ref && (
                    <div className="relative h-12 w-12 shrink-0 overflow-hidden rounded-md">
                      <Image
                        src={card.photo_ref}
                        alt={card.common_name}
                        fill
                        className="object-cover"
                        sizes="48px"
                      />
                    </div>
                  )}
                  <div>
                    <div className="font-medium text-accent">{card.common_name}</div>
                    {card.scientific_name && (
                      <div className="text-sm italic text-muted">
                        {card.scientific_name}
                      </div>
                    )}
                    <div className="text-xs font-mono text-muted">
                      {taxonomyLine || `Taxonomy confirmed to ${card.taxon_rank} level only`}
                    </div>
                  </div>
                </Link>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}
