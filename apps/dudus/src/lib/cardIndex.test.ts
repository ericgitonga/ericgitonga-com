import { describe, expect, it } from "vitest";
import { filterCardsByQuery, gridCardName, sortCardsByCommonName } from "@/lib/cardIndex";
import type { Card } from "@/types/card";

function card(id: string, common_name: string): Card {
  return {
    id,
    common_name,
    scientific_name: null,
    family: null,
    order: null,
    taxon_rank: "species",
    sourcing: "",
    sections: [],
    technical_sections: [],
    source_report_ref: { technical: "", lay: "" },
    photo_ref: null,
    reviewed_by: null,
    reviewed_at: null,
  };
}

describe("sortCardsByCommonName", () => {
  it("sorts alphabetically by common name", () => {
    const cards = [card("c", "Zebra Spider"), card("a", "Aedes Mosquito"), card("b", "Monarch Butterfly")];
    expect(sortCardsByCommonName(cards).map((c) => c.id)).toEqual(["a", "b", "c"]);
  });

  it("does not mutate the input array", () => {
    const cards = [card("b", "Zebra Spider"), card("a", "Aedes Mosquito")];
    const original = [...cards];
    sortCardsByCommonName(cards);
    expect(cards).toEqual(original);
  });

  it("returns an empty array for an empty input", () => {
    expect(sortCardsByCommonName([])).toEqual([]);
  });
});

describe("filterCardsByQuery", () => {
  const cards = [card("a", "Aedes Mosquito"), card("b", "Zebra Spider"), card("c", "Monarch Butterfly")];

  it("returns all cards when the query is empty", () => {
    expect(filterCardsByQuery(cards, "")).toEqual(cards);
  });

  it("returns all cards when the query is only whitespace", () => {
    expect(filterCardsByQuery(cards, "   ")).toEqual(cards);
  });

  it("matches case-insensitively", () => {
    expect(filterCardsByQuery(cards, "ZEBRA").map((c) => c.id)).toEqual(["b"]);
  });

  it("matches a substring anywhere in the common name", () => {
    expect(filterCardsByQuery(cards, "arch").map((c) => c.id)).toEqual(["c"]);
  });

  it("trims surrounding whitespace from the query", () => {
    expect(filterCardsByQuery(cards, "  mosquito  ").map((c) => c.id)).toEqual(["a"]);
  });

  it("returns an empty array when nothing matches", () => {
    expect(filterCardsByQuery(cards, "nonexistent")).toEqual([]);
  });
});

describe("gridCardName", () => {
  it("strips a ' — ' tagline for thumbnail display", () => {
    expect(gridCardName("Click Beetle — The Insect That Flips Itself With a Snap")).toBe(
      "Click Beetle",
    );
  });

  it("passes through a name with no tagline unchanged", () => {
    expect(gridCardName("Picasso Bug")).toBe("Picasso Bug");
  });

  it("leaves a plain hyphen (no surrounding spaces) untouched", () => {
    expect(gridCardName("White-Ringed Atlas Moth")).toBe("White-Ringed Atlas Moth");
  });
});
