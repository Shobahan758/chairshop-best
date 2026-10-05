---
paths:
  - config/filesystems.php
---

# Config

## Keep private file URLs separate from public images
The local disk's signed file routes use /private-storage. Do not move them to /storage: Laravel registers those routes after application routes and would override the public-image fallback controller. Public uploads use /storage and remain accessible when a hosting provider cannot use the public/storage symlink.
