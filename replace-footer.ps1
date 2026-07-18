$files = Get-ChildItem -Path "c:\xampp\htdocs\Finzo\resources\views\auth", "c:\xampp\htdocs\Finzo\resources\views\user" -Filter "*.html"

$globalFooterHtml = @"
      <!-- Global Footer -->
      <div class="auth-global-footer">
        <ul>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">enhanced_encryption</span> 256-bit Encryption</span></li>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">verified_user</span> Secure Digital Banking Access</span></li>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">gpp_good</span> Privacy Protected</span></li>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">credit_card</span> PCI DSS Compliant</span></li>
        </ul>
      </div>
"@

foreach ($file in $files) {
    # Only target authentication pages (we know the 11 from the prompt)
    $authPages = @("login.html", "staff-login.html", "admin-login.html", "register.html", "forgot-password.html", "otp-verification.html", "reset-password.html", "change-password.html", "account-pending.html", "account-rejected.html", "complete-profile.html")
    
    if ($authPages -contains $file.Name) {
        $content = Get-Content $file.FullName -Raw
        $originalContent = $content
        
        # Replace existing Global Footer with the standard one
        $content = $content -replace '(?s)<!-- Global Footer -->.*?</div>\s*</div>', "$globalFooterHtml`n    </div>"
        
        # Replace duration-500 with duration-300
        $content = $content -replace "duration-500", "duration-300"
        $content = $content -replace "duration-700", "duration-300"
        
        if ($content -ne $originalContent) {
            Set-Content -Path $file.FullName -Value $content -NoNewline
            Write-Host "Unified footer and durations in $($file.Name)"
        }
    }
}
