import Link from "next/link";
import Image from "next/image";
import type { Card } from "@/types/card";
import { gridCardName } from "@/lib/cardIndex";

export default function DuduGridCard({ card }: { card: Card }) {
  return (
    <Link
      href={`/dudus/${card.id}`}
      data-testid="grid-card"
      data-dudu-id={card.id}
      className="flex flex-col overflow-hidden rounded-lg border border-line bg-card shadow-sm transition hover:shadow-md hover:border-accent-dim"
    >
      {card.photo_ref ? (
        <div className="relative aspect-[4/3] w-full bg-card">
          <Image
            src={card.photo_ref}
            alt={card.common_name}
            fill
            className="object-cover"
            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
          />
        </div>
      ) : (
        <div className="flex aspect-[4/3] w-full items-center justify-center bg-card text-sm text-muted">
          No photo yet
        </div>
      )}
      <div className="p-4">
        <p className="font-serif italic font-medium text-foreground">{gridCardName(card.common_name)}</p>
      </div>
    </Link>
  );
}
