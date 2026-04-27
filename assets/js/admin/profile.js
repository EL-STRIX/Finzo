/**
 * Finzo Admin — Profile Handler
 * Manages administrative account settings, avatar, and security.
 */

(function() {
    // DOM Elements
    const avatarInitials = document.getElementById('avatar-initials');
    const avatarImgPreview = document.getElementById('avatar-img-preview');
    const nameDisplay = document.getElementById('profile-name-display');
    const emailMeta = document.getElementById('profile-email-meta');
    
    const nameInput = document.getElementById('profile-name-input');
    const idInput = document.getElementById('profile-id-input');
    const emailInput = document.getElementById('profile-email-input');
    const phoneInput = document.getElementById('profile-phone-input');
    
    const currPwInput = document.getElementById('curr-pw');
    const newPwInput = document.getElementById('new-pw');
    const confPwInput = document.getElementById('conf-pw');
    const pwBar = document.getElementById('pw-bar');
    
    const verifyModal = document.getElementById('verify-modal-overlay');
    const verifyPassInput = document.getElementById('verify-pass');

    /**
     * Load Admin profile data from API
     */
    async function loadAdminProfile() {
        try {
            const response = await window.api.get('/admin/profile.php');
            if (response.success || response.status === 'success') {
                const admin = response.admin || response.data;
                
                // Compute initials
                const initials = (admin.full_name || '')
                    .split(' ')
                    .filter(word => /^[a-zA-Z]/.test(word))
                    .map(n => n[0])
                    .join('')
                    .toUpperCase() || 'A';

                // Update UI
                if (nameDisplay) nameDisplay.textContent = admin.full_name || '—';
                if (emailMeta) emailMeta.textContent = admin.email;
                if (avatarInitials) avatarInitials.textContent = initials;

                if (nameInput) nameInput.value = admin.full_name || '';
                if (idInput) idInput.value = admin.admin_id || '';
                if (emailInput) emailInput.value = admin.email || '';
                if (phoneInput) phoneInput.value = admin.phone || '';

                // Avatar handling
                if (admin.avatar) {
                    if (avatarImgPreview) {
                        avatarImgPreview.src = '../../' + admin.avatar;
                        avatarImgPreview.style.display = 'block';
                    }
                    if (avatarInitials) avatarInitials.style.display = 'none';
                } else {
                    if (avatarImgPreview) avatarImgPreview.style.display = 'none';
                    if (avatarInitials) avatarInitials.style.display = 'flex';
                }
            } else {
                window.portal.showToast(response.message || 'Failed to load profile', 'error');
            }
        } catch (error) {
            console.error('Failed to load profile:', error);
            window.portal.showToast('Network error loading profile', 'error');
        }
    }

    // Exported Object
    window.AdminProfile = {
        previewPhoto: async (input) => {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('action', 'upload_avatar');
                formData.append('avatar', file);

                try {
                    // Update local preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        if (avatarImgPreview) {
                            avatarImgPreview.src = e.target.result;
                            avatarImgPreview.style.display = 'block';
                        }
                        if (avatarInitials) avatarInitials.style.display = 'none';
                    };
                    reader.readAsDataURL(file);

                    const response = await window.api.upload('/admin/profile.php', formData);
                    if (response.success || response.status === 'success') {
                        window.portal.showToast('Profile photo updated successfully', 'success');
                    } else {
                        window.portal.showToast(response.message || 'Upload failed', 'error');
                    }
                } catch (error) {
                    console.error('Avatar upload error:', error);
                    window.portal.showToast('Photo upload failed', 'error');
                }
            }
        },

        togglePW: (id) => {
            const input = document.getElementById(id);
            if (!input) return;
            const icon = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                if (icon) icon.textContent = 'visibility';
            }
        },

        checkStrength: (pw) => {
            if (!pwBar) return;
            let strength = 0;
            if (pw.length >= 8) strength++;
            if (/[A-Z]/.test(pw)) strength++;
            if (/[0-9]/.test(pw)) strength++;
            if (/[^A-Za-z0-9]/.test(pw)) strength++;

            if (strength === 0) { 
                pwBar.style.width = '0%'; 
            } else if (strength <= 2) { 
                pwBar.style.width = '33%'; 
                pwBar.style.background = '#ef4444'; 
            } else if (strength === 3) { 
                pwBar.style.width = '66%'; 
                pwBar.style.background = '#f39c12'; 
            } else { 
                pwBar.style.width = '100%'; 
                pwBar.style.background = '#10b981'; 
            }
        },

        handlePasswordUpdate: async () => {
            const currPW = currPwInput?.value;
            const newPW = newPwInput?.value;
            const confPW = confPwInput?.value;
            
            if (!currPW || !newPW || !confPW) {
                window.portal.showToast('All password fields are required', 'error');
                return;
            }
            
            if (newPW !== confPW) {
                window.portal.showToast('New passwords do not match', 'error');
                return;
            }

            try {
                const response = await window.api.post('/admin/profile.php', {
                    action: 'change_password',
                    current_password: currPW,
                    new_password: newPW,
                    confirm_password: confPW
                });

                if (response.success || response.status === 'success') {
                    window.portal.showToast('Password updated successfully', 'success');
                    if (currPwInput) currPwInput.value = '';
                    if (newPwInput) newPwInput.value = '';
                    if (confPwInput) confPwInput.value = '';
                    if (pwBar) pwBar.style.width = '0%';
                } else {
                    window.portal.showToast(response.message || 'Password update failed', 'error');
                }
            } catch (error) {
                console.error('Password update error:', error);
                window.portal.showToast('Failed to update password', 'error');
            }
        },

        showVerifyModal: () => {
            if (verifyModal) {
                verifyModal.style.display = 'flex';
                verifyModal.classList.add('animate-fade-in');
                if (verifyPassInput) verifyPassInput.focus();
            }
        },

        closeVerifyModal: () => {
            if (verifyModal) {
                verifyModal.style.display = 'none';
                if (verifyPassInput) verifyPassInput.value = '';
            }
        },

        handleProfileSave: async () => {
            const pass = verifyPassInput?.value;
            if (!pass) {
                window.portal.showToast('Admin password is required for verification', 'error');
                return;
            }
            
            try {
                const response = await window.api.post('/admin/profile.php', {
                    action: 'update_profile',
                    name: nameInput?.value,
                    email: emailInput?.value,
                    phone: phoneInput?.value,
                    password: pass
                });

                if (response.success || response.status === 'success') {
                    window.AdminProfile.closeVerifyModal();
                    window.portal.showToast('Profile information updated successfully', 'success');
                    loadAdminProfile();
                } else {
                    window.portal.showToast(response.message || 'Update failed', 'error');
                }
            } catch (error) {
                console.error('Profile update error:', error);
                window.portal.showToast('Network error updating profile', 'error');
            }
        }
    };

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        loadAdminProfile();

        // Escape to close modal
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') window.AdminProfile.closeVerifyModal();
        });
    });

})();
