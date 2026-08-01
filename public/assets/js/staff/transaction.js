document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const type = params.get('type') || 'deposit';

    const mainContent = document.getElementById('transaction-main');
    if (mainContent) {
        mainContent.style.display = 'block';
    }

    if (type === 'withdraw') {
        document.title = "Finzo Staff - New Withdrawal";
        const titleEl = document.querySelector('h2');
        if(titleEl) titleEl.innerText = "Process Withdrawal";
        
        const subEl = document.querySelector('h2 + p');
        if(subEl) subEl.innerText = "Debit money from customer account securely.";

        // Convert success classes to danger classes for withdrawal theme
        document.querySelectorAll('.bg-success-surface').forEach(el => {
            el.classList.replace('bg-success-surface', 'bg-danger-surface');
        });
        document.querySelectorAll('.dark\\:bg-success-surface\\/10').forEach(el => {
            el.classList.replace('dark:bg-success-surface/10', 'dark:bg-danger-surface/10');
        });
        document.querySelectorAll('.text-success-base').forEach(el => {
            el.classList.replace('text-success-base', 'text-danger-base');
        });
        document.querySelectorAll('.dark\\:text-success-base').forEach(el => {
            el.classList.replace('dark:text-success-base', 'dark:text-danger-base');
        });
        document.querySelectorAll('.focus\\:border-success-base').forEach(el => {
            el.classList.replace('focus:border-success-base', 'focus:border-danger-base');
        });
        document.querySelectorAll('.dark\\:focus\\:border-success-base').forEach(el => {
            el.classList.replace('dark:focus:border-success-base', 'dark:focus:border-danger-base');
        });
        document.querySelectorAll('.shadow-emerald-500\\/20').forEach(el => {
            el.classList.replace('shadow-emerald-500/20', 'shadow-rose-500/20');
        });

        // Replace texts
        document.querySelectorAll('*').forEach(el => {
            if (el.children.length === 0 && el.textContent.includes('Deposit')) {
                el.textContent = el.textContent.replace(/Deposit/g, 'Withdrawal');
            }
            if (el.children.length === 0 && el.textContent.includes('deposit')) {
                el.textContent = el.textContent.replace(/deposit/g, 'withdrawal');
            }
        });

        // Fix specific IDs
        const amt = document.getElementById('dep-amount');
        if(amt) amt.id = 'with-amount';
        
        const typeSelect = document.getElementById('dep-type');
        if(typeSelect) {
            typeSelect.id = 'with-type';
            typeSelect.innerHTML = `
                <option value="Cash Withdrawal">Cash Withdrawal</option>
                <option value="Cheque Encashment">Cheque Encashment</option>
            `;
        }

        const note = document.getElementById('dep-note');
        if(note) {
            note.id = 'with-identity';
            const noteLabel = note.previousElementSibling;
            if (noteLabel) noteLabel.textContent = 'Identity Verification Notes (Optional)';
        }
    }
});
