# Renaming Folders
$base = "c:\xampp\htdocs\Finzo\docs\01-Architecture"

Rename-Item -Path "$base\09-Governance" -NewName "10-Governance" -ErrorAction SilentlyContinue
Rename-Item -Path "$base\08-Deployment" -NewName "09-Deployment" -ErrorAction SilentlyContinue
Rename-Item -Path "$base\07-Security" -NewName "08-Security" -ErrorAction SilentlyContinue
Rename-Item -Path "$base\06-Infrastructure" -NewName "07-Infrastructure" -ErrorAction SilentlyContinue
Rename-Item -Path "$base\05-API" -NewName "06-API" -ErrorAction SilentlyContinue
Rename-Item -Path "$base\04-Database" -NewName "05-Database" -ErrorAction SilentlyContinue
Rename-Item -Path "$base\03-Backend" -NewName "04-Backend" -ErrorAction SilentlyContinue
Rename-Item -Path "$base\02-Frontend" -NewName "03-Frontend" -ErrorAction SilentlyContinue
Rename-Item -Path "$base\diagrams" -NewName "11-Diagrams" -ErrorAction SilentlyContinue

# Moving Root Architecture Files to README.md
Move-Item -Path "$base\System-Architecture.md" -Destination "$base\01-System\README.md" -Force -ErrorAction SilentlyContinue
Move-Item -Path "$base\Software-Architecture.md" -Destination "$base\02-Software\README.md" -Force -ErrorAction SilentlyContinue
Move-Item -Path "$base\Frontend-Architecture.md" -Destination "$base\03-Frontend\README.md" -Force -ErrorAction SilentlyContinue
Move-Item -Path "$base\Backend-Architecture.md" -Destination "$base\04-Backend\README.md" -Force -ErrorAction SilentlyContinue
Move-Item -Path "$base\Database-Architecture.md" -Destination "$base\05-Database\README.md" -Force -ErrorAction SilentlyContinue
Move-Item -Path "$base\API-Architecture.md" -Destination "$base\06-API\README.md" -Force -ErrorAction SilentlyContinue
Move-Item -Path "$base\Infrastructure-Architecture.md" -Destination "$base\07-Infrastructure\README.md" -Force -ErrorAction SilentlyContinue
Move-Item -Path "$base\Security-Architecture.md" -Destination "$base\08-Security\README.md" -Force -ErrorAction SilentlyContinue
Move-Item -Path "$base\Deployment-Architecture.md" -Destination "$base\09-Deployment\README.md" -Force -ErrorAction SilentlyContinue

# Deleting Redundant Files
Remove-Item -Path "$base\Application-Lifecycle.md" -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$base\Architecture-Principles.md" -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$base\Coding-Standards.md" -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$base\Component-Diagrams.md" -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$base\Sequence-Diagrams.md" -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$base\Data-Flow.md" -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$base\Documentation-Standards.md" -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$base\Scalability.md" -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$base\Overview.md" -Force -ErrorAction SilentlyContinue

Write-Host "Restructuring Complete."
