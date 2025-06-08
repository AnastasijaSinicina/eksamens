<?php



try {
    // Include database connection
    if (!file_exists('con_db.php')) {
        throw new Exception('Database configuration file not found');
    }
    
    require_once 'con_db.php';
    
    if (!isset($savienojums) || !$savienojums) {
        throw new Exception('Database connection not established');
    }
    
    // Check database connection
    if ($savienojums->connect_error) {
        throw new Exception('Database connection failed: ' . $savienojums->connect_error);
    }

    // Fetch all users (admin and moder only) with creator and editor info
    if (isset($_GET['fetch_users'])) {
        try {
            $sql = "SELECT u.id_lietotajs, u.vards, u.uzvards, u.lietotajvards, u.epasts, u.loma, u.datums, u.red_dat,
                           creator.lietotajvards as izveidots_liet_username,
                           creator.vards as izveidots_liet_first_name,
                           creator.uzvards as izveidots_liet_last_name,
                           editor.lietotajvards as red_liet_username,
                           editor.vards as red_liet_first_name,
                           editor.uzvards as red_liet_last_name
                    FROM lietotaji_sparkly u
                    LEFT JOIN lietotaji_sparkly creator ON u.izveidots_liet = creator.id_lietotajs
                    LEFT JOIN lietotaji_sparkly editor ON u.red_liet = editor.id_lietotajs
                    WHERE u.loma IN ('admin', 'moder') 
                    ORDER BY u.loma, u.id_lietotajs";
            $result = $savienojums->query($sql);

            if ($result === false) {
                throw new Exception('Query execution failed: ' . $savienojums->error);
            }

            $users = [];
            while ($row = $result->fetch_assoc()) {
                // Format creator name
                if (!empty($row['izveidots_liet_first_name']) && !empty($row['izveidots_liet_last_name'])) {
                    $row['izveidots_liet_name'] = $row['izveidots_liet_first_name'] . ' ' . $row['izveidots_liet_last_name'];
                } else if (!empty($row['izveidots_liet_username'])) {
                    $row['izveidots_liet_name'] = $row['izveidots_liet_username'];
                } else {
                    $row['izveidots_liet_name'] = 'Nav norādīts';
                }

                // Format editor name
                if (!empty($row['red_liet_first_name']) && !empty($row['red_liet_last_name'])) {
                    $row['red_liet_name'] = $row['red_liet_first_name'] . ' ' . $row['red_liet_last_name'];
                } else if (!empty($row['red_liet_username'])) {
                    $row['red_liet_name'] = $row['red_liet_username'];
                } else {
                    $row['red_liet_name'] = '';
                }
                
                $users[] = $row;
            }

            // Clean any unwanted output
            ob_clean();
            echo json_encode($users, JSON_UNESCAPED_UNICODE);
            exit;
            
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch users: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Fetch single user by ID
    if (isset($_GET['fetch_user_single']) && isset($_GET['id'])) {
        try {
            $id = intval($_GET['id']);
            
            if ($id <= 0) {
                throw new Exception('Invalid user ID provided');
            }
            
            $sql = "SELECT id_lietotajs, vards, uzvards, lietotajvards, epasts, loma FROM lietotaji_sparkly WHERE id_lietotajs = ? AND loma IN ('admin', 'moder')";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $savienojums->error);
            }
            
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute statement: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $user = null;
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
            }
            
            $stmt->close();
            
            // Clean any unwanted output
            ob_clean();
            echo json_encode($user, JSON_UNESCAPED_UNICODE);
            exit;
            
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch user: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Fetch all clients with editor info (no creator info needed as per requirement)
    if (isset($_GET['fetch_clients'])) {
        try {
            $sql = "SELECT c.id_lietotajs, c.vards, c.uzvards, c.lietotajvards, c.epasts, c.datums, c.red_dat,
                           editor.lietotajvards as red_liet_username,
                           editor.vards as red_liet_first_name,
                           editor.uzvards as red_liet_last_name
                    FROM lietotaji_sparkly c
                    LEFT JOIN lietotaji_sparkly editor ON c.red_liet = editor.id_lietotajs
                    WHERE c.loma = 'klients' 
                    ORDER BY c.id_lietotajs";
            $result = $savienojums->query($sql);

            if ($result === false) {
                throw new Exception('Query execution failed: ' . $savienojums->error);
            }

            $clients = [];
            while ($row = $result->fetch_assoc()) {
                // Format editor name
                if (!empty($row['red_liet_first_name']) && !empty($row['red_liet_last_name'])) {
                    $row['red_liet_name'] = $row['red_liet_first_name'] . ' ' . $row['red_liet_last_name'];
                } else if (!empty($row['red_liet_username'])) {
                    $row['red_liet_name'] = $row['red_liet_username'];
                } else {
                    $row['red_liet_name'] = '';
                }
                
                $clients[] = $row;
            }

            // Clean any unwanted output
            ob_clean();
            echo json_encode($clients, JSON_UNESCAPED_UNICODE);
            exit;
            
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch clients: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Fetch single client by ID
    if (isset($_GET['fetch_client_single']) && isset($_GET['id'])) {
        try {
            $id = intval($_GET['id']);
            
            if ($id <= 0) {
                throw new Exception('Invalid client ID provided');
            }
            
            $sql = "SELECT id_lietotajs, vards, uzvards, lietotajvards, epasts FROM lietotaji_sparkly WHERE id_lietotajs = ? AND loma = 'klients'";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $savienojums->error);
            }
            
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute statement: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $client = null;
            
            if ($result->num_rows > 0) {
                $client = $result->fetch_assoc();
            }
            
            $stmt->close();
            
            // Clean any unwanted output
            ob_clean();
            echo json_encode($client, JSON_UNESCAPED_UNICODE);
            exit;
            
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch client: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // If no valid action is found
    ob_clean();
    http_response_code(400);
    echo json_encode(['error' => 'No valid action specified'], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($savienojums) && $savienojums) {
        $savienojums->close();
    }
}
?>