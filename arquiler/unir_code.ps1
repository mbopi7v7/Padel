# Script PowerShell para unir código PHP y JS recursivamente
# Guarda este código como UnirCodigo.ps1 y ejecútalo en la carpeta raíz del proyecto

# Archivo de salida en la carpeta actual
$salida = Join-Path (Get-Location) "todo_codigo.txt"

# Limpiar archivo previo si existe
if (Test-Path $salida) {
    Remove-Item $salida
}

# Buscar y concatenar archivos .php y .js
Get-ChildItem -Recurse -Include *.php, *.js | ForEach-Object {
    Add-Content -Path $salida -Value "===== ARCHIVO: $($_.FullName) ====="
    Get-Content $_ | Add-Content -Path $salida
    Add-Content -Path $salida -Value "`n`n"
}

Write-Host "Código fuente concatenado en: $salida"
