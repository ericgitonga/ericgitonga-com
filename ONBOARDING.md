# Onboarding: ericgitonga.com monorepo

Repo: [`ericgitonga/ericgitonga-com`](https://github.com/ericgitonga/ericgitonga-com). This is
the shared monorepo for everything behind ericgitonga.com — one app per subdirectory under
`apps/`, one shared workflow, one shared version. This doc covers the workflow conventions for
making changes anywhere in this repo — read it before making any change, however small.

## Apps in this repo

- `apps/hub/` — the hub site (formerly `eric-gitonga-links`): the four plate pages, the album
  galleries, the contact form. Static HTML/CSS/JS, no build step, plus two Node Vercel Functions.

`dudus-app` and `dudu-merchandise` are not migrated yet — this repo started with `apps/hub` alone
as a trial run. If they join later, they'll get their own `apps/<name>/` directory the same way.

## Every change gets an issue

Before starting *any* change — trivial or major, a one-line copy tweak or a full feature — file
a GitHub issue for it first:

```bash
gh issue create --repo ericgitonga/ericgitonga-com --title "..." --body "..."
```

If a change ever ships without one, file the issue retroactively rather than skip it.

## Branch + PR for everything

`main` is branch-protected: no direct pushes, even for docs-only changes. Every change goes
through a branch + PR:

```bash
git checkout -b some-short-description
# ... commit ...
git push -u origin some-short-description
gh pr create --title "..." --body "Closes #N"
```

Prefix the branch/PR/commit with the app it touches once there's more than one app in here (e.g.
`hub: fix nav overflow`) — not needed yet while `apps/hub` is the only one.

## CI is per-app, path-filtered

Each app keeps its own test runner/CI workflow, gated to only run when that app's own directory
changes (`.github/workflows/<app>-*.yml`, `on.push.paths: ["apps/<app>/**"]`) — a PR touching only
`apps/hub/**` never triggers another app's suite. `apps/hub` currently has no automated test
suite of its own (same as before the migration — it never had one in `eric-gitonga-links`
either); Vercel's own build check plus a manual Preview-URL check before merging is the gate,
same as before.

## Merge with `--squash`, delete the branch

```bash
gh pr merge <N> --squash --delete-branch
```

The PR body's `Closes #N` auto-closes the issue on merge — don't separately close it. Confirm the
PR actually shows Closed and the branch is actually gone after merging.

## Versioning — one shared number across every app, on purpose

**A PR that bumps `VERSION`/`CHANGELOG.md` is not done when it merges.** Cutting the tag +
GitHub Release is part of the same unit of work as the merge, not a separate later step.

Unlike a typical monorepo, this one deliberately uses **one version number for the whole
repo**, bumped on *any* change to *any* app — not independent per-app versions. That was an
explicit choice (see `extras/personal/me/unified.pdf`, outside this repo): simpler to operate,
consistent with how every other one of this family's projects already versions itself, at the
cost of a version bump not telling you on its own which app actually changed — check the
changelog entry for that. `dudus-app`'s own `tools/dudu-intake` shows the opposite pattern
(independent per-component versioning) works too, if this one ever needs revisiting.

1. Bump `VERSION` (single line, no `v` prefix, e.g. `0.2.0`) — and any framework version field
   that must mirror it (e.g. an app's own `package.json` if it has one), so they never drift.
2. Add a `CHANGELOG.md` entry.
3. Merge the PR.
4. Tag: `git tag -a vX.Y.Z -m "..."` then `git push origin vX.Y.Z`.
5. Release: `gh release create vX.Y.Z --title "..." --notes "..."`.
6. Verify with `gh release list` that the new version shows as latest.

## Documentation stays in sync with the change

A PR that changes behaviour, structure, or how an app is organized is not done until that app's
own `README.md` (and this file, if the shared workflow itself changes) reflects it.

## GitHub account

The only account used for this repo is **`ericgitonga`**. Confirm before running any `gh`/`git`
command that touches GitHub if there's ever doubt about which account is active.
