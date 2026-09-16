# Onboarding: eric-gitonga-links

Repo: [`ericgitonga/eric-gitonga-links`](https://github.com/ericgitonga/eric-gitonga-links). See `README.md` for what this project is
and how it's structured. This doc covers the **workflow conventions** for making changes to the
repo — read it before making any change, however small.

## Every change gets an issue

Before starting *any* change — trivial or major, a one-line copy tweak or a full rebuild — file
a GitHub issue for it first:

```bash
gh issue create --repo ericgitonga/eric-gitonga-links --title "..." --body "..."
```

If a change ever ships without one, file the issue retroactively rather than skip it — don't let
untracked changes accumulate.

## Branch + PR for everything

`main` has branch protection: **no direct pushes, even for a one-line copy fix.** Every change
goes through a branch + PR:

```bash
git checkout -b some-short-description
# ... commit ...
git push -u origin some-short-description
gh pr create --title "..." --body "Closes #N"
```

## No CI — the Preview URL and your own confirmation are the gate

This is a static site with no build step and no automated test suite, so there's no `e2e`/`unit`
status check the way the other repos in this family have. Instead: once a PR is open, report the
Vercel Preview URL and wait for **explicit confirmation** before merging — never merge on your
own judgment alone, and never bundle "here's the Preview" with "can I merge?" in the same
message as a done deal. The same "LGM" convention applies as everywhere else.

## Merge with `--squash`, delete the branch

```bash
gh pr merge <N> --squash --delete-branch
```

The PR body's `Closes #N` auto-closes the issue on merge — don't separately/manually close it.
Confirm the PR actually shows Closed and the branch is actually gone after merging.

## Documentation stays in sync with the change

A PR that changes behaviour, structure, or how the site is organized is not done until `README.md`
reflects it. Before opening a PR, re-read `README.md` and fix anything that's gone stale — even
drift you didn't personally cause.

## GitHub account

The only account used for this repo is **`ericgitonga`**. Confirm before running any `gh`/`git`
command that touches GitHub if there's ever doubt about which account is active.
