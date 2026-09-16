import Link from "next/link";
import PhotoCapture from "@/components/PhotoCapture";

export default function IdentifyPage() {
  return (
    <div className="flex flex-col flex-1 items-center bg-background">
      <main className="flex flex-1 w-full max-w-2xl flex-col items-stretch py-16 px-6 bg-card">
        <Link
          href="/"
          className="text-sm text-muted hover:underline"
        >
          ← All dudus
        </Link>

        <h1 className="mt-4 font-serif italic text-2xl font-medium tracking-tight text-foreground">
          Identify a dudu
        </h1>
        <p className="mt-1 text-sm text-muted">
          Photograph what you found.
        </p>

        <div className="mt-8">
          <PhotoCapture />
        </div>
      </main>
    </div>
  );
}
