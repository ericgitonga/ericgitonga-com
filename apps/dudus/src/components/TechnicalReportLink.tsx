"use client";

import { useEffect, useState } from "react";
import type { CardSection } from "@/types/card";

export default function TechnicalReportLink({
  commonName,
  sections,
}: {
  commonName: string;
  sections: CardSection[];
}) {
  const [open, setOpen] = useState(false);

  useEffect(() => {
    if (!open) return;
    const onKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") setOpen(false);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, [open]);

  if (sections.length === 0) return null;

  return (
    <>
      <button
        type="button"
        data-testid="technical-report-link"
        onClick={(e) => {
          e.preventDefault();
          e.stopPropagation();
          setOpen(true);
        }}
        className="text-sm font-medium text-accent underline underline-offset-2 hover:text-foreground"
      >
        Technical report
      </button>

      {open && (
        <div
          data-testid="technical-report-modal"
          role="dialog"
          aria-modal="true"
          aria-label={`${commonName} technical report`}
          className="fixed inset-0 z-50 flex items-center justify-center bg-foreground/50 p-4"
          onClick={() => setOpen(false)}
        >
          <div
            className="flex max-h-[80vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-card shadow-lg"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="flex items-center justify-between border-b border-line px-6 py-4">
              <h2 className="font-serif italic text-lg font-medium text-foreground">
                {commonName} — Technical Report
              </h2>
              <button
                type="button"
                data-testid="technical-report-modal-close"
                aria-label="Close"
                onClick={() => setOpen(false)}
                className="text-2xl leading-none text-muted hover:text-foreground"
              >
                ×
              </button>
            </div>
            <div className="flex flex-col gap-4 overflow-y-auto px-6 py-4">
              {sections.map((section, i) => (
                <section key={`${section.heading}-${i}`}>
                  <h3 className="font-medium text-foreground">{section.heading}</h3>
                  <p className="mt-1 text-sm text-foreground">{section.body}</p>
                </section>
              ))}
            </div>
          </div>
        </div>
      )}
    </>
  );
}
