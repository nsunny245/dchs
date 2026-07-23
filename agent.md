# Daniyal Group of Colleges Project

Before making changes to admissions, students, fees, vouchers, documents, dashboard branding, or generated PDFs, read:

`/docs/DANIYAL_GROUP_OF_COLLEGES_CODEX_CONTEXT.md`

The official shared institutional identity is Daniyal Group of Colleges.

Use:
- Official Daniyal Group of Colleges logo
- Tagline: “Where Success Is a Tradition”
- Navy and gold design system defined in the context file

Do not use the Daniyal College of Health Sciences logo on shared group-level screens or documents.

Important implementation rules:
- Inspect the existing Laravel 12 code before modifying architecture.
- Preserve existing data and relationships.
- Do not use destructive migrations.
- Keep business logic outside Blade templates.
- Use services/actions for fee calculations, installments, vouchers, and final admission submission.
- Use database transactions for final admission creation.
- Enforce fee and concession permissions using Laravel Policies.
- Add tests for admission drafts, calculations, permissions, vouchers, and final submission.