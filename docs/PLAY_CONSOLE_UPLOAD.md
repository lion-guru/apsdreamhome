# Play Console Upload Runbook — APS Dream Home Android App

Everything below is verified locally except where marked **OWNER**. Nothing here invents
credentials, hosts, or fingerprints — fill the marked `<...>` values yourself.

## Verified local state (2026-11-14)

| Item | Value |
|---|---|
| AAB to upload | `public/downloads/apsdreamhome-v1.2.11-release.aab` (**built 2026-11-14 from the autoVerify-ready tree, 84.0 MB**, signature verified against `android/key.jks` - SHA-256 `0E:58:…:0D:BE`) |
| Previous AAB | `public/downloads/apsdreamhome-v1.2.10-release.aab` (83.2 MB, 2026-11-10) |
| App version | `pubspec.yaml` → `1.2.11+14` |
| Application ID (`namespace` / `applicationId`) | `com.apsdreamhomes.mobileapp` |
| Upload-key config | `mobile/apsdreamhome_app_v2/android/key.properties` present (release signature) |
| Upload-key SHA-256 | `0E:58:5F:24:37:07:C1:D5:23:64:77:07:C2:F5:0F:6F:BC:71:96:0D:06:61:9A:6C:39:B4:E9:8E:28:B3:0D:BE` |
| Upload-key SHA-1 | `34:71:7B:EF:60:B8:65:0F:44:93:CB:D0:34:47:B7:2E:86:B1:12:D5` |
| Site-downloadable APK | `public/downloads/apsdreamhome-v1.2.10-release.apk` (90.3 MB) |
| Custom scheme (existing) | `apsdreamhome://` (already in AndroidManifest intent-filter) |
| HTTPS app links | **`autoVerify="true"` HTTPS intent-filter ADDED 2026-11-14** (`host=apsdreamhome.com`, root path) — needs the NEW build to reach users |
| `public/.well-known/assetlinks.json` | **CREATED 2026-11-14** with the upload-key SHA-256 above — served at build/deploy of the live site |
| Production host (verified from `config/deployment.php`) | `https://apsdreamhome.com` at the **root** (NOT `/apsdreamhome/`) |

## 1. Upload the AAB (OWNER)

1. Play Console → your app → **Production** (or **Internal testing** for a safe first pass).
2. **Create new release** → upload `public/downloads/apsdreamhome-v1.2.10-release.aab`.
3. Release notes (sample): `Internal testing build 1.2.10+13. Bug fixes and performance improvements.`
4. Review and roll out. If Play reports "your app bundle is not signed with the upload key that was used for the previous version", stop and compare the `key.properties` store file against the previous release — do not force-upload a differently-signed AAB.
5. OTA/sideload APK for QA is fine as-is (`apsdreamhome-v1.2.10-release.apk`); the AAB is the Play artifact.

## 2. Version code hygiene

`pubspec.yaml` is already at `1.2.10+13`. Any future change → bump to `1.2.11+14` (or higher) before building another AAB; Play rejects reused version codes. `flutter build appbundle --release` must be re-run after every change to `android/` or `pubspec.yaml`.

## 3. Upload key SHA-256 (OWNER)

Play Console → the app → **Setup → App integrity** → "Upload key certificate" → copy **SHA-256 certificate fingerprint**.
This is what you paste in step 4; it never goes through this repo, so it cannot leak here.

## 4. HTTPS deep links — assetlinks + manifest patch (DONE LOCALLY, OWNER to finish)

`assetlinks.json` has been created at `public/.well-known/assetlinks.json` with the **upload-key
SHA-256** (`0E:58:...:0D:BE`), and the HTTPS autoVerify intent-filter has been added to the launcher
`<activity>` in `android/app/src/main/AndroidManifest.xml` with `host="apsdreamhome.com"` and
`pathPrefix="/"` (production serves at the **root**, not `/apsdreamhome/`).

Remaining **OWNER** steps before deep links verify end-to-end:

1. **Deploy the site** with `public/.well-known/assetlinks.json` served at
   `https://apsdreamhome.com/.well-known/assetlinks.json` (verify: open it in Chrome — must return the JSON, 200, `application/json`).
