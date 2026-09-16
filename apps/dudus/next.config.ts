import type { NextConfig } from "next";

// Baseline HTTP security headers (repos-security-audit.md finding, dudus-app item 9;
// issue #80). This site is fully static/self-contained — no external image hosts,
// fonts self-hosted at build time via next/font/google, and @vercel/analytics +
// @vercel/speed-insights both beacon same-origin (/_vercel/...) — so the CSP can stay
// tight without an allowlist of third-party domains.
const securityHeaders = [
  { key: "X-Frame-Options", value: "DENY" },
  { key: "X-Content-Type-Options", value: "nosniff" },
  { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
  {
    key: "Content-Security-Policy",
    value: [
      "default-src 'self'",
      "script-src 'self' 'unsafe-inline'",
      "style-src 'self' 'unsafe-inline'",
      "img-src 'self' data:",
      "font-src 'self'",
      // 'blob:' needed for connect-src: /identify's capture flow fetches its own
      // in-memory blob: preview URL to strip EXIF before display (e2e/test_photo_capture.py) —
      // some browsers (Firefox in particular) don't treat blob: as implicitly same-origin for
      // fetch() the way they do for an <img src="blob:...">.
      "connect-src 'self' blob:",
      "object-src 'none'",
      "base-uri 'self'",
      "form-action 'self'",
      "frame-ancestors 'none'",
    ].join("; "),
  },
];

const nextConfig: NextConfig = {
  async headers() {
    return [
      {
        source: "/:path*",
        headers: securityHeaders,
      },
    ];
  },
};

export default nextConfig;
