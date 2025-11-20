# OpenMemory Guide

## Overview
- **Project:** APS Dream Home web platform combining legacy flat-PHP and modern MVC modules.
- **Domain:** Real estate MLM operations covering associate engagement, commissions, payouts, and admin analytics.
- **Tech Stack:** PHP 7.x custom MVC, MySQL, Bootstrap 5, Fetch/jQuery for AJAX interactions.
- **Active Focus:** MLM Engagement Dashboard (goals, notifications, leaderboards).

## Architecture
- **Entry Points:** Legacy admin templates under `admin/`; modern routes via `app/core/routes.php` hitting controllers in `app/controllers`.
- **Routing:** `Route::get/post` definitions dispatch to `AdminEngagementController` for engagement APIs.
- **Services Layer:** `EngagementService` handles data queries and business logic for metrics/goals/notifications; other services (CommissionService, PayoutService) support parallel modules.
- **Data Storage:** Key tables include `mlm_goals`, `mlm_goal_progress`, `mlm_goal_events`, `mlm_notification_feed`, `mlm_notification_log`.
- **Frontend Views:** Admin pages in `app/views/admin/` render Bootstrap cards/tables with embedded JS modules.

## User Defined Namespaces
- *(None defined)*

## Components
- **Engagement Service (`app/services/EngagementService.php`):** Provides goal CRUD, progress tracking, notification feeds, and event logging helpers.
- **Admin Engagement Controller (`app/controllers/AdminEngagementController.php`):** Exposes JSON endpoints for metrics, goals, progress, and notifications; enforces admin session.
- **MLM Engagement View (`app/views/admin/mlm_engagement.php`):** Dashboard UI with filters, goal management modals, timeline, and notification feed.
- **Notification Service (`app/services/NotificationService.php`):** Centralizes outbound email logging and delivery status for MLM notifications.

## Patterns
- **AJAX Fetch:** Forms submit via `fetch` with `FormData`, handling JSON responses and updating UI state without reloads.
- **Goal Timeline:** Progress checkpoints and events rendered into Bootstrap list groups; events logged through `logGoalEvent` helper.
- **Selection Persistence:** Goal table caching maintains highlighted row across reloads for seamless edits/progress logging.
- **Notification Actions:** Dashboard buttons call `/admin/engagement/notifications/mark-read` and `/mark-all-read` endpoints, disable controls during requests, and refresh feed state on completion.

## Roadmap Priorities
- **Phase 1 – Trust & Foundations:** Refresh transparency hub with RERA/registration details, expose live project availability, and standardize amenity/location storytelling on public pages.
- **Phase 2 – Experience & Engagement:** Deliver reusable project microsite templates, streamline enquiry-to-visit workflows, and enhance customer dashboard with favorites/comparison plus notification center.
- **Phase 3 – Differentiators & Scale:** Launch analytics/comparison tooling, expand omnichannel communications (WhatsApp/email/social proof), and experiment with advanced media such as 360° tours and AI recommendations.
- **Platform Health:** Continue security hardening, performance tuning, automated testing/CI rollout, migration to PDO, and documentation hygiene under `/docs`.
