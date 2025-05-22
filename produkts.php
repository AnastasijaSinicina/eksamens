<?php
require 'admin/db/con_db.php'; // Database connection
require 'header.php';

if (isset($_GET['id'])) {
    $_SESSION['redirect_after_login'] = "produkts.php?id=" . intval($_GET['id']);
}

if (isset($_SESSION['pazinojums'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('" . addslashes($_SESSION['pazinojums']) . "', 'success');
        });
    </script>";
    unset($_SESSION['pazinojums']);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input

    // Check if tables and columns exist
    $check_formas_table_sql = "SHOW TABLES LIKE 'sparkly_formas'";
    $formas_table_exists = $savienojums->query($check_formas_table_sql)->num_rows > 0;
    
    $check_audums_table_sql = "SHOW TABLES LIKE 'sparkly_audums'";
    $audums_table_exists = $savienojums->query($check_audums_table_sql)->num_rows > 0;
    
    $check_malu_figura_table_sql = "SHOW TABLES LIKE 'sparkly_malu_figura'";
    $malu_figura_table_exists = $savienojums->query($check_malu_figura_table_sql)->num_rows > 0;
    
    $check_dekorejums1_table_sql = "SHOW TABLES LIKE 'sparkly_dekorejums1'";
    $dekorejums1_table_exists = $savienojums->query($check_dekorejums1_table_sql)->num_rows > 0;
    
    $check_dekorejums2_table_sql = "SHOW TABLES LIKE 'sparkly_dekorejums2'";
    $dekorejums2_table_exists = $savienojums->query($check_dekorejums2_table_sql)->num_rows > 0;
    
    $check_audums_id_sql = "SHOW COLUMNS FROM produkcija_sprarkly LIKE 'audums_id'";
    $audums_id_exists = $savienojums->query($check_audums_id_sql)->num_rows > 0;
    
    $check_figura_id_sql = "SHOW COLUMNS FROM produkcija_sprarkly LIKE 'figura_id'";
    $figura_id_exists = $savienojums->query($check_figura_id_sql)->num_rows > 0;
    
    $check_dekorejums1_id_sql = "SHOW COLUMNS FROM produkcija_sprarkly LIKE 'dekorejums1_id'";
    $dekorejums1_id_exists = $savienojums->query($check_dekorejums1_id_sql)->num_rows > 0;
    
    $check_dekorejums2_id_sql = "SHOW COLUMNS FROM produkcija_sprarkly LIKE 'dekorejums2_id'";
    $dekorejums2_id_exists = $savienojums->query($check_dekorejums2_id_sql)->num_rows > 0;

    // Construct an appropriate query based on the schema
    $query = "SELECT p.* ";
    
    if ($formas_table_exists) {
        $query .= ", f.forma AS forma_name ";
    }
    
    if ($audums_table_exists && $audums_id_exists) {
        $query .= ", a.nosaukums AS audums_name ";
    }
    
    if ($malu_figura_table_exists && $figura_id_exists) {
        $query .= ", m.nosaukums AS malu_figura_name ";
    }
    
    if ($dekorejums1_table_exists && $dekorejums1_id_exists) {
        $query .= ", d1.nosaukums AS dekorejums1_name ";
    }
    
    if ($dekorejums2_table_exists && $dekorejums2_id_exists) {
        $query .= ", d2.nosaukums AS dekorejums2_name ";
    }
    
    $query .= " FROM produkcija_sprarkly p ";
    
    if ($formas_table_exists) {
        $query .= " LEFT JOIN sparkly_formas f ON p.forma = f.id_forma ";
    }
    
    if ($audums_table_exists && $audums_id_exists) {
        $query .= " LEFT JOIN sparkly_audums a ON p.audums_id = a.id_audums ";
    }
    
    if ($malu_figura_table_exists && $figura_id_exists) {
        $query .= " LEFT JOIN sparkly_malu_figura m ON p.figura_id = m.id_malu_figura ";
    }
    
    if ($dekorejums1_table_exists && $dekorejums1_id_exists) {
        $query .= " LEFT JOIN sparkly_dekorejums1 d1 ON p.dekorejums1_id = d1.id_dekorejums1 ";
    }
    
    if ($dekorejums2_table_exists && $dekorejums2_id_exists) {
        $query .= " LEFT JOIN sparkly_dekorejums2 d2 ON p.dekorejums2_id = d2.id_dekorejums2 ";
    }
    
    $query .= " WHERE p.id_bumba = ?";

    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $bumba = $result->fetch_assoc();

        $attels1Src = 'data:image/jpeg;base64,' . base64_encode($bumba['attels1']);
        $attels2Src = 'data:image/jpeg;base64,' . base64_encode($bumba['attels2']);
        $attels3Src = 'data:image/jpeg;base64,' . base64_encode($bumba['attels3']);
        
        // Determine correct display values based on schema
        $forma_display = isset($bumba['forma_name']) ? $bumba['forma_name'] : $bumba['forma'];
        
        $audums_display = isset($bumba['audums_name']) ? $bumba['audums_name'] : 
                         (isset($bumba['audums']) ? $bumba['audums'] : 
                         (isset($bumba['audums_id']) ? $bumba['audums_id'] : 'Nav norādīts'));
        
        $malu_figura_display = isset($bumba['malu_figura_name']) ? $bumba['malu_figura_name'] : 
                              (isset($bumba['malu_figura']) ? $bumba['malu_figura'] : 
                              (isset($bumba['figura_id']) ? $bumba['figura_id'] : 'Nav norādīts'));
        
        $dekorejums1_display = isset($bumba['dekorejums1_name']) ? $bumba['dekorejums1_name'] : 
                              (isset($bumba['dekorejums']) ? $bumba['dekorejums'] : 
                              (isset($bumba['dekorejums1_id']) ? $bumba['dekorejums1_id'] : 'Nav norādīts'));
        
        $dekorejums2_display = isset($bumba['dekorejums2_name']) ? $bumba['dekorejums2_name'] : 
                              (isset($bumba['dekorejums2']) ? $bumba['dekorejums2'] : 
                              (isset($bumba['dekorejums2_id']) ? $bumba['dekorejums2_id'] : 'Nav norādīts'));
?>
        <section id="produkts">
            <div class="box-container">
                <!-- Box for Image Slider -->
                <div class="box images-box">
                    <div id="main-slider" class="splide">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <li class="splide__slide"><img src="<?= $attels1Src ?>" alt="Image 1"></li>
                                <li class="splide__slide"><img src="<?= $attels2Src ?>" alt="Image 2"></li>
                                <li class="splide__slide"><img src="<?= $attels3Src ?>" alt="Image 3"></li>
                            </ul>
                        </div>
                    </div>

                    <div id="thumbnail-slider" class="splide">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <li class="splide__slide"><img src="<?= $attels1Src ?>" alt="Thumbnail 1"></li>
                                <li class="splide__slide"><img src="<?= $attels2Src ?>" alt="Thumbnail 2"></li>
                                <li class="splide__slide"><img src="<?= $attels3Src ?>" alt="Thumbnail 3"></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Box for Text Details -->
                <div class="box">
                    <h1><?= htmlspecialchars($bumba['nosaukums']) ?></h1>
                    <div class="text-box">
                        <p><strong>Forma:</strong> <?= htmlspecialchars($forma_display) ?></p>
                        <p><strong>Audums:</strong> <?= htmlspecialchars($audums_display) ?></p>
                        <p><strong>Malu figūra:</strong> <?= htmlspecialchars($malu_figura_display) ?></p>
                        <p><strong>Dekorējums (1):</strong> <?= htmlspecialchars($dekorejums1_display) ?></p>
                        <p><strong>Dekorējums (2):</strong> <?= htmlspecialchars($dekorejums2_display) ?></p>
                        <p><strong>Cena:</strong> <?= htmlspecialchars($bumba['cena']) ?>€</p>
                    </div>
                    <form action="admin/db/add_to_cart.php" method="post">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="hidden" name="nosaukums" value="<?= htmlspecialchars($bumba['nosaukums']) ?>">
                        <input type="hidden" name="cena" value="<?= htmlspecialchars($bumba['cena']) ?>">
                        <input type="hidden" name="attels" value="<?= base64_encode($bumba['attels1']) ?>">
                        <button type="submit" name="add_to_cart" class="btn">Pievienot grozam</button>
                    </form>
                </div>
            </div>
        </section>
<?php
    } else {
        echo "<p>Produkts nav atrasts.</p>";
    }
} else {
    echo "<p>Produkts nav norādīts.</p>";
}

require 'footer.php';
?>