<?php
require 'admin/db/produkts.php'; // Include the SQL query file
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

if ($product_found && isset($bumba)) {
?>
    <section id="produkts">
        <div class="box-container">
            <!-- Box for Image Slider -->
            <div class="box images-box">
                <div id="main-slider" class="splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php if (isset($attels1Src)): ?>
                                <li class="splide__slide"><img src="<?= $attels1Src ?>" alt="Image 1"></li>
                            <?php endif; ?>
                            <?php if (isset($attels2Src)): ?>
                                <li class="splide__slide"><img src="<?= $attels2Src ?>" alt="Image 2"></li>
                            <?php endif; ?>
                            <?php if (isset($attels3Src)): ?>
                                <li class="splide__slide"><img src="<?= $attels3Src ?>" alt="Image 3"></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div id="thumbnail-slider" class="splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php if (isset($attels1Src)): ?>
                                <li class="splide__slide"><img src="<?= $attels1Src ?>" alt="Thumbnail 1"></li>
                            <?php endif; ?>
                            <?php if (isset($attels2Src)): ?>
                                <li class="splide__slide"><img src="<?= $attels2Src ?>" alt="Thumbnail 2"></li>
                            <?php endif; ?>
                            <?php if (isset($attels3Src)): ?>
                                <li class="splide__slide"><img src="<?= $attels3Src ?>" alt="Thumbnail 3"></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Box for Text Details -->
            <div class="box">
                <h1><?= isset($bumba['nosaukums']) ? htmlspecialchars($bumba['nosaukums']) : 'Nav nosaukuma' ?></h1>
                <div class="text-box">
                    <p><strong>Forma:</strong> <?= isset($forma_display) ? htmlspecialchars($forma_display) : 'Nav norādīts' ?></p>
                    <p><strong>Audums:</strong> <?= isset($audums_display) ? htmlspecialchars($audums_display) : 'Nav norādīts' ?></p>
                    <p><strong>Malu figūra:</strong> <?= isset($malu_figura_display) ? htmlspecialchars($malu_figura_display) : 'Nav norādīts' ?></p>
                    <p><strong>Dekorējums:</strong> <?= isset($dekorejums1_display) ? htmlspecialchars($dekorejums1_display) : 'Nav norādīts' ?></p>
                    <p><strong>Cena:</strong> <?= isset($bumba['cena']) ? htmlspecialchars($bumba['cena']) : '0' ?>€</p>
                </div>
                <form action="admin/db/add_to_cart.php" method="post">
                    <?php if (isset($_GET['id'])): ?>
                        <input type="hidden" name="id" value="<?= intval($_GET['id']) ?>">
                    <?php endif; ?>
                    <?php if (isset($bumba['nosaukums'])): ?>
                        <input type="hidden" name="nosaukums" value="<?= htmlspecialchars($bumba['nosaukums']) ?>">
                    <?php endif; ?>
                    <?php if (isset($bumba['cena'])): ?>
                        <input type="hidden" name="cena" value="<?= htmlspecialchars($bumba['cena']) ?>">
                    <?php endif; ?>
                    <?php if (isset($bumba['attels1'])): ?>
                        <input type="hidden" name="attels" value="<?= base64_encode($bumba['attels1']) ?>">
                    <?php endif; ?>
                    <button type="submit" name="add_to_cart" class="btn">Pievienot grozam</button>
                </form>
            </div>
        </div>
    </section>
<?php
} elseif (isset($_GET['id'])) {
    echo "<p>Produkts nav atrasts.</p>";
} else {
    echo "<p>Produkts nav norādīts.</p>";
}

require 'footer.php';
?>