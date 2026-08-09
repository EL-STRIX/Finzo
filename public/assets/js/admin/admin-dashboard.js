function initAdminDashboard() {
    const clockElement = document.getElementById("header-live-clock");
    if (clockElement) {
        setInterval(function() {
            var d = new Date();
            clockElement.textContent = d.toLocaleTimeString("en-IN", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: true
            });
        }, 1000);
        
        // Initial call to prevent 1-second delay
        var initialD = new Date();
        clockElement.textContent = initialD.toLocaleTimeString("en-IN", {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: true
        });
    }
}

document.addEventListener('DOMContentLoaded', initAdminDashboard);
