/**
 * Finzo Validation Logic
 */
$(document).ready(function() {
    $('form').on('submit', function(e) {
        let valid = true;
        $(this).find('input[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('border-red-500');
                valid = false;
            } else {
                $(this).removeClass('border-red-500');
            }
        });
        
        if (!valid) {
            e.preventDefault();
            console.error('Validation failed on form submission.');
        } else {
            console.log('Form validated successfully.');
        }
    });
});
