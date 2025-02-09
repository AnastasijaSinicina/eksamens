<?php
require 'db/con_db.php'; // Database connection
require 'header.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input

    $query = "SELECT * FROM produkcija_sprarkly WHERE id_bumba = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $bumba = $result->fetch_assoc();

        $attels1Src = 'data:image/jpeg;base64,' . base64_encode($bumba['attels1']);
        $attels2Src = 'data:image/jpeg;base64,' . base64_encode($bumba['attels2']);
        $attels3Src = 'data:image/jpeg;base64,' . base64_encode($bumba['attels3']);
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
                <div class="box ">
                    <h1><?= htmlspecialchars($bumba['nosaukums']) ?></h1>
                    <div class="text-box">

                    <p><strong>Forma:</strong> <?= htmlspecialchars($bumba['forma']) ?></p>
                    <p><strong>Audums:</strong> <?= htmlspecialchars($bumba['audums']) ?></p>
                    <p><strong>Malu figūra:</strong> <?= htmlspecialchars($bumba['malu_figura']) ?></p>
                    <p><strong>Dekorējums:</strong> <?= htmlspecialchars($bumba['dekorejums']) ?>, <?= htmlspecialchars($bumba['dekorejums2']) ?></p>
                    <p><strong>Cena:</strong> <?= htmlspecialchars($bumba['cena']) ?>€</p>
                   
                </div>
                <a href="" class="btn">Pievienot grozam</a>
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
