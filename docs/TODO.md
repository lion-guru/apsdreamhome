# Solo Mode TODO (Execution Plan)

- Immediate (Today–2 days)
  - [ ] Unify entrypoint via `index.php` front controller; confirm `.htaccess` rewrite to index
  - [ ] Delegate non‑MVC routes to `router.php` auto‑routing (`router.php:254`)
  - [ ] Normalize `FrontRouter` fallbacks (`index.php:112`)
  - [ ] Extract shared layout/header/footer partials; point top pages to shared templates
  - [ ] Verify env secrets, CSP in `config/security.php`, logs path to `storage/logs/php_errors.log`
  - [ ] Schedule Agent daily run via Windows Task Scheduler (`tools/run_agent.php`)
  - [ ] Wire Agent metrics (leads funnel, inventory velocity, top sources)

- Week 1
  - [ ] Migrate high‑traffic pages to unified router
  - [ ] Consolidate assets; remove duplicates
  - [ ] Add admin Agent dashboard (tasks, last run, status) in `admin/ai_agent.php`

- Week 2
  - [ ] Implement services for leads/properties/projects/users under `app/core/Services`
  - [ ] Add reporting views or indexed queries for Agent and dashboards
  - [ ] Add health check endpoint and DB ping

- Week 3
  - [ ] Optimize images and defer non‑critical JS
  - [ ] Standardize responsive components for property cards and grids
  - [ ] Log rotation and alerting on agent failure

- Week 4
  - [ ] Add smoke tests (routing, DB connectivity)
  - [ ] Add unit tests for services; integration test for lead submission
  - [ ] Setup CI to run lint and tests
  - [ ] Consolidate duplicated MD docs; keep core docs

- Acceptance Criteria
  - [ ] Single entrypoint; legacy direct hits deprecated
  - [ ] Shared templates used by top pages
  - [ ] Agent runs daily, produces reports and tasks
  - [ ] Basic tests passing; CI green
  - [ ] Error rate reduced and business KPIs improved
