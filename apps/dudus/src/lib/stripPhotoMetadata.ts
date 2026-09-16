/**
 * Strips all metadata — EXIF, GPS/geotag, everything — from a captured photo (issue #10).
 * Decodes the file and re-encodes just the raw pixels onto a canvas: canvas output has no
 * metadata channel at all, so this is a full strip regardless of what tags the original file
 * carried, not a targeted "remove the GPS tag" edit that could miss a vendor-specific field.
 * `imageOrientation: "from-image"` bakes in the EXIF-implied rotation before it's dropped, so
 * the stripped copy still displays right-side up.
 */
export async function stripPhotoMetadata(file: File): Promise<File> {
  const bitmap = await createImageBitmap(file, { imageOrientation: "from-image" });
  const canvas = document.createElement("canvas");
  canvas.width = bitmap.width;
  canvas.height = bitmap.height;

  const ctx = canvas.getContext("2d");
  if (!ctx) throw new Error("Canvas 2D context unavailable");
  ctx.drawImage(bitmap, 0, 0);
  bitmap.close();

  const outputType = file.type === "image/png" ? "image/png" : "image/jpeg";
  const blob = await new Promise<Blob>((resolve, reject) => {
    canvas.toBlob(
      (b) => (b ? resolve(b) : reject(new Error("Canvas toBlob failed"))),
      outputType,
      0.92,
    );
  });

  return new File([blob], file.name, { type: outputType });
}
