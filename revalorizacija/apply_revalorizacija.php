<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db.class.7.php';

$db = DBstambena();

$id_revalorizacija = $_POST['id_revalorizacija'] ?? null;

if (! $id_revalorizacija) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid revalorizacija ID.']);
    exit;
}

// Fetch the koef and datum_rev from the revalorizacija table
$sqlKoef    = "SELECT koef, datum_rev FROM revalorizacija WHERE id = ?";
$resultKoef = $db->prepareAndExecute($sqlKoef, "i", $id_revalorizacija);
$koefRow    = $db->fetchNextObject($resultKoef);

if (! $koefRow) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid revalorizacija ID or koef not found.']);
    exit;
}

$koef      = $koefRow->koef;
$datum_rev = $koefRow->datum_rev;

// Get all contracts (ugovori)
$sqlUgovori    = "SELECT id FROM ugovori";
$resultUgovori = $db->query($sqlUgovori);

$results = [];

if ($resultUgovori && $resultUgovori->num_rows > 0) {
    while ($ugovor = $db->fetchNextObject($resultUgovori)) {
        $id_ugovora = $ugovor->id;

        // Step 1: Sum all rates from the `datum_rev`
        $sqlSumRates = "
        SELECT
          SUM(rev_cena) AS total_cena,
          SUM(CASE WHEN datum > ? THEN 1 ELSE 0 END) AS count
        FROM rate
        WHERE id_ugovora = ?
        ";
        $resultSumRates = $db->prepareAndExecute($sqlSumRates, "si", $datum_rev, $id_ugovora);
        $list           = $db->fetchNextObject($resultSumRates);
        $sumRow         = $list->total_cena ? $list->total_cena : null;
        $br_rata        = $list->count ? $list->count : 0;

        // sum of dugovanja from izvodstavke for the specific contract
        $sqlDug = "SELECT SUM(Dug) AS ukupno_potrazuje
        FROM izvodstavke
        WHERE id_ugovora = ? AND datum <  ? ";
        $resultDug = $db->prepareAndExecute($sqlDug, "is", $id_ugovora, $datum_rev);
        $potrazuje = ($db->fetchNextObject($resultDug))->ukupno_potrazuje;

        $preostaliDug = $sumRow - $potrazuje;

        // Step 2: Apply the koef to the total sum
        $adjusted_total = round($preostaliDug * $koef, 2);

        // Step 3: Split the adjusted total into remaining rates
        $sqlRates = "
            SELECT id, redni_broj, cena, datum,count(*) as countRATE
            FROM rate
            WHERE id_ugovora = ? AND datum > ?
            ORDER BY datum ASC
        ";

        $sqlRatesTest = "
            SELECT id, redni_broj, cena, datum FROM rate WHERE id_ugovora = $id_ugovora AND datum > $datum_rev ORDER BY datum ASC
        ";

        $resultRates = $db->prepareAndExecute($sqlRates, "is", $id_ugovora, $datum_rev);

        $total_split_sum      = 0;
        $remaining_rate_count = 0;
        if ($resultRates && $br_rata > 0) {
            $remaining_rates = [];
            while ($rateRow = $db->fetchNextObject($resultRates)) {
                $remaining_rates[] = $rateRow;
            }

            $remaining_rate_count = count($remaining_rates);
            $rate_value           = $br_rata > 0 ? round($adjusted_total / $br_rata, 2) : 0;

            foreach ($remaining_rates as $index => $rate) {
                $rev_cena = $rate_value;
                $total_split_sum += $rev_cena;

                // Update the rate with the new rev_cena
                $sqlUpdateRate = "UPDATE rate SET rev_cena = ? WHERE id = ?";
                $updateResult  = $db->prepareAndExecute($sqlUpdateRate, "di", $rev_cena, $rate->id);
            }
        }

        // Step 4: Insert into rev_ugovor table for the specific contract
        $sqlInsert    = "INSERT INTO rev_ugovor (id_ugovora, id_revalorizacije) VALUES (?, ?)";
        $insertResult = $db->executeNonSelect($sqlInsert, "ii", $id_ugovora, $id_revalorizacija);

        $results[] = [
            'id_ugovora'      => $id_ugovora,
            'success'         => $insertResult['success'],
            'adjusted_total'  => $adjusted_total,
            'br_rata'         => $br_rata,
            'suma'            => $sumRow,
            'koef'            => $koef,
            'datum_rev'       => $datum_rev,
            'preostaliDug'    => $preostaliDug,
            'potrazuje'       => $potrazuje,
            'total_split_sum' => $total_split_sum,
            'remaining_rate'  => $remaining_rate_count,
            'remaining_rates' => $remaining_rates ?? [],
        ];
    }
    echo json_encode(['success' => true, 'results' => $results]);
} else {
    echo json_encode(['success' => false, 'error' => 'No contracts found.']);
}

$db->close();
