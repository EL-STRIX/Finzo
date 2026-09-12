/**
 * Finzo User Portal — Bill Payments & Utilities Interactive Explorer
 *
 * Clean Architecture Reference (backend to be implemented later):
 *   POST /api/v1/user/bills/fetch     → fetch bill from BBPS gateway
 *   POST /api/v1/user/bills/pay       → pay fetched bill
 *   GET  /api/v1/user/bills/history   → payment history
 *   Full spec: docs/04-API/Future-Financial-Products-Spec.md
 */
(function () {
    'use strict';

    // ─────────────────────────────────────────────────────────────────────────
    // CATEGORY MASTER DATA
    // ─────────────────────────────────────────────────────────────────────────
    var CATEGORIES = {
        electricity: {
            label: 'Electricity',
            icon: 'bolt',
            billers: [
                'Tata Power Delhi',
                'Adani Electricity Mumbai',
                'BESCOM Bangalore',
                'BSES Rajdhani Delhi',
                'MSEB Maharashtra',
                'WBSEDCL West Bengal',
                'APEPDCL Andhra Pradesh'
            ],
            consumerLabel: 'Consumer / CA Number',
            consumerPlaceholder: 'e.g. 1029384756',
            consumerIcon: 'badge',
            demoAmounts: ['₹ 1,420.00', '₹ 2,185.50', '₹ 870.00', '₹ 3,200.00'],
            dueIn: 'DUE IN 8 DAYS',
            demoNames: ['Arjun Sharma', 'Priya Nair', 'Rahul Mehta']
        },
        water: {
            label: 'Water',
            icon: 'water_drop',
            billers: [
                'Delhi Jal Board (DJB)',
                'BWSSB Bangalore',
                'Municipal Corp of Mumbai',
                'Chennai Metrowater (CMWSSB)',
                'Hyderabad HMWSSB',
                'Pune Municipal Corporation'
            ],
            consumerLabel: 'Connection / Property No.',
            consumerPlaceholder: 'e.g. WTR-20293847',
            consumerIcon: 'water_drop',
            demoAmounts: ['₹ 380.00', '₹ 510.00', '₹ 230.00', '₹ 720.00'],
            dueIn: 'DUE IN 12 DAYS',
            demoNames: ['Anjali Kapoor', 'Vikram Singh', 'Sunita Rao']
        },
        mobile: {
            label: 'Mobile',
            icon: 'smartphone',
            billers: [
                'Airtel Postpaid',
                'Airtel Prepaid Recharge',
                'Jio Postpaid',
                'Jio Prepaid Recharge',
                'Vodafone Idea (Vi) Postpaid',
                'Vodafone Idea (Vi) Prepaid',
                'BSNL Mobile'
            ],
            consumerLabel: 'Mobile Number',
            consumerPlaceholder: 'e.g. 98765 43210',
            consumerIcon: 'smartphone',
            demoAmounts: ['₹ 299.00', '₹ 399.00', '₹ 666.00', '₹ 1,499.00'],
            dueIn: 'DUE IN 3 DAYS',
            demoNames: ['Rohit Verma', 'Neha Joshi', 'Aditya Kumar']
        },
        broadband: {
            label: 'Broadband',
            icon: 'wifi',
            billers: [
                'JioFiber GigaFast',
                'Airtel Xstream Fiber',
                'ACT Fibernet',
                'Tata Play Fiber',
                'Hathway Broadband',
                'BSNL FTTH'
            ],
            consumerLabel: 'Account / Subscriber ID',
            consumerPlaceholder: 'e.g. ACT-4839201',
            consumerIcon: 'router',
            demoAmounts: ['₹ 799.00', '₹ 999.00', '₹ 1,299.00', '₹ 599.00'],
            dueIn: 'DUE IN 5 DAYS',
            demoNames: ['Kavita Menon', 'Suresh Pillai', 'Deepak Gupta']
        },
        dth: {
            label: 'DTH / Cable',
            icon: 'tv',
            billers: [
                'Tata Play DTH',
                'Airtel Digital TV',
                'Dish TV India',
                'Sun Direct DTH',
                'D2H Videocon',
                'DD Free Dish'
            ],
            consumerLabel: 'Registered Mobile / Subscriber ID',
            consumerPlaceholder: 'e.g. TP-9837261045',
            consumerIcon: 'satellite_alt',
            demoAmounts: ['₹ 399.00', '₹ 649.00', '₹ 299.00', '₹ 799.00'],
            dueIn: 'DUE IN 15 DAYS',
            demoNames: ['Ramesh Iyer', 'Geeta Bose', 'Anand Tiwari']
        },
        gas: {
            label: 'Piped Gas',
            icon: 'local_fire_department',
            billers: [
                'Indraprastha Gas (IGL) Delhi',
                'Mahanagar Gas (MGL) Mumbai',
                'Adani Total Gas',
                'Gujarat Gas Ltd',
                'Torrent Gas Surat',
                'GAIL Gas Agra'
            ],
            consumerLabel: 'Consumer / BP Number',
            consumerPlaceholder: 'e.g. IGL-10293847',
            consumerIcon: 'gas_meter',
            demoAmounts: ['₹ 620.00', '₹ 980.00', '₹ 740.00', '₹ 1,150.00'],
            dueIn: 'DUE IN 10 DAYS',
            demoNames: ['Pooja Agarwal', 'Kiran Shah', 'Dinesh Patel']
        },
        fastag: {
            label: 'FASTag',
            icon: 'toll',
            billers: [
                'NHAI FASTag (HDFC Bank)',
                'NHAI FASTag (ICICI Bank)',
                'NHAI FASTag (SBI)',
                'NHAI FASTag (Axis Bank)',
                'NHAI FASTag (Paytm Payments Bank)',
                'NHAI FASTag (Kotak Bank)'
            ],
            consumerLabel: 'Vehicle Registration / Tag No.',
            consumerPlaceholder: 'e.g. DL01AB1234',
            consumerIcon: 'directions_car',
            demoAmounts: ['₹ 200.00', '₹ 500.00', '₹ 1,000.00', '₹ 2,000.00'],
            dueIn: 'LOW BALANCE',
            demoNames: ['Mohit Aggarwal', 'Smita Kulkarni', 'Rajan Nair']
        }
    };

    var currentCategory = 'electricity';
    // Track which biller is selected (updated by the Finzo dropdown's change event)
    var currentBiller   = CATEGORIES.electricity.billers[0];

    // ─────────────────────────────────────────────────────────────────────────
    // BUILD BILLER DROPDOWN OPTIONS
    // NOTE: The global ui-components.js handles open/close/animation.
    //       We only rebuild the <option> elements and reset the visible label.
    // ─────────────────────────────────────────────────────────────────────────
    function buildBillerOptions(catId) {
        var data  = CATEGORIES[catId];
        var menu  = document.getElementById('biller-dropdown-menu');
        var label = document.getElementById('biller-selected-label');
        if (!menu || !data) return;

        // Rebuild option divs
        menu.innerHTML = data.billers.map(function (b, i) {
            return '<div class="finzo-dropdown-option' + (i === 0 ? ' is-selected' : '') +
                   '" data-value="' + b + '" role="option">' + b + '</div>';
        }).join('');

        // Reset visible label in trigger
        if (label) label.textContent = data.billers[0];
        currentBiller = data.billers[0];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SET ACTIVE CATEGORY — called when a category card is clicked
    // ─────────────────────────────────────────────────────────────────────────
    function setCategory(catId, triggerEl) {
        currentCategory = catId;
        var data = CATEGORIES[catId];
        if (!data) return;

        // Update category grid styles
        document.querySelectorAll('.category-card').forEach(function (c) {
            c.classList.remove('border-primary', 'bg-primary/5', 'border-primary/40', 'active-category');
            c.classList.add('border-border-subtle', 'bg-surface/80');
        });
        if (triggerEl) {
            triggerEl.classList.remove('border-border-subtle', 'bg-surface/80');
            triggerEl.classList.add('border-primary/40', 'bg-primary/5', 'active-category');
        }

        // Update pill badge
        var pill = document.getElementById('active-category-pill');
        if (pill) pill.textContent = data.label + ' Selected';

        // Update sandbox header
        var title  = document.getElementById('sandbox-title');
        var iconEl = document.getElementById('sandbox-category-icon');
        if (title)  title.textContent  = data.label + ' Bill Sandbox';
        if (iconEl) iconEl.textContent = data.icon;

        // Update consumer field
        var consumerLabel = document.getElementById('consumer-no-label');
        var consumerInput = document.getElementById('sandbox-consumer-no');
        var consumerIcon  = document.getElementById('consumer-icon');
        if (consumerLabel) consumerLabel.textContent = data.consumerLabel;
        if (consumerInput) { consumerInput.placeholder = data.consumerPlaceholder; consumerInput.value = ''; }
        if (consumerIcon)  consumerIcon.textContent  = data.consumerIcon;

        // Rebuild biller dropdown (ui-components handles toggle automatically)
        buildBillerOptions(catId);

        // Hide previous result
        var resCard = document.getElementById('sandbox-result');
        if (resCard) resCard.classList.add('hidden');

        if (window.showToast) {
            window.showToast(data.label + ' — ' + data.billers.length + ' billers loaded.', 'info');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SIMULATE BILL FETCH
    // ─────────────────────────────────────────────────────────────────────────
    function simulateBillFetch(e) {
        e.preventDefault();

        var btn     = document.getElementById('btn-fetch-bill');
        var resCard = document.getElementById('sandbox-result');
        var data    = CATEGORIES[currentCategory];

        if (!data) return;

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span><span>Contacting BBPS Gateway\u2026</span>';
        }

        setTimeout(function () {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">search</span><span>Fetch Live Bill (Demo Simulation)</span>';
            }

            if (resCard) {
                resCard.classList.remove('hidden');

                var amount   = data.demoAmounts[Math.floor(Math.random() * data.demoAmounts.length)];
                var name     = data.demoNames[Math.floor(Math.random() * data.demoNames.length)];
                var biller   = currentBiller;

                var nameEl     = document.getElementById('demo-consumer-name');
                var amountEl   = document.getElementById('demo-bill-amount');
                var dueEl      = document.getElementById('demo-due-badge');
                var providerEl = document.getElementById('demo-provider-name');
                var nameLblEl  = document.getElementById('demo-name-label');

                if (nameEl)     nameEl.textContent    = name;
                if (amountEl)   amountEl.textContent   = amount;
                if (dueEl)      dueEl.textContent       = data.dueIn;
                if (providerEl) providerEl.textContent  = biller;
                if (nameLblEl)  nameLblEl.textContent   =
                    currentCategory === 'fastag'  ? 'Vehicle Owner'   :
                    currentCategory === 'mobile'   ? 'Account Holder'  :
                    currentCategory === 'dth'      ? 'Subscriber Name' :
                    currentCategory === 'broadband' ? 'Account Holder' :
                    'Consumer Name';
            }

            if (window.showToast) {
                window.showToast('Demo bill fetched from ' + currentBiller + ' via BBPS sandbox.', 'success');
            }
        }, 700);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INIT
    // ─────────────────────────────────────────────────────────────────────────
    function init() {
        // Build initial biller options for electricity
        buildBillerOptions('electricity');

        // ── Listen to the Finzo dropdown change event (fired by ui-components.js)
        // DO NOT attach our own click handler to the trigger — ui-components handles it.
        var billerDropdown = document.getElementById('biller-dropdown');
        if (billerDropdown) {
            billerDropdown.addEventListener('change', function (e) {
                // e.detail.value is the data-value of the selected option
                if (e.detail && e.detail.value) {
                    currentBiller = e.detail.value;
                }
            });
        }

        // ── Category grid click handlers
        document.querySelectorAll('.category-card').forEach(function (card) {
            card.addEventListener('click', function () {
                var catId = card.dataset.catId;
                if (catId) setCategory(catId, card);
            });
        });

        // ── Sandbox form submit
        var form = document.getElementById('sandbox-bill-form');
        if (form) form.addEventListener('submit', simulateBillFetch);

        // ── Waitlist CTA
        var waitlistBtn = document.getElementById('btn-bill-waitlist');
        if (waitlistBtn) {
            waitlistBtn.addEventListener('click', function () {
                if (window.showToast) window.showToast('You are on the Bharat BillPay early access list! Zero convenience fees on launch.', 'success');
            });
        }

        // ── Pre-register CTA
        var preRegBtn = document.getElementById('btn-pre-register-biller');
        if (preRegBtn) {
            preRegBtn.addEventListener('click', function () {
                if (window.showToast) window.showToast('Pre-registered for zero convenience fees and early auto-debit rollout!', 'success');
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
