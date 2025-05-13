<?php
    // Include admin header
    require 'header.php';
    
    // Database connection
    require 'db/con_db.php';

    // Handle figure deletion
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $sql = "DELETE FROM sparkly_malu_figura WHERE id_malu_figura = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('success', 'Veiksmīgi!', 'Malu figūra ir izdzēsta.');
                    });
                  </script>";
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('error', 'Kļūda!', 'Neizdevās dzēst malu figūru.');
                    });
                  </script>";
        }
        $stmt->close();
    }

    // Handle figure addition/update
    if (isset($_POST['submit'])) {
        $nosaukums = $_POST['nosaukums'];
        
        // Check if it's an update or add operation
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update existing figure
            $id = $_POST['id'];
            
            // Initialize SQL parts
            $sql_parts = ["nosaukums = ?"];
            $params = [$nosaukums];
            $types = "s";
            
            // Handle image update if provided
            if (isset($_FILES["attels"]) && $_FILES["attels"]['error'] == 0) {
                $image = file_get_contents($_FILES["attels"]['tmp_name']);
                $sql_parts[] = "attels = ?";
                $params[] = $image;
                $types .= "b"; // binary data
            }
            
            // Add ID parameter
            $params[] = $id;
            $types .= "i";
            
            // Create final SQL query
            $sql = "UPDATE sparkly_malu_figura SET " . implode(", ", $sql_parts) . " WHERE id_malu_figura = ?";
            $stmt = $savienojums->prepare($sql);
            
            // Dynamically bind parameters
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('success', 'Veiksmīgi!', 'Malu figūra ir atjaunināta.');
                        });
                      </script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Neizdevās atjaunināt malu figūru: " . $stmt->error . "');
                        });
                      </script>";
            }
            $stmt->close();
            
        } else {
            // Add new figure
            // Check if image is provided
            if (isset($_FILES['attels']) && $_FILES['attels']['error'] == 0) {
                $image = file_get_contents($_FILES['attels']['tmp_name']);
                
                $sql = "INSERT INTO sparkly_malu_figura (nosaukums, attels) VALUES (?, ?)";
                $stmt = $savienojums->prepare($sql);
                $stmt->bind_param("sb", $nosaukums, $image);
                
                if ($stmt->execute()) {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                showNotification('success', 'Veiksmīgi!', 'Malu figūra ir pievienota.');
                            });
                          </script>";
                } else {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                showNotification('error', 'Kļūda!', 'Neizdevās pievienot malu figūru: " . $stmt->error . "');
                            });
                          </script>";
                }
                $stmt->close();
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Lūdzu, augšupielādējiet attēlu.');
                        });
                      </script>";
            }
        }
        
        // Redirect to clear the form
        echo "<script>window.location.href = 'figuras.php';</script>";
    }

    // Get figure data for editing
    $editData = null;
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $sql = "SELECT * FROM sparkly_malu_figura WHERE id_malu_figura = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $editData = $result->fetch_assoc();
        }
        $stmt->close();
    }
?>

<main>
    <!-- Notification container -->
    <div class="notification-container">
        <div class="notification">
            <i class="fas fa-check-circle success"></i>
            <h3>Veiksmīgi!</h3>
            <p>Darbība veiksmīgi izpildīta.</p>
        </div>
    </div>

    <section class="admin-content">
        <h1>Malu Figūru pārvaldība</h1>
        
        <!-- Figures Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošās malu figūras</h2>
                <a href="figuras.php?action=add" class="btn add-btn"><i class="fas fa-plus"></i></a>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nosaukums</th>
                            <th>Attēls</th>
                            <th>Datums</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody>     
                        <?php
                            // Fetch all figures
                            $sql = "SELECT * FROM sparkly_malu_figura ORDER BY id_malu_figura";
                            $result = $savienojums->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['id_malu_figura']}</td>";
                                    echo "<td>{$row['nosaukums']}</td>";
                                    
                                    // Display image
                                    if (!empty($row['attels'])) {
                                        echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['attels']) . "' alt='{$row['nosaukums']}' width='50'></td>";
                                    } else {
                                        echo "<td>Nav attēla</td>";
                                    }
                                    
                                    // Get date
                                    $datums = isset($row['datums']) ? $row['datums'] : date('Y-m-d H:i:s');
                                    echo "<td>{$datums}</td>";
                                    
                                    echo "<td class='action-buttons'>";
                                    echo "<a href='figuras.php?edit={$row['id_malu_figura']}' class='btn edit-btn'><i class='fas fa-edit'></i> Rediģēt</a>";
                                    echo "<a href='figuras.php?delete={$row['id_malu_figura']}' class='btn delete-btn' onclick='return confirm(\"Vai tiešām vēlaties dzēst šo malu figūru?\")'><i class='fas fa-trash-alt'></i> Dzēst</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='no-records'>Nav atrasta neviena malu figūra</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php 
        // Show the form only when adding or editing
        if (isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit'])): 
        ?>
        <!-- Form for adding/editing -->
        <div class="custom-form-container">
            <h2><?php echo $editData ? 'Rediģēt malu figūru' : 'Pievienot jaunu malu figūru'; ?></h2>
            <form class="custom-form" method="POST" enctype="multipart/form-data">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id_malu_figura']; ?>">
                <?php endif; ?>
                
                <div class="dropdown">
                    <div id="drop">
                        <label for="nosaukums">Nosaukums:</label>
                        <input type="text" id="nosaukums" name="nosaukums" value="<?php echo $editData ? $editData['nosaukums'] : ''; ?>" required>
                    </div>
                    
                    <div id="drop">
                        <label for="attels">Attēls:</label>
                        <?php if ($editData && !empty($editData["attels"])): ?>
                            <div class="current-image">
                                <p>Pašreizējais attēls:</p>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($editData["attels"]); ?>" alt="Malu figūras attēls" width="100">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="attels" name="attels" <?php echo (!$editData) ? 'required' : ''; ?>>
                        <small>Atbalstītie formāti: JPG, JPEG, PNG</small>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="submit" class="btn"><?php echo $editData ? 'Atjaunināt malu figūru' : 'Pievienot malu figūru'; ?></button>
                    <a href="figuras.php" class="btn clear-btn">Atcelt</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
    </section>
</main>

<!-- JavaScript for notifications -->
<script>
    function showNotification(type, title, message) {
        const container = document.querySelector('.notification-container');
        const notification = document.querySelector('.notification');
        const icon = notification.querySelector('i');
        const titleElement = notification.querySelector('h3');
        const messageElement = notification.querySelector('p');
        
        // Set notification content
        icon.className = type === 'success' ? 'fas fa-check-circle success' : 'fas fa-exclamation-circle error';
        titleElement.textContent = title;
        messageElement.textContent = message;
        
        // Add class based on type
        notification.className = 'notification ' + type;
        
        // Show the notification
        container.style.display = 'block';
        
        // Hide after 5 seconds
        setTimeout(() => {
            container.style.display = 'none';
        }, 5000);
    }
</script>

