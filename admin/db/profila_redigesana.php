<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pārbauda, vai lietotājs ir pieslēdzies
if (!isset($_SESSION['lietotajvardsSIN'])) {
    header("Location: ../../login.php");
    exit();
}

require_once "con_db.php";

$lietotajvards = $_SESSION['lietotajvardsSIN'];

// Apstrādā formas nosūtīšanu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Iegūst lietotāja ID
    $query = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("s", $lietotajvards);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        $_SESSION['pazinojums'] = "Lietotājs nav atrasts!";
        header("Location: ../../profils.php");
        exit();
    }
    
    $user_id = $user['id_lietotajs'];
    
    try {
        if (isset($_POST['saglabat'])) {
            // Apstrādā parastu profila atjaunināšanu
            $vards = trim($_POST['vards']);
            $uzvards = trim($_POST['uzvards']);
            $epasts = trim($_POST['epasts']);
            
            // Validate inputs
            if (empty($vards) || empty($uzvards) || empty($epasts)) {
                $_SESSION['pazinojums'] = "Visi lauki ir obligāti!";
                header("Location: ../../profils.php");
                exit();
            }
            
            if (!filter_var($epasts, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['pazinojums'] = "Nederīgs e-pasta formāts!";
                header("Location: ../../profils.php");
                exit();
            }
            
            $update_query = "UPDATE lietotaji_sparkly SET vards = ?, uzvards = ?, epasts = ? WHERE id_lietotajs = ?";
            $update_stmt = $savienojums->prepare($update_query);
            $update_stmt->bind_param("sssi", $vards, $uzvards, $epasts, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Profils veiksmīgi atjaunināts!";
            } else {
                $_SESSION['pazinojums'] = "Kļūda! Neizdevās atjaunināt profilu: " . $savienojums->error;
                header("Location: ../../profils.php");
                exit();
            }
            
            // Process image upload if present
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadedFile = $_FILES['profile_image'];
                
                // Pārbauda faila tipu
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mimeType, $allowedTypes)) {
                    $_SESSION['pazinojums'] = "Kļūda! Atļauti tikai JPEG, JPG, PNG un GIF formāta attēli.";
                    header("Location: ../../profils.php");
                    exit();
                }
                
                // Pārbauda faila izmēru (maks. 5MB)
                if ($uploadedFile['size'] > 5 * 1024 * 1024) {
                    $_SESSION['pazinojums'] = "Kļūda! Attēla izmērs nedrīkst pārsniegt 5MB.";
                    header("Location: ../../profils.php");
                    exit();
                }
                
                // Apstrādā attēlu
                $imageData = processImage($uploadedFile['tmp_name'], $mimeType);
                
                if ($imageData === false) {
                    $_SESSION['pazinojums'] = "Kļūda! Neizdevās apstrādāt attēlu.";
                    header("Location: ../../profils.php");
                    exit();
                }
                
                // Atjaunina datubāzi ar jauno attēlu
                $image_query = "UPDATE lietotaji_sparkly SET foto = ? WHERE id_lietotajs = ?";
                $image_stmt = $savienojums->prepare($image_query);
                $image_stmt->bind_param("si", $imageData, $user_id);
                
                if ($image_stmt->execute()) {
                    $success_message = "Profils un attēls veiksmīgi atjaunināti!";
                } else {
                    $_SESSION['pazinojums'] = "Profils atjaunināts, bet neizdevās saglabāt attēlu: " . $savienojums->error;
                    header("Location: ../../profils.php");
                    exit();
                }
                $image_stmt->close();
            }
            
            $_SESSION['pazinojums'] = $success_message ?? "Profils veiksmīgi atjaunināts!";
            $update_stmt->close();
        }
        
        if (isset($_POST['delete_image'])) {
            // Apstrādā profila attēla dzēšanu
            $delete_query = "UPDATE lietotaji_sparkly SET foto = NULL WHERE id_lietotajs = ?";
            $delete_stmt = $savienojums->prepare($delete_query);
            $delete_stmt->bind_param("i", $user_id);
            
            if ($delete_stmt->execute()) {
                $_SESSION['pazinojums'] = "Profila attēls veiksmīgi dzēsts!";
            } else {
                $_SESSION['pazinojums'] = "Kļūda! Neizdevās dzēst profila attēlu: " . $savienojums->error;
            }
            $delete_stmt->close();
        }
        
    } catch (Exception $e) {
        $_SESSION['pazinojums'] = "Kļūda! " . $e->getMessage();
    }
    
    $stmt->close();
}

// Funkcija attēla apstrādei un izmēra maiņai
function processImage($imagePath, $mimeType) {
    $maxWidth = 400;
    $maxHeight = 400;
    
    // Izveido attēla resursu atkarībā no tipa
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $source = imagecreatefromjpeg($imagePath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($imagePath);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($imagePath);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    // Iegūst sākotnējos izmērus
    $originalWidth = imagesx($source);
    $originalHeight = imagesy($source);
    
    // Aprēķina jaunos izmērus
    $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
    $newWidth = round($originalWidth * $ratio);
    $newHeight = round($originalHeight * $ratio);
    
    // Izveido jaunu attēlu
    $resized = imagecreatetruecolor($newWidth, $newHeight);
    
    // Saglabā caurspīdīgumu PNG failiem
    if ($mimeType === 'image/png') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefill($resized, 0, 0, $transparent);
    }
    
    // Maina attēla izmēru
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
    
    // Izvada uz rindu
    ob_start();
    switch ($mimeType) {
        case 'image/png':
            imagepng($resized, null, 9);
            break;
        case 'image/gif':
            imagegif($resized);
            break;
        default:
            imagejpeg($resized, null, 90);
    }
    $imageData = ob_get_clean();
    
    // Iztīra resursus
    imagedestroy($source);
    imagedestroy($resized);
    
    return $imageData;
}

// Novirza atpakaļ uz iepriekšējo lapu
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../../profils.php'));
exit();
?>