<?php
// Update order status
$order_id = $_POST['order_id'];
$new_status = $_POST['new_status'];

$update_query = "UPDATE sparkly_pasutijumi SET statuss = ? WHERE id_pasutijums = ?";
$update_stmt = $savienojums->prepare($update_query);

if ($update_stmt === false) {
    die('Prepare failed: ' . $savienojums->error);
}

$update_stmt->bind_param("si", $new_status, $order_id);

if ($update_stmt->execute()) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotification('success', 'Veiksmīgi!', 'Pasūtījuma statuss ir atjaunināts.');
            });
          </script>";
} else {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotification('error', 'Kļūda!', 'Neizdevās atjaunināt pasūtījuma statusu.');
            });
          </script>";
}
?>