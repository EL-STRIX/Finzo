/* ═══════════════════════════════════════════════
   FINZO ADMIN — ANIMATED DROPDOWNS
   Global handler for all .finzo-dropdown elements
   ═══════════════════════════════════════════════ */

$(function() {
  // Toggle dropdown
  $(document).on('click', '.finzo-dropdown-trigger', function(e) {
    // 1. Stop propagation to prevent window/click-outside handlers (from this event)
    e.stopPropagation();
    
    // 2. IMPORTANT: Stop immediate propagation to prevent other handlers on 'document' 
    // (like the one in main.js) from firing and toggling the class back off.
    e.stopImmediatePropagation();
    
    const $trigger = $(this);
    const $dropdown = $trigger.closest('.finzo-dropdown');
    const $menu = $dropdown.find('.finzo-dropdown-menu');
    
    // Close others
    $('.finzo-dropdown-menu.is-open').not($menu).removeClass('is-open');
    $('.finzo-dropdown-trigger.is-open').not($trigger).removeClass('is-open');
    
    $trigger.toggleClass('is-open');
    $menu.toggleClass('is-open');
  });

  // Selection logic
  $(document).on('click', '.finzo-dropdown-option', function(e) {
    e.stopPropagation();
    e.stopImmediatePropagation();
    
    const $option = $(this);
    const $dropdown = $option.closest('.finzo-dropdown');
    const $trigger = $dropdown.find('.finzo-dropdown-trigger');
    const $menu = $dropdown.find('.finzo-dropdown-menu');
    const $input = $dropdown.find('input[type="hidden"]');
    const $label = $trigger.find('.dropdown-label');
    
    const val = $option.data('value');
    const txt = $option.text();
    
    // Update selection
    $dropdown.find('.finzo-dropdown-option').removeClass('is-selected');
    $option.addClass('is-selected');
    
    // Update UI
    $input.val(val).trigger('change');
    $label.text(txt);
    
    // Close
    $trigger.removeClass('is-open');
    $menu.removeClass('is-open');
  });

  // Close on outside click
  $(document).on('click', function() {
    $('.finzo-dropdown-trigger, .finzo-dropdown-menu').removeClass('is-open');
  });
});
