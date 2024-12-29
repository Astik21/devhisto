try {
# Définir le chemin du dossier à analyser
$folderPath = (Resolve-Path "C:\\Users\\rpard\\source\\repos\\Astik21\\devhisto").Path

# Définir un chemin pour sauvegarder l'arborescence si nécessaire
$outputFile = (Resolve-Path "C:\\Users\\rpard\\source\\repos\\Astik21\\devhisto\\arborescence.txt").Path

# Définir une liste de dossiers et fichiers à exclure
$excludedFolders = @(".github", ".git", ".vs")
$excludedFiles = @("arborescence.txt", "foldertree.ps1")

# Vérifier si le dossier existe
if (!(Test-Path -Path $folderPath)) {
    Write-Host "Le chemin spécifié n'existe pas : $folderPath" -ForegroundColor Red
    exit
}

# Fonction récursive pour construire l'arborescence
function Get-FolderTree {
    param (
        [string]$Path,
        [int]$Level = 0
    )

    # Normaliser le chemin pour éviter les doubles barres
    $Path = (Resolve-Path $Path).Path

    # Calculer le chemin relatif uniquement si valide
    if ($Path.StartsWith($folderPath)) {
        $relativePath = $Path.Substring($folderPath.Length).TrimStart("\\")
    } else {
        $relativePath = $Path
    }

    # Vérifier si le dossier est exclu
    $folderName = [System.IO.Path]::GetFileName($Path)
    if ($folderName -in $excludedFolders) {
        return
    }

    # Ajouter le nom du dossier courant avec une mise en forme visuelle
    ("|" + ("  |" * $Level) + "--> $relativePath") | Out-File -Append -FilePath $outputFile

    # Récupérer tous les éléments dans le dossier courant
    $items = Get-ChildItem -Path $Path -Force | Sort-Object PSIsContainer, Name

    foreach ($item in $items) {
        if ($item.PSIsContainer) {
            # Si c'est un dossier, appeler la fonction récursive
            Get-FolderTree -Path $item.FullName -Level ($Level + 1)
        } elseif ($item.Name -notin $excludedFiles) {
            # Ajouter les fichiers avec une mise en forme si non exclus
            ("|" + ("  |" * ($Level + 1)) + "--> $($item.Name)") | Out-File -Append -FilePath $outputFile
        }
    }
}

# Initialiser le fichier de sortie
"Arborescence du dossier :" > $outputFile

# Générer l'arborescence
Get-FolderTree -Path $folderPath

Write-Host "L'arborescence a été générée et sauvegardée dans : $outputFile" -ForegroundColor Green
# Empêcher la fenêtre de se fermer à la fin
Write-Host "Appuyez sur une touche pour fermer..." -ForegroundColor Yellow
Read-Host | Out-Null

} catch {
    Write-Error "Une erreur s'est produite : $_"
    Write-Host "Appuyez sur une touche pour continuer..." -ForegroundColor Yellow
    Read-Host | Out-Null
}
