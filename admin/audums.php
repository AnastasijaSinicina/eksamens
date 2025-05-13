<?php
    // Include admin header
    require 'header.php';
    
    // Database connection
    require 'db/con_db.php';

    // Handle fabric deletion
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $sql = "DELETE FROM sparkly_audums WHERE id_audums = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('success', 'Veiksmīgi!', 'Audums ir izdzēsts.');
                    });
                  </script>";
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('error', 'Kļūda!', 'Neizdevās dzēst audumu.');
                    });
                  </script>";
        }
        $stmt->close();
    }

    // Handle fabric addition/update
    if (isset($_POST['submit'])) {
        $nosaukums = $_POST['nosaukums'];
        
        // Check if we're updating or adding
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update existing fabric
            $id = $_POST['id'];
            $sql = "UPDATE sparkly_audums SET nosaukums = ? WHERE id_audums = ?";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("si", $nosaukums, $id);
            
            if ($stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('success', 'Veiksmīgi!', 'Audums ir atjaunināts.');
                        });
                      </script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Neizdevās atjaunināt audumu: " . $stmt->error . "');
                        });
                      </script>";
            }
            $stmt->close();
            
        } else {
            // Add new fabric
            $sql = "INSERT INTO sparkly_audums (nosaukums) VALUES (?)";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("s", $nosaukums);
            
            if ($stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('success', 'Veiksmīgi!', 'Audums ir pievienots.');
                        });
                      </script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Neizdevās pievienot audumu: " . $stmt->error . "');
                        });
                      </script>";
            }
            $stmt->close();
        }
        
        // Redirect to clear the form
        echo "<script>window.location.href = 'audums.php';</script>";
    }

    // Get fabric data for editing
    $editData = null;
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $sql = "SELECT * FROM sparkly_audums WHERE id_audums = ?";
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
        <h1>Audumu pārvaldība</h1>
        
        <!-- Fabric Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošie audumi</h2>
                <a href="audums.php?action=add" class="btn add-btn"><i class="fas fa-plus"></i></a>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nosaukums</th>
                            <th>Datums</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody>     
                        <?php
                            // Fetch all fabrics
                            $sql = "SELECT * FROM sparkly_audums ORDER BY id_audums";
                            $result = $savienojums->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['id_audums']}</td>";
                                    echo "<td>{$row['nosaukums']}</td>";
                                    // Get date from datums column, or show current date if not available
                                    $datums = isset($row['datums']) ? $row['datums'] : date('Y-m-d H:i:s');
                                    echo "<td>{$datums}</td>";
                                    
                                    echo "<td class='action-buttons'>";
                                    echo "<a href='audums.php?edit={$row['id_audums']}' class='btn edit-btn'><i class='fas fa-edit'></i> Rediģēt</a>";
                                    echo "<a href='audums.php?delete={$row['id_audums']}' class='btn delete-btn' onclick='return confirm(\"Vai tiešām vēlaties dzēst šo audumu?\")'><i class='fas fa-trash-alt'></i> Dzēst</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='no-records'>Nav atrasts neviens audums</td></tr>";
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
        <!-- Fabric Form -->
        <div class="custom-form-container">
            <h2><?php echo $editData ? 'Rediģēt audumu' : 'Pievienot jaunu audumu'; ?></h2>
            <form class="custom-form" method="POST">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id_audums']; ?>">
                <?php endif; ?>
                
                <div class="dropdown">
                    <div id="drop">
                        <label for="nosaukums">Nosaukums:</label>
                        <input type="text" id="nosaukums" name="nosaukums" value="<?php echo $editData ? $editData['nosaukums'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="submit" class="btn"><?php echo $editData ? 'Atjaunināt audumu' : 'Pievienot audumu'; ?></button>
                    <a href="audums.php" class="btn clear-btn">Atcelt</a>
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

