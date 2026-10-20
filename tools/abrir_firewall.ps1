# Ejecutar como Administrador (clic derecho > Ejecutar con PowerShell como administrador)
# Abre los puertos 80 (HTTP) y 443 (HTTPS) del Firewall de Windows para Apache/XAMPP.

$ErrorActionPreference = 'Stop'

if (-not ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Host "ERROR: este script requiere permisos de administrador." -ForegroundColor Red
    Write-Host "Abre PowerShell como Administrador y vuelve a ejecutarlo." -ForegroundColor Yellow
    exit 1
}

foreach ($rule in @(
    @{ Name = 'XAMPP Apache 80 (HTTP)';  Port = 80 },
    @{ Name = 'XAMPP Apache 443 (HTTPS)'; Port = 443 }
)) {
    $existing = Get-NetFirewallRule -DisplayName $rule.Name -ErrorAction SilentlyContinue
    if ($existing) {
        Write-Host "Ya existe: $($rule.Name)" -ForegroundColor DarkYellow
        continue
    }
    New-NetFirewallRule -DisplayName $rule.Name -Direction Inbound -Protocol TCP -LocalPort $rule.Port -Action Allow | Out-Null
    Write-Host "Creada: $($rule.Name) (puerto $($rule.Port))" -ForegroundColor Green
}

Write-Host ""
Write-Host "Listo. El carnet ahora es accesible desde otros dispositivos en la misma red." -ForegroundColor Green
$ips = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike '127.*' -and $_.IPAddress -notlike '169.254.*' }).IPAddress
foreach ($ip in $ips) {
    Write-Host "  http://$ip/verificar/<token>" -ForegroundColor Cyan
}
