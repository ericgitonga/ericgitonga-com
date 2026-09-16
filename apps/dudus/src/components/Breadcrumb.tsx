import Link from "next/link";

export type BreadcrumbItem = { label: string; href?: string };

export default function Breadcrumb({
  items,
  "data-testid": testId,
}: {
  items: BreadcrumbItem[];
  "data-testid"?: string;
}) {
  return (
    <nav
      data-testid={testId}
      className="mb-4 flex flex-wrap items-center gap-1 text-sm text-muted"
    >
      {items.map((item, i) => (
        <span key={i} className="flex items-center gap-1">
          {i > 0 && <span className="text-muted">/</span>}
          {item.href ? (
            <Link
              href={item.href}
              data-testid={testId ? `${testId}-item` : undefined}
              className="text-accent hover:text-foreground hover:underline"
            >
              {item.label}
            </Link>
          ) : (
            <span className="font-medium text-foreground">{item.label}</span>
          )}
        </span>
      ))}
    </nav>
  );
}
