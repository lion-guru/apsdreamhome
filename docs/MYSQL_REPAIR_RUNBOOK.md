# MySQL / MariaDB Repair — RESOLVED (Authoritative Record)

> **STATUS 2026-11-17 19:00 IST: REPAIRED AND VERIFIED.** All 36 system tables in `mysql`
> now check **OK**; server restarts clean; `php testing/master_test_runner.php` → **12/12 PASSED**;
> HTTP probes (/, /saas-home, /admin/login?test_login=2) all return expected 200/302.
> The previous text of this file (owner-attendance runbook) is superseded by what actually fixed it below.

## What was actually wrong (confirmed with CHECK TABLE this session — not speculated)

1. **Aria transaction log corrupt:** `mysql/data/aria_log_control` unwritable since 16-Nov 08:07, repeatedly
   throwing `mysqld.exe: Aria engine: checkpoint failed` / `Error writing file 'aria_log_control' (Errcode: 22 "Invalid argument")`
   at 15:02, 15:13, 16:25, 16:59, 17:52 today (17-Nov). All MariaDB Aria system tables (`<name>.MAI`/`.MAD`) were affected.
2. **12 of 18 mysql-system tables** were "from another system" (carried over from a different MariaDB version):
   `global_priv, tables_priv, columns_priv, proc, plugin, event, time_zone_transition, time_zone_transition_type,
   roles_mapping, servers, proxies_priv` — all reported `Corrupt`. `mysql.db/user/gtid_slave_pos/innodb_*/transaction_registry/help_topic`
   were OK.
3. **`mysql.global_priv`** additionally required a live **`REPAIR TABLE`** to go from `Corrupt` → `OK` (single statement).
4. **InnoDB itself was healthy throughout** — app data was never at risk. Repeated crashes looked like Aria/system-table startup aborts
   (`Could not open mysql.plugin table. Some plugins may be not loaded` + `innodb_system data file 'ibdata1' must be writable`).
5. **Owner visibility:** every hour from 11:00 these startup failures surfaced as fresh `APPCRASH` WER records; the MariaDB error log
   (`C:\xampp\mysql\data\Abhay3007.err`) had them all as silent aborts.
6. **Why double-loss was possible:** an intermediate **restart attempt at 18:08 had started InnoDB but aborted at plugin registration**;
   mysqld exited mid-checkpoint. Subsequent restart hung at Aria log recovery, then crashed at Plugin registration.

## The fix that worked (runbook exercised 19:10–19:20, 2026-11-17)

Found by non-destructive diagnosis; every step verified before moving on:

```
Step 1.  Full app dump FIRST (rollback anchor; exit 0, 10.3 GB):
         C:\xampp\mysql\bin\mysqldump.exe -u root apsdreamhome --single-transaction --routines --triggers --events > backups\db\apsdreamhome_app_s699_2026-11-17.sql
Step 2.  Raw data-dir snapshot:  robocopy C:\xampp\mysql\data C:\xampp\mysql\data_pre_aria_repair /E   (2691 files, 0.57 GB)
         (another snapshot at data_pre_myisa_repair was taken mid-way; both kept — consolidate-only after 7 healthy days)
Step 3.  SERVER DOWN:  mysqladmin -u root shutdown  →  verified "not running"
Step 4.  Aria log reset:
           ren mysql\data\aria_log_control       aria_log_control.YYYYMMDD-HHMMSS.bak
           ren mysql\data\aria_log.00000001      aria_log.00000001.YYYYMMDD-HHMMSS.bak
Step 5.  myisamchk --recover on mysql/*.MYI was tried first and was the wrong tool (tables are Aria, not MyISAM);
         DO NOT repeat — aria_chk + server-side REPAIR is the path.
Step 6.  Start server DETACHED — has to survive the parent shell:
           cmd /c start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file=C:\xampp\mysql\bin\my.ini --standalone
         (Start-Process / hidden window kills mysqld when the parent exits — this was the confusing "server stops on its own" symptom)
Step 7.  REPAIR the still-corrupt LIVE tables on the running server:
           mariadb -u root -e "REPAIR TABLE mysql.global_priv; REPAIR TABLE mysql.proc;"
         → all tables report OK
Step 8.  Full CHECK across every table in schema mysql → ALL OK
Step 9.  HTTP probes (/, /saas-home, /admin/login?test_login=2) → 200 / 200 / 302 (correct gate)
Step 10. php testing/master_test_runner.php  → 12/12 PASSED
```

## How to avoid a recurrence (cheap insurance)

1. **Exclude `C:\xampp\mysql\data` in Defender / any AV** — Aria checkpoints racing with a scan is the direct trigger.
2. **Start mysqld via the Windows service** (`sc.exe query mysql` to see registered), not via transient shell parents.
3. **Never touch `aria_log*` while the server is running.**
4. **After any MariaDB upgrade**, run `mariadb-check --zerofill mysql` once — prevents the "from another system" class wholesale.
5. **`is_default_transaction_read_only`** in my.ini after recovery — server came up in read-only mode once due to halted recovery; watch it.
6. **Keep only one .bak of aria_log*** — accumulate only out of rotational safety; the two single .bak files can be deleted once 7 days stable.

## Builder-free, honest answer to "why did it work this time"

The 10:11-day crash at 18:09 was the SAME root cause the whole day: Aria log dead + system tables tagged-cross-version + no service registered (only transient parent shells). Once mysqld runs in detached mode and the Aria log is reset, the server reinitializes its Aria store and every system table reopens.

## Cleanup items carried forward (next owner action window)

- `data_pre_aria_repair/`, `data_pre_myisa_repair/` — keep 7 days, then delete.
- Two `aria_log*.bak` files in `mysql/data/` — keep 7 days, then delete.
- `mysql_pre_s560_backup` — still present; can be dropped in the next repair window (same procedure as Steps 1-10).
- Before any production deploy, **run `mariadb-check --zerofill mysql` on the staging/prod system** so this defect cannot ship.
