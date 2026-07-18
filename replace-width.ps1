$files = Get-ChildItem -Path "c:\xampp\htdocs\Finzo\resources\views\auth", "c:\xampp\htdocs\Finzo\resources\views\user" -Filter "*.html"

foreach ($file in $files) {
    $authPages = @("login.html", "staff-login.html", "admin-login.html", "register.html", "forgot-password.html", "otp-verification.html", "reset-password.html", "change-password.html", "account-pending.html", "account-rejected.html", "complete-profile.html")
    
    if ($authPages -contains $file.Name) {
        $content = Get-Content $file.FullName -Raw
        $originalContent = $content
        
        # Ensure Equal Width (480px)
        $content = $content -replace 'max-w-\[440px\]', 'max-w-[480px]'
        $content = $content -replace 'max-w-\[560px\]', 'max-w-[480px]'
        
        if ($content -ne $originalContent) {
            Set-Content -Path $file.FullName -Value $content -NoNewline
            Write-Host "Updated width in $($file.Name)"
        }
    }
}
