# Frontend Architecture

This project mirrors the same MVC-style frontend organization as the reference repository.

## Folder organization

```txt
app/ or admin/
├── index.html        # Controller and app orchestration
├── data/*.js         # Model layer (API calls only)
├── component/*       # View layer (template + style + formatting)
└── css/*             # Global styles
```

## Rules

- `index.html` coordinates events and rendering (`window.C`, `window.V`).
- `data/*.js` contains all `fetch` logic and server endpoint details.
- `component/*` never calls the API directly.
- Components return HTML strings from template placeholders.
