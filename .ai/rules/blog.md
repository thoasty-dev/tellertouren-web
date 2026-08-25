---
paths:
  - 'resources/blog/**'
---

# Blog

## Static Blogframe content is authoritative
Blog articles and their media live under resources/blog and are the runtime source of truth. A null published_at means draft and must be excluded from every public lookup. Do not query legacy article, translation, tag, picture, or media tables.
