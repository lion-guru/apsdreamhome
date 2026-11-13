package com.apsdreamhomes.mobileapp

import android.content.Intent
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

/**
 * MainActivity - Flutter host with deep-link plumbing.
 *
 * The APS Dream Home ecosystem shares links such as:
 *   apsdreamhome://property/123
 *   apsdreamhome://colony/3
 *   apsdreamhome://offers?promo=FESTIVE21
 *
 * The launch intent (cold start) and every onNewIntent (warm tap) are handed
 * to Dart over the method channel "apsdreamhome/deeplinks":
 *   getInitialLink -> String?  (Dart pulls once on startup)
 *   onLink         -> event    (Dart handler fires on warm taps)
 */
class MainActivity: FlutterActivity() {
    private val channelName = "apsdreamhome/deeplinks"

    private var channel: MethodChannel? = null
    private var pendingLink: String? = null

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)

        pendingLink = intent?.dataString

        val ch = MethodChannel(flutterEngine.dartExecutor.binaryMessenger, channelName)
        ch.setMethodCallHandler { call, result ->
            when (call.method) {
                "getInitialLink" -> {
                    result.success(pendingLink)
                    pendingLink = null
                }
                else -> result.notImplemented()
            }
        }
        channel = ch
        // Cold start: Dart pulls the launch link via getInitialLink.
        // Warm taps: onNewIntent below pushes onLink events.
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        val link = intent.dataString ?: return
        pendingLink = link
        val ch = channel
        if (ch != null) {
            android.os.Handler(mainLooper).post {
                ch.invokeMethod("onLink", link)
            }
        }
    }
}
