<?php
$page_title = 'Marketing Toolkit - APS Dream Home';
$stats = $stats ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3><i class="fas fa-bullhorn me-2 text-primary"></i>Marketing Toolkit</h3>
        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Free for all team members</span>
    </div>

    <p class="text-muted mb-4">
        <i class="fas fa-info-circle me-1"></i>
        Photo par logo lagao, WhatsApp banner banao, AI se Hindi post likhwao — phir download karke WhatsApp / Instagram / Facebook par share karo.
        <strong>Koi paid API nahi, sab free.</strong>
    </p>

    <?php if (!empty($stats)): ?>
    <div class="row g-3 mb-4">
        <?php foreach ($stats as $s): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small"><?= ucfirst(htmlspecialchars($s['tool_type'])) ?></div>
                    <div class="h4 mb-0"><?= (int)$s['count'] ?> <small class="text-muted fs-6">uses</small></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Personal Branding (apna naam/number ek baar save karo, har post par auto) -->
    <div class="card border-0 shadow-sm mb-4" style="border-left:4px solid #FACC15 !important">
        <div class="card-body">
            <h5 class="mb-1"><i class="fas fa-id-card me-2 text-warning"></i>Apni Branding (ek baar save karo)</h5>
            <p class="text-muted small mb-3">Naam + number yahan save karo — collage, festival post, visiting card, slideshow me <strong>auto</strong> lag jayega. Lead direct tumko aayega.</p>
            <form id="brandingForm" class="row g-2">
                <div class="col-md-3"><input type="text" class="form-control" name="display_name" id="brandName" placeholder="Apna naam"></div>
                <div class="col-md-3"><input type="text" class="form-control" name="phone" id="brandPhone" placeholder="Mobile number"></div>
                <div class="col-md-4"><input type="text" class="form-control" name="tagline" id="brandTagline" placeholder="Tagline (e.g. Associate | Gorakhpur)"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Save</button></div>
            </form>
            <div id="brandingResult" class="mt-2"></div>
        </div>
    </div>

    <!-- AI Provider Settings (AI provider choose karo + API key) -->
    <div class="card border-0 shadow-sm mb-4" style="border-left:4px solid #6366F1 !important">
        <div class="card-body">
            <h5 class="mb-1"><i class="fas fa-robot me-2 text-primary"></i>AI Provider Settings</h5>
            <p class="text-muted small mb-3">Apna preferred AI provider choose karo. API key dalo (optional - Ollama free hai). Ye setting AI Post Writer, Video Caption, Morning Post me kaam aayegi.</            <form id="aiProviderForm" class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">AI Provider</label>
                    <select class="form-select" name="ai_provider" id="aiProviderSelect">
                        <option value="ollama">Ollama (Local, Free, Unlimited)</option>
                        <option value="groq">Groq (Fastest, Free tier: 30 RPM)</option>
                        <option value="xai_grok">xAI Grok (Free tier via xAI API)</option>
                        <option value="gemini">Google Gemini (Free: 15 RPM, 1M tokens/day)</option>
                        <option value="deepseek">DeepSeek (Free tier via DeepSeek API)</option>
                        <option value="huggingface">HuggingFace (Free: 30k tokens/day)</option>
                        <option value="together_ai">Together.ai (Free: 100k tokens/day)</option>
                        <option value="deepseek">DeepSeek (Free tier via DeepSeek API)</option>
                        <option value="together">Together.ai (Free: 100k tokens/day)</option>
                        <option value="cohere">Cohere (Free: 100 calls/min)</option>
                        <option value="openrouter">OpenRouter (Free models, last resort)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">API Key (Optional - Ollama ke liye nahi chahiye)</label>
                    <input type="password" class="form-control" name="ai_api_key" id="aiApiKey" placeholder="API Key (Groq, Gemini, Grok, etc.)">
                    <small class="text-muted">Ollama local chal raha ho to khali chhodo</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Model (Optional)</label>
                    <input type="text" class="form-control" name="ai_model" id="aiModel" placeholder="Model name (khali chhodo = default)">
                    <small class="text-muted">Khali chhodo to default model use hoga</small>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-brain me-1"></i>Save AI Settings</button>
                </div>
            </form>
            <div id="aiProviderResult" class="mt-2"></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- 1. Watermark Tool -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-stamp me-2 text-primary"></i>1. Photo Watermark</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Property photo par company logo lagao (bottom-right corner).</p>
                    <form id="watermarkForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Photo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="photo" accept="image/*" required>
                            <small class="text-muted">JPG/PNG/GIF/WebP, max 10MB</small>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Position</label>
                                <select class="form-select" name="position">
                                    <option value="bottom-right">Bottom Right</option>
                                    <option value="bottom-left">Bottom Left</option>
                                    <option value="top-right">Top Right</option>
                                    <option value="top-left">Top Left</option>
                                    <option value="center">Center</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Opacity</label>
                                <select class="form-select" name="opacity">
                                    <option value="80" selected>80%</option>
                                    <option value="60">60%</option>
                                    <option value="100">100%</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-magic me-2"></i>Add Logo
                        </button>
                    </form>
                    <div id="watermarkResult" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- 2. Banner Generator -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-image me-2 text-success"></i>2. WhatsApp Banner</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">1080×1920 banner — WhatsApp Status / Instagram Story size.</p>
                    <form id="bannerForm" enctype="multipart/form-data">
                        <div class="mb-2">
                            <label class="form-label">Photo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="photo" accept="image/*" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="price" placeholder="Price (e.g. ₹999/sq.ft.)">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="location" placeholder="Location (e.g. Suryoday, Kalesar)">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="offer" placeholder="Offer (e.g. 40% Cashback)">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="property_type" placeholder="Type (e.g. Residential Plot)">
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-wand-magic-sparkles me-2"></i>Generate Banner
                        </button>
                    </form>
                    <div id="bannerResult" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- 3. AI Content Writer -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-robot me-2 text-warning"></i>3. AI Post Writer</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Photo details do → AI Hindi marketing post likhega. <span class="badge bg-success">Free (local AI)</span></p>
                    <form id="aiForm">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="text" class="form-control" name="property_type" placeholder="Type (प्लॉट)" value="प्लॉट">
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control" name="size_sqft" placeholder="Size (1000)">
                            </div>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="price" placeholder="Price (₹999/sq.ft.)">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="location" placeholder="Location (गोरखपुर)" value="गोरखपुर">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="offer" placeholder="Offer (40% Cashback)">
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-pen-nib me-2"></i>Write Post
                        </button>
                    </form>
                    <div id="aiResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- V2 Tools Row -->
    <div class="row g-4 mt-1">
        <!-- 4. Sticker -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-tags me-2 text-danger"></i>4. Sticker / Badge</h5></div>
                <div class="card-body">
                    <p class="text-muted small">NEW LAUNCH, PRICE DROP, SOLD, OFFER ribbon lagao.</p>
                    <form id="stickerForm" enctype="multipart/form-data">
                        <div class="mb-2"><input type="file" class="form-control" name="photo" accept="image/*" required></div>
                        <div class="mb-2">
                            <select class="form-select" name="sticker_type">
                                <option value="new_launch">NEW LAUNCH</option>
                                <option value="price_drop">PRICE DROP</option>
                                <option value="offer">SPECIAL OFFER</option>
                                <option value="urgent">URGENT SALE</option>
                                <option value="sold">SOLD OUT</option>
                                <option value="verified">VERIFIED</option>
                            </select>
                        </div>
                        <div class="mb-3"><input type="text" class="form-control" name="custom_text" placeholder="Custom text (offer ke liye)"></div>
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-tag me-2"></i>Add Sticker</button>
                    </form>
                    <div id="stickerResult" class="mt-3"></div>
                </div>
            </div>
        </div>
        <!-- 5. Collage -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-th me-2 text-info"></i>5. Collage Maker</h5></div>
                <div class="card-body">
                    <p class="text-muted small">2-4 photos ek image me + apna naam/number auto.</p>
                    <form id="collageForm" enctype="multipart/form-data">
                        <div class="mb-2"><input type="file" class="form-control" name="photos[]" accept="image/*" multiple required></div>
                        <div class="mb-3">
                            <select class="form-select" name="layout">
                                <option value="grid2x2">2x2 Grid (4 photos)</option>
                                <option value="row2">Side by side (2 photos)</option>
                                <option value="row3">3 in a row</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-info w-100 text-white"><i class="fas fa-th-large me-2"></i>Make Collage</button>
                    </form>
                    <div id="collageResult" class="mt-3"></div>
                </div>
            </div>
        </div>
        <!-- 6. Festival -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-gifts me-2 text-success"></i>6. Festival Post</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Diwali/Holi/New Year greeting + apni branding auto.</p>
                    <form id="festivalForm">
                        <div class="mb-3">
                            <select class="form-select" name="festival">
                                <option value="diwali">Diwali 🪔</option>
                                <option value="holi">Holi 🎨</option>
                                <option value="newyear">New Year 🎉</option>
                                <option value="dussehra">Dussehra 🏹</option>
                                <option value="eid">Eid 🌙</option>
                                <option value="independence">Independence Day 🇮🇳</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-gift me-2"></i>Create Post</button>
                    </form>
                    <div id="festivalResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <!-- 7. QR Code -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-qrcode me-2 text-dark"></i>7. QR Code</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Apne referral link ka QR banao. Khali chhodo to auto (tumhara link).</p>
                    <form id="qrForm">
                        <div class="mb-3"><input type="text" class="form-control" name="text" placeholder="Link (khali = mera referral link)"></div>
                        <button type="submit" class="btn btn-dark w-100"><i class="fas fa-qrcode me-2"></i>Generate QR</button>
                    </form>
                    <div id="qrResult" class="mt-3"></div>
                    <hr>
                    <p class="text-muted small mb-2"><strong>Photo par QR lagao</strong> (scan → tumhara link)</p>
                    <form id="qrPhotoForm" enctype="multipart/form-data">
                        <div class="mb-2"><input type="file" class="form-control" name="photo" accept="image/*" required></div>
                        <button type="submit" class="btn btn-outline-dark w-100 btn-sm"><i class="fas fa-camera me-1"></i>Photo + QR</button>
                    </form>
                    <div id="qrPhotoResult" class="mt-2"></div>
                </div>
            </div>
        </div>
        <!-- 8. Slideshow GIF -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-film me-2 text-primary"></i>8. Slideshow GIF</h5></div>
                <div class="card-body">
                    <p class="text-muted small">2-5 photos → animated GIF (WhatsApp par video jaise chalega).</p>
                    <form id="slideshowForm" enctype="multipart/form-data">
                        <div class="mb-3"><input type="file" class="form-control" name="photos[]" accept="image/*" multiple required></div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-play me-2"></i>Make GIF</button>
                    </form>
                    <div id="slideshowResult" class="mt-3"></div>
                </div>
            </div>
        </div>
        <!-- 9. Visiting Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-id-card me-2 text-warning"></i>9. Visiting Card</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Digital card + referral QR. Branding se auto-bharega.</p>
                    <form id="vcardForm">
                        <div class="mb-2"><input type="text" class="form-control" name="display_name" placeholder="Naam (khali = saved)"></div>
                        <div class="mb-2"><input type="text" class="form-control" name="phone" placeholder="Number (khali = saved)"></div>
                        <div class="mb-3"><input type="text" class="form-control" name="tagline" placeholder="Tagline"></div>
                        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-id-badge me-2"></i>Generate Card</button>
                    </form>
                    <div id="vcardResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- V4 Tools Row -->
    <div class="row g-4 mt-1">
        <!-- 10. Video Pack -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-video me-2 text-danger"></i>10. Video Share Pack</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Apna video upload karo (Gemini/mobile se bana hua) → branded cover + caption milega → download karke post karo.</p>
                    <form id="videoForm" enctype="multipart/form-data">
                        <div class="mb-2"><input type="file" class="form-control" name="video" accept="video/*" required><small class="text-muted">mp4/mov, max 100MB</small></div>
                        <div class="mb-2"><input type="text" class="form-control" name="title" placeholder="Video title"></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><input type="text" class="form-control" name="price" placeholder="Price"></div>
                            <div class="col-6"><input type="text" class="form-control" name="location" placeholder="Location"></div>
                        </div>
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-clapperboard me-2"></i>Make Pack</button>
                    </form>
                    <div id="videoResult" class="mt-3"></div>
                </div>
            </div>
        </div>
        <!-- 11. Copy-Paste Text Templates -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-clipboard-list me-2 text-info"></i>11. Ready SMS Text</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Template chuno → tumhara naam/number auto → <strong>Copy</strong> karke SMS/WhatsApp me paste karo. Tumhare phone ke 300 free SMS kaam aayenge — <strong>koi TRAI block nahi</strong> (tum khud bhej rahe ho).</p>
                    <div class="mb-2">
                        <select class="form-select" id="textTplSelect">
                            <option value="new_launch">New Launch</option>
                            <option value="price_drop">Price Drop</option>
                            <option value="emi_reminder">EMI Offer</option>
                            <option value="site_visit">Site Visit Invite</option>
                            <option value="festival">Festival Wish</option>
                            <option value="followup">Follow-up Nudge</option>
                            <option value="sold_fomo">Sold FOMO</option>
                            <option value="referral">Refer & Earn</option>
                        </select>
                    </div>
                    <div class="mb-3"><input type="text" class="form-control" id="textTplPrice" placeholder="Price (optional)"></div>
                    <button class="btn btn-info w-100 text-white" onclick="fillTextTpl()"><i class="fas fa-fill-drip me-2"></i>Fill + Copy</button>
                    <div id="textTplResult" class="mt-3"></div>
                </div>
            </div>
        </div>
        <!-- 12. Morning Post -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border:2px solid #FACC15 !important">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-sun me-2 text-warning"></i>12. Aaj Ka Post (1-Click)</h5></div>
                <div class="card-body text-center">
                    <p class="text-muted small">Roz subah ek click → Good Morning post tumhari branding ke saath taiyaar → status lagao.</p>
                    <div class="display-6 mb-2">🌅</div>
                    <button class="btn btn-warning btn-lg w-100" onclick="morningPost()"><i class="fas fa-bolt me-2"></i>Aaj Ka Post Banao</button>
                    <div id="morningResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Share explainer -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h6><i class="fas fa-share-nodes me-2 text-primary"></i>Share kaise karein (non-technical ke liye)</h6>
            <div class="row g-2 mt-2">
                <div class="col-md-3"><a class="btn btn-success btn-sm w-100" href="#" onclick="return false"><i class="fab fa-whatsapp me-1"></i>WhatsApp: button dabao → chat chuno → bhejo</a></div>
                <div class="col-md-3"><a class="btn btn-outline-success btn-sm w-100" href="sms:?body=APS%20Dream%20Home"><i class="fas fa-sms me-1"></i>SMS: tumhare phone se jayega</a></div>
                <div class="col-md-3"><a class="btn btn-outline-dark btn-sm w-100" href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram me-1"></i>Instagram: download → app kholo → post</a></div>
                <div class="col-md-3"><a class="btn btn-outline-primary btn-sm w-100" href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook me-1"></i>Facebook: download → post</a></div>
            </div>
            <small class="text-muted d-block mt-2">SMS tumhare phone ke 300 roz free SMS se jayega — server se nahi, isliye TRAI block nahi karega. Instagram/FB me direct auto-post ke liye Meta approval chahiye (hafte lagte hain) — isliye download + manual post, 30 second ka kaam.</small>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        <strong><i class="fas fa-share-alt me-1"></i>WhatsApp / Instagram par kaise share karein?</strong>
        <ol class="mb-0 mt-2">
            <li><strong>WhatsApp:</strong> "WhatsApp Share" button dabao → chat select karo → bhejo. Koi API/login nahi chahiye.</li>
            <li><strong>Instagram/Facebook:</strong> Image download karo → app kholo → post/status me upload karo. Direct auto-post ke liye Meta approval + paid API chahiye (abhi mat karo).</li>
            <li><strong>Google Login:</strong> Website par login ke liye hai (pehle se bana hai). Posting se iska koi lena-dena nahi.</li>
        </ol>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    const base = '<?= $base ?>';

    function showResult(elId, data) {
        const el = document.getElementById(elId);
        if (!data.success) {
            el.innerHTML = '<div class="alert alert-danger">' + escapeHtml(data.message || 'Failed') + '</div>';
            return;
        }
        let html = '<div class="alert alert-success">Done! <a href="' + escapeHtml(data.url) + '" download class="btn btn-sm btn-success ms-2"><i class="fas fa-download me-1"></i>Download</a></div>';
        if (data.url) {
            html += '<img src="' + escapeHtml(data.url) + '" class="img-fluid rounded mb-2" style="max-height:300px">';
        }
        if (data.whatsapp_share) {
            html += '<a href="' + escapeHtml(data.whatsapp_share) + '" target="_blank" class="btn btn-success btn-sm w-100"><i class="fab fa-whatsapp me-1"></i>WhatsApp Share</a>';
        }
        el.innerHTML = html;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // Watermark form
    document.getElementById('watermarkForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const el = document.getElementById('watermarkResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Processing...</div>';
        try {
            const resp = await fetch(base + '/marketing/watermark', { method: 'POST', body: new FormData(this) });
            showResult('watermarkResult', await resp.json());
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger">Network error</div>';
        }
    });

    // Banner form
    document.getElementById('bannerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const el = document.getElementById('bannerResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Generating...</div>';
        try {
            const resp = await fetch(base + '/marketing/banner', { method: 'POST', body: new FormData(this) });
            showResult('bannerResult', await resp.json());
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger">Network error</div>';
        }
    });

    // AI form
    document.getElementById('aiForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const el = document.getElementById('aiResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> AI likh raha hai...</div>';
        try {
            const fd = new FormData(this);
            const data = {};
            fd.forEach((v, k) => data[k] = v);
            const resp = await fetch(base + '/marketing/ai-writer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await resp.json();
            if (!result.success) {
                el.innerHTML = '<div class="alert alert-danger">' + escapeHtml(result.message || 'Failed') + '</div>';
                return;
            }
            let html = '<div class="card"><div class="card-body">';
            html += '<h6>' + escapeHtml(result.headline || '') + '</h6>';
            html += '<p class="small" style="white-space:pre-wrap">' + escapeHtml(result.body || '') + '</p>';
            html += '<p class="small text-primary">' + escapeHtml(result.hashtags || '') + '</p>';
            if (!result.ai_generated) html += '<span class="badge bg-secondary">Template (AI unavailable)</span>';
            else html += '<span class="badge bg-success">AI Generated</span>';
            html += '<button class="btn btn-sm btn-outline-primary mt-2" onclick="copyPost(this)">Copy Post</button> ';
            if (result.whatsapp_share) {
                html += '<a href="' + escapeHtml(result.whatsapp_share) + '" target="_blank" class="btn btn-success btn-sm mt-2"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>';
            }
            html += '<div style="display:none" class="post-text">' + escapeHtml((result.headline || '') + '\n\n' + (result.body || '') + '\n\n' + (result.hashtags || '')) + '</div>';
            html += '</div></div>';
            el.innerHTML = html;
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger">Network error</div>';
        }
    });

    window.copyPost = function(btn) {
        const text = btn.parentElement.querySelector('.post-text').textContent;
        navigator.clipboard.writeText(text).then(() => {
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = 'Copy Post', 2000);
        });
    };

    // Load saved branding on page load
    fetch(base + '/marketing/branding').then(r => r.json()).then(d => {
        if (d.success && d.data) {
            if (d.data.display_name) document.getElementById('brandName').value = d.data.display_name;
            if (d.data.phone) document.getElementById('brandPhone').value = d.data.phone;
            if (d.data.tagline) document.getElementById('brandTagline').value = d.data.tagline;
        }
    }).catch(() => {});

    // Branding form
    document.getElementById('brandingForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const el = document.getElementById('brandingResult');
        try {
            const fd = new FormData(this);
            const data = {};
            fd.forEach((v, k) => data[k] = v);
            const resp = await fetch(base + '/marketing/branding/save', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
            });
            const r = await resp.json();
            el.innerHTML = r.success ? '<div class="alert alert-success py-1 small">Saved! Ab har post par auto lagega.</div>' : '<div class="alert alert-danger py-1 small">' + escapeHtml(r.message) + '</div>';
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger py-1 small">Network error</div>';
        }
    });

    // Generic multipart handler
    async function submitMultipart(formId, resultId, endpoint, loadingText) {
        document.getElementById(formId).addEventListener('submit', async function(e) {
            e.preventDefault();
            const el = document.getElementById(resultId);
            el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> ' + (loadingText || 'Processing...') + '</div>';
            try {
                const resp = await fetch(base + endpoint, { method: 'POST', body: new FormData(this) });
                showResult(resultId, await resp.json());
            } catch (err) {
                el.innerHTML = '<div class="alert alert-danger">Network error</div>';
            }
        });
    }
    submitMultipart('stickerForm', 'stickerResult', '/marketing/sticker');
    submitMultipart('collageForm', 'collageResult', '/marketing/collage', 'Making collage...');
    submitMultipart('qrPhotoForm', 'qrPhotoResult', '/marketing/qr-photo');
    submitMultipart('slideshowForm', 'slideshowResult', '/marketing/slideshow', 'Making GIF...');

    // Generic JSON handler
    async function submitJson(formId, resultId, endpoint, loadingText) {
        document.getElementById(formId).addEventListener('submit', async function(e) {
            e.preventDefault();
            const el = document.getElementById(resultId);
            el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> ' + (loadingText || 'Processing...') + '</div>';
            try {
                const fd = new FormData(this);
                const data = {};
                fd.forEach((v, k) => data[k] = v);
                const resp = await fetch(base + endpoint, {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
                });
                showResult(resultId, await resp.json());
            } catch (err) {
                el.innerHTML = '<div class="alert alert-danger">Network error</div>';
            }
        });
    }
    submitJson('festivalForm', 'festivalResult', '/marketing/festival', 'Creating...');
    submitJson('qrForm', 'qrResult', '/marketing/qr', 'Generating QR...');
    submitJson('vcardForm', 'vcardResult', '/marketing/visiting-card', 'Creating card...');
    submitMultipart('videoForm', 'videoResult', '/marketing/video-pack', 'Uploading video...');

    // Text template fill + copy
    window.fillTextTpl = async function() {
        const el = document.getElementById('textTplResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Filling...</div>';
        try {
            const resp = await fetch(base + '/marketing/fill-template', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    template: document.getElementById('textTplSelect').value,
                    price: document.getElementById('textTplPrice').value
                })
            });
            const r = await resp.json();
            if (!r.success) {
                el.innerHTML = '<div class="alert alert-danger">' + escapeHtml(r.message) + '</div>';
                return;
            }
            let html = '<div class="card"><div class="card-body">';
            html += '<p class="small" style="white-space:pre-wrap">' + escapeHtml(r.text) + '</p>';
            html += '<div class="d-flex gap-1 flex-wrap">';
            html += '<button class="btn btn-sm btn-outline-primary" onclick="copyTplText(this)">Copy</button>';
            if (r.links) {
                if (r.links.whatsapp) html += '<a href="' + escapeHtml(r.links.whatsapp) + '" target="_blank" class="btn btn-success btn-sm">WhatsApp</a>';
                if (r.links.sms) html += '<a href="' + escapeHtml(r.links.sms) + '" class="btn btn-outline-success btn-sm">SMS</a>';
                if (r.links.email) html += '<a href="' + escapeHtml(r.links.email) + '" class="btn btn-outline-secondary btn-sm">Email</a>';
                html += '<a href="https://www.instagram.com/" target="_blank" class="btn btn-outline-dark btn-sm">Instagram</a>';
            }
            html += '</div><div style="display:none" class="tpl-text">' + escapeHtml(r.text) + '</div>';
            html += '</div></div>';
            el.innerHTML = html;
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger">Network error</div>';
        }
    };

    window.copyTplText = function(btn) {
        const text = btn.closest('.card-body').querySelector('.tpl-text').textContent;
        navigator.clipboard.writeText(text).then(() => {
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = 'Copy', 2000);
        });
    };

    // Morning post
    window.morningPost = async function() {
        const el = document.getElementById('morningResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Bana rahe hain...</div>';
        try {
            const resp = await fetch(base + '/marketing/morning-post', { method: 'POST' });
            showResult('morningResult', await resp.json());
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger">Network error</div>';
        }
    };

    // AI Provider Settings form
    document.getElementById('aiProviderForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const el = document.getElementById('aiProviderResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Saving...</div>';
        try {
            const fd = new FormData(this);
            const data = {};
            fd.forEach((v, k) => data[k] = v);
            const resp = await fetch(base + '/marketing/ai-provider/save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const r = await resp.json();
            el.innerHTML = r.success ? '<div class="alert alert-success py-1 small">Saved! Ab AI tools me ye provider use hoga.</div>' : '<div class="alert alert-danger py-1 small">' + escapeHtml(r.message) + '</div>';
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger py-1 small">Network error</div>';
        }
    });

    // Load saved AI provider settings on page load
    fetch(base + '/marketing/ai-provider').then(r => r.json()).then(d => {
        if (d.success && d.data) {
            if (d.data.ai_provider) document.getElementById('aiProviderSelect').value = d.data.ai_provider;
            if (d.data.ai_api_key) document.getElementById('aiApiKey').value = d.data.ai_api_key;
            if (d.data.ai_model) document.getElementById('aiModel').value = d.data.ai_model;
        }
    }).catch(() => {});
})();
</script>