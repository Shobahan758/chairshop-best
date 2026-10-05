---
paths:
  - app/Http/Controllers/Admin/ProductController.php
  - 'app/Http/Controllers/Admin/**'
---

# Admin

## Preserve product images until replacements are saved
Store all replacement images and save product attributes before deleting old image files. If any upload or product save fails, delete only the newly uploaded files and retain the original images. Apply cleanup to product creation as well to avoid orphan uploads.

## Preserve product associations when editing catalog records
Brands and subcategories with products cannot be deleted; move or remove their products first. A subcategory with products cannot change parent category because Product.category_id must remain consistent with its subcategory's category_id. Unused subcategories may move freely.
