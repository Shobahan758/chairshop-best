---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Enforce storefront availability and order ownership
Cart add/update, checkout and product reviews must reject inactive products and inactive categories. Order-success and invoice pages must use CustomerAccounts::orders() to verify ownership; order IDs alone are not authorization. Fake orders are excluded from public success pages and regular admin status updates.
