# Performance Operations Playbook

Living guide for maintaining APS Dream Home's performance posture. Derived from `docs/PERFORMANCE_MODULE.md` and related optimization work to translate capabilities into day-to-day operations.

## Roles & Responsibilities

| Role | Responsibilities |
| ---- | ---------------- |
| Performance Lead | Owns performance roadmap, approves optimization initiatives, reports key metrics to leadership. |
| Backend Engineering | Implements caching/database optimizations, maintains performance libraries, profiles code hotspots. |
| DevOps / Infrastructure | Manages PHP runtime tuning (OPcache), server resources, scaling strategies, and monitoring stack. |
| QA / Performance Testing | Runs load/performance tests pre-release, validates cache effectiveness, tracks regressions. |
| Analytics & Data | Monitors behavioral KPIs, evaluates impact of predictive caching and user experience metrics. |

## Core Components

- **Caching Platform**: Multi-driver support (memory, file, Redis-ready), automatic key generation, dependency-based invalidation, configurable TTLs.
- **Database Optimization**: Prepared statements, query result caching, indexing guidance, slow query detection, connection pooling.
- **PHP Runtime**: OPcache configuration, adaptive runtime settings, memory management, profiling hooks.
- **Monitoring & Logging**: Response time, memory, CPU, cache hit/miss, slow query logs.
- **Scalability**: Stateless design, distributed caching, load balancing readiness, microservice compatibility roadmap.

## Daily / Weekly Checklist

| Cadence | Task | Owner |
| ------- | ---- | ----- |
| Daily | Review performance dashboards for latency spikes and cache hit ratios. | Performance Lead / DevOps |
| Daily | Inspect slow query logs produced by `$performance_log_config`. | Backend Engineering |
| Weekly | Run load/regression benchmarks; compare against baseline (response time, throughput). | QA / Performance |
| Weekly | Validate cache invalidation routines after major data changes. | Backend Engineering |
| Weekly | Review resource utilization (CPU/memory) and adjust OPcache/runtime settings if needed. | DevOps |

## Caching Operations

1. Ensure cache configuration (`$cache_config`) matches deployment environment (memory vs file vs Redis).
2. Review TTL tiers (short/medium/long); adjust for seasonal campaigns or heavy traffic events.
3. Monitor cache hit/miss rates; investigate sustained misses (>20%) for hot keys.
4. Confirm dependency-based invalidation hooks cover recent domain changes (e.g., property updates, lead ingestion).

## Database Performance Management

- **Profiling**: Enable slow query logging with threshold ≤ 0.5s; feed results into optimization backlog.
- **Indexing**: Quarterly review of new/changed tables for index opportunities (coordinate with database team).
- **Batching**: Encourage bulk operations for intensive tasks (imports, report generation) to minimize per-row overhead.
- **Connection Limits**: Monitor connection pool usage (`max_connections`); adjust based on scaling events.

## PHP Runtime & OPcache

- Maintain OPcache settings (memory 128 MB, max accelerated files 4000) and revalidate frequency (60s) per production sizing.
- After deployments, verify OPcache resets and that `fast_shutdown` remains enabled.
- Monitor `opcache_get_status()` metrics for hit rate (<95% triggers investigation).
- Tune `memory_limit`, `max_execution_time`, and similar PHP INI values based on profiling feedback.

## Monitoring Stack

- Logging config:

  ```php
  $performance_log_config = [
      'enabled' => true,
      'threshold' => 'warning',
      'channels' => ['performance', 'database'],
      'log_slow_queries' => true
  ];
  ```

- Ensure logs feed into centralized monitoring (e.g., ELK, Grafana). Set alerts for:
  - Average response time > target (e.g., 500ms web, 300ms API).
  - Cache hit rate <80% on primary cache.
  - Slow query count spikes (>20/hour).
  - OPcache hit rate <95% or memory usage >90%.

## Scaling & Capacity Planning

- Review scaling roadmap quarterly: distributed caching, load balancers, readiness for containerization/microservices.
- Conduct load testing before major feature launches; document baseline throughput and saturation point.
- Maintain stateless session strategy to support horizontal scaling; coordinate with security for secure cache design.

## Advanced Initiatives

- **Predictive Caching**: Coordinate with analytics to evaluate machine learning-based cache warming (pilot in staging first).
- **Adaptive Tuning**: Utilize environment-based overrides for peak hours (e.g., increase cache TTL during high load).
- **Future Enhancements**: Track roadmap items (Redis enhancements, performance dashboard, cloud-native features) and schedule research spikes.

## Incident Response

| Incident | Immediate Actions | Follow-up |
| -------- | ---------------- | --------- |
| Latency spike | Check cache health, slow queries, and recent deployments; roll back if needed. | Root-cause analysis, add guardrails. |
| Cache stampede | Implement locking/batching for hot keys, adjust TTLs. | Improve cache invalidation strategy, add request coalescing. |
| Database saturation | Scale read replicas/connection pools, throttle heavy jobs. | Optimize offending queries, re-index tables. |
| OPcache exhaustion | Increase memory, purge stale scripts, review deployment process. | Adjust configuration, add monitoring thresholds. |
| Load test regression | Halt release, compare metrics vs baseline, identify regressions. | Patch performance issues, update tests. |

## Metrics & Targets

| Metric | Target | Source |
| ------ | ------ | ------ |
| P95 web response time | ≤ 500 ms | APM / monitoring dashboards |
| P95 API response time | ≤ 300 ms | API monitoring |
| Cache hit ratio | ≥ 80% | Cache metrics |
| Slow query rate | < 2 per hour | Database logs |
| OPcache hit rate | ≥ 95% | PHP status |
| Throughput regression | 0% drop vs baseline | Load test suite |

## References

- Source module: `docs/PERFORMANCE_MODULE.md`
- Supporting docs: `docs/database/README.md`, `docs/operations/security.md`
- Monitoring integration: coordinate with DevOps dashboard tooling
- Roadmap alignment: maintain updates in `docs/roadmap.md`