2. **Upload the rebuilt AAB** containing the new intent-filter (see §1/§5 — rebuild required because the manifest changed).
3. **Add the Play App Signing key SHA-256** to the assetlinks.json list. Play Console → App integrity → "Play App Signing certificate" → copy its SHA-256 → append it to the `sha256_cert_fingerprints` array (both fingerprints must be listed; the upload-key fingerprint alone only verifies direct APK installs, NOT Play-installed apps).
4. Verify on a real device: Chrome address bar on `https://apsdreamhome.com/project/<slug>` → "Open in app" / taps open the app at that page.

**Play App Signing key vs Upload key** — the APK you upload is signed with the upload key, but Google
**re-signs** Play releases with the Play App Signing key. Direct-APK users get the upload-key cert;
Play users get the app-signing cert. `assetlinks.json` needs BOTH fingerprints.

### Firebase / Google API keys embedded in the APK (informational, owner action)

The APK embeds `google-services.json`'s Firebase `api_key` (39 chars, ships in every Firebase-Android app) plus
a Maps meta-data with an `AIzaSy…` placeholder. Firebase API keys are not secrets, but they should be
**restricted**: Google Cloud Console → APIs & Services → Credentials → find the Firebase API key →
"API restrictions" → select only the APIs used (Firebase + Maps), and "Application restrictions" →
**Android apps** → add `com.apsdreamhomes.mobileapp` + SHA-1 `34:71:7B:EF:60:B8:65:0F:44:93:CB:D0:34:47:B7:2E:86:B1:12:D5`.
That makes the copied key useless to anyone but this app. Cannot be done from here (Cloud Console login).

## 5. Order of operations (do not reorder)

1. Step 1 (upload 1.2.10+13) — get Play green first.
2. Step 3 (copy the SHA-256).
3. Step 4 (publish assetlinks + manifest patch) — only after the Play upload succeeds, because the upload key is confirmed only then.
4. Bump version, rebuild, re-upload (autoVerify changes require a new AAB).

## Status (2026-11-14, this repo)

- ✅ Upload-key SHA-256 extracted from `android/key.jks` (alias `upload`) — `0E:58:5F:24:37:07:C1:D5:23:64:77:07:C2:F5:0F:6F:BC:71:96:0D:06:61:9A:6C:39:B4:E9:8E:28:B3:0D:BE` (SHA-1 also recorded).
- ✅ `public/.well-known/assetlinks.json` created with the upload-key fingerprint (200 OK locally at `/apsdreamhome/public/.well-known/assetlinks.json`; production serves it at `https://apsdreamhome.com/.well-known/assetlinks.json` — do NOT serve from the dev mountpoint `/apsdreamhome/.well-known/*`, that path is 403 by design (dotfile policy)).
- ✅ `android/app/src/main/AndroidManifest.xml` — HTTPS `autoVerify="true"` intent-filter added to the launcher activity (`host=apsdreamhome.com`, root `pathPrefix="/"`).
- ✅ `pubspec.yaml` bumped to **1.2.11+14**; **new AAB built and signature-verified** (upload key) → `public/downloads/apsdreamhome-v1.2.11-release.aab`.
- ✅ Dart router unblocked (parallel actor's broken unterminated-string import + `ListingPackagesPage()` missing required `propertyId` — fixed minimally: `/listing-packages/:propertyId`; `flutter analyze` clean, build green).
- ⏳ OWNER: deploy site (assetlinks.json live at domain root) + **upload AAB 1.2.11+14** via Play Console (steps in §1) + append Play App Signing key SHA-256 to assetlinks.json after the first upload (Play re-signs; upload key ≠ app signing key in the release channel).
- ⏳ OWNER: Google Cloud Console → restrict the Firebase API key (Android restriction + SHA-1) and confirm the Maps `AIzaSy` meta-data (currently a placeholder; if unused, remove it).
- ⏳ OWNER decision: `bohisawagar-banner.jpg` — no banner exists in `public/assets/images/projects/gorakhpur/bohisawagar-banner.jpg`; options are (a) drop the owner-provided JPG into that path (easiest), or (b) keep the current fallback (real Gorakhpur township photo) specified in the view — already in place, so nothing is visually broken.

## What to send back here if stuck
- The exact Play error text (it almost always names the problem).
- Which host/path the live site serves (`https://<host>/apsdreamhome/`) — then the manifest patch + `assetlinks.json` can be written for you in one shot.
