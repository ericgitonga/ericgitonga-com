import { notFound } from "next/navigation";
import Image from "next/image";
import cards from "@/data/card_index.json";
import type { Card } from "@/types/card";
import { UNCLASSIFIED_LABEL, orderSlug } from "@/lib/orders";
import Breadcrumb from "@/components/Breadcrumb";
import TechnicalReportLink from "@/components/TechnicalReportLink";

const allCards = cards as Card[];

export function generateStaticParams() {
  return allCards.map((card) => ({ id: card.id }));
}

export default async function DuduPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const card = allCards.find((c) => c.id === id);
  if (!card) notFound();

  const taxonomyLine = [card.family, card.order].filter(Boolean).join(" · ");
  const order = card.order ?? UNCLASSIFIED_LABEL;

  return (
    <div className="flex flex-col flex-1 items-center bg-background">
      <main
        data-testid="dudu-detail"
        className="flex flex-1 w-full max-w-2xl flex-col items-stretch py-16 px-6 bg-card"
      >
        <Breadcrumb
          data-testid="dudu-breadcrumb"
          items={[
            { label: "Dudus", href: "/" },
            { label: order, href: `/orders/${orderSlug(order)}` },
            { label: card.common_name },
          ]}
        />

        <h1 className="font-serif italic text-2xl font-medium tracking-tight text-foreground">
          {card.common_name}
        </h1>
        {card.scientific_name && (
          <p className="mt-1 italic text-muted">
            {card.scientific_name}
          </p>
        )}
        <p className="mt-1 text-sm font-mono text-muted">
          {taxonomyLine || `Taxonomy confirmed to ${card.taxon_rank} level only`}
        </p>

        {card.photo_ref && (
          <div className="relative mt-4 aspect-[3/2] w-full overflow-hidden rounded-lg">
            <Image
              data-testid="dudu-photo"
              src={card.photo_ref}
              alt={card.common_name}
              fill
              className="object-cover"
              sizes="(max-width: 672px) 100vw, 672px"
            />
          </div>
        )}

        {card.technical_sections.length > 0 && (
          <div className="mt-3">
            <TechnicalReportLink
              commonName={card.common_name}
              sections={card.technical_sections}
            />
          </div>
        )}

        <div className="mt-8 flex flex-col gap-6">
          {card.sections.map((section) => (
            <section key={section.heading}>
              <h2 className="text-lg font-medium text-foreground">{section.heading}</h2>
              <p className="mt-1 text-foreground">
                {section.body}
              </p>
            </section>
          ))}
        </div>

        {card.technical_sections.length > 0 && (
          <div className="mt-8 border-t border-line pt-6">
            <TechnicalReportLink
              commonName={card.common_name}
              sections={card.technical_sections}
            />
          </div>
        )}
      </main>
    </div>
  );
}
