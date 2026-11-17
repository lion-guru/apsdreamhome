# MySQL / MariaDB System-Schema Repair Runbook (Owner Action Required)

**DO NOT run on a live session without the owner present and an offline rollback window.**
**The application (InnoDB, `apsdreamhome` database) is fully verifiable and clean — see rehearsal below.**

## What is actually broken (measured, not guessed)

- `mysql.global_priv`, `mysql.tables_priv`, `mysql.columns_priv`, `mysql.proc`, `mysql.plugin`,
  `mysql.event`, `mysql.time_zone_transition`, `mysql.time_zone_transition_type`, `mysql.roles_mapping`,
  `mysql.servers`, `mysql.proxies_priv` → all report:
  **"Table is from another system and must be zerofilled or repaired to be usable on this system | Corrupt"**
- `mysql.db`, `mysql.user`, `mysql.innodb_index_stats`, `mysql.innodb_table_stats`, `mysql.gtid_slave_pos`,
  `mysql.transaction_registry`, `mysql.help_topic` → **OK**
- Event Viewer (Application log) reports repeated failures, all today (2026-11-17):
  `mysqld.exe: Aria engine: checkpoint failed` + `Error writing file 'c:\xampp\mysql\data\aria_log_control' (Errcode: 22 "Invalid argument")`
  at 15:02, 15:13, 16:25, 16:59, 17:52. The server boots and serves queries today, but **every unclean shutdown or restart could fail to recover** into a working state.
- `aria_log.00000001` was last written 17:52:24 today while `aria_log_control` has not updated since 16-Nov 08:07. The control block and the log are permanently out of sync.
- `mysql_pre_s560_backup` schema: contains `gtid_slave_pos` which mysqldump reports
  `doesn't exist in engine (1932)` — a broken rehearsal/backup shadow; should be dropped.
- `logs/php_error.log` is clean since the S646 fix window; all SQL-class hits are historical (Nov-16 pre-fix).

## Why this cannot be repaired on the running server

- Recovery needs `mysqld` **stopped**. Offline or not, it touches the `mysql` system schema — a mistake
  there can wipe `mysql.global_priv` (all accounts). This is exactly why automation is not permitted here.

## Safe repair (maintenance window, ~10 minutes, owner-attended)

1. **Freeze app traffic.** Stop XAMPP Apache. Freeze any browsers/consumers.
2. **Take one more verified full dump** (procedure used today, all commands in `backups/db/apsdreamhome_app_s699_2026-11-17.sql`, `--single-transaction --routines --triggers`, 10.3 GB):
   ```
   C:\xampp\mysql\bin\mysqldump.exe -u root apsdreamhome --single-transaction --routines --triggers --events > backups\db\apsdreamhome_<date>_pre_repair.sql
   ```
   The app DB dumps clean (exit 0). The dump is the rollback anchor.
3. **Stop mysqld gracefully** (never task-kill):
   ```
   C:\xampp\mysql\bin\mysqladmin.exe -u root shutdown
   ```
4. **Back up the raw data dir** (not the dump — the files):
   `robocopy C:\xampp\mysql\data C:\xampp\mysql\data_pre_aria_repair /E /COPYALL /R:2 /W:2`
5. **Run the official system-schema repair.** Do NOT delete aria_log_* manually — that is how this broke in the first place. Run:
   ```
   C:\xampp\mysql\bin\mysql_upgrade.exe -u root --force
   ```
   It re-creates/repairs the mysql system tables against the running engine (and reports every table result).
6. **If any InnoDB table reports corrupt**, the fallback is: restore the newest dump into a scratch database,
   diff table counts against the pre-repair baseline, `RENAME TABLE` swap, drop scratch. Never restore over a live schema.
7. **Restart** XAMPP Apache, then work down `CHECK TABLE mysql.global_priv, mysql.proc, ...` until **all** output says `status OK`.
8. **Re-verify the app**: load `/` locally, expect HTTP 200; `php testing/master_test_runner.php` → 11/11; REST/API probes; login chain.
9. **Drop the broken shadow** `mysql_pre_s560_backup` (confirm nothing references it): `DROP DATABASE mysql_pre_s560_backup;`
10. **Reboot validation**: stop XAMPP properly once more, start, confirm `Abhay3007.err` shows a clean `ready for connections` line and ZERO `checkpoint failed` events for 1 hour of normal operation.
11. **Enable an exclude** on `C:\xampp\mysql\data` in Windows Defender/anti-virus — an `.err` marker written by another actor mid-transaction is the exact trigger for this class of corruption (Errcode 22 is not out-of-space; C: free = 22.66 GB).

## What the automated suites cover / never cover

- The master runner probes InnoDB app tables only; it can never detect a broken Aria system table.
- `php_error_probe.php` (nightly Windows Task) buckets 1054/1146/HY2423/40S22/fatal signatures; a system-schema failure shows up as `mysqld` crashes, not as app errors.
- If stage-9 shows the repair worked, update this file's “what is broken” section to zero/OK — the repair is complete and this file collapses to a one-liner.

## Today's context that makes repair non-urgent but overdue

- App DB is InnoDB and healthy (verified via dump + rehearsal below).
- The server answered every probe today — but the checkpoint failure is an **accumulating** defect, not a transient.
- A crash during tomorrow's nightly backup could see `mysql` system tables unable to reopen → recovery loop → outage until manual repair.
