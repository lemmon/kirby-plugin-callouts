# Task Pool (next steps)

Public repo; keep this list short (about 7) and up to date. Rules: no numbering (keeps churn low), prune completed items, and replace them with the next priority. Contributors: pick one task, keep PRs focused, and update the list as things land.

-   Sanitize or escape `classPrefix` before inserting into HTML class attributes to prevent malformed markup or unintended attributes in rendered callouts.
-   Add label translation support with a configurable locale map and safe fallback to the current uppercase labels.
-   Provide a Tailwind-friendly styling preset or guidance that maps callout states to utility classes.
-   Create a Kirby Blocks field blueprint/snippet to make callouts easier for editors to insert consistently.
-   Add snapshot or fixture tests for renderer HTML output to catch parser regressions.
