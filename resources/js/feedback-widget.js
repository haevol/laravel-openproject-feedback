/**
 * Laravel OpenProject Feedback Widget
 * Automatically creates work packages in OpenProject from user feedback
 */

(function() {
    'use strict';

    // Get configuration from window object (set by Blade component)
    const config = window.OpenProjectFeedbackConfig || {
        route: '/api/feedback',
        position: 'bottom-left',
        offset: { bottom: 64, top: 16, left: 0, right: 16 },
        zIndex: 50,
        colors: { primary: '#3b82f6', hover: '#2563eb' },
        text: 'FEEDBACK',
        showOnlyAuthenticated: true,
    };

    class FeedbackWidget {
        constructor() {
            this.isOpen = false;
            this.config = config;
            this.init();
        }

        init() {
            if (!this.shouldShow()) return;
            
            this.createButton();
            this.createModal();
            this.attachEventListeners();
        }

        shouldShow() {
            if (this.config.showOnlyAuthenticated) {
                return document.body.classList.contains('authenticated') || 
                       document.querySelector('[data-user-id]');
            }
            return true;
        }

        getButtonClasses() {
            const position = this.config.position || 'bottom-left';
            const positions = {
                'top-left': `top-${this.config.offset.top || 16} left-${this.config.offset.left || 0}`,
                'top-right': `top-${this.config.offset.top || 16} right-${this.config.offset.right || 16}`,
                'bottom-left': `bottom-${this.config.offset.bottom || 64} left-${this.config.offset.left || 0}`,
                'bottom-right': `bottom-${this.config.offset.bottom || 64} right-${this.config.offset.right || 16}`,
            };
            
            return `fixed ${positions[position] || positions['bottom-left']} z-${this.config.zIndex || 50} rounded-tr-md rounded-bl-none px-1.5 py-3 shadow-lg transition-all duration-300`;
        }

        getButtonStyles() {
            return `background-color: ${this.config.colors.primary}; writing-mode: vertical-rl; text-orientation: mixed;`;
        }

        createButton() {
            const button = document.createElement('button');
            button.id = 'feedback-widget-button';
            button.className = this.getButtonClasses();
            button.style.cssText = this.getButtonStyles();
            button.setAttribute('aria-label', 'Send feedback');
            button.innerHTML = `
                <div class="flex flex-col items-center gap-1 text-white">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <span class="text-[10px] font-semibold tracking-wider">${this.config.text || 'FEEDBACK'}</span>
                </div>
            `;
            
            button.addEventListener('mouseenter', () => {
                button.style.backgroundColor = this.config.colors.hover || this.config.colors.primary;
            });
            button.addEventListener('mouseleave', () => {
                button.style.backgroundColor = this.config.colors.primary;
            });
            
            document.body.appendChild(button);
        }

        createModal() {
            const modal = document.createElement('div');
            modal.id = 'feedback-widget-modal';
            modal.className = 'fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50';
            modal.innerHTML = `
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Send Feedback</h2>
                            <button id="feedback-widget-close" class="text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        <form id="feedback-widget-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="feedback-subject" name="subject" required maxlength="255"
                                       placeholder="Brief description of the issue or suggestion"
                                       class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Description <span class="text-red-500">*</span>
                                </label>
                                <textarea id="feedback-description" name="description" required rows="6" maxlength="5000"
                                          placeholder="Describe the issue or suggestion in detail..."
                                          class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                    <span id="feedback-char-count">0</span> / 5000 characters
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Screenshot (optional)
                                </label>
                                <input type="file" id="feedback-screenshot" name="screenshot" accept="image/*"
                                       class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                    Maximum 5MB. Supported formats: JPG, PNG, GIF
                                </p>
                                <div id="feedback-screenshot-preview" class="mt-2 hidden">
                                    <img id="feedback-screenshot-img" src="" alt="Preview" class="max-w-full h-auto rounded-lg border border-neutral-300 dark:border-neutral-600">
                                </div>
                            </div>

                            <div id="feedback-widget-message" class="hidden"></div>

                            <div class="flex gap-3 pt-4">
                                <button type="submit" id="feedback-submit-btn"
                                        class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span id="feedback-submit-text">Send Feedback</span>
                                    <span id="feedback-submit-loading" class="hidden">Sending...</span>
                                </button>
                                <button type="button" id="feedback-cancel-btn"
                                        class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 rounded-lg font-semibold transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        attachEventListeners() {
            const button = document.getElementById('feedback-widget-button');
            if (button) button.addEventListener('click', () => this.open());

            const closeBtn = document.getElementById('feedback-widget-close');
            if (closeBtn) closeBtn.addEventListener('click', () => this.close());

            const cancelBtn = document.getElementById('feedback-cancel-btn');
            if (cancelBtn) cancelBtn.addEventListener('click', () => this.close());

            const modal = document.getElementById('feedback-widget-modal');
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target.id === 'feedback-widget-modal') this.close();
                });
            }

            const descriptionTextarea = document.getElementById('feedback-description');
            if (descriptionTextarea) {
                descriptionTextarea.addEventListener('input', () => {
                    const count = descriptionTextarea.value.length;
                    const charCount = document.getElementById('feedback-char-count');
                    if (charCount) charCount.textContent = count;
                });
            }

            const screenshotInput = document.getElementById('feedback-screenshot');
            if (screenshotInput) {
                screenshotInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            const preview = document.getElementById('feedback-screenshot-preview');
                            const img = document.getElementById('feedback-screenshot-img');
                            if (preview && img) {
                                img.src = event.target.result;
                                preview.classList.remove('hidden');
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            const form = document.getElementById('feedback-widget-form');
            if (form) form.addEventListener('submit', (e) => { e.preventDefault(); this.submit(); });
        }

        open() {
            const modal = document.getElementById('feedback-widget-modal');
            if (!modal) return;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            this.isOpen = true;
            
            const form = document.getElementById('feedback-widget-form');
            if (form) {
                const urlInput = document.createElement('input');
                urlInput.type = 'hidden';
                urlInput.name = 'url';
                urlInput.value = window.location.href;
                form.appendChild(urlInput);
            }
        }

        close() {
            const modal = document.getElementById('feedback-widget-modal');
            if (!modal) return;
            
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            this.isOpen = false;
            
            const form = document.getElementById('feedback-widget-form');
            if (form) form.reset();
            
            const charCount = document.getElementById('feedback-char-count');
            if (charCount) charCount.textContent = '0';
            
            const preview = document.getElementById('feedback-screenshot-preview');
            if (preview) preview.classList.add('hidden');
            
            const message = document.getElementById('feedback-widget-message');
            if (message) message.classList.add('hidden');
        }

        showMessage(message, type = 'success') {
            const messageDiv = document.getElementById('feedback-widget-message');
            if (!messageDiv) return;
            
            const colors = {
                success: 'bg-green-100 dark:bg-green-900 border-green-400 dark:border-green-700 text-green-700 dark:text-green-300',
                error: 'bg-red-100 dark:bg-red-900 border-red-400 dark:border-red-700 text-red-700 dark:text-red-300',
            };
            
            messageDiv.className = `p-4 border rounded-lg ${colors[type] || colors.success}`;
            messageDiv.textContent = message;
            messageDiv.classList.remove('hidden');
            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        async submit() {
            const form = document.getElementById('feedback-widget-form');
            if (!form) return;
            
            const formData = new FormData(form);
            const submitBtn = document.getElementById('feedback-submit-btn');
            const submitText = document.getElementById('feedback-submit-text');
            const submitLoading = document.getElementById('feedback-submit-loading');

            if (submitBtn) submitBtn.disabled = true;
            if (submitText) submitText.classList.add('hidden');
            if (submitLoading) submitLoading.classList.remove('hidden');

            try {
                const response = await fetch(this.config.route || '/api/feedback', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (data.success) {
                    this.showMessage(data.message || 'Feedback submitted successfully! Thank you for your contribution.', 'success');
                    setTimeout(() => this.close(), 2000);
                } else {
                    this.showMessage(data.message || 'Error submitting feedback. Please try again later.', 'error');
                    if (submitBtn) submitBtn.disabled = false;
                    if (submitText) submitText.classList.remove('hidden');
                    if (submitLoading) submitLoading.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error submitting feedback:', error);
                this.showMessage('Connection error. Please try again later.', 'error');
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.classList.remove('hidden');
                if (submitLoading) submitLoading.classList.add('hidden');
            }
        }
    }

    function initFeedbackWidget() {
        if (!document.body) return;
        
        if (config.showOnlyAuthenticated) {
            if (!document.body.classList.contains('authenticated') && !document.querySelector('[data-user-id]')) {
                return;
            }
        }
        
        try {
            new FeedbackWidget();
        } catch (error) {
            console.error('Error initializing FeedbackWidget:', error);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFeedbackWidget);
    } else {
        initFeedbackWidget();
    }

    document.addEventListener('livewire:navigated', () => {
        const existingButton = document.getElementById('feedback-widget-button');
        const existingModal = document.getElementById('feedback-widget-modal');
        if (existingButton) existingButton.remove();
        if (existingModal) existingModal.remove();
        initFeedbackWidget();
    });
})();

