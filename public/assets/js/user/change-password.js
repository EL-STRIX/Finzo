// Include JS functions required for password strength if not in user-shared.js
function togglePw(id, eyeId) {
    const input = document.getElementById(id);
    const eye = document.getElementById(eyeId);
    if (input.type === 'password') {
        input.type = 'text';
        eye.innerText = 'visibility_off';
    } else {
        input.type = 'password';
        eye.innerText = 'visibility';
    }
}

function checkStrength() {
    const pw = document.getElementById('new-pw').value;
    const meter = document.getElementById('pw-meter') || document.getElementById('strength-fill');
    const strText = document.getElementById('pw-strength-text');
    if (!meter || !strText) return;
    
    let strength = 0;
    if (pw.length > 7) strength += 25;
    if (pw.match(/[a-z]+/)) strength += 25;
    if (pw.match(/[A-Z]+/)) strength += 25;
    if (pw.match(/[0-9]+/)) strength += 25;
    
    meter.style.width = strength + '%';
    
    if (strength <= 25) {
        meter.className = 'h-full transition-all duration-300 ease-out bg-danger-base';
        strText.innerText = 'Weak';
        strText.className = 'text-xs font-bold text-danger-base';
    } else if (strength <= 50) {
        meter.className = 'h-full transition-all duration-300 ease-out bg-warning-base';
        strText.innerText = 'Fair';
        strText.className = 'text-xs font-bold text-warning-base';
    } else if (strength <= 75) {
        meter.className = 'h-full transition-all duration-300 ease-out bg-info-base';
        strText.innerText = 'Good';
        strText.className = 'text-xs font-bold text-info-base';
    } else {
        meter.className = 'h-full transition-all duration-300 ease-out bg-success-base';
        strText.innerText = 'Strong';
        strText.className = 'text-xs font-bold text-success-base';
    }
}
