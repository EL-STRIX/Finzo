$files = Get-ChildItem -Path "c:\xampp\htdocs\Finzo\resources\views\auth", "c:\xampp\htdocs\Finzo\resources\views\user" -Filter "*.html"

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    $originalContent = $content
    
    # Secure Login -> Secure Digital Banking Access
    $content = $content -replace "Secure Login", "Secure Digital Banking Access"
    
    # Create Account -> Open Your Finzo Banking Account
    $content = $content -replace "Create Account", "Open Your Finzo Banking Account"
    
    # Open an Account -> Open Your Finzo Banking Account
    $content = $content -replace "Open an Account", "Open Your Finzo Banking Account"
    
    # Verify -> Verify Your Identity (handling specific occurrences like ">Verify<" and "Verify Securely")
    $content = $content -replace ">Verify<", ">Verify Your Identity<"
    $content = $content -replace "Verify Securely", "Verify Your Identity"
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "Updated $($file.Name)"
    }
}
