<?php
    // Include admin header
    require 'header.php';
    
    // Database connection
    require 'db/con_db.php';

    // Handle form deletion
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $sql = "DELETE FROM sparkly_formas WHERE id_forma = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('success', 'Veiksmīgi!', 'Forma ir izdzēsta.');
                    });
                  </script>";
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('error', 'Kļūda!', 'Neizdevās dzēst formu.');
                    });
                  </script>";
        }
        $stmt->close();
    }

    // Handle form addition/update
    if (isset($_POST['submit'])) {
        $forma = $_POST['forma'];
        
        // Check if we're updating or adding
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update existing form
            $id = $_POST['id'];
            $sql = "UPDATE sparkly_formas SET forma = ? WHERE id_forma = ?";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("si", $forma, $id);
            
            if ($stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('success', 'Veiksmīgi!', 'Forma ir atjaunināta.');
                        });
                      </script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Neizdevās atjaunināt formu: " . $stmt->error . "');
                        });
                      </script>";
            }
            $stmt->close();
            
        } else {
            // Add new form
            $sql = "INSERT INTO sparkly_formas (forma) VALUES (?)";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("s", $forma);
            
            if ($stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('success', 'Veiksmīgi!', 'Forma ir pievienota.');
                        });
                      </script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Kļūda!', 'Neizdevās pievienot formu: " . $stmt->error . "');
                        });
                      </script>";
            }
            $stmt->close();
        }
        
        // Redirect to clear the form
        echo "<script>window.location.href = 'formas.php';</script>";
    }

    // Get form data for editing
    $editData = null;
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $sql = "SELECT * FROM sparkly_formas WHERE id_forma = ?";
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
        <h1>Formu pārvaldība</h1>
        
        <!-- Forms Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošās formas</h2>
                <a href="formas.php?action=add" class="btn add-btn"><i class="fas fa-plus"></i></a>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Forma</th>
                            <th>Datums</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody>     
                        <?php
                            // Fetch all forms
                            $sql = "SELECT * FROM sparkly_formas ORDER BY id_forma";
                            $result = $savienojums->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['id_forma']}</td>";
                                    echo "<td>{$row['forma']}</td>";
                                    // Get date from datums column, or show current date if not available
                                    $datums = isset($row['datums']) ? $row['datums'] : date('Y-m-d H:i:s');
                                    echo "<td>{$datums}</td>";
                                    
                                    echo "<td class='action-buttons'>";
                                    echo "<a href='formas.php?edit={$row['id_forma']}' class='btn edit-btn'><i class='fas fa-edit'></i> Rediģēt</a>";
                                    echo "<a href='formas.php?delete={$row['id_forma']}' class='btn delete-btn' onclick='return confirm(\"Vai tiešām vēlaties dzēst šo formu?\")'><i class='fas fa-trash-alt'></i> Dzēst</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='no-records'>Nav atrasta neviena forma</td></tr>";
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
            <h2><?php echo $editData ? 'Rediģēt formu' : 'Pievienot jaunu formu'; ?></h2>
            <form class="custom-form" method="POST">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id_forma']; ?>">
                <?php endif; ?>
                
                <div class="dropdown">
                    <div id="drop">
                        <label for="forma">Forma:</label>
                        <input type="text" id="forma" name="forma" value="<?php echo $editData ? $editData['forma'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="submit" class="btn"><?php echo $editData ? 'Atjaunināt formu' : 'Pievienot formu'; ?></button>
                    <a href="formas.php" class="btn clear-btn">Atcelt</a>
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

