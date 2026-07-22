# Push local HEROHub changes to GitHub and trigger Hostinger deploy.
# Usage: .\scripts\push-and-deploy.ps1 "Your commit message"

param(
    [Parameter(Mandatory = $true)]
    [string]$Message
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent $PSScriptRoot

Push-Location $ProjectRoot

try {
    git status
    git add -A

    $pending = git diff --cached --name-only
    if (-not $pending) {
        Write-Host "No staged changes to commit."
    } else {
        git commit -m $Message
    }

    git push origin main

    $gh = Get-Command gh -ErrorAction SilentlyContinue
    if ($gh) {
        gh workflow run deploy.yml --ref main
        Write-Host "Deploy workflow triggered. Check: https://github.com/matlobalam640/HEROHub/actions"
    } else {
        Write-Host "GitHub CLI not found in PATH. Deploy will run from the push to main if secrets are configured."
    }
}
finally {
    Pop-Location
}
