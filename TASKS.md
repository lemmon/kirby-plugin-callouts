# Task Pool (next steps)

Public repo; keep this list short (about 7) and up to date. Rules: no numbering (keeps churn low), prune completed items, and replace them with the next priority. Contributors: pick one task, keep PRs focused, and update the list as things land.

-   Avoid re-entering KirbyText hooks when rendering callout content; ensure callout bodies still pass through KirbyText exactly once, either by rendering without hooks or by moving the transform later in the pipeline.

-   Sanitize or escape `classPrefix` before inserting into HTML class attributes to prevent malformed markup or unintended attributes in rendered callouts.
