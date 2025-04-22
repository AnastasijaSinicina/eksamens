<?php
    // Include admin header
    require 'header.php';
    
    // Database connection
    require 'db/con_db.php';

    // Handle user deletion
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $sql = "DELETE FROM lietotaji_sparkly WHERE id_lietotajs = ? AND (loma = 'admin' OR loma = 'moder')";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('success', 'Veiksmīgi!', 'Lietotājs ir izdzēsts.');
                    });
                  </script>";
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('error', 'Kļūda!', 'Neizdevās dzēst lietotāju.');
                    });
                  </script>";
        }
        $stmt->close();
    }

    // Handle user addition/update
    if (isset($_POST['submit'])) {
        $vards = $_POST['vards'];
        $uzvards = $_POST['uzvards'];
        $lietotajvards = $_POST['lietotajvards'];
        $epasts = $_POST['epasts'];
        $parole = $_POST['parole'];
        $loma = $_POST['loma'];
        
        // Ensure loma is only admin or moder
        if ($loma != 'admin' && $loma != 'moder') {
            $loma = 'moder'; // Default to moder if invalid value is passed
        }
        
        // Check if we're updating or adding
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update existing user
            $id = $_POST['id'];
            
            // Check if password was provided for update
            if (!empty($parole)) {
                // Hash the password
                $hashed_password = password_hash($parole, PASSWORD_DEFAULT);
                $sql = "UPDATE lietotaji_sparkly SET vards = ?, uzvards = ?, lietotajvards = ?, epasts = ?, parole = ?, loma = ? WHERE id_lietotajs = ? AND (loma = 'admin' OR loma = 'moder')";
                $stmt = $savienojums->prepare($sql);
                $stmt->bind_param("ssssssi", $vards, $uzvards, $lietotajvards, $epasts, $hashed_password, $loma, $id);
            } else {
                // Update without changing password
                $sql = "UPDATE lietotaji_sparkly SET vards = ?, uzvards = ?, lietotajvards = ?, epasts = ?, loma = ? WHERE id_lietotajs = ? AND (loma = 'admin' OR loma = 'moder')";
                $stmt = $savienojums->prepare($sql);
                $stmt->bind_param("sssssi", $vards, $uzvards, $lietotajvards, $epasts, $loma, $id);
            }
            
            if ($stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('success', 'Veiksmīgi!', 'Lietotājs ir atjaunināts.');
                        });
                      </script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Neizdevās atjaunināt lietotāju: " . $stmt->error . "');
                        });
                      </script>";
            }
            $stmt->close();
            
        } else {
            // Add new user
            // Hash the password
            $hashed_password = password_hash($parole, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO lietotaji_sparkly (vards, uzvards, lietotajvards, epasts, parole, loma) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("ssssss", $vards, $uzvards, $lietotajvards, $epasts, $hashed_password, $loma);
            
            if ($stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('success', 'Veiksmīgi!', 'Lietotājs ir pievienots.');
                        });
                      </script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Neizdevās pievienot lietotāju: " . $stmt->error . "');
                        });
                      </script>";
            }
            $stmt->close();
        }
        
        // Redirect to clear the form
        echo "<script>window.location.href = 'lietotaji.php';</script>";
    }

    // Get user data for editing
    $editData = null;
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $sql = "SELECT * FROM lietotaji_sparkly WHERE id_lietotajs = ? AND (loma = 'admin' OR loma = 'moder')";
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
        <h1>Administrātoru un moderātoru pārvaldība</h1>
        
        <!-- Users Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošie lietotāji</h2>
                <a href="lietotaji.php?action=add" class="btn add-btn"><i class="fas fa-plus"></i></a>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vārds</th>
                            <th>Uzvārds</th>
                            <th>Lietotājvārds</th>
                            <th>E-pasts</th>
                            <th>Loma</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody>     
                        <?php
                            // Fetch all admin and moder users
                            $sql = "SELECT * FROM lietotaji_sparkly WHERE loma = 'admin' OR loma = 'moder' ORDER BY loma, id_lietotajs";
                            $result = $savienojums->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['id_lietotajs']}</td>";
                                    echo "<td>{$row['vards']}</td>";
                                    echo "<td>{$row['uzvards']}</td>";
                                    echo "<td>{$row['lietotajvards']}</td>";
                                    echo "<td>{$row['epasts']}</td>";
                                    echo "<td>" . ucfirst($row['loma']) . "</td>";
                                    
                                    echo "<td class='action-buttons'>";
                                    echo "<a href='lietotaji.php?edit={$row['id_lietotajs']}' class='btn edit-btn'><i class='fas fa-edit'></i> Rediģēt</a>";
                                    echo "<a href='lietotaji.php?delete={$row['id_lietotajs']}' class='btn delete-btn' onclick='return confirm(\"Vai tiešām vēlaties dzēst šo lietotāju?\")'><i class='fas fa-trash-alt'></i> Dzēst</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='no-records'>Nav atrasts neviens administrators vai moderators</td></tr>";
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
            <h2><?php echo $editData ? 'Rediģēt lietotāju' : 'Pievienot jaunu lietotāju'; ?></h2>
            <form class="custom-form" method="POST">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id_lietotajs']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="vards">Vārds:</label>
                    <input type="text" id="vards" name="vards" value="<?php echo $editData ? $editData['vards'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="uzvards">Uzvārds:</label>
                    <input type="text" id="uzvards" name="uzvards" value="<?php echo $editData ? $editData['uzvards'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="lietotajvards">Lietotājvārds:</label>
                    <input type="text" id="lietotajvards" name="lietotajvards" value="<?php echo $editData ? $editData['lietotajvards'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="epasts">E-pasts:</label>
                    <input type="email" id="epasts" name="epasts" value="<?php echo $editData ? $editData['epasts'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="parole">Parole:<?php echo $editData ? ' (atstājiet tukšu, lai nemainītu)' : ''; ?></label>
<<<<<<< HEAD
                    <input type="password" id="parole" name="parole" <?php echo $editData ? '' : 'required'; ?> autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
=======
                    <input type="password" id="parole" name="parole" <?php echo $editData ? '' : 'required'; ?>>
>>>>>>> 2693dfa7b12716cdcc4ed99fa269d70868694183
                </div>
                
                <div class="form-group">
                    <label for="loma">Loma:</label>
                    <select id="loma" name="loma" required>
                        <option value="admin" <?php echo ($editData && $editData['loma'] == 'admin') ? 'selected' : ''; ?>>Administrators</option>
                        <option value="moder" <?php echo ($editData && $editData['loma'] == 'moder') ? 'selected' : ''; ?>>Moderators</option>
                    </select>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="submit" class="btn"><?php echo $editData ? 'Atjaunināt lietotāju' : 'Pievienot lietotāju'; ?></button>
                    <a href="lietotaji.php" class="btn clear-btn">Atcelt</a>
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
