# APS Dream Home – Documentation Hub

This directory is the landing zone for all living documentation that supports the APS Dream Home platform. Use it alongside the root `README.md` for a complete view of system architecture, operations, and roadmap.

## Structure & Ownership

| Area | Primary Docs | Status |
| ---- | ------------ | ------ |
| **Architecture** | [architecture/README.md](architecture/README.md) | ✅ Active – update with ongoing structural changes. |
| **Database** | [database/README.md](database/README.md), [database/er-diagram.md](database/er-diagram.md) | ⚠️ Review required to ensure alignment with current schema. |
| **API** | [api/reference.md](api/reference.md), [API_DEVELOPER_GUIDE.md](API_DEVELOPER_GUIDE.md) | ✅ Active – consolidate duplicate authentication guides into the reference. |
| **Frontend & Build** | [frontend.md](frontend.md) | ✅ Consolidated; archived copies in [archive/README_ENHANCED.md](archive/README_ENHANCED.md) & [archive/ADVANCED_FEATURES_GUIDE.md](archive/ADVANCED_FEATURES_GUIDE.md). |
| **Deployment & Ops** | [deployment/README.md](deployment/README.md) | ✅ Consolidated; legacy guides archived in `docs/archive/`. |
| **Operations Playbooks** | [operations/README.md](operations/README.md) | ✅ CRM, associate, colonizer, security, and performance runbooks. |
| **User/Admin Guides** | [2-user-guides/](2-user-guides/) | ✅ Active – ensure `ADMIN_USER_GUIDE.md` is linked here. |
| **Contributing** | [CONTRIBUTING.md](CONTRIBUTING.md) | ✅ Mirrors root contributing guide. |
| **FAQ** | [FAQ.md](FAQ.md) | 🔄 Update after documentation consolidation. |
| **Historical Reports** | [archive/README.md](archive/README.md) *(new)* | 🗂️ Stage milestone summaries slated for archival. |

> Keep one authoritative document per topic. If you create a new guide, add it to the table above and cross-reference it from the root `README.md` if appropriate.

## Migration Checklist

1. **Frontend** – Confirm consumers use `docs/frontend.md`; archive copies live in `docs/archive/README_ENHANCED.md` and `docs/archive/ADVANCED_FEATURES_GUIDE.md`.
2. **Deployment** – ✅ Combined into `docs/deployment/README.md`; archive log maintained in `docs/archive/README.md`.
3. **Historical Archives** – List redundant `_COMPLETE`, `_STATUS`, `_SUMMARY` files in `docs/archive/README.md` before relocating them.
4. **Module Guides** – Extract actionable configuration from `APS_CRM_COMPLETE.md`, `ASSOCIATE_SYSTEM_SUMMARY.md`, etc., into their respective sections.

## Quick Links

- [Setup & Deployment](deployment/README.md)
- [API Reference](api/reference.md)
- [Frontend Build Guide](frontend.md)
- [Operations Runbooks](operations/README.md)
- [Documentation Archive Plan](archive/README.md)
