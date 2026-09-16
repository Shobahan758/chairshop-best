---
paths:
  - 'resources/views/**'
---

# Views

## Editable storefront content
Non-product storefront copy is defined in config/site_content.php, rendered through the SiteContent service, and edited in Admin Settings > Site Settings. Add new editable copy to the page/section catalog and use escaped Blade output; keep product data in product management and operational values such as order statuses out of the content editor.
