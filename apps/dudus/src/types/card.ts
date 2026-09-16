export interface CardSection {
  heading: string;
  body: string;
}

export type TaxonRank = "species" | "genus" | "family" | "order";

export interface Card {
  id: string;
  common_name: string;
  scientific_name: string | null;
  family: string | null;
  order: string | null;
  taxon_rank: TaxonRank;
  sourcing: string;
  sections: CardSection[];
  technical_sections: CardSection[];
  source_report_ref: {
    technical: string | string[];
    lay: string | string[];
  };
  photo_ref: string | null;
  reviewed_by: string | null;
  reviewed_at: string | null;
}
