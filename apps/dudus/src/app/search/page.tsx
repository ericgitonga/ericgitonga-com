import Link from "next/link";
import cards from "@/data/card_index.json";
import type { Card } from "@/types/card";
import CardBrowser from "@/components/CardBrowser";

export default function SearchPage() {
  return (
    <div className="flex flex-col flex-1 items-center bg-background">
      <main className="flex flex-1 w-full max-w-2xl flex-col items-stretch py-16 px-6 bg-card">
        <nav aria-label="breadcrumb" className="text-sm text-muted">
          <Link href="/" data-testid="breadcrumb-home" className="text-accent hover:underline">
            Home
          </Link>
          <span className="mx-2">/</span>
          <span className="text-foreground">Search</span>
        </nav>

        <h1 className="mt-4 font-serif italic text-2xl font-medium tracking-tight text-foreground">
          Search dudus
        </h1>
        <p className="mt-1 text-sm text-muted">
          Kenyan arthropods, researched and reviewed.
        </p>
        <div className="mt-8">
          <CardBrowser cards={cards as Card[]} />
        </div>
      </main>
    </div>
  );
}
