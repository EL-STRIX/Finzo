# Run OWASP ZAP Baseline Scan via Docker
param (
    [string]$TargetUrl = "http://localhost:8080"
)

Write-Host "Starting OWASP ZAP Baseline Scan against $TargetUrl..."
Write-Host "This requires Docker to be running locally."

docker run -t owasp/zap2docker-stable zap-baseline.py -t $TargetUrl -r zap_report.html

Write-Host "ZAP Scan completed. Report saved to zap_report.html (inside container)."
Write-Host "For a local report, map a volume: docker run -v $(pwd):/zap/wrk/:rw -t owasp/zap2docker-stable zap-baseline.py -t $TargetUrl -r zap_report.html"
