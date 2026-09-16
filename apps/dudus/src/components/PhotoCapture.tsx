"use client";

import { useState } from "react";

import { stripPhotoMetadata } from "@/lib/stripPhotoMetadata";

/**
 * In-app photo capture (issue #7). Uses accept="image/*" capture="environment" so mobile
 * browsers open the camera directly rather than the gallery — the honest limitation, agreed
 * with the user before building this, is that desktop browsers ignore `capture` and fall back
 * to a normal file picker (gallery included). No fully custom getUserMedia viewfinder was built;
 * this is the deliberately simpler option.
 *
 * Deliberately does nothing with the captured photo beyond a local preview — no upload, no
 * network call, nothing leaves the device. That's not a gap to fill in here: taxon-guessing
 * (#8) and index-matching (#9) are separate tickets. Every captured photo is stripped of
 * EXIF/geotag metadata (#10) before it's ever held as state or displayed, so no downstream
 * consumer of this component can accidentally see or forward the original.
 */
export default function PhotoCapture() {
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  async function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;
    const clean = await stripPhotoMetadata(file);
    setPreviewUrl((prev) => {
      if (prev) URL.revokeObjectURL(prev);
      return URL.createObjectURL(clean);
    });
  }

  return (
    <div data-testid="photo-capture">
      <label
        htmlFor="specimen-photo"
        className="inline-block cursor-pointer rounded-lg border border-accent-dim px-4 py-2 text-sm font-medium text-accent hover:border-accent hover:opacity-70"
      >
        Take a photo
      </label>
      <input
        id="specimen-photo"
        type="file"
        accept="image/*"
        capture="environment"
        onChange={handleChange}
        className="sr-only"
        aria-label="Capture a photo of a specimen"
      />

      {previewUrl && (
        <div className="mt-4">
          {/* eslint-disable-next-line @next/next/no-img-element -- a locally captured blob: URL, not an optimizable static/remote asset */}
          <img
            data-testid="photo-preview"
            src={previewUrl}
            alt="Captured specimen"
            className="max-w-full rounded-lg"
          />
          <p className="mt-2 text-sm text-muted">
            Identification isn&apos;t wired up yet — this just proves capture works.
          </p>
        </div>
      )}
    </div>
  );
}
