<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style_admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/script_admin.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/css/splide.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/js/splide.min.js"></script>
    <link rel="shortcut icon" href="../images/bumba.png" type="image/x-icon">
    <title>Sparkly Dream - Admin</title>
</head>
<body>
    <aside class="sidebar">
        <header class="sidebar-header">
            <a href="#" class="header-logo">
                <img src="../images/bumba.png" alt="">
            </a>
            <button class="toggler sidebar-toggler">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            <button class="toggler menu-toggler">
                <i class="fa-solid fa-bars"></i>
            </button>
        </header>
        <nav class="sidebar-nav">
            <ul class="nav-list primary-nav">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-house"></i>
                        </span>
                        <span class="nav-label">Sākumlapa</span>
                    </a>
                    <span class="nav-tooltip">Sākumlapa</span>
                </li>
                <li class="nav-item">
                    <a href="pasutijumi.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-receipt"></i>
                        </span>
                        <span class="nav-label">Pasūtījumi</span>
                    </a>
                    <span class="nav-tooltip">Pasūtījumi</span>
                </li>
                <li class="nav-item">
                    <a href="spec_pas.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-palette"></i>
                        </span>
                        <span class="nav-label">Pielāgoti</span>
                    </a>
                    <span class="nav-tooltip">Pielāgoti pasūtījumi</span>
                </li>
                <li class="nav-item">
                    <a href="klienti.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-users"></i>
                        </span>
                        <span class="nav-label">Klienti</span>
                    </a>
                    <span class="nav-tooltip">Klienti</span>
                </li>
                <li class="nav-item">
                    <a href="produkcija.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-shop"></i>
                        </span>
                        <span class="nav-label">Produkcija</span>
                    </a>
                    <span class="nav-tooltip">Produkcija</span>
                </li>
                <li class="nav-item">
                    <a href="audums.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-swatchbook"></i>
                        </span>
                        <span class="nav-label">Audumi</span>
                    </a>
                    <span class="nav-tooltip">Audumi</span>
                </li>
                <li class="nav-item">
                    <a href="formas.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-circle"></i>
                        </span>
                        <span class="nav-label">Formas</span>
                    </a>
                    <span class="nav-tooltip">Formas</span>
                </li>
                <li class="nav-item">
                    <a href="figuras.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-star"></i>
                        </span>
                        <span class="nav-label">Mālu figūras</span>
                    </a>
                    <span class="nav-tooltip">Mālu figūras</span>
                </li>
                <li class="nav-item">
                    <a href="dekorejumi.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-gem"></i>
                        </span>
                        <span class="nav-label">Dekorējumi</span>
                    </a>
                    <span class="nav-tooltip">Dekorējumi</span>
                </li>
                <li class="nav-item">
                    <a href="atsauksmes.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-comments"></i>
                        </span>
                        <span class="nav-label">Atsauksmes</span>
                    </a>
                    <span class="nav-tooltip">Atsauksmes</span>
                </li>
                <?php if (isset($_SESSION['loma']) && $_SESSION['loma'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="lietotaji.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-user-tie"></i>
                        </span>
                        <span class="nav-label">Lietotāji</span>
                    </a>
                    <span class="nav-tooltip">Lietotāji</span>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="nav-list secondary-nav">
                <li class="nav-item">
                    <a href="profils.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-gear"></i>
                        </span>
                        <span class="nav-label">Mans profils</span>
                    </a>
                    <span class="nav-tooltip">Mans profils</span>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </span>
                        <span class="nav-label">Iziet</span>
                    </a>
                    <span class="nav-tooltip">Iziet</span>
                </li>
            </ul>
        </nav>
    </aside>
</body>
</html>