import type { Card } from "@/types/card";

export const UNCLASSIFIED_LABEL = "Order not yet identified";
const UNCLASSIFIED_SLUG = "unclassified";

export interface OrderGroup {
  order: string;
  slug: string;
  cards: Card[];
}

export function orderSlug(order: string): string {
  return order === UNCLASSIFIED_LABEL ? UNCLASSIFIED_SLUG : order.toLowerCase();
}

export function groupByOrder(cards: Card[]): OrderGroup[] {
  const groups = new Map<string, Card[]>();
  for (const card of cards) {
    const key = card.order ?? UNCLASSIFIED_LABEL;
    const group = groups.get(key) ?? [];
    group.push(card);
    groups.set(key, group);
  }
  const orders = [...groups.keys()]
    .filter((order) => order !== UNCLASSIFIED_LABEL)
    .sort((a, b) => a.localeCompare(b));
  if (groups.has(UNCLASSIFIED_LABEL)) orders.push(UNCLASSIFIED_LABEL);
  return orders.map((order) => ({
    order,
    slug: orderSlug(order),
    cards: groups.get(order)!,
  }));
}

// Example dudus per order, for the tagline shown under each order button — not derived from
// the bundled card index, since these describe the order in general rather than what's
// currently catalogued.
const ORDER_TAGLINES: Record<string, string> = {
  Araneae: "spiders",
  Coleoptera: "beetles",
  Diptera: "flies and mosquitoes",
  Hemiptera: "true bugs, cicadas and aphids",
  Hymenoptera: "bees, wasps and ants",
  Lepidoptera: "moths and butterflies",
  Orthoptera: "grasshoppers, crickets and locusts",
  Phasmatodea: "stick and leaf insects",
  Trombidiformes: "mites and chiggers",
  [UNCLASSIFIED_LABEL]: "awaiting taxonomic identification",
};

export function orderTagline(order: string): string {
  return ORDER_TAGLINES[order] ?? "various arthropods";
}
