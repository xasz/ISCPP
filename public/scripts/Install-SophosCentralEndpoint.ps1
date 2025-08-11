
# ----- Configruation -----
$ErrorActionPreference = "Stop"

# The custom field in NinjaRMM where the Sophos Central Endpoint installer URL is stored
$InstallerCustomField = 'sophosCentralEndpointWindowsInstallerUrl' 

# The name of the software to match against installed applications - hopefully never changes
$softwareMatchName = "Sophos Endpoint Agent"

# Installer parameters
$parameters = @()
$parameters += "--quiet"
$parameters += "--products=antivirus,intercept"

# here is place for you custom parameters if you need


# ----- Script -----

Write-Host "Testing if Sophos Central Endpoint is already installed..."

if((Get-ItemProperty HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\* | Where-Object {$_.DisplayName -like "*$softwareMatchName*"} | Measure-Object).Count -gt 0 -or 
   (Get-ItemProperty HKLM:\Software\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\* | Where-Object {$_.DisplayName -like "*$softwareMatchName*"} | Measure-Object).Count -gt 0 ) {
  Write-Host "$softwareMatchName already present on the system. Exiting script."
  exit 0
}

$InstallerPath = $null

Write-Host "Fetching Sophos Central Endpoint installer"
try{
    $field = Get-NinjaProperty -Name $InstallerCustomField -Type Url
    if (-not $field) {
        throw "Custom field '$InstallerCustomField' not found or empty."
    }
    $targetPath = '{0}\SophosCentralEndpointInstaller.exe' -f $($env:TEMP)
    $response = Invoke-WebRequest -Uri $targetPath -OutFile $params.OutFile -UseBasicParsing -PassThru

    if(($response).StatusCode -ne 200){
        Write-Host "Failed to download the installer from $field. Status code: $($response.StatusCode)"
        exit 1
    }
    $InstallerPath = $targetPath
}
catch {
    Write-Host "Error retrieving Ninja property: $_"
    exit 1
}

Write-Host "Installing Sophos Central Endpoint..."
Start-Process -FilePath $InstallerPath -ArgumentList ($parameters -join " ")
Write-OITLog "Installation gestartet"
