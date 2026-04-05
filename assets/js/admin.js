/**
 * Finzo Admin Logic
 */

$(document).ready(function() {
    // Admin specific toggles or actions
    console.log("Admin module loaded.");
    
    // Simulate approval/rejection blocks
    $('button:contains("Approve")').on('click', function(e) {
        e.preventDefault();
        const row = $(this).closest('tr');
        row.find('.status-badge').removeClass('bg-yellow-100 text-yellow-800').addClass('bg-green-100 text-green-800').text('Approved');
        $(this).parent().html('<span class="text-sm text-gray-500">Processed</span>');
    });

    $('button:contains("Reject")').on('click', function(e) {
        e.preventDefault();
        const row = $(this).closest('tr');
        row.find('.status-badge').removeClass('bg-yellow-100 text-yellow-800').addClass('bg-red-100 text-red-800').text('Rejected');
        $(this).parent().html('<span class="text-sm text-gray-500">Processed</span>');
    });
});
