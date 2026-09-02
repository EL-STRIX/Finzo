/**
 * Finzo Admin — System Settings Engine
 * Version 2.0 — Dynamic Parameters, Form Hydration, Modal Confirmation, and Liquid Glass UI
 */

(function() {
    let settingsData = {};
    let pendingBackupType = 'full';

    /**
     * Unified Toast Notification Dispatcher
     */
    function toast(msg, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(msg, type);
        } else if (window.FinzoUtils && typeof window.FinzoUtils.showToast === 'function') {
            window.FinzoUtils.showToast(msg, type);
        } else if (window.portal && typeof window.portal.showToast === 'function') {
            window.portal.showToast(msg, type);
        } else {
            alert(msg);
        }
    }

    /**
     * Helper to format snake_case keys into Title Case
     */
    function formatKey(key) {
        if (!key) return '';
        return key.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    }

    /**
     * Fetch settings grouped by category from backend
     */
    async function loadSettings() {
        try {
            const response = await window.api.get('/admin/settings');
            if (response && (response.success || response.status === 200 || response.data)) {
                settingsData = response.data || response;
                updateStatsOverview();
                renderSettings();
            } else {
                toast(response.message || 'Failed to load system settings', 'error');
            }
        } catch (error) {
            console.error('Failed to load settings:', error);
            toast('Failed to communicate with settings engine', 'error');
        }
    }

    /**
     * Update top KPI summary stats
     */
    function updateStatsOverview() {
        let totalCount = 0;
        let isMaintenance = false;

        Object.keys(settingsData).forEach(cat => {
            const list = Array.isArray(settingsData[cat]) ? settingsData[cat] : [];
            totalCount += list.length;
            list.forEach(s => {
                if (s.setting_key === 'maintenance_mode') {
                    isMaintenance = (s.setting_value === 'true' || s.setting_value === '1' || s.setting_value === true);
                }
            });
        });

        const countEl = document.getElementById('stat-total-settings');
        if (countEl) countEl.textContent = `${totalCount} Configured`;

        const statusEl = document.getElementById('stat-platform-status');
        if (statusEl) {
            if (isMaintenance) {
                statusEl.textContent = 'Maintenance Mode';
                statusEl.className = 'text-xl font-black font-display text-warning';
            } else {
                statusEl.textContent = 'Operational';
                statusEl.className = 'text-xl font-black font-display text-emerald-500';
            }
        }
    }

    /**
     * Render settings inputs into category containers
     */
    function renderSettings() {
        const categories = ['banking', 'security', 'application', 'operational'];
        
        categories.forEach(cat => {
            const container = document.getElementById(`grid-${cat}`);
            if (!container) return;

            const categorySettings = Array.isArray(settingsData[cat]) ? settingsData[cat] : [];
            
            if (categorySettings.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full py-8 text-center text-text-secondary text-xs font-bold uppercase tracking-wider border border-dashed border-border-subtle rounded-2xl">
                        No ${cat} settings found in database
                    </div>
                `;
                return;
            }

            let html = '';
            categorySettings.forEach(setting => {
                html += renderSettingInput(setting);
            });
            container.innerHTML = html;
        });
    }

    /**
     * Generate HTML for individual setting field
     */
    function renderSettingInput(setting) {
        const id = `setting_${setting.setting_key}`;
        const label = setting.label && setting.label.trim() !== '' ? setting.label : formatKey(setting.setting_key);
        const description = setting.description || `Configure ${label.toLowerCase()}`;
        const type = setting.setting_type || 'string';

        if (type === 'boolean') {
            const isChecked = (setting.setting_value === 'true' || setting.setting_value === '1' || setting.setting_value === true) ? 'checked' : '';
            return `
            <div class="flex items-center justify-between p-5 rounded-2xl border border-border-subtle bg-background hover:border-primary/30 transition-all shadow-sm">
                <div class="pr-4">
                    <label class="font-headline font-bold text-sm text-text-primary cursor-pointer" for="${id}">${label}</label>
                    <p class="text-xs text-text-secondary mt-1 leading-relaxed">${description}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" id="${id}" name="settings[${setting.setting_key}]" value="1" class="sr-only peer setting-input" data-key="${setting.setting_key}" data-type="boolean" ${isChecked}>
                    <div class="w-12 h-6 bg-surface-subtle peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary shadow-inner border border-border-subtle"></div>
                </label>
            </div>
            `;
        } 
        else if (type === 'number') {
            return `
            <div class="p-5 rounded-2xl border border-border-subtle bg-background hover:border-primary/30 transition-all shadow-sm flex flex-col justify-between">
                <div>
                    <label class="font-headline font-bold text-sm text-text-primary block" for="${id}">${label}</label>
                    <p class="text-xs text-text-secondary mt-1 mb-3 leading-relaxed">${description}</p>
                </div>
                <input type="number" id="${id}" name="settings[${setting.setting_key}]" value="${setting.setting_value ?? ''}" class="fin-input !h-10 !text-sm font-mono font-bold setting-input" data-key="${setting.setting_key}" data-type="number" step="any" />
            </div>
            `;
        } 
        else { // string or default
            return `
            <div class="col-span-full p-5 rounded-2xl border border-border-subtle bg-background hover:border-primary/30 transition-all shadow-sm flex flex-col justify-between">
                <div>
                    <label class="font-headline font-bold text-sm text-text-primary block" for="${id}">${label}</label>
                    <p class="text-xs text-text-secondary mt-1 mb-3 leading-relaxed">${description}</p>
                </div>
                <input type="text" id="${id}" name="settings[${setting.setting_key}]" value="${setting.setting_value ?? ''}" class="fin-input !h-10 !text-sm font-mono setting-input" data-key="${setting.setting_key}" data-type="string" />
            </div>
            `;
        }
    }

    /**
     * Tab Switcher Navigation
     */
    function initTabs() {
        const tabs = document.querySelectorAll('.settings-tab');
        const panes = document.querySelectorAll('.settings-pane');
        const saveBtn = document.getElementById('btn-save-settings');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => {
                    t.classList.remove('bg-primary-50', 'text-primary', 'border', 'border-primary/20');
                    t.classList.add('text-text-secondary');
                });

                tab.classList.remove('text-text-secondary');
                tab.classList.add('bg-primary-50', 'text-primary', 'border', 'border-primary/20');

                panes.forEach(p => p.classList.add('hidden'));
                
                const target = tab.dataset.target;
                const activePane = document.getElementById(`settings-${target}`);
                if (activePane) activePane.classList.remove('hidden');

                // Hide save button on backup panel
                if (target === 'backup' && saveBtn) {
                    saveBtn.classList.add('hidden');
                } else if (saveBtn) {
                    saveBtn.classList.remove('hidden');
                }
            });
        });
    }

    /**
     * Save Modified Settings Handler
     */
    function initSave() {
        const btn = document.getElementById('btn-save-settings');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const inputs = document.querySelectorAll('.setting-input');
            const payload = { settings: {} };

            inputs.forEach(input => {
                const key = input.dataset.key;
                if (!key) return;

                let val;
                if (input.dataset.type === 'boolean') {
                    val = input.checked ? 'true' : 'false';
                } else {
                    val = input.value.trim();
                }
                payload.settings[key] = val;
            });

            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">refresh</span><span>Saving...</span>';
            btn.disabled = true;

            try {
                const res = await window.api.put('/admin/settings', payload);
                if (res && (res.success || res.status === 200)) {
                    toast(res.message || 'System settings updated successfully', 'success');
                    loadSettings();
                } else {
                    toast(res.message || 'Failed to update system settings', 'error');
                }
            } catch (error) {
                console.error('Settings save error:', error);
                toast('Network or permission error updating settings', 'error');
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    }

    /**
     * BCDR Backup Modal & Execution Workflow
     */
    function initBackup() {
        const modal = document.getElementById('backup-modal');
        const modalTypeSpan = document.getElementById('backup-modal-type');
        const cancelBtn = document.getElementById('backup-modal-cancel');
        const confirmBtn = document.getElementById('backup-modal-confirm');

        document.querySelectorAll('.btn-trigger-backup').forEach(btn => {
            btn.addEventListener('click', (e) => {
                pendingBackupType = e.currentTarget.dataset.type || 'full';
                if (modalTypeSpan) modalTypeSpan.textContent = pendingBackupType.toUpperCase();
                if (modal) modal.classList.remove('hidden');
            });
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                if (modal) modal.classList.add('hidden');
            });
        }

        if (confirmBtn) {
            confirmBtn.addEventListener('click', async () => {
                const originalHtml = confirmBtn.innerHTML;
                confirmBtn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">refresh</span><span>Processing...</span>';
                confirmBtn.disabled = true;

                try {
                    const res = await window.api.post('/admin/settings/backup', { type: pendingBackupType });
                    if (res && (res.success || res.status === 200)) {
                        toast(res.message || 'Database snapshot generated successfully', 'success');
                        if (modal) modal.classList.add('hidden');
                    } else {
                        toast(res.message || 'Database backup failed', 'error');
                    }
                } catch (error) {
                    console.error('Backup error:', error);
                    toast('Error executing database backup snapshot', 'error');
                } finally {
                    confirmBtn.innerHTML = originalHtml;
                    confirmBtn.disabled = false;
                }
            });
        }
    }

    /**
     * Bootstrap on DOM Ready
     */
    function init() {
        initTabs();
        initSave();
        initBackup();
        loadSettings();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
