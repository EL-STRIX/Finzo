// Adjusting progress bar dynamically if required
function updateProgress(step) {
    const steps = ['25% Complete', '50% Complete', '75% Complete', '100% Complete'];
    const widths = ['25%', '50%', '75%', '100%'];
    document.getElementById('progress-text').innerText = steps[step-1];
    document.getElementById('progress-bar').style.width = widths[step-1];
}
