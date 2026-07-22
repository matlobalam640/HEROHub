# One-time setup: add Hostinger SSH credentials to GitHub Actions secrets.
# Prerequisite: run `gh auth login` first.
#
# Usage:
#   .\scripts\setup-github-secrets.ps1 -SshPassword 'your-ssh-password'

param(
    [string]$SshHost = "92.113.18.69",
    [string]$SshUser = "u407096753",
    [string]$SshPassword,
    [string]$SshPort = "65002"
)

$ErrorActionPreference = "Stop"
$repo = "matlobalam640/HEROHub"

function Set-RepoSecret {
    param(
        [string]$Name,
        [string]$Value
    )

    if (-not $Value) {
        throw "Missing value for secret: $Name"
    }

    $Value | gh secret set $Name --repo $repo
    Write-Host "Set secret: $Name"
}

if (-not $SshPassword) {
    $secure = Read-Host "SSH password" -AsSecureString
    $SshPassword = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    )
}

Write-Host "Setting GitHub Actions secrets for $repo ..."

Set-RepoSecret -Name "SSH_HOST" -Value $SshHost
Set-RepoSecret -Name "SSH_USERNAME" -Value $SshUser
Set-RepoSecret -Name "SSH_PASSWORD" -Value $SshPassword
Set-RepoSecret -Name "SSH_PORT" -Value $SshPort

Write-Host "Done. Trigger deploy: gh workflow run deploy.yml --repo $repo"
