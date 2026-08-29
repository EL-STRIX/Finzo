document.addEventListener('DOMContentLoaded', function() {
  if (typeof fetchUserSession === 'function') fetchUserSession();
  if (typeof buildSidebar === 'function') buildSidebar('security');
  if (typeof buildNavbar === 'function') buildNavbar('Account & Privacy', 'Security & 2FA');
});
