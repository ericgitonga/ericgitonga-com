import type { Card } from "@/types/card";

export function sortCardsByCommonName(cards: Card[]): Card[] {
  return [...cards].sort((a, b) => a.common_name.localeCompare(b.common_name));
}

export function filterCardsByQuery(cards: Card[], query: string): Card[] {
  const q = query.trim().toLowerCase();
  if (!q) return cards;
  return cards.filter((card) => card.common_name.toLowerCase().includes(q));
}

/**
 * A few common_name values are a name plus a descriptive tagline, joined by " — "
 * (e.g. "Click Beetle — The Insect That Flips Itself With a Snap") — meant for the report
 * itself, not the grid-card thumbnail. Strips that tagline for thumbnail display; names without
 * one pass through unchanged.
 */
export function gridCardName(commonName: string): string {
  return commonName.split(" — ")[0];
}
