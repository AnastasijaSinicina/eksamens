<?php
    // Include admin header
    require 'header.php';
    
    // Database connection
    require 'db/con_db.php';

    // Handle product deletion
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $sql = "DELETE FROM produkcija_sprarkly WHERE id_bumba = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('success', 'Veiksmīgi!', 'Produkts ir izdzēsts.');
                    });
                  </script>";
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('error', 'Kļūda!', 'Neizdevās dzēst produktu.');
                    });
                  </script>";
        }
        $stmt->close();
    }

    // Check if dekorejums tables exist
    $check_dekorejums1_table_sql = "SHOW TABLES LIKE 'sparkly_dekorejums1'";
    $dekorejums1_table_exists = $savienojums->query($check_dekorejums1_table_sql)->num_rows > 0;
    
    $check_dekorejums2_table_sql = "SHOW TABLES LIKE 'sparkly_dekorejums2'";
    $dekorejums2_table_exists = $savienojums->query($check_dekorejums2_table_sql)->num_rows > 0;

    // Handle product addition/update
    if (isset($_POST['submit'])) {
        $forma = $_POST['forma'];
        $nosaukums = $_POST['nosaukums'];
        $audums_id = $_POST['audums_id'];
        $figura_id = $_POST['figura_id'];
        $dekorejums1_id = $_POST['dekorejums1_id'];
        $dekorejums2_id = $_POST['dekorejums2_id'];
        $cena = $_POST['cena'];
        
        // Check if we're updating or adding
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update existing product
            $id = $_POST['id'];
            
            // Initialize SQL query parts
            $sql_parts = [];
            $params = [];
            $types = "";
            
            // Basic fields
            $sql_parts[] = "forma = ?";
            $sql_parts[] = "nosaukums = ?";
            $sql_parts[] = "audums_id = ?";
            $sql_parts[] = "figura_id = ?";
            $sql_parts[] = "dekorejums1_id = ?";
            $sql_parts[] = "dekorejums2_id = ?";
            $sql_parts[] = "cena = ?";
            $params[] = $forma;
            $params[] = $nosaukums;
            $params[] = $audums_id;
            $params[] = $figura_id;
            $params[] = $dekorejums1_id;
            $params[] = $dekorejums2_id;
            $params[] = $cena;
            $types .= "isiiiisd"; // i for integer, s for strings, d for double (cena)
            
            // Handle image uploads if provided
            for ($i = 1; $i <= 3; $i++) {
                if (isset($_FILES["attels$i"]) && $_FILES["attels$i"]['error'] == 0) {
                    $image = file_get_contents($_FILES["attels$i"]['tmp_name']);
                    $sql_parts[] = "attels$i = ?";
                    $params[] = $image;
                    $types .= "b"; // binary data
                }
            }
            
            // Add ID parameter
            $params[] = $id;
            $types .= "i";
            
            // Create final SQL query
            $sql = "UPDATE produkcija_sprarkly SET " . implode(", ", $sql_parts) . " WHERE id_bumba = ?";
            $stmt = $savienojums->prepare($sql);
            
            // Dynamically bind parameters
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('success', 'Veiksmīgi!', 'Produkts ir atjaunināts.');
                        });
                      </script>";
                // Redirect to clear the form
                echo "<script>window.location.href = 'produkts.php';</script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Neizdevās atjaunināt produktu: " . $stmt->error . "');
                        });
                      </script>";
            }
            $stmt->close();
            
        } else {
            // Add new product
            // Check if all required images are provided
            if (isset($_FILES['attels1']) && $_FILES['attels1']['error'] == 0 &&
                isset($_FILES['attels2']) && $_FILES['attels2']['error'] == 0 &&
                isset($_FILES['attels3']) && $_FILES['attels3']['error'] == 0) {
                
                // Process images
                $attels1 = file_get_contents($_FILES['attels1']['tmp_name']);
                $attels2 = file_get_contents($_FILES['attels2']['tmp_name']);
                $attels3 = file_get_contents($_FILES['attels3']['tmp_name']);
                
                // Insert product with images
                $sql = "INSERT INTO produkcija_sprarkly (forma, nosaukums, audums_id, figura_id, dekorejums1_id, dekorejums2_id, attels1, attels2, attels3, cena) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $savienojums->prepare($sql);
                $stmt->bind_param("isiiiibbbd", $forma, $nosaukums, $audums_id, $figura_id, $dekorejums1_id, $dekorejums2_id, $attels1, $attels2, $attels3, $cena);
                
                if ($stmt->execute()) {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                showNotification('success', 'Veiksmīgi!', 'Produkts ir pievienots.');
                            });
                          </script>";
                    // Redirect to clear the form
                    echo "<script>window.location.href = 'produkts.php';</script>";
                } else {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                showNotification('error', 'Kļūda!', 'Neizdevās pievienot produktu: " . $stmt->error . "');
                            });
                          </script>";
                }
                $stmt->close();
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Lūdzu, augšupielādējiet visus trīs attēlus.');
                        });
                      </script>";
            }
        }
    }

    // Get product data for editing
    $editData = null;
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $sql = "SELECT * FROM produkcija_sprarkly WHERE id_bumba = ?";
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
        <h1>Produktu pārvaldība</h1>
        
        <!-- Product Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošie produkti</h2>
                <a href="produkts.php?action=add" class="btn add-btn"><i class="fas fa-plus"></i></a>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Attēls</th>
                            <th>Nosaukums</th>
                            <th>Forma</th>
                            <th>Audums</th>
                            <th>Malu figūra</th>
                            <th>Dekorējums (1)</th>
                            <th>Dekorējums (2)</th>
                            <th>Cena</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody>     
                        <?php
                            // Check if tables exist
                            $check_formas_table_sql = "SHOW TABLES LIKE 'sparkly_formas'";
                            $formas_table_exists = $savienojums->query($check_formas_table_sql)->num_rows > 0;
                            
                            $check_audumi_table_sql = "SHOW TABLES LIKE 'sparkly_audums'";
                            $audumi_table_exists = $savienojums->query($check_audumi_table_sql)->num_rows > 0;
                            
                            $check_malu_figura_table_sql = "SHOW TABLES LIKE 'sparkly_malu_figura'";
                            $malu_figura_table_exists = $savienojums->query($check_malu_figura_table_sql)->num_rows > 0;
                            
                            // Get all products with joined tables as needed
                            $sql = "SELECT p.*, ";
                            
                            // Add optional joins based on table existence
                            if ($formas_table_exists) {
                                $sql .= "f.forma AS forma_name, ";
                            } else {
                                $sql .= "p.forma AS forma_name, ";
                            }
                            
                            if ($audumi_table_exists) {
                                $sql .= "a.nosaukums AS audums_name, ";
                            } else {
                                $sql .= "p.audums_id AS audums_name, ";
                            }
                            
                            if ($malu_figura_table_exists) {
                                $sql .= "m.nosaukums AS malu_figura_name, ";
                            } else {
                                $sql .= "p.figura_id AS malu_figura_name, ";
                            }
                            
                            // Get dekorejums names from the appropriate tables
                            if ($dekorejums1_table_exists) {
                                $sql .= "d1.nosaukums AS dekorejums1_name, ";
                            } else {
                                $sql .= "p.dekorejums1_id AS dekorejums1_name, ";
                            }
                            
                            if ($dekorejums2_table_exists) {
                                $sql .= "d2.nosaukums AS dekorejums2_name ";
                            } else {
                                $sql .= "p.dekorejums2_id AS dekorejums2_name ";
                            }
                            
                            $sql .= "FROM produkcija_sprarkly p ";
                            
                            // Add optional LEFT JOINs based on table existence
                            if ($formas_table_exists) {
                                $sql .= "LEFT JOIN sparkly_formas f ON p.forma = f.id_forma ";
                            }
                            
                            if ($audumi_table_exists) {
                                $sql .= "LEFT JOIN sparkly_audums a ON p.audums_id = a.id_audums ";
                            }
                            
                            if ($malu_figura_table_exists) {
                                $sql .= "LEFT JOIN sparkly_malu_figura m ON p.figura_id = m.id_malu_figura ";
                            }
                            
                            // Use the correct join for dekorejums1 if table exists
                            if ($dekorejums1_table_exists) {
                                $sql .= "LEFT JOIN sparkly_dekorejums1 d1 ON p.dekorejums1_id = d1.id_dekorejums1 ";
                            }
                            
                            // Use the correct join for dekorejums2 if table exists
                            if ($dekorejums2_table_exists) {
                                $sql .= "LEFT JOIN sparkly_dekorejums2 d2 ON p.dekorejums2_id = d2.id_dekorejums2 ";
                            }
                            
                            $sql .= "ORDER BY p.id_bumba DESC";
                            
                            $result = $savienojums->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['id_bumba']}</td>";
                                    // Display first image as preview
                                    echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['attels1']) . "' alt='{$row['nosaukums']}' width='50'></td>";
                                    echo "<td>{$row['nosaukums']}</td>";
                                    echo "<td>{$row['forma_name']}</td>";
                                    echo "<td>{$row['audums_name']}</td>";
                                    echo "<td>{$row['malu_figura_name']}</td>";
                                    echo "<td>{$row['dekorejums1_name']}</td>";
                                    echo "<td>{$row['dekorejums2_name']}</td>";
                                    echo "<td>€{$row['cena']}</td>";
                                    
                                    echo "<td class='action-buttons'>";
                                    echo "<a href='produkts.php?edit={$row['id_bumba']}' class='btn edit-btn'><i class='fas fa-edit'></i> Rediģēt</a>";
                                    echo "<a href='produkts.php?delete={$row['id_bumba']}' class='btn delete-btn' onclick='return confirm(\"Vai tiešām vēlaties dzēst šo produktu?\")'><i class='fas fa-trash-alt'></i> Dzēst</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10' class='no-records'>Nav atrasts neviens produkts</td></tr>";
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
        <!-- Product Form -->
        <div class="custom-form-container">
            <h2><?php echo $editData ? 'Rediģēt produktu' : 'Pievienot jaunu produktu'; ?></h2>
            <form class="custom-form" method="POST" enctype="multipart/form-data">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id_bumba']; ?>">
                <?php endif; ?>
                
                <div class="dropdown">
                    <?php 
                    // Check if sparkly_formas table exists
                    $check_formas_table_sql = "SHOW TABLES LIKE 'sparkly_formas'";
                    $formas_table_exists = $savienojums->query($check_formas_table_sql)->num_rows > 0;
                    
                    if ($formas_table_exists):
                    ?>
                    <div id="drop">
                        <label for="forma">Forma:</label>
                        <select id="forma" name="forma" required>
                            <option value="">Izvēlieties formu</option>
                            <?php
                                // Fetch all forms from sparkly_formas table
                                $forma_sql = "SELECT * FROM sparkly_formas ORDER BY forma";
                                $forma_result = $savienojums->query($forma_sql);
                                
                                if ($forma_result && $forma_result->num_rows > 0) {
                                    while ($forma_row = $forma_result->fetch_assoc()) {
                                        $selected = ($editData && $editData['forma'] == $forma_row['id_forma']) ? 'selected' : '';
                                        echo "<option value='{$forma_row['id_forma']}' {$selected}>{$forma_row['forma']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div id="drop">
                        <label for="forma">Forma:</label>
                        <input type="number" id="forma" name="forma" value="<?php echo $editData ? $editData['forma'] : ''; ?>" required>
                    </div>
                    <?php endif; ?>
                    
                    <div id="drop">
                        <label for="nosaukums">Nosaukums:</label>
                        <input type="text" id="nosaukums" name="nosaukums" value="<?php echo $editData ? $editData['nosaukums'] : ''; ?>" required>
                    </div>
                    
                    <?php 
                    // Check if sparkly_audums table exists
                    $check_audumi_table_sql = "SHOW TABLES LIKE 'sparkly_audums'";
                    $audumi_table_exists = $savienojums->query($check_audumi_table_sql)->num_rows > 0;
                    
                    if ($audumi_table_exists):
                    ?>
                    <div id="drop">
                        <label for="audums_id">Audums:</label>
                        <select id="audums_id" name="audums_id" required>
                            <option value="">Izvēlieties audumu</option>
                            <?php
                                // Fetch all fabrics from sparkly_audums table
                                $audums_sql = "SELECT * FROM sparkly_audums ORDER BY nosaukums";
                                $audums_result = $savienojums->query($audums_sql);
                                
                                if ($audums_result && $audums_result->num_rows > 0) {
                                    while ($audums_row = $audums_result->fetch_assoc()) {
                                        $selected = ($editData && isset($editData['audums_id']) && $editData['audums_id'] == $audums_row['id_audums']) ? 'selected' : '';
                                        echo "<option value='{$audums_row['id_audums']}' {$selected}>{$audums_row['nosaukums']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div id="drop">
                        <label for="audums_id">Audums ID:</label>
                        <input type="number" id="audums_id" name="audums_id" value="<?php echo $editData ? $editData['audums_id'] : ''; ?>" required>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Check if sparkly_malu_figura table exists
                    $check_malu_figura_table_sql = "SHOW TABLES LIKE 'sparkly_malu_figura'";
                    $malu_figura_table_exists = $savienojums->query($check_malu_figura_table_sql)->num_rows > 0;
                    
                    if ($malu_figura_table_exists):
                    ?>
                    <div id="drop">
                        <label for="figura_id">Malu figūra:</label>
                        <select id="figura_id" name="figura_id" required>
                            <option value="">Izvēlieties malu figūru</option>
                            <?php
                                // Fetch all edge figures from sparkly_malu_figura table
                                $malu_figura_sql = "SELECT * FROM sparkly_malu_figura ORDER BY nosaukums";
                                $malu_figura_result = $savienojums->query($malu_figura_sql);
                                
                                if ($malu_figura_result && $malu_figura_result->num_rows > 0) {
                                    while ($malu_figura_row = $malu_figura_result->fetch_assoc()) {
                                        $selected = ($editData && isset($editData['figura_id']) && $editData['figura_id'] == $malu_figura_row['id_malu_figura']) ? 'selected' : '';
                                        echo "<option value='{$malu_figura_row['id_malu_figura']}' {$selected}>{$malu_figura_row['nosaukums']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div id="drop">
                        <label for="figura_id">Malu figūra ID:</label>
                        <input type="number" id="figura_id" name="figura_id" value="<?php echo $editData ? $editData['figura_id'] : ''; ?>" required>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Check for dekorejums1 table
                    if ($dekorejums1_table_exists):
                    ?>
                    <div id="drop">
                        <label for="dekorejums1_id">Dekorējums 1:</label>
                        <select id="dekorejums1_id" name="dekorejums1_id" required>
                            <option value="">Izvēlieties dekorējumu</option>
                            <?php
                                // Fetch all decorations from sparkly_dekorejums table
                                $dekorejums_sql = "SELECT * FROM sparkly_dekorejums1 ORDER BY nosaukums";
                                $dekorejums_result = $savienojums->query($dekorejums_sql);
                                
                                if ($dekorejums_result && $dekorejums_result->num_rows > 0) {
                                    while ($dekorejums_row = $dekorejums_result->fetch_assoc()) {
                                        $selected = ($editData && isset($editData['dekorejums1_id']) && $editData['dekorejums1_id'] == $dekorejums_row['id_dekorejums1']) ? 'selected' : '';
                                        echo "<option value='{$dekorejums_row['id_dekorejums']}' {$selected}>{$dekorejums_row['nosaukums']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div id="drop">
                        <label for="dekorejums1_id">Dekorējums 1 ID:</label>
                        <input type="number" id="dekorejums1_id" name="dekorejums1_id" value="<?php echo $editData ? $editData['dekorejums1_id'] : ''; ?>" required>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Check for dekorejums2 table
                    if ($dekorejums2_table_exists):
                    ?>
                    <div id="drop">
                        <label for="dekorejums2_id">Dekorējums 2:</label>
                        <select id="dekorejums2_id" name="dekorejums2_id" required>
                            <option value="">Izvēlieties dekorējumu</option>
                            <?php
                                // Fetch all decorations from sparkly_dekorejums2 table
                                $dekorejums2_sql = "SELECT * FROM sparkly_dekorejums2 ORDER BY nosaukums";
                                $dekorejums2_result = $savienojums->query($dekorejums2_sql);
                                
                                if ($dekorejums2_result && $dekorejums2_result->num_rows > 0) {
                                    while ($dekorejums2_row = $dekorejums2_result->fetch_assoc()) {
                                        $selected = ($editData && isset($editData['dekorejums2_id']) && $editData['dekorejums2_id'] == $dekorejums2_row['id_dekorejums2']) ? 'selected' : '';
                                        echo "<option value='{$dekorejums2_row['id_dekorejums2']}' {$selected}>{$dekorejums2_row['nosaukums']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div id="drop">
                        <label for="dekorejums2_id">Dekorējums 2 ID:</label>
                        <input type="number" id="dekorejums2_id" name="dekorejums2_id" value="<?php echo $editData ? $editData['dekorejums2_id'] : ''; ?>" required>
                    </div>
                    <?php endif; ?>
                    
                    <div id="drop">
                        <label for="cena">Cena:</label>
                        <input type="number" id="cena" name="cena" step="0.01" value="<?php echo $editData ? $editData['cena'] : ''; ?>" required>
                    </div>
                    
                    <!-- Image uploads -->
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div id="drop">
                        <label for="attels<?php echo $i; ?>">Attēls <?php echo $i; ?>:</label>
                        <?php if ($editData && !empty($editData["attels$i"])): ?>
                            <div class="current-image">
                                <p>Pašreizējais attēls <?php echo $i; ?>:</p>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($editData["attels$i"]); ?>" alt="Attēls <?php echo $i; ?>" width="100">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="attels<?php echo $i; ?>" name="attels<?php echo $i; ?>" <?php echo (!$editData) ? 'required' : ''; ?>>
                        <small>Atbalstītie formāti: JPG, JPEG, PNG</small>
                    </div>
                    <?php endfor; ?>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="submit" class="btn"><?php echo $editData ? 'Atjaunināt produktu' : 'Pievienot produktu'; ?></button>
                    <a href="produkts.php" class="btn clear-btn">Atcelt</a>
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