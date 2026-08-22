(function() {
    let settingsData = {};

    async function loadSettings() {
        try {
            const response = await window.api.get('/admin/settings');
            if (response.success) {
                settingsData = response.data || {};
                renderSettings();
            }
        } catch (error) {
            console.error('Failed to load settings:', error);
            window.showToast('Failed to load settings', 'error');
        }
    }

    function renderSettings() {
        const categories = ['banking', 'security', 'application', 'operational'];
        
        categories.forEach(cat => {
            const container = document.getElementById(`grid-${cat}`);
            if (!container) return;

            const categorySettings = settingsData[cat] || [];
            
            if (categorySettings.length === 0) {
                container.innerHTML = `<div class="col-span-full py-8 text-center text-text-muted text-sm border border-dashed border-border-subtle rounded-2xl">No ${cat} settings found in database.</div>`;
                return;
            }

            let html = '';
            categorySettings.forEach(setting => {
                html += renderSettingInput(setting);
            });
            container.innerHTML = html;
        });
    }

    function renderSettingInput(setting) {
        const id = `setting_${setting.setting_key}`;
        let inputHtml = '';

        if (setting.setting_type === 'boolean') {
            const isChecked = setting.setting_value === 'true' || setting.setting_value === '1' ? 'checked' : '';
            return `
            <div class="flex items-center justify-between p-4 rounded-xl border border-border-subtle bg-background">
                <div>
                    <label class="font-bold text-sm text-text-primary cursor-pointer" for="${id}">${setting.label}</label>
                    <p class="text-[10px] text-text-muted mt-0.5">${setting.description || ''}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" id="${id}" name="settings[${setting.setting_key}]" value="1" class="sr-only peer setting-input" data-key="${setting.setting_key}" data-type="boolean" ${isChecked}>
                    <div class="w-11 h-6 bg-surface-subtle peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary shadow-inner border border-border-subtle"></div>
                </label>
            </div>
            `;
        } 
        else if (setting.setting_type === 'number') {
            return `
            <div class="p-4 rounded-xl border border-border-subtle bg-background">
                <label class="font-bold text-sm text-text-primary" for="${id}">${setting.label}</label>
                <p class="text-[10px] text-text-muted mt-0.5 mb-3">${setting.description || ''}</p>
                <input type="number" id="${id}" name="settings[${setting.setting_key}]" value="${setting.setting_value}" class="fin-input !h-9 !py-1 !text-sm setting-input" data-key="${setting.setting_key}" data-type="number" step="any" />
            </div>
            `;
        } 
        else { // string or json
            return `
            <div class="col-span-full p-4 rounded-xl border border-border-subtle bg-background">
                <label class="font-bold text-sm text-text-primary" for="${id}">${setting.label}</label>
                <p class="text-[10px] text-text-muted mt-0.5 mb-3">${setting.description || ''}</p>
                <input type="text" id="${id}" name="settings[${setting.setting_key}]" value="${setting.setting_value}" class="fin-input !h-9 !py-1 !text-sm setting-input" data-key="${setting.setting_key}" data-type="string" />
            </div>
            `;
        }
    }

    function initTabs() {
        const tabs = document.querySelectorAll('.settings-tab');
        const panes = document.querySelectorAll('.settings-pane');
        const saveBtn = document.getElementById('btn-save-settings');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active from all tabs
                tabs.forEach(t => {
                    t.classList.remove('bg-primary-50', 'text-primary');
                    t.classList.add('text-text-secondary');
                });
                // Add active to clicked tab
                tab.classList.remove('text-text-secondary');
                tab.classList.add('bg-primary-50', 'text-primary');

                // Hide all panes
                panes.forEach(p => p.classList.add('hidden'));
                
                // Show target pane
                const target = tab.dataset.target;
                document.getElementById(`settings-${target}`).classList.remove('hidden');

                // Hide save button if on backup tab
                if (target === 'backup') {
                    saveBtn.classList.add('hidden');
                } else {
                    saveBtn.classList.remove('hidden');
                }
            });
        });
    }

    function initSave() {
        document.getElementById('btn-save-settings')?.addEventListener('click', async () => {
            const inputs = document.querySelectorAll('.setting-input');
            const payload = { settings: {} };

            inputs.forEach(input => {
                const key = input.dataset.key;
                let val;
                
                if (input.dataset.type === 'boolean') {
                    val = input.checked ? 'true' : 'false';
                } else {
                    val = input.value;
                }
                
                payload.settings[key] = val;
            });

            const btn = document.getElementById('btn-save-settings');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loader"></span> Saving...';
            btn.disabled = true;

            try {
                const res = await window.api.put('/admin/settings', payload);
                if (res.success) {
                    window.showToast('Settings saved successfully', 'success');
                } else {
                    window.showToast(res.message || 'Failed to save settings', 'error');
                }
            } catch (error) {
                console.error(error);
                window.showToast('Error saving settings', 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }

    function initBackup() {
        document.querySelectorAll('.btn-trigger-backup').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const type = e.currentTarget.dataset.type;
                if(!confirm(`Are you sure you want to trigger a ${type.toUpperCase()} database backup?`)) return;

                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="loader"></span> Processing...';
                btn.disabled = true;

                try {
                    const res = await window.api.post('/admin/settings/backup', { type: type });
                    if(res.success) {
                        window.showToast(res.message, 'success');
                    } else {
                        window.showToast(res.message || 'Backup failed', 'error');
                    }
                } catch (error) {
                    window.showToast('Error triggering backup', 'error');
                } finally {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        });
    }

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
