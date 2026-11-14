# Play Console Upload Runbook — APS Dream Home Android App

Everything below is verified locally except where marked **OWNER**. Nothing here invents
credentials, hosts, or fingerprints — fill the marked `<...>` values yourself.

## Verified local state (2026-11-14)

| Item | Value |
|---|---|
| AAB to upload | `public/downloads/apsdreamhome-v1.2.10-release.aab` (83.2 MB, 2026-11-10) |
| App version | `pubspec.yaml` → `1.2.10+13` |
| Application ID (`namespace` / `applicationId`) | `com.apsdreamhomes.mobileapp` |
| Upload-key config | `mobile/apsdreamhome_app_v2/android/key.properties` present (release signature) |
| Site-downloadable APK | `public/downloads/apsdreamhome-v1.2.10-release.apk` (90.3 MB) |
| Custom scheme (existing) | `apsdreamhome://` (already in AndroidManifest intent-filter) |
| HTTPS app links (missing) | no `autoVerify="true"` intent-filter yet — see step 4 |

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

## 4. HTTPS deep links — assetlinks + manifest patch (OWNER)

Without this, `apsdreamhome://` still works but opening `https://<production-host>/...` in Chrome will NOT open the app.

**4a. Serve the file** at `https://<production-host>/.well-known/assetlinks.json` on the **same host** that the intent-filter claims. If the site is at a subpath (e.g. `/apsdreamhome/`), the file must still be at the domain root. Content:

```json
[
  {
    "relation": ["delegate_permission/common.handle_all_urls"],
    "target": {
      "namespace": "android_app",
      "package_name": "com.apsdreamhomes.mobileapp",
      "sha256_cert_fingerprints": [
        "<SHA-256 from step 3, colon-free form, e.g. AB:CD:EF:...>"
      ]
    }
  }
]
```

The SHA-256 list may contain multiple fingerprints (upload key + Play App Signing key — Play shows both; adding both is the safest).

**4b. Add the HTTPS intent-filter** in `mobile/apsdreamhome_app_v2/android/app/src/main/AndroidManifest.xml` inside the launcher `<activity>`, next to the existing `apsdreamhome://` filter:

```xml
<intent-filter android:autoVerify="true">
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data android:scheme="https"
          android:host="<production-host>"
          android:pathPrefix="/<site-path, e.g. apsdreamhome/>" />
</intent-filter>
```

Rules: `host` must exactly match the domain serving assetlinks.json; `pathPrefix` must be a real path of the site (here it is `/apsdreamhome/`, since the app is deployed as `https://<host>/apsdreamhome/`). `autoVerify` requires both sides to match — a typo means the link silently doesn't open the app (no crash anywhere; Play just shows "verification failed").

**4c. Rebuild + redeploy (OWNER)**: bump `pubspec.yaml` → rebuild AAB → upload (step 1) → commit `AndroidManifest.xml` change in this repo.

**4d. Verify** (OWNER): open the production URL in Chrome on a device → tap a property link → the app should open to the property page (routing must be handled for that URL shape; the app currently has an `apsdreamhome://` handler and a generic route fallback).

## 5. Order of operations (do not reorder)

1. Step 1 (upload 1.2.10+13) — get Play green first.
2. Step 3 (copy the SHA-256).
3. Step 4 (publish assetlinks + manifest patch) — only after the Play upload succeeds, because the upload key is confirmed only then.
4. Bump version, rebuild, re-upload (autoVerify changes require a new AAB).

## What to send back here if stuck
- The exact Play error text (it almost always names the problem).
- Which host/path the live site serves (`https://<host>/apsdreamhome/`) — then the manifest patch + `assetlinks.json` can be written for you in one shot.
