/**
 * Image Uploader for Property Listing Wizard
 * Drag-drop, preview thumbnails, reorder, delete, max 10 images
 * Pure vanilla JS, no dependencies.
 */

(function() {
    'use strict';

    class ImageUploader {
        constructor(container) {
            this.container = container;
            this.uploadUrl = container.dataset.uploadUrl;
            this.csrf = container.dataset.csrf;
            this.max = parseInt(container.dataset.max || '10', 10);
            this.dropzone = container.querySelector('.upload-dropzone');
            this.fileInput = container.querySelector('#file-input');
            this.browseBtn = container.querySelector('#browse-btn');
            this.thumbnailsEl = container.querySelector('#thumbnails');
            this.progressEl = container.querySelector('#upload-progress');
            this.progressBar = this.progressEl ? this.progressEl.querySelector('.progress-bar') : null;
            this.images = [];

            try {
                const existing = JSON.parse(container.dataset.existing || '[]');
                if (Array.isArray(existing)) this.images = existing.filter(s => typeof s === 'string' && s);
            } catch (e) { this.images = []; }
            this.render();

            this.browseBtn.addEventListener('click', () => this.fileInput.click());
            this.dropzone.addEventListener('click', (e) => {
                if (e.target === this.browseBtn) return;
                if (e.target.closest('.thumb-item')) return;
                this.fileInput.click();
            });
            this.fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files));

            ['dragenter', 'dragover'].forEach(evt =>
                this.dropzone.addEventListener(evt, (e) => { e.preventDefault(); this.dropzone.classList.add('border-primary', 'bg-light'); })
            );
            ['dragleave', 'drop'].forEach(evt =>
                this.dropzone.addEventListener(evt, (e) => { e.preventDefault(); this.dropzone.classList.remove('border-primary', 'bg-light'); })
            );
            this.dropzone.addEventListener('drop', (e) => {
                if (e.dataTransfer && e.dataTransfer.files) this.handleFiles(e.dataTransfer.files);
            });
        }

        handleFiles(files) {
            const list = Array.from(files).filter(f => /image\/(jpeg|png|webp)/.test(f.type));
            const remaining = this.max - this.images.length;
            if (remaining <= 0) { this.showError('Max ' + this.max + ' images allowed'); return; }
            const toUpload = list.slice(0, remaining);
            toUpload.forEach((f, idx) => this.upload(f, idx, toUpload.length));
        }

        upload(file, idx, total) {
            const fd = new FormData();
            fd.append('image', file);
            fd.append('csrf_token', this.csrf);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', this.uploadUrl, true);
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable && this.progressBar) {
                    const pct = ((idx + e.loaded / e.total) / total) * 100;
                    this.progressBar.style.width = pct.toFixed(1) + '%';
                    this.progressEl.style.display = 'block';
                }
            });
            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp.ok && resp.url) {
                            this.images.push(resp.url);
                            this.render();
                        } else {
                            this.showError(resp.error || 'Upload failed');
                        }
                    } catch (e) { this.showError('Bad response'); }
                } else {
                    this.showError('HTTP ' + xhr.status);
                }
                if (idx === total - 1 && this.progressEl) {
                    setTimeout(() => { this.progressEl.style.display = 'none'; this.progressBar.style.width = '0%'; }, 500);
                }
            };
            xhr.onerror = () => this.showError('Network error');
            xhr.send(fd);
        }

        remove(index) {
            this.images.splice(index, 1);
            this.render();
        }

        move(index, direction) {
            const newIdx = index + direction;
            if (newIdx < 0 || newIdx >= this.images.length) return;
            const tmp = this.images[index];
            this.images[index] = this.images[newIdx];
            this.images[newIdx] = tmp;
            this.render();
        }

        render() {
            this.thumbnailsEl.innerHTML = '';
            const hiddenContainer = document.getElementById('uploaded-images-container');
            if (hiddenContainer) hiddenContainer.innerHTML = '';
            this.images.forEach((url, idx) => {
                const div = document.createElement('div');
                div.className = 'thumb-item position-relative';
                div.style.cssText = 'width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;';
                div.innerHTML = `
                    <img src="${this.escape(url)}" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
                    <div class="thumb-actions position-absolute top-0 end-0 d-flex">
                        <button type="button" class="btn btn-sm btn-light p-1" data-action="left" title="Move left"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="btn btn-sm btn-light p-1" data-action="right" title="Move right"><i class="fas fa-chevron-right"></i></button>
                        <button type="button" class="btn btn-sm btn-danger p-1" data-action="delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>
                    <span class="position-absolute bottom-0 start-0 badge bg-dark m-1">${idx + 1}</span>
                `;
                div.querySelector('[data-action="delete"]').addEventListener('click', () => this.remove(idx));
                div.querySelector('[data-action="left"]').addEventListener('click', () => this.move(idx, -1));
                div.querySelector('[data-action="right"]').addEventListener('click', () => this.move(idx, 1));
                this.thumbnailsEl.appendChild(div);
                if (hiddenContainer) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'uploaded_images[]';
                    input.value = url;
                    input.dataset.index = idx;
                    hiddenContainer.appendChild(input);
                }
            });
        }

        escape(s) {
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }

        showError(msg) {
            const div = document.createElement('div');
            div.className = 'alert alert-warning alert-dismissible fade show mt-2';
            div.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> ' + this.escape(msg) +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            this.container.appendChild(div);
            setTimeout(() => { if (div.parentNode) div.remove(); }, 4000);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('image-uploader');
        if (el) window.propertyImageUploader = new ImageUploader(el);
    });
})();
