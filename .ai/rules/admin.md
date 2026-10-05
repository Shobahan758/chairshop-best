---
paths:
  - app/Http/Controllers/Admin/ProductController.php
---

# Admin

## Preserve product images until replacements are saved
Store all replacement images and save product attributes before deleting old image files. If any upload or product save fails, delete only the newly uploaded files and retain the original images. Apply cleanup to product creation as well to avoid orphan uploads.
