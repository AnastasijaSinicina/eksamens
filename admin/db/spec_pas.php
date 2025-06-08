<?php

require_once 'con_db.php';

// Iegūst lietotāja datus pēc lietotājvārda
if (isset($_SESSION['lietotajvardsSIN'])) {
    $username = $_SESSION['lietotajvardsSIN'];
    $user_query = "SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $user_stmt = $savienojums->prepare($user_query);
    $user_stmt->bind_param("s", $username);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_stmt->close();
}

// Pārbauda vai forma ir iesniegta un apstrādā pielāgotā pasūtījuma izveidi
if (isset($_POST['submit_custom_order'])) {
    // Definē obligātos laukus
    $required_fields = [
        'forma' => 'Forma',
        'audums' => 'Audums',
        'malu_figura' => 'Mālu figūra',
        'dekorejums1' => 'Dekorējums 1',
        'vards' => 'Vārds',
        'uzvards' => 'Uzvārds',
        'epasts' => 'E-pasts',
        'talrunis' => 'Tālrunis',
        'daudzums' => 'Daudzums'
    ];
    
    $errors = [];
    
    // Validē obligātos laukus
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || $_POST[$field] === '') {
            $errors[] = "Lauks '$label' ir obligāts";
        }
    }
    
    // Validē e-pasta formātu
    if (!empty($_POST['epasts']) && !filter_var($_POST['epasts'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Nepareizs e-pasta formāts";
    }
    
    // Validē daudzumu - jābūt pozitīvam skaitlim
    if (!empty($_POST['daudzums']) && (int)$_POST['daudzums'] < 1) {
        $errors[] = "Daudzums jābūt vismaz 1";
    }
    
    // Ja nav validācijas kļūdu, turpina ar datu saglabāšanu
    if (empty($errors)) {
        try {
            // Sāk transakriju
            $savienojums->autocommit(FALSE);
            
            // Sagatavo datus ierakstīšanai datubāzē
            $order_data = [
                'vards' => htmlspecialchars($_POST['vards']),
                'uzvards' => htmlspecialchars($_POST['uzvards']),
                'epasts' => htmlspecialchars($_POST['epasts']),
                'talrunis' => htmlspecialchars($_POST['talrunis']),
                'forma' => htmlspecialchars($_POST['forma']),
                'audums' => htmlspecialchars($_POST['audums']),
                'malu_figura' => htmlspecialchars($_POST['malu_figura'] ?? ''),
                'dekorejums1' => htmlspecialchars($_POST['dekorejums1'] ?? ''),
                'daudzums' => intval($_POST['daudzums']),
                'piezimes' => htmlspecialchars($_POST['piezimes'])
            ];
            
            // Ievieto pielāgoto pasūtījumu datubāzē
            $insert_query = "INSERT INTO sparkly_spec_pas 
                            (lietotajs_id, vards, uzvards, epasts, talrunis, 
                             forma, audums, malu_figura, dekorejums1, 
                             daudzums, piezimes, statuss, datums) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Iesniegts', CURRENT_TIMESTAMP)";
            
            $stmt = $savienojums->prepare($insert_query);
            
            if (!$stmt) {
                throw new Exception("Datubāzes sagatavošanas kļūda: " . $savienojums->error);
            }
            
            // Saista parametrus
            $stmt->bind_param("issssssssis", 
                $user['id_lietotajs'],
                $order_data['vards'], 
                $order_data['uzvards'], 
                $order_data['epasts'], 
                $order_data['talrunis'], 
                $order_data['forma'], 
                $order_data['audums'], 
                $order_data['malu_figura'], 
                $order_data['dekorejums1'], 
                $order_data['daudzums'], 
                $order_data['piezimes']
            );
            
            // Izpilda vaicājumu
            if (!$stmt->execute()) {
                throw new Exception("Neizdevās ievietot pielāgoto pasūtījumu: " . $stmt->error);
            }
            
            $custom_order_id = $savienojums->insert_id;
            error_log("Pielāgots pasūtījums izveidots ar ID: " . $custom_order_id);
            $stmt->close();
            
            // Atjaunina lietotāja pielāgoto pasūtījumu skaitu
            $update_custom_order_count = $savienojums->prepare("UPDATE lietotaji_sparkly SET spec_pas_skaits = spec_pas_skaits + 1 WHERE id_lietotajs = ?");
            $update_custom_order_count->bind_param("i", $user['id_lietotajs']);
            
            if (!$update_custom_order_count->execute()) {
                throw new Exception("Neizdevās atjaunināt lietotāja pielāgoto pasūtījumu skaitu: " . $update_custom_order_count->error);
            }
            
            error_log("Lietotāja pielāgoto pasūtījumu skaits palielināts lietotājam ar ID: " . $user['id_lietotajs']);
            $update_custom_order_count->close();
            
            // Apstiprina transakriju
            $savienojums->commit();
            $savienojums->autocommit(TRUE);
            
            // Iestatīta veiksmīga ziņa un pāradresē uz profila lapu
            $_SESSION['pazinojums'] = "Jūsu pielāgotā produkta pieprasījums ir veiksmīgi nosūtīts! Mēs sazināsimies ar jums drīzumā.";
            header("Location: profils.php?tab=orders&success=1");
            exit();
            
        } catch (Exception $e) {
            // Atceļ transakriju kļūdas gadījumā
            $savienojums->rollback();
            $savienojums->autocommit(TRUE);
            
            error_log("Kļūda izveidojot pielāgoto pasūtījumu: " . $e->getMessage());
            
            // Aizvēr statements ja tie eksistē
            if (isset($stmt)) {
                $stmt->close();
            }
            if (isset($update_custom_order_count)) {
                $update_custom_order_count->close();
            }
            
            // Iestatīta kļūdas ziņa
            $error_message = "Kļūda nosūtot pieprasījumu: " . $e->getMessage();
        }
    } else {
        // Ja ir validācijas kļūdas, parāda tās
        $error_message = "Lūdzu izlabojiet šādas kļūdas:<br>• " . implode("<br>• ", $errors);
    }
}

?>