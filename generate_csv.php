<?php
$filename = "products_1000000.csv";
$fp = fopen($filename, 'w');

// Write CSV header
fputcsv($fp, ['sku', 'name', 'description', 'price']);

// Generate 1,000,000 rows
for ($i = 1; $i <= 20000; $i++) {
    $sku = "SKU" . $i;
    $name = "Product " . $i;
    $desc = "Description for product " . $i;
    $price = rand(10, 1000); // Random price
    fputcsv($fp, [$sku, $name, $desc, $price]);

    // Optional progress message every 100k rows
    if ($i % 100000 === 0) {
        echo "$i rows written\n";
    }
}

fclose($fp);
echo "CSV generated: $filename\n";
